<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model {


    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
}
