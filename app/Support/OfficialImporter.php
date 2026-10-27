<?php

namespace App\Support;

use App\Models\Official;
use App\Models\Part;
use DateTimeInterface;
use Illuminate\Support\Str;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use OpenSpout\Writer\XLSX\Writer as XlsxWriter;

/** Nhập thẻ tăng ni từ file Excel/CSV, nhận cột theo tên tiêu đề (không phụ thuộc thứ tự cột). */
class OfficialImporter
{
    /** Tiêu đề cột của file mẫu. */
    public const COLUMNS = [
        'phapdanh' => 'Pháp danh',
        'hovaten' => 'Họ và tên',
        'ngaysinh' => 'Ngày sinh',
        'noisinh' => 'Nơi sinh',
        'cccd' => 'Số CCCD',
        'ngaycapcccd' => 'Ngày cấp CCCD',
        'thogioi' => 'Thọ giới',
        'diachi' => 'Nơi tu học',
        'part' => 'Tỉnh thành',
        'hephai' => 'Hệ phái',
        'image' => 'Tên hình',
        'ngaycap' => 'Ngày cấp',
    ];

    /** Tên cột khác được chấp nhận (đã chuẩn hóa) => field. */
    private const ALIASES = [
        'hoten' => 'hovaten', 'noituhoc' => 'diachi', 'thuongtru' => 'diachi',
        'noituhocthuongtru' => 'diachi', 'gioidan' => 'part', 'tinh' => 'part', 'cccdso' => 'cccd', 'tenanh' => 'image', 'hinh' => 'image', 'anh' => 'image',
    ];

    private array $parts = [];

    public function __construct(private ?int $defaultPartId = null)
    {
        foreach (Part::all() as $p) {
            $this->parts['id:'.$p->id] = $p->id;
            foreach ([$p->title, $p->alias] as $name) {
                $this->parts['t:'.self::partKey($name)] ??= $p->id;
            }
        }
    }

    /** @return array{created:int, updated:int, skipped:int, errors:string[]} */
    public function import(string $path, string $ext): array
    {
        $reader = strtolower($ext) === 'csv' ? new CsvReader : new XlsxReader;
        $reader->open($path);

        $result = ['created' => 0, 'updated' => 0, 'skipped' => 0, 'errors' => []];
        $map = null;

        foreach ($reader->getSheetIterator() as $sheet) {
            $line = 0;
            foreach ($sheet->getRowIterator() as $row) {
                $line++;
                $cells = array_map(fn ($c) => $c->getValue(), $row->getCells());

                if ($map === null) {
                    $found = $this->headerMap($cells);
                    $map = (in_array('hovaten', $found, true) || in_array('ten', $found, true)) ? $found : null; // chưa gặp dòng tiêu đề thì thử dòng sau

                    continue;
                }

                if (implode('', array_map(fn ($v) => $this->text($v), $cells)) === '') {
                    continue;
                }

                try {
                    $this->importRow($map, $cells, $result);
                } catch (\Throwable $e) {
                    $result['skipped']++;
                    $result['errors'][] = "Dòng $line: ".$e->getMessage();
                }
            }
            break; // chỉ đọc sheet đầu tiên
        }
        $reader->close();

        if ($map === null) {
            $result['errors'][] = 'Không tìm thấy dòng tiêu đề (cần có cột "Họ và tên"). Hãy tải file mẫu.';
        }

        return $result;
    }

    public static function writeTemplate(string $path): void
    {
        $w = new XlsxWriter;
        $w->openToFile($path);
        $w->addRow(Row::fromValues(array_values(self::COLUMNS)));
        $w->close();
    }

    private function headerMap(array $cells): array
    {
        $byNorm = self::ALIASES;
        foreach (self::COLUMNS as $field => $title) {
            $byNorm[self::norm($title)] = $field;
            $byNorm[$field] = $field;
        }

        $map = [];
        foreach ($cells as $i => $v) {
            $n = self::norm($this->text($v));
            if (isset($byNorm[$n])) {
                $map[$i] = $byNorm[$n];
            }
        }

        return $map;
    }

