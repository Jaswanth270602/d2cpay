<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bankdetail extends Model
{
    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
