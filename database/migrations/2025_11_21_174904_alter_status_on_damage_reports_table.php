<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // 🔥 Adjust ENUM values to include 'pending' and 'rejected'
        DB::statement("
            ALTER TABLE damage_reports
            MODIFY COLUMN status ENUM('pending','open','resolved','rejected') NOT NULL DEFAULT 'pending'
        ");
    }

    public function down(): void
    {
        // Rollback to old enum (adjust to whatever you had before)
        DB::statement("
            ALTER TABLE damage_reports
            MODIFY COLUMN status ENUM('open','resolved') NOT NULL DEFAULT 'open'
        ");
    }
};

