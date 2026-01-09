<?php

namespace App\Enums;

enum ProjectRole: string
{
    case Owner = 'owner';
    case Admin = 'admin';
    case Editor = 'editor';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Editor => 'Editor',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageTeam(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canManageSettings(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canEditRecords(): bool
    {
        return in_array($this, [self::Owner, self::Admin, self::Editor]);
    }

    public function canDeleteRecords(): bool
    {
        return in_array($this, [self::Owner, self::Admin]);
    }

    public function canViewTables(): bool
    {
        return true;
    }

    /**
     * Get roles that can be assigned (excludes Owner).
     *
     * @return array<ProjectRole>
     */
    public static function assignable(): array
    {
        return [self::Admin, self::Editor, self::Viewer];
    }
}
