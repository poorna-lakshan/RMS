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
        Schema::create('bom_header', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->date('date');
            $table->unsignedBigInteger('ware_house_id');
            $table->unsignedBigInteger('item_id');
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
        });

        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_header');
    }
};
