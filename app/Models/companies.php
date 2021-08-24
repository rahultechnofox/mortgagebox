<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdvisorProfile;
use App\Models\AdvisorBids;

class companies extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name'
    ];

    public function team_members(){
        return $this->hasMany('App\Models\CompanyTeamMembers',"company_id","id")->with('team_data')->with('team_data_advisor_profile');
    }

    public function adviser(){
        return $this->hasMany('App\Models\AdvisorProfile',"company_id","id");
    }

    public function notes(){
        return $this->hasMany('App\Models\Notes',"company_id","id");
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
                $row->success_percent = 0;
                $success_per = 0;
                if(isset($row->adviser) && count($row->adviser)>0){
                    foreach($row->adviser as $adviser_data){
                        $row->accepted_leads = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',1)->count();
                        $row->value = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_leads');
                        $row->cost = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_discounted');
                        $row->completed = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',2)->count();
                        $total_bids = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('advisor_status', '=', 1)->count();
                        if($total_bids!=0){
                            $success_per = ($row->accepted_leads / $total_bids) * 100;
                        }
                        $row->success_percent = $success_per;
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
            $data = $query->where('id',$id)->with('team_members')->with('notes')->first();
            $teamadmin = 0;
            $success_per = 0;
            if($data){
                $data->total_advisor = count($data->team_members);
                // $accepted_leads = 0;
                // $value = 0;
                // $cost = 0;
                // $live_leads = 0;
                // $hired = 0;
                // $completed = 0;
                foreach($data->team_members as $team_members_data){
                    $team_members_data->accepted_leads = 0;
                    $team_members_data->value = 0;
                    $team_members_data->cost = 0;
                    $team_members_data->completed = 0;
                    $team_members_data->live_leads = 0;
                    $team_members_data->hired = 0;
                    $teamadmin = $team_members_data->advisor_id;
                    $team_members_data->accepted_leads = AdvisorBids::where('advisor_id',$team_members_data->advisor_id)->count();
                    $team_members_data->value = AdvisorBids::where('advisor_id',$team_members_data->advisor_id)->sum('cost_leads');
                    $team_members_data->cost = AdvisorBids::where('advisor_id',$team_members_data->advisor_id)->sum('cost_discounted');
                    $team_members_data->completed = AdvisorBids::where('advisor_id',$team_members_data->advisor_id)->where('status',2)->count();
                    $total_bids = AdvisorBids::where('advisor_id',$team_members_data->advisor_id)->where('advisor_status', '=', 1)->count();
                    if($total_bids!=0){
                        $success_per = ($team_members_data->accepted_leads / $total_bids) * 100;
                    }
                    $team_members_data->success_percent = $success_per; 
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
