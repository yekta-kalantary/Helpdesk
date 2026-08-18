<?php

namespace Modules\Identity\Infrastructure\Models;

use Database\Factories\UserFactory;
use DomainException;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Domain\Enums\UserRole;

#[Fillable(['client_id', 'role', 'name', 'last_name', 'email', 'mobile', 'password', 'is_active', 'last_login_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements CanResetPasswordContract
{
    /** @use HasFactory<UserFactory> */
    use CanResetPassword, HasFactory, Notifiable;

    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected static function booted(): void
    {
        static::saving(function (User $user): void {
            $role = $user->role instanceof UserRole
                ? $user->role
                : UserRole::tryFrom((string) $user->role);

            if ($role === UserRole::Customer && ! $user->client_id) {
                throw new DomainException('Customer users must belong to a client.');
            }

            if ($role === UserRole::Admin && $user->client_id) {
                throw new DomainException('Admin users cannot belong to a client.');
            }
        });
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins(Builder $query): Builder
    {
        return $query->where('role', UserRole::Admin->value);
    }

    public function scopeCustomers(Builder $query): Builder
    {
        return $query->where('role', UserRole::Customer->value);
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isCustomer(): bool
    {
        return $this->role === UserRole::Customer;
    }

    public function canAuthenticate(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        return $this->client()->active()->exists();
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => $value === null ? null : Str::lower(trim($value)),
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim($this->name.' '.$this->last_name),
        );
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'role' => UserRole::class,
        ];
    }
}
