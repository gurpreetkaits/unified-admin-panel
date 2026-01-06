<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Database\Connection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProjectDatabaseService
{
    protected ?Connection $connection = null;

    public function __construct(protected Project $project) {}

    public function connect(): ?Connection
    {
        if (! $this->project->hasDatabase()) {
            return null;
        }

        $connectionName = 'project_' . $this->project->id;
        $driver = $this->project->db_driver ?: 'mysql';

        config([
            "database.connections.{$connectionName}" => [
                'driver' => $driver,
                'host' => $this->project->db_host,
                'port' => $this->project->db_port,
                'database' => $this->project->db_database,
                'username' => $this->project->db_username,
                'password' => $this->project->db_password,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => true,
                'engine' => null,
            ],
        ]);

        $this->connection = DB::connection($connectionName);

        return $this->connection;
    }

    /**
     * @return array{connected: bool, error: string|null}
     */
    public function testConnection(): array
    {
        try {
            $connection = $this->connect();
            if (! $connection) {
                return ['connected' => false, 'error' => 'No database configuration provided'];
            }

            $connection->getPdo();

            return ['connected' => true, 'error' => null];
        } catch (\Exception $e) {
            return ['connected' => false, 'error' => $this->sanitizeErrorMessage($e->getMessage())];
        }
    }

    /**
     * Sanitize error messages to avoid exposing sensitive information.
     */
    private function sanitizeErrorMessage(string $message): string
    {
        // Common database error patterns with user-friendly messages
        $patterns = [
            '/SQLSTATE\[HY000\] \[2002\].*/' => 'Connection refused - check host and port',
            '/SQLSTATE\[HY000\] \[1045\].*/' => 'Access denied - check username and password',
            '/SQLSTATE\[HY000\] \[1049\].*/' => 'Unknown database - check database name',
            '/SQLSTATE\[HY000\] \[2005\].*/' => 'Unknown host - check hostname',
            '/SQLSTATE\[08006\].*/' => 'Connection failed - check connection settings',
            '/SQLSTATE\[.*\].*/' => 'Database connection error',
        ];

        foreach ($patterns as $pattern => $replacement) {
            if (preg_match($pattern, $message)) {
                return $replacement;
            }
        }

        // Default: return generic error without exposing details
        return 'Database connection failed';
    }

    /**
     * @return LengthAwarePaginator<object>
     */
    public function getUsers(int $perPage = 15): LengthAwarePaginator
    {
        $connection = $this->connect();

        if (! $connection) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        $table = $this->project->users_table ?: 'users';

        return $connection->table($table)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * @return LengthAwarePaginator<object>
     */
    public function getFeedbacks(int $perPage = 15): LengthAwarePaginator
    {
        $connection = $this->connect();

        if (! $connection || ! $this->project->feedbacks_table) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        return $connection->table($this->project->feedbacks_table)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, object>
     */
    public function getTables(): Collection
    {
        $connection = $this->connect();

        if (! $connection) {
            return collect();
        }

        $tables = $connection->select('SHOW TABLES');
        $key = 'Tables_in_' . $this->project->db_database;

        return collect($tables)->pluck($key);
    }

    /**
     * @return Collection<int, object>
     */
    public function getTableColumns(string $table): Collection
    {
        $connection = $this->connect();

        if (! $connection) {
            return collect();
        }

        return collect($connection->getSchemaBuilder()->getColumnListing($table));
    }

    /**
     * @return LengthAwarePaginator<object>
     */
    public function getTableData(string $table, int $perPage = 15): LengthAwarePaginator
    {
        $connection = $this->connect();

        if (! $connection) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        return $connection->table($table)
            ->paginate($perPage);
    }

    public function getTableRowCount(string $table): int
    {
        $connection = $this->connect();

        if (! $connection) {
            return 0;
        }

        return $connection->table($table)->count();
    }

    public function disconnect(): void
    {
        if ($this->connection) {
            $connectionName = 'project_' . $this->project->id;
            DB::purge($connectionName);
            $this->connection = null;
        }
    }
}
