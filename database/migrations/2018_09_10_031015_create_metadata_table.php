<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetadataTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metadata', function (Blueprint $table) {
            $table->increments('id');
            $table->string('table_name', 100);
            $table->text('retention_policy');
            $table->text('debug_policy');
            $table->text('dependencies');
            $table->string('manager', 200);
            $table->string('relevance', 10);
            $table->string('access', 20);
            $table->text('tags');
            $table->string('status', 20);
            $table->integer('database_id', false, true);
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
        Schema::dropIfExists('metadata');
    }
}
