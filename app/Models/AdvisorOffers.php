<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorOffers extends Model
{
    use HasFactory;
    protected $fillable = [
        'offer_id','advisor_id','offer','description','status'
    ];
}
