<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStandortTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('standort', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 300);
            $table->timestamps();
        });

        DB::table('standort')->insert([
            'name' => "Berlin"]);
        DB::table('standort')->insert([
            'name' => "München"]);
        DB::table('standort')->insert([
            'name' => "Würzburg"]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('standort');
    }
}
