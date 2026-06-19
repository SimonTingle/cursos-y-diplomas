<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Idempotently guarantees the `users` table has every extra column the app
 * relies on. Unlike a migration (which Laravel records and never re-runs),
 * this command runs on EVERY boot, so it repairs the schema even when the
 * `migrations` table claims the columns already exist but they physically do
 * not — the recorded-but-missing divergence seen on the persistent SQLite
 * volume after a destructive table rebuild.
 */
class EnsureUserColumns extends Command
{
    protected $signature = 'users:ensure-columns';

    protected $description = 'Idempotently add any missing extra columns to the users table';

    public function handle(): int
    {
        if (!Schema::hasTable('users')) {
            $this->warn('[ensure-columns] users table does not exist yet; skipping.');
            return self::SUCCESS;
        }

        $added = [];

        Schema::table('users', function (Blueprint $table) use (&$added) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('student');
                $added[] = 'role';
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
                $added[] = 'phone';
            }
            if (!Schema::hasColumn('users', 'title')) {
                $table->string('title')->nullable();
                $added[] = 'title';
            }
            if (!Schema::hasColumn('users', 'bio')) {
                $table->text('bio')->nullable();
                $added[] = 'bio';
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable();
                $added[] = 'avatar';
            }
            if (!Schema::hasColumn('users', 'is_active')) {
                $table->boolean('is_active')->default(true);
                $added[] = 'is_active';
            }
            if (!Schema::hasColumn('users', 'color')) {
                $table->string('color')->default('#6366f1');
                $added[] = 'color';
            }
        });

        if (empty($added)) {
            $this->info('[ensure-columns] users table already has all columns.');
        } else {
            $this->info('[ensure-columns] added missing columns: ' . implode(', ', $added));
        }

        return self::SUCCESS;
    }
}
