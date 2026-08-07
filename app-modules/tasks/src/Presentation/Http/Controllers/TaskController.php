<?php

namespace Modules\Tasks\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Tasks\Application\Actions\SaveTask;
use Modules\Tasks\Application\Queries\TaskAccessScope;
use Modules\Tasks\Application\Queries\TaskFormOptions;
use Modules\Tasks\Domain\Contracts\TaskAttachmentReader;
use Modules\Tasks\Domain\Contracts\TaskAttachmentStore;
use Modules\Tasks\Domain\Contracts\TaskRepository;
use Modules\Tasks\Domain\Enums\TaskPriority;
use Modules\Tasks\Domain\Enums\TaskStatus;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskRepository $tasks,
        private readonly SaveTask $saveTask,
        private readonly TaskAccessScope $scopeBuilder,
        private readonly TaskFormOptions $formOptions,
        private readonly TaskAttachmentReader $attachmentReader,
        private readonly TaskAttachmentStore $attachmentStore,
    ) {
    }

    public function index(Request $request): View
    {
        $scope = $this->scope();
        $projectId = $request->integer('project') ?: null;

        return view('tasks::index', [
            'tasks' => $this->tasks->search($scope, $projectId, $request->string('q')->trim()->value() ?: null),
            'statuses' => TaskStatus::cases(),
            'projectId' => $projectId,
        ]);
    }

    public function create(): View
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tasks::form', [
            'task' => null,
            'options' => $this->formOptions->get($user, $scope),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $scope = $this->scope();
        $data = $this->validateTask($request);
        $this->assertProjectAllowed((int) $data['project_id'], $scope);
        $this->assertAssigneeAllowed($data['assigned_to'] ?? null);

        $taskId = $this->saveTask->execute(null, [
            ...$this->attributes($data),
            'created_by' => auth()->id(),
        ], $request->file('attachments', []));

        return redirect()->route('tasks.show', $taskId)->with('success', __('app.created_successfully'));
    }

    public function show(int $task): View
    {
        $scope = $this->scope();

        return view('tasks::show', [
            'task' => $this->tasks->findAccessible($task, $scope),
            'customerView' => (bool) $scope['customer_id'],
        ]);
    }

    public function edit(int $task): View
    {
        /** @var User $user */
        $user = auth()->user();
        $scope = $this->scopeBuilder->for($user);

        return view('tasks::form', [
            'task' => $this->tasks->findAccessible($task, $scope),
            'options' => $this->formOptions->get($user, $scope),
            'statuses' => TaskStatus::cases(),
            'priorities' => TaskPriority::cases(),
        ]);
    }

    public function update(Request $request, int $task): RedirectResponse
    {
        $scope = $this->scope();
        $current = $this->tasks->findAccessible($task, $scope);
        $data = $this->validateTask($request);
        $this->assertProjectAllowed((int) $data['project_id'], $scope);
        $this->assertAssigneeAllowed($data['assigned_to'] ?? null);

        $this->saveTask->execute($task, $this->attributes($data), $request->file('attachments', []), $current['assigned_to']);

        return redirect()->route('tasks.show', $task)->with('success', __('app.updated_successfully'));
    }

    public function updateStatus(Request $request, int $task): RedirectResponse
    {
        $this->tasks->findAccessible($task, $this->scope());
        $data = $request->validate(['status' => ['required', Rule::enum(TaskStatus::class)]]);
        $this->tasks->update($task, ['status' => $data['status']]);

        return back()->with('success', __('app.updated_successfully'));
    }

    public function destroy(int $task): RedirectResponse
    {
        $this->tasks->findAccessible($task, $this->scope());
        $this->tasks->delete($task);

        return redirect()->route('tasks.index')->with('success', __('app.deleted_successfully'));
    }

    public function comment(Request $request, int $task): RedirectResponse
    {
        $this->tasks->findAccessible($task, $this->scope());
        $data = $request->validate(['body' => ['required', 'string', 'max:5000']]);
        $this->tasks->addComment($task, (int) auth()->id(), $data['body']);

        return back()->with('success', __('app.created_successfully'));
    }

    public function download(int $task, int $media): BinaryFileResponse
    {
        $this->tasks->findAccessible($task, $this->scope());
        $file = $this->attachmentReader->get($task, $media);

        return response()->download($file['path'], $file['name'], ['Content-Type' => $file['mime_type'] ?? 'application/octet-stream']);
    }

    public function deleteAttachment(int $task, int $media): RedirectResponse
    {
        $this->tasks->findAccessible($task, $this->scope());
        $this->attachmentStore->delete($task, $media);

        return back()->with('success', __('app.deleted_successfully'));
    }

    private function validateTask(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'integer', Rule::exists('projects', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'assigned_to' => ['nullable', 'integer', 'exists:users,id'],
            'priority' => ['required', Rule::enum(TaskPriority::class)],
            'status' => ['required', Rule::enum(TaskStatus::class)],
            'is_customer_visible' => ['nullable', 'boolean'],
            'due_at' => ['nullable', 'date'],
            'estimated_minutes' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'spent_minutes' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'attachments' => ['array', 'max:10'],
            'attachments.*' => ['file', 'max:10240'],
        ]);
    }

    private function attributes(array $data): array
    {
        return [
            'project_id' => $data['project_id'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'assigned_to' => $data['assigned_to'] ?? null,
            'priority' => $data['priority'],
            'status' => $data['status'],
            'is_customer_visible' => (bool) ($data['is_customer_visible'] ?? false),
            'due_at' => $data['due_at'] ?? null,
            'estimated_minutes' => $data['estimated_minutes'] ?? null,
            'spent_minutes' => $data['spent_minutes'] ?? null,
        ];
    }

    private function scope(): array
    {
        /** @var User $user */
        $user = auth()->user();
        return $this->scopeBuilder->for($user);
    }

    private function assertProjectAllowed(int $projectId, array $scope): void
    {
        abort_if($scope['customer_id'], 403);

        if ($scope['manage_all']) {
            return;
        }

        abort_unless(
            \Illuminate\Support\Facades\DB::table('project_user')->where('project_id', $projectId)->where('user_id', $scope['actor_id'])->exists(),
            403,
        );
    }

    private function assertAssigneeAllowed(?int $userId): void
    {
        if (! $userId) {
            return;
        }

        $user = User::query()->findOrFail($userId);
        abort_if(! $user->is_active || $user->hasRole('customer'), 422);
    }
}
