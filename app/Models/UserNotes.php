<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotes extends Model
{
    use HasFactory;
    protected $fillable = [
        'notes', 'status','user_id','advice_id'
    ];
}
