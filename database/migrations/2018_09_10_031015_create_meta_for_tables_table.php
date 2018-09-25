<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMetaForTablesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('metadata_tables', function (Blueprint $table) {
            $table->increments('id');
            $table->text('retention_policy');
            $table->text('debug_policy');
            $table->text('dependencies');
            $table->string('manager', 200);
            $table->string('relevance', 10);
            $table->string('access', 20);
            $table->integer('table_id', false, true);
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
        Schema::dropIfExists('metadata_tables');
    }
}
