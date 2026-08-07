<?php

namespace Modules\Tickets\Presentation\Livewire;

use App\Enums\PersonType;
use App\Models\User;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Tickets\Application\Actions\ReplyTicket;
use Modules\Tickets\Application\Queries\TicketAccessScope;
use Modules\Tickets\Application\Queries\TicketFormOptions;
use Modules\Tickets\Domain\Contracts\TicketRepository;
use Modules\Tickets\Domain\Enums\TicketStatus;

class Show extends Component
{
    use WithFileUploads;

    #[Locked]
    public int $ticketId;

    public string $replyBody = '';

    public array $replyAttachments = [];

    public string $status = 'open';

    public ?int $assigned_to = null;

    protected TicketRepository $tickets;

    protected TicketAccessScope $scopeBuilder;

    protected TicketFormOptions $formOptions;

    protected ReplyTicket $replyTicket;

    public function boot(
        TicketRepository $tickets,
        TicketAccessScope $scopeBuilder,
        TicketFormOptions $formOptions,
        ReplyTicket $replyTicket,
    ): void {
        $this->tickets = $tickets;
        $this->scopeBuilder = $scopeBuilder;
        $this->formOptions = $formOptions;
        $this->replyTicket = $replyTicket;
    }

    public function mount(int $ticket): void
    {
        $item = $this->tickets->findAccessible($ticket, $this->scope());
        $this->ticketId = $ticket;
        $this->status = $item['status'];
        $this->assigned_to = $item['assigned_to'] ? (int) $item['assigned_to'] : null;
    }

    public function reply(): void
    {
        abort_unless(auth()->user()?->can('tickets.reply'), 403);

        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scope();
        $current = $this->tickets->findAccessible($this->ticketId, $scope);
        $data = $this->validate([
            'replyBody' => ['required', 'string', 'max:10000'],
            'replyAttachments' => ['array', 'max:10'],
            'replyAttachments.*' => ['file', 'max:10240'],
        ]);

        $this->replyTicket->execute(
            $this->ticketId,
            $user->id,
            $data['replyBody'],
            $this->replyAttachments,
            (bool) $scope['customer_id'],
            $current['subject'],
        );

        $this->reset('replyBody', 'replyAttachments');
        session()->flash('success', __('app.created_successfully'));
    }

    public function manage(): void
    {
        abort_unless(auth()->user()?->can('tickets.manage'), 403);
        $this->tickets->findAccessible($this->ticketId, $this->scope());

        $data = $this->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (! empty($data['assigned_to'])) {
            $assignee = User::query()->findOrFail($data['assigned_to']);
            abort_if(! $assignee->is_active || $assignee->person?->type !== PersonType::Employee, 422);
        }

        $this->tickets->updateManagement($this->ticketId, $data['status'], $data['assigned_to'] ?: null);
        session()->flash('success', __('app.updated_successfully'));
    }

    public function deleteTicket()
    {
        abort_unless(auth()->user()?->can('tickets.delete'), 403);
        $this->tickets->findAccessible($this->ticketId, $this->scope());
        $this->tickets->delete($this->ticketId);
        session()->flash('success', __('app.deleted_successfully'));

        return $this->redirectRoute('tickets.index', navigate: true);
    }

    private function scope(): array
    {
        /** @var User $user */
        $user = auth()->user();

        return $this->scopeBuilder->for($user);
    }

    public function render()
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);
        $ticket = $this->tickets->findAccessible($this->ticketId, $scope);

        return view('tickets::show', [
            'ticket' => $ticket,
            'scope' => $scope,
            'options' => $this->formOptions->get($user, $scope),
            'statuses' => TicketStatus::cases(),
        ])->title($ticket['subject']);
    }
}
