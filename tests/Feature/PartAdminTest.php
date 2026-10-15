<?php

namespace Tests\Feature;

use App\Models\Official;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PartAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_part_pages_render_and_create_generates_alias(): void
    {
        $this->actingAs(User::factory()->create());
        $p = Part::create(['title' => 'Tỉnh Đồng Nai', 'alias' => 'Tinh-Dong-Nai', 'status' => 1]);

        $this->get('/admin/parts')->assertOk()->assertSee('Tỉnh thành');
        $this->get('/admin/parts/create')->assertOk();
        $this->get("/admin/parts/{$p->id}/edit")->assertOk();
        $o = Official::create([
            'id' => 5, 'sothe' => '1', 'sochungnhan' => '2', 'phapdanh' => 'Thích Nữ A', 'ho' => 'Trang', 'ten' => 'B',
            'part_id' => $p->id, 'status' => 1, 'ngaycap_ngay' => 1, 'ngaycap_thang' => 2, 'ngaycap_nam' => 2023,
        ]);
        $this->get('/admin/officials')->assertOk()->assertSee('Thích Nữ A');
        $this->get('/admin/officials/create')->assertOk();
        $this->get("/admin/officials/{$o->id}/edit")->assertOk()->assertSee('Xem thẻ');
    }
}
