<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBegegnungTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('begegnung', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('Modus', \App\Enums\ModusDerBegegnung::toArray());
            $table->integer('standort_id');
            $table->timestamps();
        });

        Schema::create('begegnung_has_spieler_team1', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spieler_id');
            $table->integer('begegnung_id');

            $table->index(['spieler_id','begegnung_id'])->unique();
        });
        Schema::create('begegnung_has_spieler_team2', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spieler_id');
            $table->integer('begegnung_id');

            $table->index(['spieler_id','begegnung_id'])->unique();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('begegnung');
    }
}
