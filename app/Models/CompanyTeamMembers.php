<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyTeamMembers extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_id','name','email','advisor_id','status','is_joined'
    ];
    public function team_data_advisor_profile(){
        return $this->hasOne('App\Models\AdvisorProfile',"email","email");
    }
    public function team_data(){
        return $this->hasOne('App\Models\User',"email","email");
    }
}
