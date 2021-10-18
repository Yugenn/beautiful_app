<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'body',
    ];

    public function image()
    {
        return $this->hasOne(Image::class);
    }

    public function getImagePathAttribute()
    {
        return 'articles/' . $this->image->name;
    }

    public function getImageUrlAttribute()
    {
        return Storage::url($this->image_path);
    }
}
