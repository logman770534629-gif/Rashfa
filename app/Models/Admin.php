<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'admins';

    protected $fillable = ['email', 'full_name'];

    protected $hidden = ['password_hash', 'failed_attempts', 'locked_until'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'locked_until' => 'datetime',
            'last_login_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function isLocked(): bool
    {
        return $this->locked_until?->isFuture() ?? false;
    }

    public function canManageContent(): bool
    {
        return in_array($this->role, ['super_admin', 'editor'], true);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }
}
