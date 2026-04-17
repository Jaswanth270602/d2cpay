<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    protected $fillable = ['api_name','username','password','api_url','api_key','status','company_id'];


    public function status(){
        return $this->belongsTo('App\Models\Status');
    }

    public function report(){
        return $this->belongsTo('App\Models\Report');
    }


}
