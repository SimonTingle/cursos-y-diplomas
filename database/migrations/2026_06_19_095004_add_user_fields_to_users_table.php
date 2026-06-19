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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->string('title')->nullable()->after('phone');
            $table->text('bio')->nullable()->after('title');
            $table->string('avatar')->nullable()->after('bio');
            $table->boolean('is_active')->default(true)->after('avatar');
            $table->string('color')->default('#6366f1')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'title', 'bio', 'avatar', 'is_active', 'color']);
        });
    }
};
