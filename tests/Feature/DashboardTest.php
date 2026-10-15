<?php

namespace Tests\Feature;

use App\Models\Official;
use App\Models\Part;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_with_data(): void
    {
        $user = User::factory()->create(['name' => 'Nika']);
        $this->actingAs($user);
        $part = Part::create(['id' => 1, 'title' => 'Tỉnh A', 'alias' => 'Tinh-A', 'status' => 1]);
        foreach ([1, 2] as $i) {
            Official::create(['id' => $i, 'sothe' => 'S1', 'sochungnhan' => '1', 'phapdanh' => "Pháp $i", 'ho' => 'H', 'ten' => "T$i",
                'part_id' => 1, 'status' => 1, 'ngaycap_ngay' => 1, 'ngaycap_thang' => 1, 'ngaycap_nam' => 2024]);
        }

        $this->get('/admin')->assertOk()->assertSee('Xin chào, Nika')->assertSee('Tổng số thẻ')
            ->assertSee('Thẻ cấp theo năm')->assertSee('Thẻ mới nhập')->assertSee('Cần xử lý');
    }
}
