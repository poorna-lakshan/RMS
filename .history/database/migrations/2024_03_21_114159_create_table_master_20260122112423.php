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
        Schema::create('table_master', function (Blueprint $table) {
            $table->id();
            $table->string('category')->nullable();
            $table->string('name');
            $table->integer('capacity')->default(1);
            $table->boolean('is_booking')->default(false);
             $table->boolean('is_sync')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_master');
    }
};
