<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AdvisorProfile;
use App\Models\AdvisorBids;
use DB;
class companies extends Model
{
    use HasFactory;
    protected $fillable = [
        'company_name'
    ];

    public function team_members(){
        return $this->hasMany('App\Models\CompanyTeamMembers',"company_id","id")->where('is_joined',1)->with('team_data')->with('team_data_advisor_profile');
    }

    public function adviser(){
        return $this->hasMany('App\Models\AdvisorProfile',"company_id","id");
    }

    public function adviser_data(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","company_admin");
    }

    public function notes(){
        return $this->hasMany('App\Models\Notes',"company_id","id");
    }

    public static function getCompanies($search){
        try {
            $query = new Self;
            $companyId = array();
            
            if(isset($search['search']) && $search['search']!=''){
                $final_arr_m = array(-1);
                $advisor = AdvisorProfile::select('advisorId')->where('display_name', 'like', '%' .strtolower($search['search']). '%')->get();
                if(count($advisor)){
                    foreach($advisor as $advisor_data){
                        array_push($final_arr_m,$advisor_data->advisorId);
                    }
                }
                $companyId = array(-1);
                $getData = companies::where('company_name', 'like', '%' .strtolower($search['search']). '%')->orWhereIn('company_admin',$final_arr_m)->get();
                if(count($getData)){
                    foreach($getData as $getRow){
                        array_push($companyId,$getRow->company_admin);
                    }
                }
            }

            
            if(count($companyId)>0){
                $query = $query->whereIn('company_admin',$companyId);
            }

            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('status',$search['status']);
            }

            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }

            $data = $query->with('adviser')->with('team_members')->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            
            foreach($data as $row){
                $success_per = 0;
                $team_arr = array(-1);
                $admin = CompanyTeamMembers::where('company_id',$row->id)->where('isCompanyAdmin',1)->first();
                if($admin){
                    $ad_user = AdvisorProfile::where('email',$admin->email)->first();
                    if($ad_user){
                        $row->company_admin_name = $ad_user->display_name;
                    }else{
                        $row->company_admin_name = "";
                    }
                }

                $final_live_lead = 'NA';                
                $final_cost_of_lead = 0;
                $final_eastimated_lead = 0;
                $cost_lead = 0;
                $cost_lead_final = 0;

                $area_arr = array(-1);
                foreach($row->team_members as $team_members_data)
                {
                    $advisor_data_team = AdvisorProfile::where('email',$team_members_data->email)->first();
                    if($advisor_data_team){
                        $es_val = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)->where('status',2)->where('advisor_status',1)->get();
                        $estimated = config('app.currency').number_format(0.00,0);
                        
                        if(count($es_val)){
                            foreach($es_val as $es_val_data){
                                array_push($area_arr,$es_val_data->area_id);
                            }
                        }

                        if(count($area_arr)){
                            $value_data = Advice_area::whereIn('id',$area_arr)->sum('size_want');
                            $main_value = ($value_data/100);
                            $advisorDetaultPercent = 0;
                            $services = DB::table('app_settings')->where('key','estimate_calculation_percent')->first();
                            if($services){
                                $advisorDetaultPercent = $services->value;
                            }
                            $lead_value = ($main_value)*($advisorDetaultPercent);
                            $final_eastimated_lead = $final_eastimated_lead + $lead_value;
                        }
                        else{
                            $lead_value = 0;
                            $final_eastimated_lead = $final_eastimated_lead + $lead_value;
                        }
                        
                        if($advisor_data_team->advisorId!=null){
                            array_push($team_arr,$advisor_data_team->advisorId);
                        }
                        $cost_val = AdvisorBids::where('advisor_id','=',$advisor_data_team->advisorId)->get();
                        if(count($cost_val)){
                            foreach($cost_val as $cost_val_data){
                                // if($cost_val_data->cost_discounted!=0){
                                //     $cost_lead = $cost_lead + $cost_val_data->cost_discounted;
                                // }
                                // if($cost_val_data->cost_discounted==0){
                                // }
                                $cost_lead = $cost_lead + $cost_val_data->cost_leads;
                            }
                            $cost_lead_final = $cost_lead;
                        }
                        $final_cost_of_lead = $cost_lead_final;
                        // $final_cost_of_lead = $final_cost_of_lead + $cost_lead_final;

                    }                    
                }
                

