<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Phục vụ tìm thẻ trùng CCCD.
        Schema::table('officials', fn (Blueprint $table) => $table->index('cccd'));
    }

    public function down(): void
    {
        Schema::table('officials', fn (Blueprint $table) => $table->dropIndex(['cccd']));
    }
};
