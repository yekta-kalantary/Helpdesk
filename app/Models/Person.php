<?php

namespace App\Models;

use App\Enums\PersonType;
use Database\Factories\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

#[Fillable(['type', 'first_name', 'last_name', 'email', 'mobile'])]
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory;

    protected static function booted(): void
    {
        static::updating(function (self $person): void {
            if ($person->isDirty('type')) {
                throw new \DomainException('person_type_immutable');
            }
        });

        static::saving(function (self $person): void {
            Validator::make(
                [
                    'type' => $person->type instanceof PersonType ? $person->type->value : $person->type,
                    'first_name' => $person->first_name,
                    'last_name' => $person->last_name,
                    'email' => $person->email,
                    'mobile' => $person->mobile,
                ],
                [
                    'type' => ['required', Rule::enum(PersonType::class)],
                    'first_name' => ['required', 'string', 'max:255'],
                    'last_name' => ['required', 'string', 'max:255'],
                    'email' => ['required', 'email', 'max:255'],
                    'mobile' => ['required', 'string', 'max:32'],
                ],
            )->validate();
        });
    }

    public function user(): HasOne
    {
        return $this->hasOne(User::class);
    }

    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn (): string => trim($this->first_name.' '.$this->last_name),
        );
    }

    protected function casts(): array
    {
        return [
            'type' => PersonType::class,
        ];
    }
}
