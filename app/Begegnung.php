<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * Class MarketingFunnel
 * @package App
 *
 * @property int $id
 * @property string $modus
 * @property Standort $standort
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Begegnung extends Model
{

    protected $table = 'begegnung';

    /**
     * Get the comments for the blog post.
     */
    public function standort()
    {
        return $this->hasOne(Standort::class, 'standort_id', 'id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function spielerTeam1()
    {
        return $this->hasMany(User::class, 'begegnung_has_spieler_team1', 'begegnung_id ', 'spieler_id');
    }

    /**
     * Get the comments for the blog post.
     */
    public function spielerTeam2()
    {
        return $this->hasMany(User::class, 'begegnung_has_spieler_team2', 'begegnung_id ', 'spieler_id');
    }
}
