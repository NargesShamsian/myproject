<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMediaTable extends Migration
{
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id(); // شناسه یکتای مدیا
            $table->string('file_path'); // مسیر فایل
            $table->string('type'); // نوع فایل (عکس، ویدئو و ...)
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی
        });
    }

    public function down()
    {
        Schema::dropIfExists('media');
    }
}
