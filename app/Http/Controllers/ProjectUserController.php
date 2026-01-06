<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectDatabaseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectUserController extends Controller
{
    public function index(Request $request): Response
    {
        $projectId = $request->session()->get('current_project_id');
        $project = Project::find($projectId);

        $users = collect();
        $columns = collect();

        if ($project && $project->hasDatabase()) {
            $dbService = new ProjectDatabaseService($project);
            $users = $dbService->getUsers(15);
            $columns = $dbService->getTableColumns($project->users_table ?: 'users');
            $dbService->disconnect();
        }

        return Inertia::render('users/index', [
            'users' => $users,
            'columns' => $columns,
            'hasDatabase' => $project?->hasDatabase() ?? false,
        ]);
    }
}
