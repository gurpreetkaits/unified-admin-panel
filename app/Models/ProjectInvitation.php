<?php

namespace App\Models;

use App\Enums\ProjectRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class ProjectInvitation extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'email',
        'role',
        'token',
        'expires_at',
        'accepted_at',
        'invited_by',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'role' => ProjectRole::class,
            'expires_at' => 'datetime',
            'accepted_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
    }

    public static function generateToken(): string
    {
        return Str::random(64);
    }

    /**
     * Additional projects this invitation grants access to.
     *
     * @return BelongsToMany<Project, $this>
     */
    public function additionalProjects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_invitation_projects')
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Get all projects (primary + additional) this invitation grants access to.
     *
     * @return array<int, array{project_id: int, role: ProjectRole}>
     */
    public function getAllProjectAccess(): array
    {
        $access = [
            ['project_id' => $this->project_id, 'role' => $this->role],
        ];

        foreach ($this->additionalProjects as $project) {
            $access[] = [
                'project_id' => $project->id,
                'role' => ProjectRole::from($project->pivot->role),
            ];
        }

        return $access;
    }
}
