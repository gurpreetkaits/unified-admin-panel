<?php

namespace App\Http\Middleware;

use App\Models\Project;
use App\Services\ProjectDatabaseService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        // Get accessible projects (owned + member of)
        $accessibleProjects = $user
            ? $user->accessibleProjects()->get()
            : collect();

        $currentProjectId = $request->session()->get('current_project_id');
        $currentProject = $currentProjectId
            ? $accessibleProjects->firstWhere('id', $currentProjectId)
            : $accessibleProjects->first();

        // Ensure current project is accessible
        if ($currentProjectId && $currentProject === null) {
            $currentProject = $accessibleProjects->first();
            if ($currentProject) {
                $request->session()->put('current_project_id', $currentProject->id);
            } else {
                $request->session()->forget('current_project_id');
            }
        }

        // Set the current project in session if not set
        if (! $currentProjectId && $currentProject) {
            $request->session()->put('current_project_id', $currentProject->id);
        }

        // Get user's role for current project
        $currentProjectRole = null;
        if ($currentProject && $user) {
            $currentProjectRole = $currentProject->getMemberRole($user)?->value;
        }

        // Get database tables for sidebar
        $databaseTables = [];
        if ($currentProject && $currentProject->hasDatabase()) {
            $dbService = new ProjectDatabaseService($currentProject);
            $result = $dbService->testConnection();
            if ($result['connected']) {
                $databaseTables = $dbService->getTables()->toArray();
            }
            $dbService->disconnect();
        }

        // Get permissions for current project
        $permissions = null;
        if ($currentProject && $user) {
            $permissions = [
                'canViewTables' => $user->can('viewTables', $currentProject),
                'canEditRecords' => $user->can('editRecords', $currentProject),
                'canDeleteRecords' => $user->can('deleteRecords', $currentProject),
                'canManageSettings' => $user->can('manageSettings', $currentProject),
                'canManageTeam' => $user->can('manageTeam', $currentProject),
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'projects' => $accessibleProjects,
            'currentProject' => $currentProject,
            'currentProjectRole' => $currentProjectRole,
            'databaseTables' => $databaseTables,
            'permissions' => $permissions,
        ];
    }
}