    private function importRow(array $map, array $cells, array &$result): void
    {
        $d = [];
        foreach ($map as $i => $field) {
            $d[$field] = $cells[$i] ?? null;
        }
        $str = fn ($k) => $this->text($d[$k] ?? null);

        // Họ và tên gộp một cột: chữ cuối là Tên, phần còn lại là Họ và đệm.
        $ho = $str('ho');
        $ten = $str('ten');
        if ($str('hovaten') !== '') {
            $parts = preg_split('/\s+/u', $str('hovaten'));
            $ten = array_pop($parts);
            $ho = implode(' ', $parts);
        }
        if ($ten === '') {
            throw new \RuntimeException('thiếu Họ và tên');
        }
        $partId = $this->resolvePart($str('part'));
        if (! $partId) {
            throw new \RuntimeException('không xác định được Tỉnh thành "'.$str('part').'"');
        }
        $cap = $this->parseDate($d['ngaycap'] ?? null);
        if (! $cap) {
            throw new \RuntimeException('thiếu hoặc sai Ngày cấp (dd/mm/yyyy)');
        }

        $data = ['part_id' => $partId, 'ngaycap_ngay' => $cap['d'], 'ngaycap_thang' => $cap['m'], 'ngaycap_nam' => $cap['y']];
        foreach (['phapdanh', 'ngaysinh', 'noisinh', 'cccd', 'ngaycapcccd', 'thogioi', 'diachi', 'hephai'] as $k) {
            $data[$k] = $str($k);
        }
        $data['image'] = basename($str('image'));

        $data['ho'] = $ho;
        $data['ten'] = $ten;

        // Số thẻ tự sinh: 7 số đầu CCCD + 5 số ngẫu nhiên.
        $data['sothe'] = $this->generateSothe($data['cccd']);

        // Số chứng nhận = số của thẻ mới thêm gần nhất + 1.
        $data['sochungnhan'] = $this->nextSochungnhan();

        Official::create($data + ['status' => 1]);
        $result['created']++;
    }

    /** Lấy số chứng nhận của thẻ thêm gần nhất rồi tăng phần số cuối lên 1 (giữ tiền tố, số 0 đầu). */
    private function nextSochungnhan(): string
    {
        $last = Official::where('sochungnhan', '!=', '')->orderByDesc('id')->value('sochungnhan');
        if ($last === null || ! preg_match('/^(.*?)(\d+)(\D*)$/', $last, $m)) {
            return '1';
        }

        return $m[1].str_pad((string) ((int) $m[2] + 1), strlen($m[2]), '0', STR_PAD_LEFT).$m[3];
    }

    private function generateSothe(string $cccd): string
    {
        $digits = preg_replace('/\D/', '', $cccd);
        if (strlen($digits) < 7) {
            return '';
        }
        $prefix = substr($digits, 0, 7);

        do {
            $sothe = $prefix.str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
        } while (Official::where('sothe', $sothe)->exists());

        return $sothe;
    }

    private function resolvePart(string $value): ?int
    {
        if ($value === '') {
            return $this->defaultPartId;
        }
        if (ctype_digit($value) && isset($this->parts['id:'.$value])) {
            return (int) $value;
        }

        $key = self::partKey($value);

        return $this->parts['t:'.($key === 'saigon' ? 'hcm' : $key)] ?? $this->defaultPartId;
    }

    /** Ô ngày của Excel được đọc thành DateTime, quy về dd/mm/yyyy như dữ liệu cũ. */
    private function text(mixed $v): string
    {
        if ($v instanceof DateTimeInterface) {
            return $v->format('d/m/Y');
        }
        if (is_float($v) && floor($v) == $v) {
            $v = (int) $v;
        }

        return trim((string) $v);
    }

    /** @return array{d:int,m:int,y:int}|null */
    private function parseDate(mixed $v): ?array
    {
        if ($v instanceof DateTimeInterface) {
            return ['d' => (int) $v->format('j'), 'm' => (int) $v->format('n'), 'y' => (int) $v->format('Y')];
        }
        $s = $this->text($v);
        if (preg_match('#^(\d{1,2})[/\-.](\d{1,2})[/\-.](\d{4})$#', $s, $m)) {
            return ['d' => (int) $m[1], 'm' => (int) $m[2], 'y' => (int) $m[3]];
        }
        if (preg_match('#^(\d{4})-(\d{1,2})-(\d{1,2})#', $s, $m)) {
            return ['d' => (int) $m[3], 'm' => (int) $m[2], 'y' => (int) $m[1]];
        }

        return null;
    }

    /** Khóa so khớp tỉnh thành: bỏ tiền tố "Tỉnh", "TP.", "Thành phố" để "Đồng Nai" khớp "Tỉnh Đồng Nai". */
    private static function partKey(string $s): string
    {
        $k = self::norm($s);
        $k = preg_replace('/^(thanhpho|tinh|tp)(?=.)/', '', $k);

        return match ($k) {
            'hochiminh' => 'hcm',
            'thuathienhue' => 'hue',
            default => $k,
        };
    }

    /** Chuẩn hóa tiêu đề/tên: bỏ dấu, chữ thường, chỉ giữ chữ-số. */
    private static function norm(string $s): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($s)));
    }
}
