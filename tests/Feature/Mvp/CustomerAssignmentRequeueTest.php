<?php

use App\Support\CustomerAssignmentRequeuer;
use DomainException;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Identity\Infrastructure\Models\User;

it('requires an active admin actor for automatic customer assignment requeue', function (): void {
    $client = Client::factory()->create();
    $customer = User::factory()->customer($client)->create();
    $nonAdminActor = User::factory()->customer($client)->create();

    expect(fn () => app(CustomerAssignmentRequeuer::class)->requeue($customer, $nonAdminActor))
        ->toThrow(DomainException::class);
});
