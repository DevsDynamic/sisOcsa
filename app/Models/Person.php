<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'user_id');
    }

    public function scopeOperationalClients($query)
    {
        return $query->whereDoesntHave('user', function ($userQuery) {
            $userQuery->where('is_system_owner', true);
        });
    }
}
