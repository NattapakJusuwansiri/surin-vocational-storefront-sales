<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
            $table->string('prefix')->nullable();
            $table->string('name');
            $table->string('position')->nullable();
            $table->string('email')->nullable();
            $table->string('role')->default('employee');
            $table->string('employee_id')->unique();
            $table->boolean('password_changed')->default(false);
            $table->timestamps();
        });

        DB::table('users')->insert([
            [
                'username' => 'approver',
                'password' => Hash::make('123456789'),
                'prefix' => 'Mr.',
                'name' => 'Approver',
                'position' => 'Approver',
                'email'=>'approver@example.com',
                'role' => 'approver',
                'employee_id' => 'EMP001',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'manager',
                'password' => Hash::make('123456789'),
                'prefix' => 'Ms.',
                'name' => 'Manager',
                'position' => 'Manager',
                'email'=>'manager@example.com',
                'role' => 'manager',
                'employee_id' => 'EMP002',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'employee1',
                'password' => Hash::make('123456789'),
                'prefix' => 'Mr.',
                'name' => 'Employee1 Example',
                'position' => 'Staff',
                'email'=>'employee1@example.com',
                'role' => 'employee',
                'employee_id' => 'EMP003',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'employee2',
                'password' => Hash::make('123456789'),
                'prefix' => 'Mr.',
                'name' => 'Employee2 Example',
                'position' => 'Staff',
                'email'=>'employee2@example.com',
                'role' => 'employee',
                'employee_id' => 'EMP004',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
