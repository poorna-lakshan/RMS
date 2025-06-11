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
        Schema::create('addone_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category');
            $table->unsignedBigInteger('addone');
            $table->decimal('unit_price',18,2);
            $table->timestamps();

            $table->foreign('category')->references('id')->on('addone_category')->onDelete('cascade');
            $table->foreign('addone')->references('id')->on('addone_master')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addone_details');
    }
};
