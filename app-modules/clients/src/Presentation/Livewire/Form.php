<?php

namespace Modules\Clients\Presentation\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Modules\Clients\Domain\Enums\ClientStatus;
use Modules\Clients\Infrastructure\Models\Client;

class Form extends Component
{
    #[Locked]
    public ?int $clientId = null;

    public string $name = '';

    public ?string $description = null;

    public string $status = 'active';

    public function mount(?int $client = null): void
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        if (! $client) {
            return;
        }

        $item = Client::query()->findOrFail($client);
        $this->clientId = $item->id;
        $this->name = $item->name;
        $this->description = $item->description;
        $this->status = $item->status->value;
    }

    public function save()
    {
        abort_unless(auth()->user()?->isAdmin(), 403);

        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'status' => ['required', Rule::enum(ClientStatus::class)],
        ]);

        $client = $this->clientId
            ? Client::query()->findOrFail($this->clientId)
            : new Client;

        $client->fill([
            'name' => trim($data['name']),
            'description' => filled($data['description']) ? trim($data['description']) : null,
            'status' => $data['status'],
        ])->save();

        session()->flash('success', $this->clientId ? __('app.updated_successfully') : __('app.created_successfully'));

        return $this->redirectRoute('clients.show', ['client' => $client->id], navigate: true);
    }

    public function render()
    {
        return view('clients::form')->title($this->clientId ? 'ویرایش مشتری' : 'مشتری جدید');
    }
}
