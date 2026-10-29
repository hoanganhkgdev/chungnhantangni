<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nơi thường trú và tên giới đàn, tách riêng khỏi Nơi tu học / Tỉnh thành.
        Schema::table('officials', function (Blueprint $table) {
            $table->string('thuongtru')->default('')->after('diachi');
            $table->string('gioidan')->default('')->after('thogioi');
        });
    }

    public function down(): void
    {
        Schema::table('officials', fn (Blueprint $table) => $table->dropColumn(['thuongtru', 'gioidan']));
    }
};
