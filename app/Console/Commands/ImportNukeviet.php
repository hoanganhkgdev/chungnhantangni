<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportNukeviet extends Command
{
    protected $signature = 'nukeviet:import {--fresh : Xoá dữ liệu parts/officials trước khi nhập}';

    protected $description = 'Nhập bộ phận và thẻ tăng ni từ DB NukeViet cũ (kết nối "legacy"), giữ nguyên id.';

    public function handle(): int
    {
        $legacy = DB::connection('legacy');

        if ($this->option('fresh')) {
            DB::table('officials')->delete();
            DB::table('parts')->delete();
        }

        $now = now();

        $parts = $legacy->table('nv4_vi_official_part')->get()->map(fn ($p) => [
            'id' => $p->id,
            'parent_id' => $p->parentid,
            'title' => $p->title,
            'alias' => $p->alias,
            'note' => $p->note,
            'weight' => $p->weight,
            'status' => $p->status,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();
        DB::table('parts')->upsert($parts, ['id']);
        $this->info(count($parts).' bộ phận');

        $total = 0;
        $legacy->table('nv4_vi_official_rows')->orderBy('id')->chunk(500, function ($rows) use (&$total) {
            $batch = $rows->map(fn ($r) => [
                'id' => $r->id,
                'sothe' => $r->sothe,
                'sochungnhan' => $r->sochungnhan,
                'phapdanh' => $r->phapdanh,
                'ho' => $r->ho,
                'ten' => $r->ten,
                'ngaysinh' => $r->ngaysinh,
                'noisinh' => $r->noisinh,
                'cccd' => $r->cccd,
                'ngaycapcccd' => $r->ngaycapcccd,
                'thogioi' => $r->thogioi,
                'diachi' => $r->diachi,
                'ngaycap_ngay' => $r->ngaycap_ngay,
                'ngaycap_thang' => $r->ngaycap_thang,
                'ngaycap_nam' => $r->ngaycap_nam,
                'image' => $r->image,
                'part_id' => $r->part_id,
                'hephai' => $r->hephai,
                'file_pdf' => $r->file_pdf,
                'prior' => $r->prior,
                'status' => $r->status,
                'created_at' => $r->addtime ? Carbon::createFromTimestamp($r->addtime) : now(),
                'updated_at' => $r->edittime ? Carbon::createFromTimestamp($r->edittime) : ($r->addtime ? Carbon::createFromTimestamp($r->addtime) : now()),
            ])->all();
            DB::table('officials')->upsert($batch, ['id']);
            $total += count($batch);
        });
        $this->info("$total thẻ");

        return self::SUCCESS;
    }
}
