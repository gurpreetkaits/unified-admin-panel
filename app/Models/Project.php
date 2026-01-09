<?php

namespace App\Models;

use App\Enums\ProjectRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'db_driver',
        'db_host',
        'db_port',
        'db_database',
        'db_username',
        'db_password',
        'users_table',
        'feedbacks_table',
        'pinned_tables',
        'is_connected',
    ];

    protected $hidden = [
        'db_password',
    ];

    protected function casts(): array
    {
        return [
            'db_password' => 'encrypted',
            'db_port' => 'integer',
            'is_connected' => 'boolean',
            'pinned_tables' => 'array',
        ];
    }

    public function hasDatabase(): bool
    {
        return ! empty($this->db_host) && ! empty($this->db_database);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<ProjectMember, $this>
     */
    public function members(): HasMany
    {
        return $this->hasMany(ProjectMember::class);
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'project_members')
            ->withPivot(['role', 'invited_at', 'accepted_at'])
            ->withTimestamps();
    }

    /**
     * @return HasMany<ProjectInvitation, $this>
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class);
    }

    /**
     * @return HasMany<ProjectInvitation, $this>
     */
    public function pendingInvitations(): HasMany
    {
        return $this->hasMany(ProjectInvitation::class)
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now());
    }

    public function getMemberRole(User $user): ?ProjectRole
    {
        if ($this->user_id === $user->id) {
            return ProjectRole::Owner;
        }

        $member = $this->members()->where('user_id', $user->id)->first();

        return $member?->role;
    }

    public function hasMember(User $user): bool
    {
        return $this->user_id === $user->id
            || $this->members()->where('user_id', $user->id)->whereNotNull('accepted_at')->exists();
    }
}
