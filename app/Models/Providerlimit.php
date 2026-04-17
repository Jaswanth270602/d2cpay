<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Providerlimit extends Model
{
    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
