<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Apicommreport extends Model
{
    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
}
