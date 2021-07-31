<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorPreferencesCustomer extends Model
{
    use HasFactory;
    protected $fillable = [
        'self_employed','non_uk_citizen','adverse_credit','ltv_max','lti_max','asap','next_3_month','more_3_month','fees_preference','advisor_id'
    ];
}
