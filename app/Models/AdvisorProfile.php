<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorProfile extends Model
{
    use HasFactory;
    protected $fillable = [
         'display_name','tagline','FCANumber','company_name','phone_number','address_line1','address_line2','city','postcode','web_address','facebook','twitter','about_us','role','image','short_description','status','advisorId','serve_range','linkedin_link','description','company_logo','network','email','gender','language','company_id','stripe_customer_id','FCA_verified'
    ];
}
