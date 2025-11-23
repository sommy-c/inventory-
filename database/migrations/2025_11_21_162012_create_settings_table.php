<?php
// database/migrations/2025_01_01_000000_create_settings_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('value')->nullable();
            $table->timestamps();
        });

        // default VAT 7.5% = 7.5 (store as percentage, not fraction)
        DB::table('settings')->insert([
            'key'       => 'vat_rate',
            'value'     => '7.5',
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
