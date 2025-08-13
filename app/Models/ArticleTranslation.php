<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ArticleTranslation extends Model
{
    //
    use HasUlids;
    protected $table = 'article_translations';

    protected $fillable = [
        'locale',
        'title',
        'content',
        'description',
        'category',
        'article_id',
    ];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }
}
