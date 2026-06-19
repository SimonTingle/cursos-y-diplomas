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
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description');
            $table->string('category')->default('user_management');
            $table->timestamps();
        });

        DB::table('permissions')->insert([
            ['name' => 'create_users', 'description' => 'Create new user accounts', 'category' => 'user_management', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'edit_users', 'description' => 'Edit user information', 'category' => 'user_management', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'delete_users', 'description' => 'Delete user accounts', 'category' => 'user_management', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'import_users', 'description' => 'Bulk import users from CSV/JSON', 'category' => 'user_management', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'view_audit_logs', 'description' => 'View user audit logs', 'category' => 'user_management', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
