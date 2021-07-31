<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorEnquiries extends Model
{
    use HasFactory;
    protected $fillable = [
        'name','email','mortgage_required','prop_value','combined_income','how_soon','post_code','anything_else','advisor_id','user_id'
    ];
}
