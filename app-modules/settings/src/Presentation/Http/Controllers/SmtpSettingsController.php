<?php

namespace Modules\Settings\Presentation\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Settings\Infrastructure\Settings\SmtpSettings;

class SmtpSettingsController extends Controller
{
    public function edit(SmtpSettings $settings): View
    {
        return view('settings::smtp', [
            'settings' => [
                'enabled' => $settings->enabled,
                'host' => $settings->host,
                'port' => $settings->port,
                'username' => $settings->username,
                'password_configured' => filled($settings->password),
                'scheme' => $settings->scheme,
                'from_address' => $settings->from_address,
                'from_name' => $settings->from_name,
            ],
        ]);
    }

    public function update(Request $request, SmtpSettings $settings): RedirectResponse
    {
        $data = $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'host' => ['nullable', 'string', 'max:255'],
            'port' => ['required', 'integer', 'min:1', 'max:65535'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'max:1000'],
            'scheme' => ['nullable', Rule::in(['smtp', 'smtps'])],
            'from_address' => ['required', 'email', 'max:255'],
            'from_name' => ['required', 'string', 'max:255'],
        ]);

        $settings->enabled = $request->boolean('enabled');
        $settings->host = $data['host'] ?? null;
        $settings->port = (int) $data['port'];
        $settings->username = $data['username'] ?? null;
        if (filled($data['password'] ?? null)) {
            $settings->password = $data['password'];
        }
        $settings->scheme = $data['scheme'] ?? null;
        $settings->from_address = $data['from_address'];
        $settings->from_name = $data['from_name'];
        $settings->save();

        return back()->with('success', __('app.updated_successfully'));
    }
}
