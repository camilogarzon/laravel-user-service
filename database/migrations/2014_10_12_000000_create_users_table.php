<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email', 200)->unique();
            $table->string('phone_number', 20)->unique();
            $table->string('full_name', 200)->nullable();
            $table->string('password', 100);
            $table->string('key', 100)->unique();
            $table->string('account_key', 100)->nullable()->unique();
            $table->string('metadata', 2000)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
