<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSpielTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('spiel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer("begegnung_id");
            $table->string("ergebnis");
            $table->timestamps();
        });

        Schema::create('spieler_has_position_in_spiel', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spieler_id');
            $table->integer('spiel_id');
            $table->enum('Modus', \App\Enums\PositionDerSpieler::toArray());

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('spiel');
        Schema::dropIfExists('spieler_has_position_in_spiel');

    }
}
