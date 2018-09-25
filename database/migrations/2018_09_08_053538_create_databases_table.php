<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDatabasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('databases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('driver', 50);
            $table->string('host', 50);
            $table->integer('port', false, true);
            $table->string('user', 100);
            $table->string('password', 200)->nullable();
            $table->string('charset', 100);
            $table->string('collation', 100);
            $table->integer('system_id', false, true);
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
        Schema::dropIfExists('databases');
    }
}
