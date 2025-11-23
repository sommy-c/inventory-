<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->decimal('vat_amount', 12, 2)->default(0)->after('total'); // VAT value
            $table->decimal('vat_rate', 5, 2)->default(0)->after('vat_amount');       // VAT percentage
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['vat_amount', 'vat_rate']);
        });
    }
};
