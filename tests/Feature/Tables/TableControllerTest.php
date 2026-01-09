<?php

use App\Models\Project;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('tables index', function () {
    test('guests are redirected to login', function () {
        $this->get(route('tables.index'))->assertRedirect(route('login'));
    });

    test('authenticated users can access tables index', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->component('tables/index'));
    });

    test('shows no database message when project has no database', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tables/index')
                ->where('hasDatabase', false)
                ->where('tables', [])
            );
    });

    test('returns filters in response', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', [
                'search' => 'users',
                'sort' => 'name',
                'direction' => 'desc',
            ]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tables/index')
                ->where('filters.search', 'users')
                ->where('filters.sort', 'name')
                ->where('filters.direction', 'desc')
            );
    });

    test('validates sort direction and defaults to asc for invalid values', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['direction' => 'invalid']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.direction', 'asc')
            );
    });

    test('validates sort column and defaults to name for invalid values', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['sort' => 'invalid_column']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.sort', 'name')
            );
    });

    test('passes open tab parameter', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['tab' => 'users']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('openTab', 'users')
            );
    });

    test('returns pinned tables from project', function () {
        $project = Project::factory()
            ->withPinnedTables(['users', 'posts'])
            ->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('pinnedTables', ['users', 'posts'])
            );
    });

    test('allows ascending sort direction', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.direction', 'asc')
            );
    });

    test('allows descending sort direction', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['direction' => 'desc']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.direction', 'desc')
            );
    });

    test('allows sorting by name column', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['sort' => 'name']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.sort', 'name')
            );
    });

    test('allows sorting by row_count column', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.index', ['sort' => 'row_count']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.sort', 'row_count')
            );
    });
});

