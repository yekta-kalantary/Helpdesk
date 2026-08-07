<?php

namespace Modules\Settings\Presentation\Livewire;

use Illuminate\Validation\Rule;
use Livewire\Component;
use Modules\Settings\Infrastructure\Settings\SmtpSettings;

class Smtp extends Component
{
    public bool $enabled = false;

    public ?string $host = null;

    public int $port = 587;

    public ?string $username = null;

    public string $password = '';

    public ?string $scheme = null;

    public string $from_address = 'helpdesk@example.com';

    public string $from_name = 'Helpdesk';

    public bool $passwordConfigured = false;

    protected SmtpSettings $settings;

    public function boot(SmtpSettings $settings): void
    {
        $this->settings = $settings;
    }

    public function mount(): void
    {
        $this->enabled = $this->settings->enabled;
        $this->host = $this->settings->host;
        $this->port = $this->settings->port;
        $this->username = $this->settings->username;
        $this->passwordConfigured = filled($this->settings->password);
        $this->scheme = $this->settings->scheme;
        $this->from_address = $this->settings->from_address;
        $this->from_name = $this->settings->from_name;
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('settings.manage'), 403);

        $data = $this->validate([
            'enabled' => ['boolean'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1000'],
            'scheme' => ['nullable', Rule::in(['smtp', 'smtps'])],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $this->settings->enabled = $data['enabled'];
        $this->settings->host = $data['host'] ?: null;
        $this->settings->port = (int) $data['port'];
        $this->settings->username = $data['username'] ?: null;

        if (filled($data['password'])) {
            $this->settings->password = $data['password'];
            $this->passwordConfigured = true;
        }

        $this->settings->scheme = $data['scheme'] ?: null;
        $this->settings->from_address = $data['from_address'];
        $this->settings->from_name = $data['from_name'];
        $this->settings->save();

        $this->reset('password');
        session()->flash('success', __('app.updated_successfully'));
    }

    public function render()
    {
        return view('settings::smtp')->title(__('settings::messages.smtp'));
    }
}
