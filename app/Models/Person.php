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

    public function typePerson()
    {
        return $this->belongsTo(TypePerson::class, 'type_person_id');
    }

    public function typeHistory()
    {
        return $this->hasMany(PersonTypeHistory::class)->latest();
    }

    protected function casts(): array
    {
        return [
            'marketing_consent' => 'boolean',
            'marketing_consent_at' => 'datetime',
            'marketing_opt_out_at' => 'datetime',
            'converted_at' => 'datetime',
        ];
    }

    public function scopeOperationalClients($query)
    {
        return $query->whereDoesntHave('user', function ($userQuery) {
            $userQuery->where('is_system_owner', true);
        });
    }

    public function scopeActiveGpsSources($query)
    {
        return $query
            ->operationalClients()
            ->where('status', true)
            ->whereNotNull('token')
            ->where('token', '<>', '');
    }
}
