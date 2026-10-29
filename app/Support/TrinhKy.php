<?php

namespace App\Support;

use App\Models\Official;
use Illuminate\Support\Collection;
use ZipArchive;

/** Tạo file Word trình ký "Cấp giấy chứng nhận Tăng Ni" từ mẫu resources/templates/trinh-ky.docx. */
class TrinhKy
{
    /** Nhiều thẻ thì gộp vào một file, mỗi thẻ một trang. Trả về đường dẫn file tạm. */
    public static function make(Collection $officials): string
    {
        $path = tempnam(sys_get_temp_dir(), 'tk').'.docx';
        copy(resource_path('templates/trinh-ky.docx'), $path);

        $zip = new ZipArchive;
        $zip->open($path);
        $xml = $zip->getFromName('word/document.xml');

        // Thân tài liệu nằm giữa <w:body> và <w:sectPr> (khổ giấy, lề) ở cuối.
        $start = strpos($xml, '<w:body>') + strlen('<w:body>');
        $end = strrpos($xml, '<w:sectPr');
        $body = substr($xml, $start, $end - $start);

        $pages = $officials->map(fn (Official $o) => strtr($body, self::values($o)));
        $xml = substr($xml, 0, $start)
            .$pages->implode('<w:p><w:r><w:br w:type="page"/></w:r></w:p>')
            .substr($xml, $end);

        $zip->addFromString('word/document.xml', $xml);
        $zip->close();

        return $path;
    }

    private static function values(Official $o): array
    {
        $v = [
            'sochungnhan' => $o->sochungnhan,
            'ngay' => sprintf('%02d', $o->ngaycap_ngay),
            'thang' => sprintf('%02d', $o->ngaycap_thang),
            'nam' => (string) $o->ngaycap_nam,
            'tieude' => 'CẤP GIẤY '.$o->card_title,
            'phapdanh' => $o->phapdanh,
            'thedanh' => $o->fullname,
            'ngaysinh' => $o->ngaysinh,
            'noisinh' => $o->noisinh,
            'cccd' => $o->cccd,
            'ngaycapcccd' => $o->ngaycapcccd,
            'thogioi' => $o->thogioi,
            'gioidan' => $o->gioidan,
            'noigioidan' => $o->part?->title ?? '',
            'diachi' => $o->diachi,
            // Thẻ cũ chưa có nơi thường trú riêng thì dùng nơi tu học như trước.
            'thuongtru' => $o->thuongtru !== '' ? $o->thuongtru : $o->diachi,
            'sothe' => $o->sothe,
        ];

        $out = [];
        foreach ($v as $k => $val) {
            $out['${'.$k.'}'] = htmlspecialchars((string) $val, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }

        return $out;
    }
}
