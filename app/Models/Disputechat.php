<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Disputechat extends Model
{
     public function user(){
        return $this->belongsTo('App\Models\User');
    }

     public function dispute(){
        return $this->belongsTo('App\Models\Dispute');
    }
}
