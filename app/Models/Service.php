<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    public function wallet(){
        return $this->belongsTo('App\Models\Wallet');
    }
}
