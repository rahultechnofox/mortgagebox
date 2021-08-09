<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdvisorProfile;
use App\Models\AdvisorBids;

class Companies extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name'
    ];

    public function team_members(){
        return $this->hasMany('App\Models\CompanyTeamMembers',"company_id","id");
    }

    public function adviser(){
        return $this->hasMany('App\Models\AdvisorProfile',"company_id","id");
    }

    public static function getCompanies($search){
        try {
            $query = new Self;
            if(isset($search['name']) && $search['name']!=''){
                $query = $query->where('question', 'like', '%' .strtolower($search['name']). '%');
            }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('status',$search['status']);
            }
            $data = $query->with('adviser')->with('team_members')->orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
            foreach($data as $row){
                $row->total_advisor = count($row->adviser);
                $row->accepted_leads = 0;
                $row->value = 0;
                $row->cost = 0;
                $row->live_leads = 0;
                $row->hired = 0;
                $row->completed = 0;
                if(isset($row->adviser) && count($row->adviser)>0){
                    foreach($row->adviser as $adviser_data){
                        $row->accepted_leads = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',1)->count();
                        $row->value = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_leads');
                        $row->cost = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_discounted');
                        $row->completed = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',2)->count();
                    }
                }
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getCompanyDetail($id){
        try {
            $query = new Self;
            $data = $query->where('id',$id)->with('team_members')->first();
            $teamadmin = 0;
            if($data){
                foreach($data->team_members as $team_members_data){
                    $teamadmin = $team_members_data->advisor_id;
                }
                $data->adviser = AdvisorProfile::where('advisorId',$teamadmin)->where('company_id',$data->id)->first();
            }
            // foreach($data as $row){
            //     $row->total_advisor = count($row->adviser);
            //     $row->accepted_leads = 0;
            //     $row->value = 0;
            //     $row->cost = 0;
            //     $row->live_leads = 0;
            //     $row->hired = 0;
            //     $row->completed = 0;
            //     if(isset($row->adviser) && count($row->adviser)>0){
            //         foreach($row->adviser as $adviser_data){
            //             $row->accepted_leads = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',1)->count();
            //             $row->value = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_leads');
            //             $row->cost = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_discounted');
            //             $row->completed = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',2)->count();
            //         }
            //     }
            // }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
