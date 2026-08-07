<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['person_id', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->person?->first_name ?? '',
        );
    }

    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->person?->last_name ?? '',
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->person?->email ?? '',
        );
    }

    protected function mobile(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->person?->mobile ?? '',
        );
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->person?->full_name ?? '',
        );
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
