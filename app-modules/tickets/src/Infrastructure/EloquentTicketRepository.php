<?php

namespace Modules\Tickets\Infrastructure;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Support\Facades\DB;
use Modules\Tickets\Domain\Contracts\TicketRepository;
use Modules\Tickets\Infrastructure\Models\Ticket;
use Modules\Tickets\Infrastructure\Models\TicketMessage;

class EloquentTicketRepository implements TicketRepository
{
    public function search(array $scope, ?int $projectId = null, ?string $term = null): array
    {
        $query = DB::table('tickets')
            ->join('customers', 'customers.id', '=', 'tickets.customer_id')
            ->leftJoin('projects', 'projects.id', '=', 'tickets.project_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'tickets.assigned_to')
            ->whereNull('tickets.deleted_at')
            ->select([
                'tickets.id', 'tickets.customer_id', 'tickets.project_id', 'tickets.subject', 'tickets.category',
                'tickets.priority', 'tickets.status', 'tickets.updated_at', 'customers.name as customer_name',
                'projects.title as project_title', 'assignees.name as assignee_name',
            ]);

        $this->applyDbScope($query, $scope);

        return $query
            ->when($projectId, fn ($builder) => $builder->where('tickets.project_id', $projectId))
            ->when($term, fn ($builder) => $builder->where('tickets.subject', 'like', "%{$term}%"))
            ->orderByDesc('tickets.updated_at')
            ->get()
            ->map(fn (object $row) => (array) $row)
            ->all();
    }

    public function findAccessible(int $id, array $scope): array
    {
        $query = Ticket::query()
            ->with(['assignee:id,name', 'creator:id,name', 'messages.user:id,name'])
            ->whereKey($id);

        $this->applyEloquentScope($query, $scope);
        $ticket = $query->firstOrFail();

        $customerName = DB::table('customers')->where('id', $ticket->customer_id)->value('name');
        $projectTitle = $ticket->project_id ? DB::table('projects')->where('id', $ticket->project_id)->value('title') : null;

        return [
            'id' => $ticket->id,
            'customer_id' => $ticket->customer_id,
            'customer_name' => $customerName,
            'project_id' => $ticket->project_id,
            'project_title' => $projectTitle,
            'created_by' => $ticket->created_by,
            'creator_name' => $ticket->creator?->name,
            'assigned_to' => $ticket->assigned_to,
            'assignee_name' => $ticket->assignee?->name,
            'subject' => $ticket->subject,
            'category' => $ticket->category->value,
            'priority' => $ticket->priority->value,
            'status' => $ticket->status->value,
            'messages' => $ticket->messages->map(fn (TicketMessage $message) => [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'user_name' => $message->user?->name,
                'body' => $message->body,
                'created_at' => $message->created_at,
                'attachments' => $message->getMedia('attachments')->map(fn ($media) => [
                    'id' => $media->id,
                    'name' => $media->file_name,
                    'size' => $media->size,
                ])->all(),
            ])->all(),
        ];
    }

    public function create(array $attributes): int
    {
        return Ticket::create($attributes)->id;
    }

    public function updateManagement(int $id, string $status, ?int $assignedTo): void
    {
        Ticket::query()->findOrFail($id)->update(['status' => $status, 'assigned_to' => $assignedTo]);
    }

    public function addMessage(int $ticketId, int $userId, string $body): int
    {
        $message = TicketMessage::create(['ticket_id' => $ticketId, 'user_id' => $userId, 'body' => $body]);
        Ticket::query()->whereKey($ticketId)->touch();

        return $message->id;
    }

    public function updateStatus(int $id, string $status): void
    {
        Ticket::query()->findOrFail($id)->update(['status' => $status]);
    }

    public function delete(int $id): void
    {
        Ticket::query()->findOrFail($id)->delete();
    }

    private function applyDbScope($query, array $scope): void
    {
        if ($scope['customer_id']) {
            $query->where('tickets.customer_id', $scope['customer_id']);

            return;
        }

        if (! $scope['manage_all']) {
            $query->where(function ($nested) use ($scope): void {
                $nested->where('tickets.assigned_to', $scope['actor_id'])
                    ->orWhereExists(fn ($sub) => $sub->selectRaw('1')->from('project_user')
                        ->whereColumn('project_user.project_id', 'tickets.project_id')
                        ->where('project_user.user_id', $scope['actor_id']));
            });
        }
    }

    private function applyEloquentScope(EloquentBuilder $query, array $scope): void
    {
        if ($scope['customer_id']) {
            $query->where('customer_id', $scope['customer_id']);

            return;
        }

        if (! $scope['manage_all']) {
            $query->where(function (EloquentBuilder $nested) use ($scope): void {
                $nested->where('assigned_to', $scope['actor_id'])
                    ->orWhereIn('project_id', DB::table('project_user')->where('user_id', $scope['actor_id'])->select('project_id'));
            });
        }
    }
}
