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
        Schema::table('videos', function (Blueprint $table) {
            if (!Schema::hasColumn('videos', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index('course_id');
            }
            if (!Schema::hasColumn('videos', 'event_id')) {
                $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index('event_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->dropForeignIdFor('courses');
            $table->dropForeignIdFor('events');
            $table->dropColumn(['course_id', 'event_id']);
        });
    }
};
