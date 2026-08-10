<?php

namespace Modules\Projects\Application\Queries;

use Illuminate\Support\Facades\DB;
use Modules\Identity\Infrastructure\Models\User;

class ProjectFormOptions
{
    /** @return array{contacts:array<int,array{id:int,name:string,email:string,mobile:string}>,members:array<int,array{id:int,name:string}>} */
    public function get(?string $contactSearch = null): array
    {
        return [
            'contacts' => $this->contacts($contactSearch),
            'members' => User::query()
                ->select('users.*')
                ->join('contacts', 'contacts.id', '=', 'users.contact_id')
                ->with('contact')
                ->where('users.is_active', true)
                ->orderBy('contacts.first_name')
                ->orderBy('contacts.last_name')
                ->get()
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->full_name])
                ->all(),
        ];
    }

    /** @return array{id:int,name:string,email:string,mobile:string}|null */
    public function findContact(int $contactId): ?array
    {
        $contact = DB::table('contacts')
            ->where('id', $contactId)
            ->first(['id', 'first_name', 'last_name', 'email', 'mobile']);

        return $contact ? $this->mapContact($contact) : null;
    }

    /** @return array<int,array{id:int,name:string,email:string,mobile:string}> */
    private function contacts(?string $search): array
    {
        $query = DB::table('contacts');
        $search = $this->normalizeSearch((string) $search);

        if ($search !== '') {
            foreach (preg_split('/\s+/u', $search) ?: [] as $term) {
                $like = '%'.addcslashes($term, '\\%_').'%';

                $query->where(fn ($contactQuery) => $contactQuery
                    ->where('first_name', 'like', $like)
                    ->orWhere('last_name', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('mobile', 'like', $like));
            }
        }

        return $query
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(25)
            ->get(['id', 'first_name', 'last_name', 'email', 'mobile'])
            ->map(fn (object $contact) => $this->mapContact($contact))
            ->all();
    }

    /** @return array{id:int,name:string,email:string,mobile:string} */
    private function mapContact(object $contact): array
    {
        return [
            'id' => (int) $contact->id,
            'name' => trim($contact->first_name.' '.$contact->last_name),
            'email' => (string) $contact->email,
            'mobile' => (string) $contact->mobile,
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
