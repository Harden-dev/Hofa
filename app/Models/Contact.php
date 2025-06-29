<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasUlids;
    //
    protected $table = 'contacts';

    protected $fillable = [
        'slug',
        'name',
        'email',
        'subject',
        'message',
    ];
}
