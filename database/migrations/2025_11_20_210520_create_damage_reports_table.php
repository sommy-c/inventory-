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
      Schema::create('damage_reports', function (Blueprint $table) {
     $table->id();

            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->enum('type', ['damaged', 'expired']);
            $table->unsignedInteger('quantity');

            // expiry only really applies when type = expired
            $table->date('expiry_date')->nullable();

            $table->enum('status', ['open', 'resolved'])->default('open');
            $table->unsignedInteger('resolved_quantity')->default(0);

            $table->text('note')->nullable();

            $table->timestamps();
});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('damage_reports');
    }
};
