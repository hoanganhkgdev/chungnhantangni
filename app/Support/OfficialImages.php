<?php

namespace App\Support;

use App\Models\Official;

/** Ghép ảnh vừa upload vào thẻ theo "Tên hình" đã điền (khi import hoặc sửa thẻ). */
class OfficialImages
{
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
        return mb_strtolower(pathinfo($name, PATHINFO_FILENAME));
    }
}
