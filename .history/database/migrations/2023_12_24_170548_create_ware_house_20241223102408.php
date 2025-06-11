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
        Schema::create('ware_house', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('ap_acc');
            $table->string('ar_acc');
            $table->string('cash_acc');
            $table->string('sales_acc');
            $table->string('cos_acc');
            $table->string('inv_acc');
            $table->string('price_level');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ware_house');
    }
};
