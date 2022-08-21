<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventUser extends Model
{
    use HasFactory;
    function created_by(){
        return $this->hasOne(User::class,'id','user_id');
    }
    function event_details(){
        return $this->hasOne(Event::class,'id','event_id');
    }

    protected $hidden = [
        'user_id',
        'event_id',
        'id',
        'created_at',
        'updated_at'
    ];
}
