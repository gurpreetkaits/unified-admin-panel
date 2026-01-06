<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->string('db_host')->nullable();
            $table->integer('db_port')->default(3306);
            $table->string('db_database')->nullable();
            $table->string('db_username')->nullable();
            $table->text('db_password')->nullable(); // Encrypted
            $table->string('users_table')->default('users');
            $table->string('feedbacks_table')->nullable();
            $table->boolean('is_connected')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn([
                'db_host',
                'db_port',
                'db_database',
                'db_username',
                'db_password',
                'users_table',
                'feedbacks_table',
                'is_connected',
            ]);
        });
    }
};
