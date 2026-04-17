<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{


    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function masterbank(){
        return $this->belongsTo('App\Models\Masterbank');
    }
       public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
