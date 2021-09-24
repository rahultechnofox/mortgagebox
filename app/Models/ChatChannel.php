<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatChannel extends Model
{
    use HasFactory;
    protected $fillable = [
        'from_user_id','to_user_id','channel_name','advicearea_id'
    ];
    public function from_user(){
        return $this->hasOne('App\Models\User',"id","from_user_id");
    }
    public function to_user(){
        return $this->hasOne('App\Models\User',"id","to_user_id");
    }
}
