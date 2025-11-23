<?php

// database/migrations/2025_01_01_000000_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Optional link to supplier table if you have one
            $table->foreignId('supplier_id')->nullable()->constrained()->nullOnDelete();

            // For free text supplier name (printed on document)
            $table->string('supplier_name')->nullable();

            // Order info
            $table->string('order_number')->unique()->nullable(); // can be filled after create like ORD-0001
            $table->date('expected_date')->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            // Status: waiting = manager submitted, pending = admin approved, supplied = received
            $table->enum('status', ['waiting', 'pending', 'supplied'])->default('waiting');

            // Totals
            $table->decimal('total', 15, 2)->default(0);

            // Manager / Admin info
            $table->string('manager_name')->nullable();
            $table->timestamp('manager_signed_at')->nullable();

            $table->string('admin_name')->nullable();
            $table->timestamp('admin_approved_at')->nullable();

            // Who created (manager)
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
