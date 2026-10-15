<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Official extends Model
{
    protected $guarded = [];

    protected $casts = ['status' => 'boolean'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function getFullnameAttribute(): string
    {
        return trim($this->ho.' '.$this->ten);
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image ? '/uploads/official/'.$this->image : '/images/no_avatar.png';
    }

    public function getPdfUrlAttribute(): ?string
    {
        return $this->file_pdf ? '/uploads/official/'.$this->file_pdf : null;
    }

    /**
     * Đường dẫn công khai, trùng với URL đã mã hoá trong QR code:
     * /official/{alias-bộ-phận}/{ho-ten-không-dấu}-{id}
     */
    public function getPublicPathAttribute(): string
    {
        return '/official/'.($this->part?->alias ?? 'x').'/'.$this->slug.'-'.$this->id;
    }

    public function getSlugAttribute(): string
    {
        return strtolower(Str::slug($this->fullname));
    }

    public function getNgaycapTextAttribute(): string
    {
        return $this->ngaycap_ngay ? sprintf('%02d/%02d/%d', $this->ngaycap_ngay, $this->ngaycap_thang, $this->ngaycap_nam) : '';
    }

    /** Tiêu đề in trên mặt trước thẻ: tu nữ / tu sĩ / tăng ni tùy thọ giới và hệ phái. */
    public function getCardTitleAttribute(): string
    {
        if (preg_match('/(?<![\p{L}\p{N}])ni(?![\p{L}\p{N}])/iu', $this->thogioi)) {
            return 'CHỨNG NHẬN TU NỮ';
        }
        if (preg_match('/(?<![\p{L}\p{N}])nam\s*tông(?![\p{L}\p{N}])/iu', $this->hephai)) {
            return 'CHỨNG NHẬN TU SĨ';
        }

        return 'CHỨNG NHẬN TĂNG NI';
    }

    /** Địa chỉ đã escape, xuống dòng sau dấu phẩy thứ 3 cho vừa khung thẻ. */
    public function getAddressHtmlAttribute(): string
    {
        $parts = explode(',', (string) $this->diachi, 4);
        if (count($parts) < 4) {
            return e($this->diachi);
        }

        return e(implode(',', array_slice($parts, 0, 3)).',').'<br>'.e($parts[3]);
    }

    /** Thêm dup_sothe_ids / dup_cccd_ids: id các thẻ khác cùng số thẻ / cùng CCCD (chuỗi cách nhau dấu phẩy). */
    public function scopeWithDup($q)
    {
        $t = $this->getTable();
        $same = fn (string $col) => "select group_concat(d.id) from $t d where d.$col <> '' and d.$col = $t.$col and d.id <> $t.id";

        return $q->select("$t.*")
            ->selectSub($same('sothe'), 'dup_sothe_ids')
            ->selectSub($same('cccd'), 'dup_cccd_ids');
    }

    /** Thẻ trùng số thẻ hoặc trùng CCCD với thẻ khác. */
    public function scopeDuplicated($q)
    {
        $t = $this->getTable();
        $exists = fn (string $col) => "($t.$col <> '' and exists (select 1 from $t d where d.$col = $t.$col and d.id <> $t.id))";

        return $q->whereRaw($exists('sothe').' or '.$exists('cccd'));
    }

    public function scopeActive($q)
    {
        return $q->where('status', 1);
    }

    /** Thứ tự như NukeViet: prior DESC, addtime ASC. */
    public function scopeOrdered($q)
    {
        return $q->orderByDesc('prior')->orderBy('created_at')->orderBy('id');
    }
}
