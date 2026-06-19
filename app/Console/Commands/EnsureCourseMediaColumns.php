<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotently guarantees the `courses`, `gallery_images`, `pdfs`, and `videos`
 * tables have every foreign key and column the app relies on. Unlike migrations
 * (which Laravel records and never re-runs), this command runs on EVERY boot,
 * so it repairs the schema even when the `migrations` table claims the columns
 * already exist but they physically do not — the recorded-but-missing divergence
 * seen on persistent SQLite volumes after destructive table rebuilds.
 */
class EnsureCourseMediaColumns extends Command
{
    protected $signature = 'schema:ensure-course-media';

    protected $description = 'Idempotently add any missing course media columns and foreign keys';

    public function handle(): int
    {
        $added = [];

        $this->ensureCoursesTable($added);
        $this->ensureGalleryImagesTable($added);
        $this->ensurePdfsTable($added);
        $this->ensureVideosTable($added);

        if (empty($added)) {
            $this->info('[ensure-course-media] all tables already have required columns.');
        } else {
            $this->info('[ensure-course-media] added missing columns: ' . implode(', ', $added));
        }

        return self::SUCCESS;
    }

    private function ensureCoursesTable(array &$added): void
    {
        if (!Schema::hasTable('courses')) {
            $this->warn('[ensure-course-media] courses table does not exist yet; skipping.');
            return;
        }

        Schema::table('courses', function (Blueprint $table) use (&$added) {
            if (!Schema::hasColumn('courses', 'instructor_id')) {
                $table->foreignId('instructor_id')->nullable()->constrained('instructors')->nullOnDelete();
                $table->index('instructor_id');
                $added[] = 'courses.instructor_id';
            }
        });
    }

    private function ensureGalleryImagesTable(array &$added): void
    {
        if (!Schema::hasTable('gallery_images')) {
            $this->warn('[ensure-course-media] gallery_images table does not exist yet; skipping.');
            return;
        }

        Schema::table('gallery_images', function (Blueprint $table) use (&$added) {
            if (!Schema::hasColumn('gallery_images', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index(['course_id', 'is_featured']);
                $added[] = 'gallery_images.course_id';
            }
            if (!Schema::hasColumn('gallery_images', 'event_id')) {
                $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index(['event_id', 'is_featured']);
                $added[] = 'gallery_images.event_id';
            }
            if (!Schema::hasColumn('gallery_images', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
                $added[] = 'gallery_images.is_featured';
            }
        });
    }

    private function ensurePdfsTable(array &$added): void
    {
        if (!Schema::hasTable('pdfs')) {
            $this->warn('[ensure-course-media] pdfs table does not exist yet; skipping.');
            return;
        }

        Schema::table('pdfs', function (Blueprint $table) use (&$added) {
            if (!Schema::hasColumn('pdfs', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index('course_id');
                $added[] = 'pdfs.course_id';
            }
            if (!Schema::hasColumn('pdfs', 'event_id')) {
                $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index('event_id');
                $added[] = 'pdfs.event_id';
            }
        });
    }

    private function ensureVideosTable(array &$added): void
    {
        if (!Schema::hasTable('videos')) {
            $this->warn('[ensure-course-media] videos table does not exist yet; skipping.');
            return;
        }

        Schema::table('videos', function (Blueprint $table) use (&$added) {
            if (!Schema::hasColumn('videos', 'course_id')) {
                $table->foreignId('course_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index('course_id');
                $added[] = 'videos.course_id';
            }
            if (!Schema::hasColumn('videos', 'event_id')) {
                $table->foreignId('event_id')->nullable()->constrained()->cascadeOnDelete();
                $table->index('event_id');
                $added[] = 'videos.event_id';
            }
        });
    }
}
