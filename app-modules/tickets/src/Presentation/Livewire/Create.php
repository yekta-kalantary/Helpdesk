<?php

namespace Modules\Tickets\Presentation\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Tickets\Application\Actions\CreateTicket;
use Modules\Tickets\Application\Queries\TicketAccessScope;
use Modules\Tickets\Application\Queries\TicketFormOptions;
use Modules\Tickets\Domain\Enums\TicketCategory;
use Modules\Tickets\Domain\Enums\TicketPriority;
use Modules\Tickets\Domain\Enums\TicketStatus;

class Create extends Component
{
    use WithFileUploads;

    public ?int $customer_id = null;

    public ?int $project_id = null;

    public string $subject = '';

    public string $category = 'general';

    public string $priority = 'medium';

    public string $body = '';

    public array $attachments = [];

    protected TicketAccessScope $scopeBuilder;

    protected TicketFormOptions $formOptions;

    protected CreateTicket $createTicket;

    public function boot(
        TicketAccessScope $scopeBuilder,
        TicketFormOptions $formOptions,
        CreateTicket $createTicket,
    ): void {
        $this->scopeBuilder = $scopeBuilder;
        $this->formOptions = $formOptions;
        $this->createTicket = $createTicket;
    }

    public function mount(): void
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        $this->customer_id = $scope['customer_id'] ? (int) $scope['customer_id'] : null;
        $project = request()->integer('project');
        $this->project_id = $project ?: null;
    }

    public function save()
    {
        abort_unless(auth()->user()?->can('tickets.create'), 403);

        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);
        $customerActor = (bool) $scope['customer_id'];
        $data = $this->validate($this->rulesFor($customerActor));

        $customerId = $customerActor ? (int) $scope['customer_id'] : (int) $data['customer_id'];
        $projectId = $data['project_id'] ? (int) $data['project_id'] : null;

        abort_unless($customerId, 403);
        $this->assertProjectBelongsToCustomer($projectId, $customerId);
        $this->assertStaffCustomerAccess($customerId, $projectId, $scope);

        $ticketId = $this->createTicket->execute([
            'customer_id' => $customerId,
            'project_id' => $projectId,
            'created_by' => $user->id,
            'assigned_to' => $customerActor ? null : $user->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => TicketStatus::Open->value,
        ], $user->id, $data['body'], $this->attachments, $customerActor);

        session()->flash('success', __('app.created_successfully'));

        return $this->redirectRoute('tickets.show', ['ticket' => $ticketId], navigate: true);
    }

    private function rulesFor(bool $customerActor): array
    {
        return [
            'customer_id' => [$customerActor ? 'nullable' : 'required', 'integer', 'exists:customers,id'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(TicketCategory::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'body' => ['required', 'string', 'max:10000'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ];
    }

    private function assertProjectBelongsToCustomer(?int $projectId, int $customerId): void
    {
        if (! $projectId) {
            return;
        }

        abort_unless(
            DB::table('projects')
                ->where('id', $projectId)
                ->where('customer_id', $customerId)
                ->whereNull('deleted_at')
                ->exists(),
            422,
        );
    }

    private function assertStaffCustomerAccess(int $customerId, ?int $projectId, array $scope): void
    {
        if ($scope['customer_id'] || $scope['manage_all']) {
            return;
        }

        $actorId = $scope['actor_id'];

        $hasCustomerAccess = DB::table('projects')
            ->join('project_user', 'project_user.project_id', '=', 'projects.id')
            ->where('projects.customer_id', $customerId)
            ->where('project_user.user_id', $actorId)
            ->whereNull('projects.deleted_at')
            ->exists();

        abort_unless($hasCustomerAccess, 403);

        if (! $projectId) {
            return;
        }

        abort_unless(
            DB::table('project_user')
                ->join('projects', 'projects.id', '=', 'project_user.project_id')
                ->where('project_user.project_id', $projectId)
                ->where('project_user.user_id', $actorId)
                ->where('projects.customer_id', $customerId)
                ->whereNull('projects.deleted_at')
                ->exists(),
            403,
        );
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tickets::create', [
            'scope' => $scope,
            'options' => $this->formOptions->get($user, $scope),
            'categories' => TicketCategory::cases(),
            'priorities' => TicketPriority::cases(),
        ])->title(__('tickets::messages.new_ticket'));
    }
}
