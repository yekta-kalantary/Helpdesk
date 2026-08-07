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
            ->join('people as customer_people', 'customer_people.id', '=', 'customers.person_id')
            ->leftJoin('projects', 'projects.id', '=', 'tickets.project_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'tickets.assigned_to')
            ->leftJoin('people as assignee_people', 'assignee_people.id', '=', 'assignees.person_id')
            ->whereNull('tickets.deleted_at')
            ->select([
                'tickets.id', 'tickets.customer_id', 'tickets.project_id', 'tickets.subject', 'tickets.category',
                'tickets.priority', 'tickets.status', 'tickets.updated_at', 'tickets.assigned_to',
                'customer_people.first_name as customer_first_name', 'customer_people.last_name as customer_last_name',
                'projects.title as project_title',
                'assignee_people.first_name as assignee_first_name', 'assignee_people.last_name as assignee_last_name',
            ]);

        $this->applyDbScope($query, $scope);

        return $query
            ->when($projectId, fn ($builder) => $builder->where('tickets.project_id', $projectId))
            ->when($term, fn ($builder) => $builder->where('tickets.subject', 'like', "%{$term}%"))
            ->orderByDesc('tickets.updated_at')
            ->get()
            ->map(fn (object $row) => [
                'id' => $row->id,
                'customer_id' => $row->customer_id,
                'project_id' => $row->project_id,
                'subject' => $row->subject,
                'category' => $row->category,
                'priority' => $row->priority,
                'status' => $row->status,
                'updated_at' => $row->updated_at,
                'customer_name' => trim($row->customer_first_name.' '.$row->customer_last_name),
                'project_title' => $row->project_title,
                'assignee_name' => $row->assigned_to
                    ? trim(($row->assignee_first_name ?? '').' '.($row->assignee_last_name ?? ''))
                    : null,
            ])
            ->all();
    }

    public function findAccessible(int $id, array $scope): array
    {
        $query = Ticket::query()
            ->with(['assignee.person', 'creator.person', 'messages.user.person'])
            ->whereKey($id);

        $this->applyEloquentScope($query, $scope);
        $ticket = $query->firstOrFail();

        $customer = DB::table('customers')
            ->join('people', 'people.id', '=', 'customers.person_id')
            ->where('customers.id', $ticket->customer_id)
            ->first(['people.first_name', 'people.last_name']);
        $projectTitle = $ticket->project_id ? DB::table('projects')->where('id', $ticket->project_id)->value('title') : null;

        return [
            'id' => $ticket->id,
            'customer_id' => $ticket->customer_id,
            'customer_name' => $customer ? trim($customer->first_name.' '.$customer->last_name) : null,
            'project_id' => $ticket->project_id,
            'project_title' => $projectTitle,
            'created_by' => $ticket->created_by,
            'creator_name' => $ticket->creator?->full_name,
            'assigned_to' => $ticket->assigned_to,
            'assignee_name' => $ticket->assignee?->full_name,
            'subject' => $ticket->subject,
            'category' => $ticket->category->value,
            'priority' => $ticket->priority->value,
            'status' => $ticket->status->value,
            'messages' => $ticket->messages->map(fn (TicketMessage $message) => [
                'id' => $message->id,
                'user_id' => $message->user_id,
                'user_name' => $message->user?->full_name,
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
