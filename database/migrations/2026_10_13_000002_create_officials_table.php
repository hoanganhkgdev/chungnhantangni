<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // id giữ nguyên như hệ thống cũ vì QR code trên thẻ đã in chứa id này.
        Schema::create('officials', function (Blueprint $table) {
            $table->unsignedMediumInteger('id')->autoIncrement();
            $table->string('sothe', 50)->default('')->index();
            $table->string('sochungnhan', 50)->default('');
            $table->string('phapdanh')->index();
            $table->string('ho');
            $table->string('ten');
            $table->string('ngaysinh', 50)->default('');
            $table->string('noisinh')->default('');
            $table->string('cccd', 50)->default('');
            $table->string('ngaycapcccd', 50)->default('');
            $table->string('thogioi')->default('');
            $table->string('diachi')->default('');
            $table->unsignedTinyInteger('ngaycap_ngay')->default(0);
            $table->unsignedTinyInteger('ngaycap_thang')->default(0);
            $table->unsignedSmallInteger('ngaycap_nam')->default(0);
            $table->string('image')->default('');
            $table->unsignedSmallInteger('part_id')->default(0)->index();
            $table->string('hephai')->default('');
            $table->string('file_pdf')->default('');
            $table->unsignedTinyInteger('prior')->default(0);
            $table->boolean('status')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};
