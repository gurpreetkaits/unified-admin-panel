<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->company();

        return [
            'user_id' => User::factory(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 10000),
            'description' => fake()->sentence(),
            'db_driver' => 'mysql',
            'db_host' => null,
            'db_port' => 3306,
            'db_database' => null,
            'db_username' => null,
            'db_password' => null,
            'users_table' => 'users',
            'feedbacks_table' => null,
            'pinned_tables' => [],
            'is_connected' => false,
        ];
    }

    /**
     * Indicate that the project has database configuration.
     */
    public function withDatabase(): static
    {
        return $this->state(fn (array $attributes) => [
            'db_host' => 'localhost',
            'db_port' => 3306,
            'db_database' => 'test_database',
            'db_username' => 'test_user',
            'db_password' => 'test_password',
            'is_connected' => true,
        ]);
    }

    /**
     * Indicate that the project has pinned tables.
     *
     * @param  array<string>  $tables
     */
    public function withPinnedTables(array $tables): static
    {
        return $this->state(fn (array $attributes) => [
            'pinned_tables' => $tables,
        ]);
    }
}
