<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_method')->default('cod');
            $table->string('payment_status')->default('pending');
            $table->decimal('shipping_fee', 12, 2)->default(0);
        });

        DB::table('orders')
            ->where('status', 'completed')
            ->update(['payment_status' => 'paid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'payment_status', 'shipping_fee']);
        });
    }
};
