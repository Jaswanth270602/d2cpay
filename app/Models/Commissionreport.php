<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commissionreport extends Model
{
      public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
     public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
}
