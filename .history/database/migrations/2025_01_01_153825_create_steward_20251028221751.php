<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DefaultCodegen extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */


    public function up(): void
    {
        Schema::create('steward', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('_item_category');
    }
}
