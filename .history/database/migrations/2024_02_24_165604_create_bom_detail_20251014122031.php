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
        Schema::create('bom_detail', function (Blueprint $table) {
            $table->id();
            $table->integer('line_no'); // line number in BOM
            $table->unsignedBigInteger('bom_id'); // reference to BOM header
            $table->unsignedBigInteger('item_id'); // ingredient
            $table->unsignedBigInteger('base_uom_id')->nullable(); // optional class/category
            $table->decimal('qty', 18, 2)->default(0); // quantity required
            $table->decimal('base_qty', 18, 4)->default(0); // quantity required
            $table->boolean('is_alternative')->default(false); // true if alternative
            $table->timestamps();

            // foreign keys
            $table->foreign('bom_id')->references('id')->on('bom_header')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('item')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bom_detail');
    }
};
