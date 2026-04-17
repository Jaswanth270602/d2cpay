<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mreport extends Model
{

    public function report(){
        return $this->belongsTo('App\Models\Report');
    }

    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }

    public function status(){
        return $this->belongsTo('App\Models\Status');
    }

    public function beneficiary(){
        return $this->belongsTo('App\Models\Beneficiary');
    }

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

}
