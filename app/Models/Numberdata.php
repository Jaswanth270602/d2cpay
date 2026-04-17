<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Numberdata extends Model {


    public function state(){
        return $this->belongsTo('App\Models\State');
    }

    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
