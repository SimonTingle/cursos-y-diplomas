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
        Schema::table('gallery_images', function (Blueprint $table) {
            if (!Schema::hasColumn('gallery_images', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index(['course_id', 'is_featured']);
            }
            if (!Schema::hasColumn('gallery_images', 'event_id')) {
                $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index(['event_id', 'is_featured']);
            }
            if (!Schema::hasColumn('gallery_images', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gallery_images', function (Blueprint $table) {
            $table->dropForeignIdFor('courses');
            $table->dropForeignIdFor('events');
            $table->dropColumn(['course_id', 'event_id', 'is_featured']);
        });
    }
};
