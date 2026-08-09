<?php

namespace Modules\Projects\Application\Queries;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProjectFormOptions
{
    /** @return array{customers:array<int,array{id:int,name:string,email:string,mobile:string}>,members:array<int,array{id:int,name:string}>} */
    public function get(?string $customerSearch = null): array
    {
        return [
            'customers' => $this->customers($customerSearch),
            'members' => User::query()
                ->select('users.*')
                ->join('people', 'people.id', '=', 'users.person_id')
                ->with('person')
                ->where('users.is_active', true)
                ->where('people.type', PersonType::Employee->value)
                ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
                ->orderBy('people.first_name')
                ->orderBy('people.last_name')
                ->get()
                ->map(fn (User $user) => ['id' => $user->id, 'name' => $user->full_name])
                ->all(),
        ];
    }

    /** @return array{id:int,name:string,email:string,mobile:string}|null */
    public function findCustomer(int $customerId): ?array
    {
        $customer = DB::table('customers')
            ->join('people', 'people.id', '=', 'customers.person_id')
            ->whereNull('customers.deleted_at')
            ->where('customers.id', $customerId)
            ->first([
                'customers.id',
                'people.first_name',
                'people.last_name',
                'people.email',
                'people.mobile',
            ]);

        return $customer ? $this->mapCustomer($customer) : null;
    }

    /** @return array<int,array{id:int,name:string,email:string,mobile:string}> */
    private function customers(?string $search): array
    {
        $query = DB::table('customers')
            ->join('people', 'people.id', '=', 'customers.person_id')
            ->whereNull('customers.deleted_at');

        $search = $this->normalizeSearch((string) $search);

        if ($search !== '') {
            foreach (preg_split('/\s+/u', $search) ?: [] as $term) {
                $like = '%'.addcslashes($term, '\\%_').'%';

                $query->where(fn ($customerQuery) => $customerQuery
                    ->where('people.first_name', 'like', $like)
                    ->orWhere('people.last_name', 'like', $like)
                    ->orWhere('people.email', 'like', $like)
                    ->orWhere('people.mobile', 'like', $like));
            }
        }

        return $query
            ->orderBy('people.first_name')
            ->orderBy('people.last_name')
            ->limit(25)
            ->get([
                'customers.id',
                'people.first_name',
                'people.last_name',
                'people.email',
                'people.mobile',
            ])
            ->map(fn (object $customer) => $this->mapCustomer($customer))
            ->all();
    }

    /** @return array{id:int,name:string,email:string,mobile:string} */
    private function mapCustomer(object $customer): array
    {
        return [
            'id' => (int) $customer->id,
            'name' => trim($customer->first_name.' '.$customer->last_name),
            'email' => (string) $customer->email,
            'mobile' => (string) $customer->mobile,
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
