<?php

namespace Tests\Feature;

use App\Models\Official;
use App\Models\Part;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfficialUrlTest extends TestCase
{
    use RefreshDatabase;

    private function make(array $o = []): Official
    {
        $part = Part::create(['id' => 3, 'title' => 'Tỉnh Đồng Nai', 'alias' => 'Tinh-Dong-Nai', 'status' => 1]);

        return Official::create($o + [
            'id' => 3, 'sothe' => '07732201636', 'sochungnhan' => '41319', 'phapdanh' => 'Thích Nữ Nhật Tịnh',
            'ho' => 'Trang', 'ten' => 'Thị Bích Phụng', 'part_id' => $part->id, 'status' => 1,
            'ngaycap_ngay' => 12, 'ngaycap_thang' => 4, 'ngaycap_nam' => 2023,
        ]);
    }

    public function test_qr_url_format_renders_card(): void
    {
        $o = $this->make();

        $this->assertSame('/official/Tinh-Dong-Nai/trang-thi-bich-phung-3', $o->public_path);
        $this->get($o->public_path)->assertOk()->assertSee('Thích Nữ Nhật Tịnh');
    }

    public function test_card_resolves_by_id_even_if_alias_or_name_differs(): void
    {
        $this->make();

        $this->get('/official/Alias-Cu/ten-cu-3')->assertOk()->assertSee('41319/CNTN.2023', false);
    }

    public function test_unknown_or_inactive_card_is_404(): void
    {
        $this->make(['status' => 0]);

        $this->get('/official/Tinh-Dong-Nai/x-3')->assertNotFound();
        $this->get('/official/Tinh-Dong-Nai/x-999')->assertNotFound();
    }

    public function test_no_public_listing_pages(): void
    {
        $this->get('/official')->assertNotFound();
        $this->get('/official/search?q=a')->assertNotFound();
        $this->get('/official/Tinh-Dong-Nai')->assertNotFound();
        $this->get('/')->assertRedirect('/admin');
    }

    public function test_api_public_detail(): void
    {
        $this->make();

        $this->getJson('/api_public.php?action=detail&id=3')
            ->assertOk()->assertJsonPath('status', 'success')->assertJsonPath('data.part.alias', 'Tinh-Dong-Nai');
    }
}
