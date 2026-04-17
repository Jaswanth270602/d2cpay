<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apiprovider extends Model
{


    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
    public function api(){
        return $this->belongsTo('App\Models\Api');
    }

    public function service(){
        return $this->belongsTo('App\Models\Service');
    }



}