                // $row->live_leads = $final_live_lead;
                $row->eastimated_lead = $final_eastimated_lead;
                $row->cost_of_lead = config('app.currency').number_format($final_cost_of_lead,2);

                // $advice_areaCount =  AdvisorBids::where('advisor_status', 1)->whereIn('advisor_id',$team_arr)->where('status', '!=', 2)->where('status', '!=', 3)->count();

                // $row->accepted_leads = $advice_areaCount;

                $live_leads = AdvisorBids::whereIn('advisor_id',$team_arr)
                ->where('status', '=', 0)
                ->where('advisor_status', '=', 1)
                ->get();
                $live_arr = array();
                $not_responded = 0;
                foreach($live_leads as $live_leads_data){
                    $bids = AdvisorBids::where('area_id',$live_leads_data->area_id)->orWhere('status','!=',0)->get();
                    array_push($live_arr,$bids);
                    if(count($bids)){
                        $not_responded = $not_responded + 1;
                    }
                }
                $row->live_leads = $not_responded;

                $accepted_leads = AdvisorBids::whereIn('advisor_id',$team_arr)
                ->count();
                $row->accepted_leads = $accepted_leads;

                $hired_leads = AdvisorBids::whereIn('advisor_id',$team_arr)->where('status', '=', 1)->where('advisor_status', '=', 1)->count();
                $row->hired_leads = $hired_leads;

                $completed_leads = AdvisorBids::whereIn('advisor_id',$team_arr)->where('status', '=', 2)->where('advisor_status', '=', 1)->count();
                $row->completed_leads = $completed_leads;
    
