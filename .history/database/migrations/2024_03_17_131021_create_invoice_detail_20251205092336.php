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
        Schema::create('invoice_detail', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('reference_no');
            $table->integer('line_no');
            $table->unsignedBigInteger('item_id');
            $table->string('comment');
            $table->decimal('qty',18,2);
            $table->decimal('unit_cost',18,2);
            $table->decimal('unit_price',18,2);
            $table->timestamps();

            $table->foreign('reference_no')->references('id')->on('invoice_header')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_detail');
    }
};
