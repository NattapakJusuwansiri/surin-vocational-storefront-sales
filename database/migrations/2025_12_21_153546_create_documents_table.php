<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();

            $table->string('document_type'); 
            // tax_invoice, receipt, quotation, invoice

            $table->string('document_no')->unique();
            $table->date('document_date');

            $table->unsignedBigInteger('bill_id')->nullable();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('vat_amount', 10, 2)->default(0);

            // ข้อมูลผู้ขาย
            $table->string('seller_name');
            $table->string('seller_tax_id')->nullable();
            $table->text('seller_address')->nullable();

            // ข้อมูลผู้ซื้อ
            $table->string('buyer_name')->nullable();
            $table->string('buyer_tax_id')->nullable();
            $table->text('buyer_address')->nullable();

            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
