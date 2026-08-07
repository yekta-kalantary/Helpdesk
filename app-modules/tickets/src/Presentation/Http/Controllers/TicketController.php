<?php

namespace Modules\Tickets\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Tickets\Application\Actions\CreateTicket;
use Modules\Tickets\Application\Actions\ReplyTicket;
use Modules\Tickets\Application\Queries\TicketAccessScope;
use Modules\Tickets\Application\Queries\TicketFormOptions;
use Modules\Tickets\Domain\Contracts\TicketAttachmentStore;
use Modules\Tickets\Domain\Contracts\TicketRepository;
use Modules\Tickets\Domain\Enums\TicketCategory;
use Modules\Tickets\Domain\Enums\TicketPriority;
use Modules\Tickets\Domain\Enums\TicketStatus;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TicketController extends Controller
{
    public function __construct(
        private readonly TicketRepository $tickets,
        private readonly TicketAccessScope $scopeBuilder,
        private readonly TicketFormOptions $formOptions,
        private readonly CreateTicket $createTicket,
        private readonly ReplyTicket $replyTicket,
        private readonly TicketAttachmentStore $attachments,
    ) {
    }

    public function index(Request $request): View
    {
        return view('tickets::index', [
            'tickets' => $this->tickets->search($this->scope(), $request->integer('project') ?: null, $request->string('q')->trim()->value() ?: null),
        ]);
    }

    public function create(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tickets::create', [
            'scope' => $scope,
            'options' => $this->formOptions->get($user, $scope),
            'categories' => TicketCategory::cases(),
            'priorities' => TicketPriority::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);
        $customerActor = (bool) $scope['customer_id'];

        $data = $request->validate([
            'customer_id' => [$customerActor ? 'nullable' : 'required', 'integer', 'exists:customers,id'],
            'project_id' => ['nullable', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::enum(TicketCategory::class)],
            'priority' => ['required', Rule::enum(TicketPriority::class)],
            'body' => ['required', 'string', 'max:10000'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $customerId = $customerActor ? $scope['customer_id'] : (int) $data['customer_id'];
        abort_unless($customerId, 403);
        $this->assertProjectBelongsToCustomer($data['project_id'] ?? null, (int) $customerId);

        $ticketId = $this->createTicket->execute([
            'customer_id' => $customerId,
            'project_id' => $data['project_id'] ?? null,
            'created_by' => $user->id,
            'assigned_to' => $customerActor ? null : $user->id,
            'subject' => $data['subject'],
            'category' => $data['category'],
            'priority' => $data['priority'],
            'status' => TicketStatus::Open->value,
        ], $user->id, $data['body'], $request->file('attachments', []), $customerActor);

        return redirect()->route('tickets.show', $ticketId)->with('success', __('app.created_successfully'));
    }

    public function show(int $ticket): View
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tickets::show', [
            'ticket' => $this->tickets->findAccessible($ticket, $scope),
            'scope' => $scope,
            'options' => $this->formOptions->get($user, $scope),
            'statuses' => TicketStatus::cases(),
        ]);
    }

    public function reply(Request $request, int $ticket): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);
        $current = $this->tickets->findAccessible($ticket, $scope);
        $data = $request->validate([
            'body' => ['required', 'string', 'max:10000'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ]);

        $this->replyTicket->execute($ticket, $user->id, $data['body'], $request->file('attachments', []), (bool) $scope['customer_id'], $current['subject']);

        return back()->with('success', __('app.created_successfully'));
    }

    public function manage(Request $request, int $ticket): RedirectResponse
    {
        $this->tickets->findAccessible($ticket, $this->scope());
        $data = $request->validate([
            'status' => ['required', Rule::enum(TicketStatus::class)],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
        ]);

        if (! empty($data['assigned_to'])) {
            $assignee = User::query()->findOrFail($data['assigned_to']);
            abort_if(! $assignee->is_active || $assignee->hasRole('customer'), 422);
        }

        $this->tickets->updateManagement($ticket, $data['status'], $data['assigned_to'] ?? null);

        return back()->with('success', __('app.updated_successfully'));
    }

    public function destroy(int $ticket): RedirectResponse
    {
        $this->tickets->findAccessible($ticket, $this->scope());
        $this->tickets->delete($ticket);

        return redirect()->route('tickets.index')->with('success', __('app.deleted_successfully'));
    }

    public function download(int $ticket, int $message, int $media): BinaryFileResponse
    {
        $this->tickets->findAccessible($ticket, $this->scope());
        $file = $this->attachments->get($ticket, $message, $media);

        return response()->download($file['path'], $file['name'], ['Content-Type' => $file['mime_type'] ?? 'application/octet-stream']);
    }

    private function scope(): array
    {
        /** @var User $user */
        $user = auth()->user();
        return $this->scopeBuilder->for($user);
    }

    private function assertProjectBelongsToCustomer(?int $projectId, int $customerId): void
    {
        if (! $projectId) {
            return;
        }

        abort_unless(DB::table('projects')->where('id', $projectId)->where('customer_id', $customerId)->whereNull('deleted_at')->exists(), 422);
    }
}
