<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paymentmethod extends Model
{
    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
}
