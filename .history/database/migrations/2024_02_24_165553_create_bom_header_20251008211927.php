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
            $table->string('code')->unique(); // BOM code
            $table->date('date'); // creation date
            $table->unsignedBigInteger('ware_house_id'); // warehouse
            $table->unsignedBigInteger('item_id'); // final product / dish
            $table->boolean('active')->default(true);
            $table->timestamps();

            // foreign keys
            $table->foreign('ware_house_id')->references('id')->on('ware_house')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
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
