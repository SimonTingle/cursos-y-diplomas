<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No-op: SQLite ->change() on this column forces full table rebuild,
        // which was dropping other columns (phone, title, bio, avatar, is_active, color).
        // DB-level default is non-essential: AdminController::storeUser and
        // UserImportService always pass explicit role. Fresh volumes avoid the rebuild trap.
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('instructor')->change();
        });
    }
};
