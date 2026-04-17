<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Returnrequest extends Model
{
     public function user(){
        return $this->belongsTo('App\Models\User');
    }

     public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
