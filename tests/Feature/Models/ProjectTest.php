<?php

use App\Models\Project;
use App\Models\User;

test('project belongs to a user', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    expect($project->user)->toBeInstanceOf(User::class);
    expect($project->user->id)->toBe($user->id);
});

test('project can exist without a user', function () {
    $project = Project::factory()->create(['user_id' => null]);

    expect($project->user)->toBeNull();
});

test('user has many projects', function () {
    $user = User::factory()->create();
    Project::factory()->count(3)->create(['user_id' => $user->id]);

    expect($user->projects)->toHaveCount(3);
    expect($user->projects->first())->toBeInstanceOf(Project::class);
});

test('deleting user sets project user_id to null', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $user->delete();

    $project->refresh();
    expect($project->user_id)->toBeNull();
});
