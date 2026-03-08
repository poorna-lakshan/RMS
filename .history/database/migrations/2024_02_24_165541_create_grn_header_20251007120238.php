<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('grn_header', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->unsignedBigInteger('vendor_id');
            $table->unsignedBigInteger('ware_house_id');
            $table->string('sup_inv');
            $table->string(column: 'gl_acc')->nullable();
            $table->decimal(column: 'line_discount', total: 18, places: 2);
            $table->decimal(column: 'discount_pra', total: 18, places: 2);
            $table->decimal(column: 'discount', total: 18, places: 2);
            $table->decimal(column: 'nbt', total: 18, places: 2);
            $table->decimal(column: 'vat', total: 18, places: 2);
            $table->decimal(column: 'gross_total', total: 18, places: 2);
            $table->decimal(column: 'net_total', total: 18, places: 2);
            $table->boolean('is_confirm');
            $table->boolean('is_void');
            $table->decimal(column: 'paid_amount', total: 18, places: 2)->default(0);
            $table->timestamps();

            $table->foreign('vendor_id')->references('id')->on('vendor')->onDelete('cascade');
            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_header');
    }
};
