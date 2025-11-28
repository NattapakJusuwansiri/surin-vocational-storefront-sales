<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('stock_id')->constrained('stocks')->onDelete('cascade');
        $table->integer('quantity'); // จำนวนที่ย้ายหรือเติม
        $table->enum('from_location', ['front','back','supplier'])->nullable(); // supplier = เพิ่มสินค้า
        $table->enum('to_location', ['front','back'])->nullable();
        $table->enum('unit_type', ['unit','pack','box','dozen'])->default('unit'); // รูปแบบการเพิ่ม
        $table->timestamps();
    });

    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
