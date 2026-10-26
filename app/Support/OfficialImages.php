<?php

namespace App\Support;

use App\Models\Official;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/** Ghép ảnh vừa upload vào thẻ theo "Tên hình" đã điền (khi import hoặc sửa thẻ). */
class OfficialImages
{
    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    /**
     * Nhận danh sách tệp đã upload (ảnh hoặc zip), giải nén zip rồi ghép ảnh vào thẻ.
     * Tệp zip tạm được xoá sau khi xử lý.
     *
     * @param  string[]  $paths  đường dẫn trên disk uploads
     * @return array{matched:int, unmatched:string[], extracted:int}
     */
    public static function process(array $paths): array
    {
        $disk = Storage::disk('uploads');
        $images = [];
        foreach ($paths as $path) {
            if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'zip') {
                try {
                    array_push($images, ...self::extractZip($disk->path($path)));
                } finally {
                    $disk->delete($path);
                }
            } else {
                $images[] = $path;
            }
        }

        return self::attach(array_values(array_unique($images))) + ['extracted' => count($images)];
    }

    /** @return string[] đường dẫn ảnh đã lưu vào official/ */
    private static function extractZip(string $file): array
    {
        $zip = new ZipArchive;
        if ($zip->open($file) !== true) {
            throw new \RuntimeException('Không mở được file zip.');
        }

        $saved = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $entry = $zip->getNameIndex($i);
            if (str_ends_with($entry, '/') || str_contains($entry, '__MACOSX/')) {
                continue;
            }
            if (! mb_check_encoding($entry, 'UTF-8')) {
                $entry = mb_convert_encoding($entry, 'UTF-8', 'CP437'); // zip tạo từ Windows cũ
            }
            $name = self::normalize(basename(str_replace('\\', '/', $entry)));
            if ($name === '' || $name[0] === '.' || ! in_array(strtolower(pathinfo($name, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
                continue;
            }
            $stat = $zip->statIndex($i);
            if ($stat['size'] > 5 * 1024 * 1024) {
                continue;
            }
            $stream = $zip->getStream($zip->getNameIndex($i));
            if ($stream && Storage::disk('uploads')->put('official/'.$name, $stream, 'public')) {
                $saved[] = 'official/'.$name;
            }
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
        $zip->close();

        return $saved;
    }

    /**
     * @param  string[]  $paths  đường dẫn tệp trên disk uploads, vd official/anh1.jpg
     * @return array{matched:int, unmatched:string[]}
     */
    public static function attach(array $paths): array
    {
        // Khớp theo tên không đuôi, không phân biệt hoa thường: "Anh1" khớp "anh1.jpg".
        $byStem = [];
        foreach (Official::where('image', '!=', '')->get(['id', 'image']) as $o) {
            $byStem[self::stem($o->image)][] = $o->id;
        }

        $result = ['matched' => 0, 'unmatched' => []];
        foreach ($paths as $path) {
            $name = basename($path);
            $ids = $byStem[self::stem($name)] ?? [];
            if (! $ids) {
                $result['unmatched'][] = $name;

                continue;
            }
            Official::whereIn('id', $ids)->update(['image' => $name]);
            $result['matched'] += count($ids);
        }

        return $result;
    }

    private static function stem(string $name): string
    {
        return mb_strtolower(self::normalize(pathinfo($name, PATHINFO_FILENAME)));
    }

    /** Chuẩn hoá Unicode về NFC (zip từ macOS lưu tiếng Việt dạng tổ hợp). */
    private static function normalize(string $s): string
    {
        return class_exists(\Normalizer::class) ? (\Normalizer::normalize($s, \Normalizer::FORM_C) ?: $s) : $s;
    }
}
