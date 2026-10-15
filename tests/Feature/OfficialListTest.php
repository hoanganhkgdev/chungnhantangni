<?php

namespace Tests\Feature;

use App\Filament\Resources\Officials\Pages\ListOfficials;
use App\Models\Official;
use App\Models\Part;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfficialListTest extends TestCase
{
    use RefreshDatabase;

    public function test_x(): void
    {
        $this->actingAs(User::factory()->create());
        $part = Part::create(['id' => 1, 'title' => 'Tỉnh A', 'alias' => 'Tinh-A', 'status' => 1]);
        foreach ([1, 2, 3] as $i) {
            Official::create(['id' => $i, 'sothe' => $i < 3 ? 'S1' : 'S3', 'sochungnhan' => '1', 'phapdanh' => "P$i",
                'ho' => 'H', 'ten' => "T$i", 'part_id' => 1, 'status' => 1, 'ngaycap_ngay' => 1, 'ngaycap_thang' => 1, 'ngaycap_nam' => 2023]);
        }
        Filament::setCurrentPanel('admin');
        foreach (['all', 'active', 'hidden', 'noimage', 'dup'] as $t) {
            $c = Livewire::test(ListOfficials::class)->set('activeTab', $t)->assertOk();
            if ($t === 'dup') {
                $c->assertSee('Trùng số thẻ với #2')->assertDontSee('P3');
            }
        }
        Livewire::test(ListOfficials::class)->sortTable('ngaycap_text')->assertOk();
    }
}
