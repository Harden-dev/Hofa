<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class NewLetter extends Model
{
    //
    use HasUlids;

    protected $fillable = [
        'email',
        'is_active',
        'slug',
    ];
    
}
