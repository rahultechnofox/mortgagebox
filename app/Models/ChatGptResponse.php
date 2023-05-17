<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatGptResponse extends Model
{
    use HasFactory;
    protected $table = "chat_gpt_response";
    protected $fillable = [
        'user_id',
        'question',
        'response',
    ];
}