                $lost_leads = AdvisorBids::whereIn('advisor_id',$team_arr)->where('status', '=', 3)->where('advisor_status', '=', 1)->count();
                $row->lost_leads = $lost_leads;

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
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getCompanyDetail($id){
        try {
            $query = new Self;
            $data = $query->where('id',$id)->with('adviser')->with('team_members')->with('notes')->first();
            // with('team_members')->
            $teamadmin = 0;
            $success_per = 0;
            $team_member = array();
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
                    $user_data = User::where('id',$data->company_admin)->first();
                    if($user){
                        $data->company_admin_name = $user->display_name;
                        $user->role = "Admin";
                        $team_data_arr = array(
                            'company_id'=>$data->id,
                            'name'=>$user->display_name,
                            'email'=>$user->email,
                            'advisor_id'=>$data->company_admin,
                            'status'=>1,
                            'isCompanyAdmin'=>0,
                            'is_joined'=>1,
                            'team_data'=>$user_data,
                            'team_data_advisor_profile'=>$user
                        );
                        array_push($team_member,$team_data_arr);
                    }else{
                        $data->company_admin_name = "";
                    }
                    array_push($team_arr,$data->company_admin);
                }else{
                    $data->company_admin_name = "";
                }
                foreach($data->team_members as $team_members_data){
                    $advice_areaCount =  AdvisorBids::where('advisor_status', 1)->where('advisor_id',$team_members_data->advisor_id)
                    ->where('status', '!=', 2)->where('status', '!=', 3)->count();

                    // $team_members_data->accepted_leads = $advice_areaCount;
                    // $live_leads_data = User::getAdvisorLeadsData($team_members_data->advisor_id);
                    // $team_members_data->live_leads = $live_leads_data['total_leads'];
                    // $team_members_data->eastimated_lead = $live_leads_data['eastimated_lead'];
                    // $team_members_data->cost_of_lead = $live_leads_data['cost_of_lead'];
                    $final_eastimated_lead = 0;
                    $cost_lead = 0;
                    $final_cost_of_lead = 0;
                    $cost_lead_final = 0;

                    $advisor_data_team = AdvisorProfile::where('email',$team_members_data->email)->first();
                    if($advisor_data_team){
                        $es_val = AdvisorBids::where('advisor_id','=',$advisor_data_team->advisorId)->where('status', '=', 2)->where('advisor_status', '=', 1)->get();
                        $estimated = config('app.currency').number_format(0.00,0);
                        $area_arr = array();
                        $lead_value = 0;    
                        if(count($es_val)){
                            foreach($es_val as $es_val_data){
                                array_push($area_arr,$es_val_data->area_id);
                            }

                            if(count($area_arr)){
                                $value_data = Advice_area::whereIn('id',$area_arr)->sum('size_want');
                                $main_value = ($value_data/100);
                                $advisorDetaultPercent = 0;
                                $services = DB::table('app_settings')->where('key','estimate_calculation_percent')->first();
                                if($services){
                                    $advisorDetaultPercent = $services->value;
                                }
                                $lead_value = ($main_value)*($advisorDetaultPercent);
                                // $estimated = config('app.currency').number_format($lead_value,2);
                            }
                        }

                        $final_eastimated_lead = $final_eastimated_lead + $lead_value;
                        // echo json_encode($lead_value);exit;
                        // $final_eastimated_lead = config('app.currency').number_format($lead_value,2);
                        if($advisor_data_team->advisorId!=null){
                            array_push($team_arr,$advisor_data_team->advisorId);
                        }
                        $cost_val = AdvisorBids::where('advisor_id','=',$advisor_data_team->advisorId)->where('status', '=', 0)->where('advisor_status', '=', 1)->get();
                        if(count($cost_val)){
                            foreach($cost_val as $cost_val_data){
                                if($cost_val_data->cost_discounted!=0){
                                    $cost_lead = $cost_lead + $cost_val_data->cost_discounted;
                                }
                                if($cost_val_data->cost_discounted==0){
                                    $cost_lead = $cost_lead + $cost_val_data->cost_leads;
                                }
                                // array_push($area_arr_cost,$cost_val_data->area_id);
                            }
                            $cost_lead_final = $cost_lead;
                            
                        }
                        $final_cost_of_lead = $final_cost_of_lead + $cost_lead_final;
                    }
                    $team_members_data->eastimated_lead = $final_eastimated_lead;
                    $team_members_data->cost_of_lead = config('app.currency').number_format($final_cost_of_lead,0);

                    $live_leads = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)
                    ->where('status', '=', 0)
                    ->where('advisor_status', '=', 1)
                    ->get();
                    $live_arr = array();
                    $not_responded = 0;
                    foreach($live_leads as $live_leads_data){
                        $bids = AdvisorBids::where('area_id',$live_leads_data->area_id)->orWhere('status','!=',0)->get();
                        array_push($live_arr,$bids);
                        if(count($bids)){
                            $not_responded = $not_responded + 1;
                        }
                    }
                    $team_members_data->live_leads = $not_responded;

                    $accepted_leads = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)
                    ->count();
                    $team_members_data->accepted_leads = $accepted_leads;

                    $hired_leads = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)
                    ->where('status', '=', 1)
                    ->where('advisor_status', '=', 1)
                    ->count();
                    $team_members_data->hired = $hired_leads;

                    $completed_leads = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)
                    ->where('status', '=', 2)
                    ->where('advisor_status', '=', 1)
                    ->count();
                    $team_members_data->completed = $completed_leads;

                    $value = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)->sum('cost_leads');
                    $cost = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)->sum('cost_discounted');
                    $team_members_data->value = $value;
                    $team_members_data->cost = $cost;
                    $total_bids = AdvisorBids::where('advisor_id',$advisor_data_team->advisorId)->where('advisor_status', '=', 1)->count();
                    if($total_bids!=0){
                        $success_per = ($team_members_data->accepted_leads / $total_bids) * 100;
                    }
                    $team_members_data->success_percent = $success_per;
                }
            }   
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
