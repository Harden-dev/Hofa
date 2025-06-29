<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Enfiler extends Model
{
    //
    use HasUlids;

    protected $table = 'enfilers';

    protected $fillable = [
        'slug',
        'enfiler_type_id',
        'name',
        'phone',
        'email',
        'motivation',
        'is_active',
    ];
    public function type_enfiler()
    {
        return $this->belongsTo(EnfilerType::class, 'enfiler_type_id');
    }
}
