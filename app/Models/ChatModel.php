<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatModel extends Model
{
  	use HasFactory;
    protected $fillable = [
        'from_user_id','to_user_id','channel_id','text','status','created_at','from_user_id_seen','to_user_id_seen','message_type','attachment'
    ];
    public function from_user(){
        return $this->hasOne('App\Models\User',"id","from_user_id");
    }
    public function to_user(){
        return $this->hasOne('App\Models\User',"id","to_user_id");
    }
}
