<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage; 

class Media extends Model
{
    use HasFactory;

    // مشخص کردن فلیلدهای قابل پر کردن
    protected $fillable = ['file_path', 'type'];

    /**
     * رابطه یک به چند با مدل Article
     */
    public function articles()
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Bootstrapping the model to handle the type detection
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($media) {
            // تشخیص نوع فایل بر اساس پسوند
            $extension = pathinfo($media->file_path, PATHINFO_EXTENSION);
            
            // مشخص کردن نوع فایل بر اساس پسوند
            switch (strtolower($extension)) {
                case 'jpeg':
                case 'jpg':
                case 'png':
                case 'gif':
                    $media->type = 'image';
                    break;
                case 'mp4':
                case 'avi':
                case 'mov':
                    $media->type = 'video';
                    break;
                case 'mp3':
                case 'wav':
                case 'ogg':
                    $media->type = 'audio';
                    break;
                default:
                    $media->type = 'unknown'; // نوع ناشناخته
                    break;
            }
        });
    }

    /**
     * Return the full URL of the media file
     * 
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }
}

