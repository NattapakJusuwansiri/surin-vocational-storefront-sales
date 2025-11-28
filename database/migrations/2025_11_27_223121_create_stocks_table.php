<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table) {
            $table->id(); // id
            $table->string('name'); // ชื่อสินค้า
            $table->string('category'); // หมวดหมู่สินค้า
            $table->string('product_code'); // หมวดหมู่สินค้า
            $table->integer('quantity_front')->default(0); // จำนวน
            $table->integer('quantity_back')->default(0); // 
            $table->decimal('price', 10, 2); // ราคาต่อหน่วย
            $table->string('barcode_unit')->nullable(); // บาร์โค้ดหน่วย
            $table->string('barcode_pack')->nullable(); // บาร์โค้ดแพ็ค
            $table->string('barcode_box')->nullable(); // บาร์โค้ดกล่อง
            $table->timestamps(); // created_at, updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
