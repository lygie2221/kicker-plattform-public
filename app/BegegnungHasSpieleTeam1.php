<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\TicketHasZammadGroup
 *
 * @mixin \Eloquent
 */
class BegegnungHasSpieleTeam1 extends Model
{
    public $timestamps = false;
    protected $table = 'begegnung_has_spieler_team1';
    protected $fillable = [ 'spieler_id', 'begegnung_id' ];
}
