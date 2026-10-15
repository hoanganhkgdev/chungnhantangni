<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum UserRole: string implements HasColor, HasLabel
{
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function getLabel(): string
    {
        return match ($this) {
            self::Admin => 'Quản trị',
            self::Editor => 'Nhập liệu',
            self::Viewer => 'Chỉ xem',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Admin => 'danger',
            self::Editor => 'primary',
            self::Viewer => 'gray',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Admin => 'Toàn quyền, gồm xóa dữ liệu, quản lý tỉnh thành và tài khoản',
            self::Editor => 'Thêm, sửa, import và xuất ảnh thẻ; không xóa, không vào Tỉnh thành và Tài khoản',
            self::Viewer => 'Chỉ xem danh sách và xuất ảnh thẻ',
        };
    }
}
