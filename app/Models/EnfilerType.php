<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class EnfilerType extends Model
{
    //
    use HasUlids;

    protected $table = 'enfiler_types';

    protected $fillable = [
        'slug',
        'label',
        'description',
    ];

    public function enfilers()
    {
        return $this->hasMany(Enfiler::class);
    }
}
