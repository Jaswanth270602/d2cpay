<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Servicebanner extends Model
{

    public function service(){
        return $this->belongsTo('App\Models\Service');
    }

     public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
