<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    //
    use HasUlids;

    protected $table = 'articles';

    protected $fillable = ['slug', 'cover_image', 'is_active'];
    public function translations()
    {
        return $this->hasMany(ArticleTranslation::class);
    }


    public function images()
    {
        return $this->hasMany(ArticleImage::class);
    }
}
