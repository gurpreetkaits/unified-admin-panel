<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
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
}
