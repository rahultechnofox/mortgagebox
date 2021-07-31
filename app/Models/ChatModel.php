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
}
