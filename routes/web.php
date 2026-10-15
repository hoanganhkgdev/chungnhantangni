<?php

use App\Http\Controllers\ApiPublicController;
use App\Http\Controllers\OfficialController;
use App\Http\Controllers\PrintController;
use Illuminate\Support\Facades\Route;

// Không có trang công khai: gốc chuyển thẳng vào khu quản trị.
Route::redirect('/', '/admin');

// API tương thích api_public.php của hệ thống cũ (client Laravel khác đang gọi).
Route::match(['get', 'options'], '/api_public.php', ApiPublicController::class);

// Chỉ giữ URL mà QR code trên thẻ đã in trỏ tới: /official/{giới-đàn}/{họ-tên}-{id}
Route::get('/official/{part}/{slug}', [OfficialController::class, 'show'])->where('slug', '.+-\d+');

// In thẻ (A5 ngang) cho quản trị viên.
Route::get('/in-the', PrintController::class)->name('official.print');
