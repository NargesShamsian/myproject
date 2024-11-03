<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(); // نام کاربر
            $table->string('phone'); // شماره تلفن بدون ویژگی unique
            $table->string('password')->nullable(); // رمز عبور
            $table->string('verification_code')->nullable(); // کد تأیید
            $table->timestamp('verification_code_expires_at')->nullable();
            $table->string('token')->unique()->nullable();
            $table->rememberToken();
            $table->boolean('is_verified')->default(false); // وضعیت تأیید
            $table->timestamps(); // زمان‌های ایجاد و بروزرسانی
        });
    }

    public function down()
    {
        Schema::dropIfExists('users'); // حذف جدول کاربران
    }
}