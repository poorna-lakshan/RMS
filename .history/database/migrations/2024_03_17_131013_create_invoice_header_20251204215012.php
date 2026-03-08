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
        Schema::create('invoice_header', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('customer_id');
            $table->string('user');
            $table->string('category');
            $table->string('payment_method');
            $table->integer('item_count');
            $table->decimal('gross_total',18,2);
            $table->decimal('dis_per',18,2);
            $table->decimal('dis_total',18,2);
            $table->decimal('vat_per',18,2);
            $table->decimal('vat_total',18,2);
            $table->decimal('nbt_per',18,2);
            $table->decimal('nbt_total',18,2);
            $table->decimal('service_charge_per',18,2);
            $table->decimal('service_charge_total',18,2);
            $table->decimal('delivery_charge',18,2);
            $table->decimal('net_total',18,2);
            $table->decimal('total_cost',18,2);
            // $table->decimal('cash_total',18,2);
            // $table->decimal('card_total',18,2);
            // $table->decimal('bank_total',18,2);
            $table->decimal('credit_total',18,2);
            $table->decimal('paid_total',18,2);
            $table->decimal('balance',18,2);
            $table->boolean('is_full_pay');
            $table->boolean('is_void');
            $table->timestamps();

            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
            $table->foreign('customer_id')->references('id')->on('customer')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_header');
    }
};
