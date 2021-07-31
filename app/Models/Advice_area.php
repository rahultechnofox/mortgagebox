<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advice_area extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'service_type','request_time', 'property','property_want', 'size_want','combined_income', 'description','occupation', 'contact_preference', 'advisor_preference', 'fees_preference','self_employed','non_uk_citizen','adverse_credit','contact_preference_face_to_face','contact_preference_online','contact_preference_telephone','contact_preference_evening_weekend','advisor_preference_local','advisor_preference_gender','advisor_preference_language','status','combined_income_currency','property_currency','size_want_currency','close_type','advisor_id','need_reminder','initial_term','start_date','ltv_max','lti_max'
    ];
}
