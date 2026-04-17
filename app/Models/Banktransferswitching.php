<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banktransferswitching extends Model
{
    use HasFactory;

    public function api(){
        return $this->belongsTo('App\Models\Api');
    }
}
