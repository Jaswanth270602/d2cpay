<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backupapi extends Model {


    public function user() {
        return $this->belongsTo('App\Models\User');
    }


    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }


    public function api(){
        return $this->belongsTo('App\Models\Api');
    }

    public function status(){
        return $this->belongsTo('App\Models\Status');
    }


}
