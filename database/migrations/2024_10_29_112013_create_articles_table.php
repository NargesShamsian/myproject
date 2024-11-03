<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArticlesTable extends Migration
{
    public function up()
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id(); // شناسه یکتای مقاله
            $table->string('title'); // عنوان مقاله
            $table->string('summery')->nullable;//پیش نمایش مقاله
            $table->text('content'); // محتوای مقاله
            $table->foreignId('category_id')->constrained()->onDelete('cascade'); // کلید خارجی به دسته‌بندی
            $table->foreignId('media_id')->constrained()->onDelete('set null'); // کلید خارجی به مدیا
            $table->timestamps(); // زمان‌های ایجاد و به‌روزرسانی
        });
    }

    public function down()
    {
        Schema::dropIfExists('articles');
    }
}
