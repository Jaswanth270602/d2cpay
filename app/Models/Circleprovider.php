<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Circleprovider extends Model
{


    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }


    public function status(){
        return $this->belongsTo('App\Models\Status');
    }

    public function state(){
        return $this->belongsTo('App\Models\State');
    }

}
