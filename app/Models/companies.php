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
            $data = $query->with('adviser')->with('team_members')->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            foreach($data as $row){
                $success_per = 0;
                $team_arr = array();
                if($row->company_admin!=0){
                    $user = AdvisorProfile::where('advisorId',$row->company_admin)->first();
                    if($user){
                        $row->company_admin_name = $user->display_name;
                    }else{
                        $row->company_admin_name = "";
                    }
                    array_push($team_arr,$row->company_admin);
                }else{
                    $row->company_admin_name = "";
                }
                foreach($row->team_members as $team_members_data){
                    array_push($team_arr,$team_members_data->advisor_id);
                }
                $advice_areaCount =  AdvisorBids::where('advisor_status', 1)->whereIn('advisor_id',$team_arr)
                ->where('status', '!=', 2)->where('status', '!=', 3)->count();

                $row->accepted_leads = $advice_areaCount;
                $live_leads = AdvisorBids::whereIn('advisor_id',$team_arr)->where('status', '=', 0)->where('advisor_status', '=', 1)->count();
                $row->live_leads = $live_leads;

                $hired_leads = AdvisorBids::whereIn('advisor_id',$team_arr)
                ->where('status', '=', 1)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->hired_leads = $hired_leads;

                $completed_leads = AdvisorBids::whereIn('advisor_id',$team_arr)
                ->where('status', '=', 2)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->completed_leads = $completed_leads;

                $value = AdvisorBids::whereIn('advisor_id',$team_arr)->sum('cost_leads');
                $cost = AdvisorBids::whereIn('advisor_id',$team_arr)->sum('cost_discounted');
                $row->value = $value;
                $row->cost = $cost;
                $total_bids = AdvisorBids::whereIn('advisor_id',$team_arr)->where('advisor_status', '=', 1)->count();
                if($total_bids!=0){
                    $success_per = ($row->completed_leads / $total_bids) * 100;
                }
                $row->success_percent = $success_per;
                $row->total_advisor = count($row->adviser);
                // $row->accepted_leads = 0;
                // $row->value = 0;
                // $row->cost = 0;
                // $row->live_leads = 0;
                // $row->hired = 0;
                // $row->completed = 0;
                // $row->success_percent = 0;
                // $success_per = 0;
                // if(isset($row->adviser) && count($row->adviser)>0){
                //     foreach($row->adviser as $adviser_data){
                //         $row->accepted_leads = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',1)->count();
                //         $row->value = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_leads');
                //         $row->cost = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->sum('cost_discounted');
                //         $row->completed = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('status',2)->count();
                //         $total_bids = AdvisorBids::where('advisor_id',$adviser_data->advisorId)->where('advisor_status', '=', 1)->count();
                //         if($total_bids!=0){
                //             $success_per = ($row->accepted_leads / $total_bids) * 100;
                //         }
                //         $row->success_percent = $success_per;
                //     }
                // }
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getCompanyDetail($id){
        try {
            $query = new Self;
            $data = $query->where('id',$id)->with('team_members')->with('adviser')->with('notes')->first();
            $teamadmin = 0;
            $success_per = 0;
            if($data){
                $data->adviser = $data->adviser[0];
                $data->total_advisor = count($data->team_members);
                // $accepted_leads = 0;
                // $value = 0;
                // $cost = 0;
                // $live_leads = 0;
                // $hired = 0;
                // $completed = 0;
                $success_per = 0;
                $team_arr = array();
                if($data->company_admin!=0){
                    $user = AdvisorProfile::where('advisorId',$data->company_admin)->first();
                    if($user){
                        $data->company_admin_name = $user->display_name;
                    }else{
                        $data->company_admin_name = "";
                    }
                    array_push($team_arr,$data->company_admin);
                }else{
                    $data->company_admin_name = "";
                }
                foreach($data->team_members as $team_members_data){
                    array_push($team_arr,$team_members_data->advisor_id);
                }

                $advice_areaCount =  AdvisorBids::where('advisor_status', 1)->whereIn('advisor_id',$team_arr)
                ->where('status', '!=', 2)->where('status', '!=', 3)->count();

                $data->accepted_leads = $advice_areaCount;
                $live_leads = AdvisorBids::whereIn('advisor_id',$team_arr)->where('status', '=', 0)->where('advisor_status', '=', 1)->count();
                $data->live_leads = $live_leads;

                $hired_leads = AdvisorBids::whereIn('advisor_id',$team_arr)
                ->where('status', '=', 1)
                ->where('advisor_status', '=', 1)
                ->count();
                $data->hired_leads = $hired_leads;

                $completed_leads = AdvisorBids::whereIn('advisor_id',$team_arr)
                ->where('status', '=', 2)
                ->where('advisor_status', '=', 1)
                ->count();
                $data->completed_leads = $completed_leads;

                $value = AdvisorBids::whereIn('advisor_id',$team_arr)->sum('cost_leads');
                $cost = AdvisorBids::whereIn('advisor_id',$team_arr)->sum('cost_discounted');
                $data->value = $value;
                $data->cost = $cost;
                $total_bids = AdvisorBids::whereIn('advisor_id',$team_arr)->where('advisor_status', '=', 1)->count();
                if($total_bids!=0){
                    $success_per = ($data->accepted_leads / $total_bids) * 100;
                }
                $data->success_percent = $success_per;
            }   
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
