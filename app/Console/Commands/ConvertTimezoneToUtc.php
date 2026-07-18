<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertTimezoneToUtc extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:convert-timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Converts all existing database created_at and updated_at timestamps from Asia/Dhaka (+06:00) to UTC (+00:00) by subtracting 6 hours';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dbName = env('DB_DATABASE');
        if (empty($dbName)) {
            $this->error('DB_DATABASE is not configured in .env file.');
            return 1;
        }

        $tables = DB::select('SHOW TABLES');
        $keyName = 'Tables_in_' . $dbName;

        if ($this->confirm('Are you sure you want to subtract 6 hours from all timestamps (created_at/updated_at) in the database? This is irreversible.')) {
            $this->info('Starting timezone conversion...');
            foreach ($tables as $tableObj) {
                if (!isset($tableObj->$keyName)) {
                    continue;
                }
                $table = $tableObj->$keyName;
                if (Schema::hasColumn($table, 'created_at')) {
                    $updates = [];
                    $updates[] = "created_at = DATE_SUB(created_at, INTERVAL 6 HOUR)";
                    if (Schema::hasColumn($table, 'updated_at')) {
                        $updates[] = "updated_at = DATE_SUB(updated_at, INTERVAL 6 HOUR)";
                    }
                    $query = "UPDATE " . $table . " SET " . implode(", ", $updates);
                    $this->info("Updating Table: " . $table);
                    $rows = DB::update($query);
                    $this->line(" - " . $rows . " rows updated.");
                }
            }
            $this->info('Database timezone conversion completed successfully.');
        } else {
            $this->warn('Conversion cancelled.');
        }

        return 0;
    }
}
