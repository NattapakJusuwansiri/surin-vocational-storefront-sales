<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();

            // รหัสนักเรียน / รหัสครู
            $table->string('member_code')->unique();

            // ชื่อ-นามสกุล
            $table->string('name');

            // ประเภทสมาชิก
            $table->enum('type', ['student', 'teacher']);

            // เครดิตคงเหลือ (ติดหนี้)
            $table->decimal('credit_balance', 10, 2)->default(0);

            // คะแนนสะสม
            $table->integer('points')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
