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
        Schema::create('item_batch', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('ware_house_id');
            $table->string('batch_no')->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('qty', 18, 4); // High precision for restaurant items
            $table->decimal('unit_cost', 18, 4);
            $table->date('received_date');
            $table->string('reference_no'); // PO, GRN, Adjustment, etc.
            $table->string('reference_type'); // PURCHASE, PRODUCTION, TRANSFER, ADJUSTMENT
            $table->text('notes')->nullable();
            $table->boolean('is_consumed')->default(false);
            $table->timestamps();

            // Indexes for performance
            $table->index(['item_id', 'ware_house_id']);
            $table->index(['item_id', 'expiry_date']);
            $table->index(['item_id', 'received_date']);
            $table->index(['item_id', 'is_consumed']);
            $table->index('batch_no');
            
            // Foreign keys
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
            $table->foreign('ware_house_id')->references('ware_house_id')->on('item_ware_house')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_batch');
    }
};