<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Services\ProjectDatabaseService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    public function index(Request $request): Response
    {
        $projectId = $request->session()->get('current_project_id');
        $project = Project::find($projectId);

        $feedbacks = collect();
        $columns = collect();

        if ($project && $project->hasDatabase() && $project->feedbacks_table) {
            $dbService = new ProjectDatabaseService($project);
            $feedbacks = $dbService->getFeedbacks(15);
            $columns = $dbService->getTableColumns($project->feedbacks_table);
            $dbService->disconnect();
        }

        return Inertia::render('feedbacks/index', [
            'feedbacks' => $feedbacks,
            'columns' => $columns,
            'hasDatabase' => $project?->hasDatabase() ?? false,
            'hasFeedbacksTable' => ! empty($project?->feedbacks_table),
        ]);
    }
}
