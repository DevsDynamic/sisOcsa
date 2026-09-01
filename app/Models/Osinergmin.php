<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Osinergmin extends Model
{
    use HasFactory;
    
    protected $guarded = ['id'];

    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    public function scopeForEnvironment($query, ?string $environment = null)
    {
        return $query->where('environment', $environment ?? \App\Services\SystemConfig::environment());
    }

    public function scopeVisibleTo($query, ?User $user)
    {
        if ($user?->is_system_owner || $user?->can('osinergmins.manage')) {
            return $query;
        }

        $personId = $user?->person?->id;

        return $personId
            ? $query->where('person_id', $personId)
            : $query->whereRaw('1 = 0');
    }
}
