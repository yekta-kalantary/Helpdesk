<?php

namespace Modules\Customers\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Customers\Application\Actions\SaveCustomer;
use Modules\Customers\Domain\Contracts\CustomerRepository;
use Modules\Customers\Domain\Enums\CustomerStatus;

class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerRepository $customers,
        private readonly SaveCustomer $saveCustomer,
    ) {}

    public function index(Request $request): View
    {
        return view('customers::index', [
            'customers' => $this->customers->search($request->string('q')->trim()->value() ?: null),
        ]);
    }

    public function create(): View
    {
        return view('customers::form', [
            'customer' => null,
            'statuses' => CustomerStatus::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateCustomer($request);
        $this->saveCustomer->execute(null, $this->attributes($data), $request->boolean('portal_enabled'), $data['portal_password'] ?? null);

        return redirect()->route('customers.index')->with('success', __('app.created_successfully'));
    }

    public function edit(int $customer): View
    {
        return view('customers::form', [
            'customer' => $this->customers->find($customer),
            'statuses' => CustomerStatus::cases(),
        ]);
    }

    public function update(Request $request, int $customer): RedirectResponse
    {
        $current = $this->customers->find($customer);
        $data = $this->validateCustomer($request, $customer, $current['user_id']);
        $this->saveCustomer->execute($customer, $this->attributes($data), $request->boolean('portal_enabled'), $data['portal_password'] ?? null);

        return redirect()->route('customers.index')->with('success', __('app.updated_successfully'));
    }

    public function destroy(int $customer): RedirectResponse
    {
        $this->saveCustomer->delete($customer);

        return redirect()->route('customers.index')->with('success', __('app.deleted_successfully'));
    }

    private function validateCustomer(Request $request, ?int $customerId = null, ?int $userId = null): array
    {
        $emailRules = ['required', 'email', 'max:255', Rule::unique('customers', 'email')->ignore($customerId)];
        if ($request->boolean('portal_enabled')) {
            $emailRules[] = Rule::unique('users', 'email')->ignore($userId);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'email' => $emailRules,
            'phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'status' => ['required', Rule::enum(CustomerStatus::class)],
            'portal_enabled' => ['nullable', 'boolean'],
            'portal_password' => [$request->boolean('portal_enabled') && ! $userId ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
        ]);
    }

    private function attributes(array $data): array
    {
        return [
            'name' => $data['name'],
            'company' => $data['company'] ?? null,
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => $data['status'],
        ];
    }
}
