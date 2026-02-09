<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'signup_method',
        'status',
        'email_verified_at',
        'google_id',
        'facebook_id',
        'microsoft_id',
        'profile_photo_path',
        'role',
        'league_reminder_sent_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'league_reminder_sent_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function league(): HasOne
    {
        return $this->hasOne(League::class);
    }

    public function claimedManagers(): HasMany
    {
        return $this->hasMany(Manager::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(ClaimsComplaint::class, 'reporter_user_id');
    }

    public function resolvedComplaints(): HasMany
    {
        return $this->hasMany(ClaimsComplaint::class, 'resolved_by');
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    public function hasClaimedProfile(): bool
    {
        return $this->claimedManagers()->whereNotNull('user_id')->exists();
    }

    public function claimedEntryId(): ?int
    {
        return $this->claimedManagers()
            ->whereNotNull('user_id')
            ->value('entry_id');
    }
}
