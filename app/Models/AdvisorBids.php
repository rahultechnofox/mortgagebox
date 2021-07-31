<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorBids extends Model
{
    use HasFactory;
    protected $fillable = [
        'advisor_id','area_id','status','advisor_status','cost_leads','cost_discounted','free_introduction','accepted_date'
    ];
}
