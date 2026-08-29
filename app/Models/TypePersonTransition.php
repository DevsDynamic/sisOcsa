<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TypePersonTransition extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array
    {
        return ['required_fields' => 'array', 'active' => 'boolean'];
    }
}
