<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;
    function created_by(){
        return $this->hasOne(User::class,'id','user_id');
    }

    protected $hidden = [
        'user_id',
        'created_at',
        'updated_at'
    ];
}
