<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocationPreferences extends Model
{
    use HasFactory;
    protected $fillable = [
        'advisor_id','post_code','miles'
    ];
}
