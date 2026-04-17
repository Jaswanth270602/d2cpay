<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Provider extends Model
{
    protected $fillable = ['provider_name','provider_code','service_id','vender_code','api_id'];


    public function service(){
        return $this->belongsTo('App\Models\Service');
    }

    public function api(){
        return $this->belongsTo('App\Models\Api');

    }
    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}


