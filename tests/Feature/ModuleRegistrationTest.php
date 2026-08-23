<?php

use Illuminate\Foundation\Http\Kernel;
use Illuminate\Support\Facades\Gate;
use Modules\Clients\Infrastructure\Models\Client;
use Modules\Clients\Presentation\Policies\ClientPolicy;
use Modules\Identity\Presentation\Http\Middleware\EnsureAccountActive;
use Modules\Projects\Infrastructure\Models\Project;
use Modules\Projects\Presentation\Policies\ProjectPolicy;
use Modules\Tasks\Infrastructure\Models\Attachment;
use Modules\Tasks\Infrastructure\Models\Task;
use Modules\Tasks\Presentation\Policies\AttachmentPolicy;
use Modules\Tasks\Presentation\Policies\TaskPolicy;

it('resolves every gate policy to its owning module', function (): void {
    expect(Gate::getPolicyFor(Client::class))->toBeInstanceOf(ClientPolicy::class)
        ->and(Gate::getPolicyFor(Project::class))->toBeInstanceOf(ProjectPolicy::class)
        ->and(Gate::getPolicyFor(Task::class))->toBeInstanceOf(TaskPolicy::class)
        ->and(Gate::getPolicyFor(Attachment::class))->toBeInstanceOf(AttachmentPolicy::class);
});

it('aliases account.active to the identity owned middleware', function (): void {
    expect(app(Kernel::class)->getMiddlewareAliases()['account.active'])->toBe(EnsureAccountActive::class);
});
