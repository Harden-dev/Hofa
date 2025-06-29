<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class BenevoleType extends Model
{
    //
    use HasUlids;

    protected $table = 'benevole_types';

    protected $fillable = [
        'slug',
        'label',
        'description',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
