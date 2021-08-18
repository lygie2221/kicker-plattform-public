<?php

namespace App;

use Illuminate\Database\Eloquent\Model;


/**
 * Class MarketingFunnel
 * @package App
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Standort extends Model
{
    protected $table = 'standort';

    public static function getAllForSelect(){
        $select=[];
        foreach(Standort::all() as $standort){
            $select[$standort->id]=$standort->name;
        }
        return $select;
    }

}
