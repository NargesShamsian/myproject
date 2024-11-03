<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id(); // شناسه یکتای دسته‌بندی
            $table->string('name')->unique(); // نام دسته‌بندی
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی
        });
    }

    public function down()
    {
        Schema::dropIfExists('categories');
    }
}

