<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $fillable = ['number','provider_id','amount','api_id','status_id','pay_id','txnid','user_id','profit','client_id','dprofit','total_balance'];

    public function provider(){
        return $this->belongsTo('App\Models\Provider');
    }
    public function user(){
        return $this->belongsTo('App\Models\User');
    }
    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
    public function status(){
        return $this->belongsTo('App\Models\Status');
    }
    public function beneficiary(){
        return $this->belongsTo('App\Models\Beneficiary');
    }
    public function credit_by(){
        return $this->belongsTo('App\Models\User');
    }
    public function payment(){
        return $this->belongsTo('App\Models\Loadcash');
    }

    public function aepsreport()
    {
        return $this->hasOne('App\Models\Aepsreport');
    }


}
