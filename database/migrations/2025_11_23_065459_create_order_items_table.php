<?php

// database/migrations/2025_01_01_000001_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained()->onDelete('cascade');

            // If you want to link to products
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // For printing even if product is later deleted
            $table->string('product_name');
            
            $table->integer('qty');
            $table->decimal('price', 15, 2); // unit cost
            $table->decimal('line_total', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