describe('pin table', function () {
    test('guests cannot pin tables', function () {
        $this->post(route('tables.pin', 'users'))
            ->assertRedirect(route('login'));
    });

    test('returns error when no project selected', function () {
        $this->actingAs($this->user)
            ->postJson(route('tables.pin', 'users'))
            ->assertBadRequest()
            ->assertJson(['success' => false, 'message' => 'No project selected']);
    });

    test('can pin a table when project has no database configured', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->postJson(route('tables.pin', 'users'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('pinned_tables', ['users']);

        expect($project->fresh()->pinned_tables)->toContain('users');
    });

    test('does not duplicate pinned table', function () {
        $project = Project::factory()
            ->withPinnedTables(['users'])
            ->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->postJson(route('tables.pin', 'users'))
            ->assertOk()
            ->assertJsonPath('pinned_tables', ['users']);

        expect($project->fresh()->pinned_tables)->toHaveCount(1);
    });

    test('can pin multiple tables', function () {
        $project = Project::factory()
            ->withPinnedTables(['users'])
            ->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->postJson(route('tables.pin', 'posts'))
            ->assertOk()
            ->assertJson(['success' => true]);

        expect($project->fresh()->pinned_tables)->toContain('users');
        expect($project->fresh()->pinned_tables)->toContain('posts');
        expect($project->fresh()->pinned_tables)->toHaveCount(2);
    });
});

describe('unpin table', function () {
    test('guests cannot unpin tables', function () {
        $this->post(route('tables.unpin', 'users'))
            ->assertRedirect(route('login'));
    });

    test('returns error when no project selected', function () {
        $this->actingAs($this->user)
            ->postJson(route('tables.unpin', 'users'))
            ->assertBadRequest()
            ->assertJson(['success' => false, 'message' => 'No project selected']);
    });

    test('can unpin a table', function () {
        $project = Project::factory()
            ->withPinnedTables(['users', 'posts'])
            ->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->postJson(route('tables.unpin', 'users'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('pinned_tables', ['posts']);

        expect($project->fresh()->pinned_tables)->not->toContain('users');
        expect($project->fresh()->pinned_tables)->toContain('posts');
    });

    test('unpinning non-pinned table is idempotent', function () {
        $project = Project::factory()
            ->withPinnedTables(['posts'])
            ->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->postJson(route('tables.unpin', 'users'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('pinned_tables', ['posts']);
    });

    test('can unpin all tables', function () {
        $project = Project::factory()
            ->withPinnedTables(['users'])
            ->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->postJson(route('tables.unpin', 'users'))
            ->assertOk()
            ->assertJson(['success' => true])
            ->assertJsonPath('pinned_tables', []);

        expect($project->fresh()->pinned_tables)->toBeEmpty();
    });
});

describe('table data endpoint', function () {
    test('guests cannot access data endpoint', function () {
        $this->get(route('tables.data', 'users'))
            ->assertRedirect(route('login'));
    });

    test('validates sort direction parameter', function () {
        $project = Project::factory()->create();

        // Without database, the endpoint still validates direction
        $response = $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->getJson(route('tables.data', ['table' => 'users', 'direction' => 'invalid']));

        // The direction should be normalized to 'desc' (default)
        $response->assertOk();
    });

    test('accepts valid sort directions', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->getJson(route('tables.data', ['table' => 'users', 'direction' => 'asc']))
            ->assertOk();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->getJson(route('tables.data', ['table' => 'users', 'direction' => 'desc']))
            ->assertOk();
    });
});

describe('table update endpoint', function () {
    test('guests cannot update records', function () {
        $this->put(route('tables.update', 'users'))
            ->assertRedirect(route('login'));
    });

    test('returns error when no project selected', function () {
        $this->actingAs($this->user)
            ->putJson(route('tables.update', 'users'), [
                'id' => 1,
                'data' => ['name' => 'Updated'],
            ])
            ->assertBadRequest()
            ->assertJson(['success' => false, 'message' => 'No project selected']);
    });

    test('returns error when project has no database configured', function () {
        $project = Project::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->putJson(route('tables.update', 'users'), [
                'id' => 1,
                'data' => ['name' => 'Updated'],
            ])
            ->assertBadRequest()
            ->assertJson(['success' => false, 'message' => 'No database configured']);
    });
});

describe('table show page', function () {
    test('guests cannot access table show page', function () {
        $this->get(route('tables.show', 'users'))
            ->assertRedirect(route('login'));
    });

    test('authenticated users can access table show page', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.show', 'users'))
            ->assertOk();
    });

    test('returns filters in show page response', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.show', ['table' => 'users', 'search' => 'test', 'direction' => 'asc']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('tables/show')
                ->where('filters.search', 'test')
                ->where('filters.direction', 'asc')
            );
    });

    test('validates sort direction in show page', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.show', ['table' => 'users', 'direction' => 'invalid']))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('filters.direction', 'desc')
            );
    });
});

describe('table record page', function () {
    test('guests cannot access record page', function () {
        $this->get(route('tables.record', ['table' => 'users', 'id' => 1]))
            ->assertRedirect(route('login'));
    });

    test('authenticated users can access record page', function () {
        $project = Project::factory()->create();

        $this->actingAs($this->user)
            ->withSession(['current_project_id' => $project->id])
            ->get(route('tables.record', ['table' => 'users', 'id' => 1]))
            ->assertOk();
    });
});

describe('project model', function () {
    test('hasDatabase returns false when no db_host', function () {
        $project = Project::factory()->create([
            'db_host' => null,
            'db_database' => 'test_db',
        ]);

        expect($project->hasDatabase())->toBeFalse();
    });

    test('hasDatabase returns false when no db_database', function () {
        $project = Project::factory()->create([
            'db_host' => 'localhost',
            'db_database' => null,
        ]);

        expect($project->hasDatabase())->toBeFalse();
    });

    test('hasDatabase returns true when both host and database are set', function () {
        $project = Project::factory()->withDatabase()->create();

        expect($project->hasDatabase())->toBeTrue();
    });

    test('pinned_tables is cast to array', function () {
        $project = Project::factory()
            ->withPinnedTables(['users', 'posts'])
            ->create();

        expect($project->pinned_tables)->toBeArray();
        expect($project->pinned_tables)->toContain('users');
        expect($project->pinned_tables)->toContain('posts');
    });

    test('pinned_tables defaults to empty array', function () {
        $project = Project::factory()->create();

        expect($project->pinned_tables)->toBeArray();
        expect($project->pinned_tables)->toBeEmpty();
    });
});
