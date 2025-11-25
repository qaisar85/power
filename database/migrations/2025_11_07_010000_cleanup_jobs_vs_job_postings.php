<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Ensure job_postings table exists before attempting data move
        if (!Schema::hasTable('job_postings') && Schema::hasTable('jobs')) {
            // If for some reason the rename didn't happen, try renaming now
            try {
                Schema::rename('jobs', 'job_postings');
                return; // Renamed; nothing further to do in this run
            } catch (\Throwable $e) {
                // fall through to attempt create-and-copy approach
            }
        }

        // If both tables exist, attempt to move domain-like rows from jobs -> job_postings
        if (Schema::hasTable('jobs') && Schema::hasTable('job_postings')) {
            $jobsColumns = Schema::getColumnListing('jobs');
            $postingsColumns = Schema::getColumnListing('job_postings');

            // Heuristic: domain columns expected on job_postings
            $domainColumns = ['user_id','title','description','location','status','created_at','updated_at','id'];
            $hasDomainShapeInJobs = count(array_intersect($domainColumns, $jobsColumns)) >= 4; // if at least 4 match, assume mixed

            if ($hasDomainShapeInJobs) {
                // Select transferable columns intersection to avoid SQL errors
                $selectCols = array_values(array_intersect($postingsColumns, $domainColumns));
                // Build insert-select for rows in jobs that look like domain rows
                // We consider any row with non-null title or description as domain
                $columnsCsv = implode(',', array_map(function ($c) { return "`$c`"; }, $selectCols));
                $selectCsv = implode(',', array_map(function ($c) { return "`$c`"; }, $selectCols));

                if (!empty($selectCols)) {
                    DB::statement(
                        "INSERT INTO `job_postings` ($columnsCsv) SELECT $selectCsv FROM `jobs` WHERE `title` IS NOT NULL OR `description` IS NOT NULL"
                    );
                }

                // Optionally delete moved rows (keep it conservative: do not delete automatically)
                // If you want to delete after confirming, create a follow-up migration or manual SQL.
            }

            // Cleanup unintended domain columns on jobs by dropping columns if they exist
            Schema::table('jobs', function (Blueprint $table) use ($jobsColumns) {
                $drop = [];
                foreach (['user_id','title','description','location','status'] as $c) {
                    if (in_array($c, $jobsColumns, true)) {
                        $drop[] = $c;
                    }
                }
                // Drop columns individually to be compatible across DBs
                foreach ($drop as $c) {
                    try {
                        $table->dropColumn($c);
                    } catch (\Throwable $e) {
                        // ignore if cannot drop due to platform limitations
                    }
                }
            });
        }
    }

    public function down(): void
    {
        // No-op: we do not move data back or restore dropped columns automatically
    }
};
