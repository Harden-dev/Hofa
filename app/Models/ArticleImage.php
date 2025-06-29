<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ArticleImage extends Model
{
    //
    use HasUlids;
    protected $table = 'article_images';

    protected $fillable = [
        'slug',
        'article_id',
        'path',
        'caption',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
