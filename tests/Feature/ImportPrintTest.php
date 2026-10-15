<?php

namespace Tests\Feature;

use App\Models\Official;
use App\Models\Part;
use App\Models\User;
use App\Support\OfficialImporter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Tests\TestCase;

class ImportPrintTest extends TestCase
{
    use RefreshDatabase;

    private function xlsx(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'imp').'.xlsx';
        $w = new Writer;
        $w->openToFile($path);
        foreach ($rows as $r) {
            $w->addRow(Row::fromValues($r));
        }
        $w->close();

        return $path;
    }

    public function test_import_creates_updates_and_reports_errors(): void
    {
        Part::create(['id' => 1, 'title' => 'Tỉnh Đồng Nai', 'alias' => 'Tinh-Dong-Nai', 'status' => 1]);
        $head = ['Số thẻ', 'Họ và đệm', 'Tên', 'Pháp danh', 'Tỉnh thành', 'Ngày cấp'];
        $path = $this->xlsx([
            $head,
            ['001', 'Nguyễn Thị', 'Hồng', 'Thích Nữ A', 'Tỉnh Đồng Nai', '12/04/2023'],
            ['002', 'Trần', 'Bình', 'Thích B', 'Nơi lạ', '12/04/2023'],
            ['003', 'Lê', 'Cúc', 'Thích C', 'tinh dong nai', '1/2/2024'],
        ]);

        $r = (new OfficialImporter)->import($path, 'xlsx');
        $this->assertSame(2, $r['created']);
        $this->assertSame(1, $r['skipped']);
        $this->assertStringContainsString('Dòng 3', $r['errors'][0]);
        $this->assertSame(2023, Official::where('sothe', '001')->value('ngaycap_nam'));

        $r = (new OfficialImporter(update: true))->import($this->xlsx([$head, ['001', '', 'Hồng', 'Pháp danh mới', 'Tỉnh Đồng Nai', '12/04/2023']]), 'xlsx');
        $this->assertSame(1, $r['updated']);
        $this->assertSame('Pháp danh mới', Official::where('sothe', '001')->value('phapdanh'));
        $this->assertSame('Nguyễn Thị', Official::where('sothe', '001')->value('ho'));
    }

    public function test_print_requires_login_and_renders_both_sides(): void
    {
        $part = Part::create(['id' => 1, 'title' => 'Tỉnh Đồng Nai', 'alias' => 'Tinh-Dong-Nai', 'status' => 1]);
        $o = Official::create(['id' => 7, 'sothe' => '1', 'sochungnhan' => '2', 'phapdanh' => 'Thích Nữ A', 'ho' => 'A', 'ten' => 'B',
            'thogioi' => 'Tỳ kheo ni', 'diachi' => 'a, b, c, d, e', 'part_id' => $part->id, 'status' => 1,
            'ngaycap_ngay' => 1, 'ngaycap_thang' => 2, 'ngaycap_nam' => 2023]);

        $this->get('/in-the?ids=7')->assertForbidden();

        $this->actingAs(User::factory()->create());
        $this->get('/in-the?ids=7')->assertOk()->assertSee('card-mattruoc')->assertSee('card-matsau')
            ->assertSee('CHỨNG NHẬN TU NỮ')->assertSee('a, b, c,<br> d, e', false);
        $this->get('/in-the?ids=999')->assertNotFound();
    }
}
