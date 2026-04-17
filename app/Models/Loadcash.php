<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Loadcash extends Model
{
    protected $fillable = ['user_id','pmethod_id','netbank_id','yacc','amount','bankref','status_id','user_id','payment_date'];

    public function user(){
        return $this->belongsTo('App\Models\User');
    }

    public function status(){
        return $this->belongsTo('App\Models\Status');
    }

     public function bankdetail(){
        return $this->belongsTo('App\Models\Bankdetail');
    }

     public function paymentmethod(){
        return $this->belongsTo('App\Models\Paymentmethod');
    }

}
