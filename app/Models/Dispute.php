<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dispute extends Model
{
     public function user(){
        return $this->belongsTo('App\Models\User');
    }

     public function api(){
        return $this->belongsTo('App\Models\Api');
    }

     public function status(){
        return $this->belongsTo('App\Models\Status');
    }

     public function report(){
        return $this->belongsTo('App\Models\Report');
    }

    public function disputereason(){
        return $this->belongsTo('App\Models\Disputereason');
    }
}
