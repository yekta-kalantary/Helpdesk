<?php

namespace Modules\Projects\Presentation\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Modules\Projects\Application\Actions\SaveProject;
use Modules\Projects\Application\Queries\ProjectFormOptions;
use Modules\Projects\Domain\Contracts\ProjectRepository;
use Modules\Projects\Domain\Enums\ProjectStatus;
use Modules\Projects\Domain\Enums\ProjectType;

class ProjectController extends Controller
{
    public function __construct(
        private readonly ProjectRepository $projects,
        private readonly SaveProject $saveProject,
        private readonly ProjectFormOptions $options,
    ) {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = auth()->user();
        $customerId = $user->hasRole('customer')
            ? DB::table('customers')->where('user_id', $user->id)->whereNull('deleted_at')->value('id')
            : null;

        return view('projects::index', [
            'projects' => $this->projects->search($request->string('q')->trim()->value() ?: null, $customerId),
        ]);
    }

    public function create(): View
    {
        return view('projects::form', [
            'project' => null,
            'options' => $this->options->get(),
            'statuses' => ProjectStatus::cases(),
            'types' => ProjectType::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateProject($request);
        $this->saveProject->execute(null, $this->attributes($data), $this->sanitizeMembers($data['member_ids'] ?? []));

        return redirect()->route('projects.index')->with('success', __('app.created_successfully'));
    }

    public function edit(int $project): View
    {
        return view('projects::form', [
            'project' => $this->projects->find($project),
            'options' => $this->options->get(),
            'statuses' => ProjectStatus::cases(),
            'types' => ProjectType::cases(),
        ]);
    }

    public function update(Request $request, int $project): RedirectResponse
    {
        $data = $this->validateProject($request);
        $this->saveProject->execute($project, $this->attributes($data), $this->sanitizeMembers($data['member_ids'] ?? []));

        return redirect()->route('projects.index')->with('success', __('app.updated_successfully'));
    }

    public function destroy(int $project): RedirectResponse
    {
        $this->projects->delete($project);

        return redirect()->route('projects.index')->with('success', __('app.deleted_successfully'));
    }

    private function validateProject(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', Rule::exists('customers', 'id')->whereNull('deleted_at')],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ProjectType::class)],
            'status' => ['required', Rule::enum(ProjectStatus::class)],
            'description' => ['nullable', 'string', 'max:10000'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
            'member_ids' => ['array'],
            'member_ids.*' => ['integer', 'exists:users,id'],
        ]);
    }

    private function attributes(array $data): array
    {
        return [
            'customer_id' => $data['customer_id'],
            'title' => $data['title'],
            'type' => $data['type'],
            'status' => $data['status'],
            'description' => $data['description'] ?? null,
            'starts_at' => $data['starts_at'] ?? null,
            'ends_at' => $data['ends_at'] ?? null,
        ];
    }

    private function sanitizeMembers(array $ids): array
    {
        return User::query()
            ->whereIn('id', $ids)
            ->whereDoesntHave('roles', fn ($query) => $query->where('name', 'customer'))
            ->pluck('id')
            ->all();
    }
}
