<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {

            // อ้างอิงสมาชิก
            $table->foreignId('member_id')
                ->nullable()
                ->after('id')
                ->constrained('members')
                ->nullOnDelete();

            // วิธีชำระเงิน
            $table->enum('payment_type', ['cash', 'credit'])
                ->default('cash')
                ->after('total');
        });
    }

    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
            $table->dropColumn(['member_id', 'payment_type']);
        });
    }
};
