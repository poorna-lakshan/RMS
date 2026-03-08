<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UserLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('user_logs', function (Blueprint $table) {
        $table->id();
        $table->string('user');
        $table->string('action');
        $table->string('module');
        $table->text('details')->nullable();

        $table->string('ip', 45)->nullable();          // IPv4 / IPv6
        $table->string('device')->nullable();          // mobile/desktop/tablet
        $table->string('browser')->nullable();
        $table->string('os')->nullable();
        $table->string('user_agent', 500)->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
