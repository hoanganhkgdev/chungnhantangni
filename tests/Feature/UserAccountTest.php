<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Official;
use App\Models\Part;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccountTest extends TestCase
{
    use RefreshDatabase;

    private function as(UserRole $role, array $extra = []): User
    {
        $u = User::factory()->create(['role' => $role] + $extra);
        $this->actingAs($u);

        return $u;
    }

    public function test_admin_manages_accounts(): void
    {
        $me = $this->as(UserRole::Admin);

        $this->get('/admin/users')->assertOk()->assertSee($me->email);
        $this->get('/admin/users/create')->assertOk();
        $this->get("/admin/users/{$me->id}/edit")->assertOk();
    }

    public function test_editor_and_viewer_cannot_open_accounts_or_provinces(): void
    {
        foreach ([UserRole::Editor, UserRole::Viewer] as $role) {
            $this->as($role);
            $this->get('/admin/users')->assertForbidden();
            $this->get('/admin/parts')->assertForbidden();
            $this->get('/admin/officials')->assertOk();
        }
    }

    public function test_viewer_cannot_create_or_edit_cards_but_editor_can(): void
    {
        $part = Part::create(['id' => 1, 'title' => 'Tỉnh A', 'alias' => 'Tinh-A', 'status' => 1]);
        $o = Official::create(['id' => 1, 'sothe' => '1', 'sochungnhan' => '1', 'phapdanh' => 'P', 'ho' => 'H', 'ten' => 'T',
            'part_id' => $part->id, 'status' => 1, 'ngaycap_ngay' => 1, 'ngaycap_thang' => 1, 'ngaycap_nam' => 2023]);

        $this->as(UserRole::Viewer);
        $this->get('/admin/officials/create')->assertForbidden();
        $this->get("/admin/officials/{$o->id}/edit")->assertForbidden();

        $this->as(UserRole::Editor);
        $this->get('/admin/officials/create')->assertOk();
        $this->get("/admin/officials/{$o->id}/edit")->assertOk();
    }

    public function test_locked_account_cannot_enter_admin(): void
    {
        $this->as(UserRole::Admin, ['is_active' => false]);

        $this->get('/admin')->assertForbidden();
    }

    public function test_last_login_is_recorded_and_last_admin_is_protected(): void
    {
        $a = User::factory()->create(['email' => 'a@x.test', 'password' => 'secret-pass-1']);
        $this->post('/admin/login', []); // không dùng Livewire: kiểm tra trực tiếp sự kiện đăng nhập
        auth()->login($a);
        event(new Login('web', $a, false));
        $this->assertNotNull($a->fresh()->last_login_at);

        $this->assertFalse(User::otherActiveAdminsExist($a));
        User::factory()->create(['role' => UserRole::Admin]);
        $this->assertTrue(User::otherActiveAdminsExist($a));
    }
}
