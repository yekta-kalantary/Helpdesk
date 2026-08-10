<?php

use App\Models\Contact;
use App\Models\User;
use Illuminate\Support\Facades\Route;
use Modules\Projects\Infrastructure\Models\Project;

it('exposes only retained business routes', function (): void {
    $admin = User::query()->with('contact')->firstOrFail();

    $this->actingAs($admin)
        ->get(route('contacts.index'))
        ->assertOk();

    $this->get(route('projects.index'))->assertOk();
    $this->get(route('tasks.index'))->assertOk();
    $this->get(route('users.index'))->assertOk();
    $this->get(route('roles.index'))->assertOk();

    expect(Route::has('customers.index'))->toBeFalse()
        ->and(Route::has('tickets.index'))->toBeFalse()
        ->and(Route::has('reports.index'))->toBeFalse()
        ->and(Route::has('settings.smtp.edit'))->toBeFalse()
        ->and(Route::has('notifications.index'))->toBeFalse()
        ->and(Route::has('dashboard'))->toBeFalse();
});

it('uses contacts as the identity source', function (): void {
    $contact = Contact::factory()->create([
        'first_name' => 'Yekta',
        'last_name' => 'Kalantary',
        'email' => 'yekta@example.test',
        'mobile' => '09120000000',
        'province' => 'Tehran',
        'city' => 'Tehran',
        'postal_code' => '1234567890',
    ]);

    $user = User::factory()->create(['contact_id' => $contact->id]);

    expect($user->contact_id)->toBe($contact->id)
        ->and($user->full_name)->toBe('Yekta Kalantary')
        ->and($user->email)->toBe('yekta@example.test');
});

it('links contact projects directly to contacts', function (): void {
    $contact = Contact::factory()->create();

    $project = Project::query()->create([
        'contact_id' => $contact->id,
        'category' => 'contact',
        'title' => 'Example project',
        'type' => 'other',
        'status' => 'planning',
    ]);

    expect($project->contact_id)->toBe($contact->id)
        ->and($project->contact()->is($contact))->toBeTrue();
});
