<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Member extends Model
{
    use HasUlids    ;
    //
    protected $table = 'members';

    protected $fillable = [
        'slug',
        'name',
        'phone',
        'email',
        'gender',
        'marital_status',
        'professional_profile',
        'benevolent_type_id',
        'is_benevolent',
        'residence',
        'benevolent_experience',
    ];

    public function benevolent_type()
    {
        return $this->belongsTo(BenevoleType::class, 'benevolent_type_id');
    }
}
