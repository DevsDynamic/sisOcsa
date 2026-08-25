<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;
//use Spatie\Permission\Models\Role;
use App\Models\Role;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'password',
        'profile_photo_path',
        'access',
        'status',
        'is_system_owner',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_system_owner' => 'boolean',
        ];
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function person()
    {
        return $this->hasOne(Person::class, 'user_id');
    }

    public function scopeVisibleTo($query, ?self $viewer)
    {
        if ($viewer?->is_system_owner) {
            return $query;
        }

        return $query->where('users.is_system_owner', false);
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->username;
    }

    public function routeNotificationForMail(): string
    {
        return $this->username;
    }

    public function getNameAttribute(): string
    {
        return $this->person?->full_name ?? $this->username;
    }

    public function adminlte_profile_url(): string
    {
        return 'mi-perfil';
    }

    public function adminlte_desc(): string
    {
        return $this->is_system_owner ? 'Dueño del sistema' : 'Usuario';
    }

    public function adminlte_image(): string
    {
        return $this->profile_photo_path
            ? route('profile.account.photo.show', ['v' => $this->updated_at?->timestamp])
            : asset('image/user_preview.png');
    }
}
