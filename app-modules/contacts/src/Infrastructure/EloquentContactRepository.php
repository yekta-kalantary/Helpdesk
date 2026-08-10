<?php

namespace Modules\Contacts\Infrastructure;

use Modules\Contacts\Domain\Contracts\ContactRepository;
use Modules\Contacts\Infrastructure\Models\Contact;

class EloquentContactRepository implements ContactRepository
{
    public function search(?string $term = null): array
    {
        $term = trim((string) $term);

        return Contact::query()
            ->when($term !== '', fn ($query) => $query->where(function ($nested) use ($term): void {
                foreach (preg_split('/\s+/u', $this->normalizeSearch($term)) ?: [] as $part) {
                    $like = '%'.addcslashes($part, '\\%_').'%';
                    $nested->where(fn ($search) => $search
                        ->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('mobile', 'like', $like));
                }
            }))
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->map(fn (Contact $contact) => $this->map($contact))
            ->all();
    }

    public function find(int $id): array
    {
        return $this->map(Contact::query()->findOrFail($id));
    }

    public function save(?int $id, array $contactAttributes): int
    {
        $contact = $id
            ? Contact::query()->findOrFail($id)
            : new Contact;

        $contact->fill($contactAttributes);
        $contact->save();

        return $contact->id;
    }

    /** @return array<string,mixed> */
    private function map(Contact $contact): array
    {
        return [
            'id' => $contact->id,
            'first_name' => $contact->first_name,
            'last_name' => $contact->last_name,
            'full_name' => $contact->full_name,
            'gender' => $contact->gender,
            'email' => $contact->email,
            'mobile' => $contact->mobile,
            'province' => $contact->province,
            'city' => $contact->city,
            'address' => $contact->address,
            'postal_code' => $contact->postal_code,
            'created_at' => $contact->created_at,
        ];
    }

    private function normalizeSearch(string $search): string
    {
        return trim(strtr($search, [
            '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4',
            '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
            '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
        ]));
    }
}
