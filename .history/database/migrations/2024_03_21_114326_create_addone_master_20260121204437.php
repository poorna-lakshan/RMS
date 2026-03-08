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
        Schema::create('addone_master', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('category')->nullable();
            $table->string('addone');
            $table->decimal('unit_price',18,2);
            $table->timestamps();

            $table->foreign('category')->references('id')->on('addone_category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addone_master');
    }
};
