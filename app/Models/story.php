<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class story extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        // 'content_image',
        'user_id',
        'category_id',
    ];

    public function users()
    {
        return $this->belongsTo(User::class, 'user_id'); ;
    }

    public function categories()
    {
        return $this->belongsTo(category::class, 'category_id');
    }

    public function bookmarks()
    {
        return $this->hasMany(bookmark::class);
    }
    public function images()
    {
        return $this->hasMany(StoryImage::class);
    }

}
