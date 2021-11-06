<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\AdvisorBids;
use App\Models\AdvisorProfile;
use App\Models\Notes;
use App\Models\Advice_area;
use App\Models\PostalCodes;
use App\Models\AppSettings;
use App\Models\AdvisorPreferencesCustomer;
use DB;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','post_code','user_role','nationality','address','fca_number','company_name','email_status','invite_count','invited_by','last_active','email_verified_at'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [];
    }
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function notes(){
        return $this->hasMany('App\Models\Notes',"company_id","company_id");
    }
    public function advisor_profile(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","id");
    }
    public function advisor_profile_by_userid(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","user_id");
    }
    
    public static function getLists($search){
        try {
            $query = new Self;
            // echo json_encode($search);exit;
            if(isset($search['search']) && $search['search']!=''){
                $query = $query->where('name', 'like', '%'.strtolower($search['search']).'%');
                // ->orWhere('email', 'like', '%' .strtolower($search['search']). '%')->orWhere('post_code', 'like', '%' .strtolower($search['search']). '%')
            }
            if(isset($search['email_status']) && $search['email_status']!=''){
                $query = $query->where('email_status',$search['email_status']);
            }
            if(isset($search['status']) && $search['status']!=''){
                if($search['status']==0){
                    $query = $query->where('status',$search['status']);
                }else if($search['status']==1){
                    $query = $query->where('status',$search['status'])->whereNotNull('email_verified_at');
                }else if($search['status']==2){
                    $query = $query->where('status',1)->whereNull('email_verified_at');
                }
            }
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->where(DB::raw("(DATE_FORMAT(created_at,'%Y-%m-%d'))"),date("Y-m-d",strtotime($search['created_at'])));
            }
            $data = $query->where('user_role','=',0)->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));

            // echo json_encode($data);exit;
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getAdvisors($search){
        try {
            $query = new Self;
            if(isset($search['search']) && $search['search']!=''){
                $query = $query->where('users.name', 'like', '%' .strtolower($search['search']). '%');
            }
            if(isset($search['email_status']) && $search['email_status']!=''){
                $query = $query->where('users.email_status',$search['email_status']);
            }
            // if(isset($search['status']) && $search['status']!=''){
            //     $query = $query->where('users.status',$search['status']);
            // }
            if(isset($search['status']) && $search['status']!=''){
                if($search['status']==0){
                    $query = $query->where('users.status',$search['status']);
                }else if($search['status']==1){
                    $query = $query->where('users.status',$search['status'])->whereNotNull('users.email_verified_at')->whereNotNull('advisor_profiles.FCA_verified');
                }else if($search['status']==3){
                    $query = $query->where('users.status',1)->whereNull('users.email_verified_at')->whereNull('advisor_profiles.FCA_verified');
                }else if($search['status']==2){
                    $query = $query->where('users.status',$search['status']);
                }
            }
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('users.created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }
            // echo json_encode($search);exit;
            $data = $query->select('advisor_profiles.*','users.email_verified_at','users.email_status','users.status as user_status')->where('users.user_role','=',1)
            ->leftJoin('advisor_profiles', 'users.id', '=', 'advisor_profiles.advisorId')
            ->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            // echo json_encode($data);exit;
            $success_per = 0;
            if(isset($data) && count($data)){
                foreach($data as $row){
                    // $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address','users.status as user_status', 'advisor_bids.advisor_id as advisor_id', 'companies.advisor_id as advisor_id')
                    // ->join('users', 'advice_areas.user_id', '=', 'users.id')
                    // ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
                    // ->where('advisor_bids.advisor_status', '=', 1)
                    // ->where('advisor_bids.advisor_id', '=', $row->advisorId)
                    // ->count();
                    // // $row->live_leads = $advice_areaCount;
                    // $row->accepted_leads = $advice_areaCount;
                    $live_leads = AdvisorBids::where('advisor_id','=',$row->advisorId)
                    ->where('status', '=', 0)
                    ->where('advisor_status', '=', 1)
                    ->get();
                    $live_arr = array();
                    $not_responded = 0;
                    foreach($live_leads as $live_leads_data){
                        // array_push($live_arr,$live_leads_data->area_id);
                        $bids = AdvisorBids::where('area_id',$live_leads_data->area_id)->orWhere('status','!=',0)->get();
                        array_push($live_arr,$bids);
                        if(count($bids)){
                            $not_responded = $not_responded + 1;
                        }
                    }

                    $count = AdvisorBids::whereIn('area_id',$live_arr)->orWhere('status','!=',1)->orWhere('status','!=',2)->orWhere('status','!=',3)->get();
                    // $row->live_leads = json_encode($live_arr);
                    
                    $row->live_leads = $not_responded;
                    
                    $accepted_leads = AdvisorBids::where('advisor_id','=',$row->advisorId)
                    ->count();
                    $row->accepted_leads = $accepted_leads;
    
                    $live_leads_data = User::getAdvisorLeadsData($row->advisorId);
                    // $row->live_leads = $live_leads_data['total_leads'];
                    $row->eastimated_lead = $live_leads_data['eastimated_lead'];
                    $row->cost_of_lead = $live_leads_data['cost_of_lead'];
    
                    $hired_leads = AdvisorBids::where('advisor_id','=',$row->advisorId)
                    ->where('status', '=', 1)
                    ->where('advisor_status', '=', 1)
                    ->count();
                    $row->hired_leads = $hired_leads;
    
                    $completed_leads = AdvisorBids::where('advisor_id','=',$row->advisorId)
                    ->where('status', '=', 2)
                    ->where('advisor_status', '=', 1)
                    ->count();
                    $row->completed_leads = $completed_leads;
    
                    $lost_leads = AdvisorBids::where('advisor_id','=',$row->advisorId)
                    ->where('status', '=', 3)
                    ->where('advisor_status', '=', 1)
                    ->count();
                    $row->lost_leads = $lost_leads;
                    $value = AdvisorBids::where('advisor_id','=',$row->advisorId)->sum('cost_leads');
                    $cost = AdvisorBids::where('advisor_id','=',$row->advisorId)->sum('cost_discounted');
                    $es_val = AdvisorBids::where('advisor_id','=',$row->advisorId)->where('status', '=', 2)->where('advisor_status', '=', 1)->get();
                    $estimated = config('app.currency').number_format(0.00,0);
                    $area_arr = array();
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
                            $estimated = config('app.currency').number_format($lead_value,0);
                        }
                        
                    }
                    // $row->value = json_encode($es_val);
                    $row->estimated_lead_value = $estimated;
                    $cost_val = AdvisorBids::where('advisor_id','=',$row->advisorId)->where('status', '=', 0)->where('advisor_status', '=', 1)->get();
                    $cost_lead_final = config('app.currency').number_format(0.00,0);
                    $cost_lead = 0;
                    $area_arr_cost = array();
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
                        $cost_lead_final = config('app.currency').number_format($cost_lead,0);
                        
                    }
                    // $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
                    
                    $area_data = Advice_area::whereIn('id',$area_arr)->get();
                    $cost_add = 0;
                    foreach($area_data as $area_data_data){
                        $adviceBid = AdvisorBids::where('area_id',$area_data_data->id)->orderBy('status','ASC')->get();                        
                        $costOfLead = ($area_data_data->size_want/100)*0.006;
                        $time1 = Date('Y-m-d H:i:s');
                        $time2 = Date('Y-m-d H:i:s',strtotime($area_data_data->created_at));
                        $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
                        $costOfLeadsStr = "";
                        $costAmount = 0;
                        $costOfLeadsDropStr = "";
                        $amount = number_format((float)$costOfLead, 2, '.', '');
                        if($hourdiff < 24) {
                            $costOfLeadsStr = "".$area_data_data->size_want_currency.$amount;
                            $in = 24-$hourdiff;
                            $hrArr = explode(".",$in);
                            $costOfLeadsDropStr = "Cost of lead drops to ".$area_data_data->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                            $costAmount = $amount;
                        }
                        if($hourdiff > 24 && $hourdiff < 48) {
                            $costOfLeadsStr = "".$area_data_data->size_want_currency.($amount/2)." (Save 50%, was ".$area_data_data->size_want_currency.$amount.")";
                            $in = 48-$hourdiff;
                            $newAmount = (75 / 100) * $amount;
                            $hrArr = explode(".",$in);
                            $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                            $costAmount = $amount/2;
                        }
                        if($hourdiff > 48 && $hourdiff < 72) {
                            $newAmount = (75 / 100) * $amount;
                            $costOfLeadsStr = "".($amount-$newAmount)." (Save 50%, was ".$area_data_data->size_want_currency.$amount.")";
                            $in = 72-$hourdiff;
                            $hrArr = explode(".",$in);
                            $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                            $costAmount = $amount-$newAmount;
                        }
                        if($hourdiff > 72) {
                            $costOfLeadsStr = ""."Free";
                            $costOfLeadsDropStr = "";
                            $costAmount = 0;
                        }
                        $row->is_accepted = 0;
                        $cost_add = $cost_add + $costAmount;
                    }
                    $row->cost_of_lead_final = $cost_add;
                    // foreach($value_data){

                    // }
                    // $costOfLead = ($item->size_want/100)*0.006;
                    // $costOfLeadsStr = "";
                    // $costOfLeadsDropStr = "";
                    // $amount = number_format((float)$costOfLead, 2, '.', '');
                    // if($hourdiff < 24) {
                    //     $costOfLeadsStr = "".$item->size_want_currency.$amount;
                    //     $in = 24-$hourdiff;
                    //     $hrArr = explode(".",$in);
                    //     $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                    // }
                    // if($hourdiff > 24 && $hourdiff < 48) {
                    //     $costOfLeadsStr = "".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                    //     $in = 48-$hourdiff;
                    //     $newAmount = (75 / 100) * $amount;
                    //     $hrArr = explode(".",$in);
                    //     $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                    // }
                    // if($hourdiff > 48 && $hourdiff < 72) {
                    //     $newAmount = (75 / 100) * $amount;
                    //     $costOfLeadsStr = "".($amount-$newAmount)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                    //     $in = 72-$hourdiff;
                    //     $hrArr = explode(".",$in);
                    //     $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                    // }
                    // if($hourdiff > 72) {
                    //     $costOfLeadsStr = ""."Free";
                    //     $costOfLeadsDropStr = "";
                    // }
                    // $row->is_accepted = 0;
                    
                    // $row->cost_of_lead_final = $costOfLeadsStr;

                    $row->cost_of_leads_value = $cost_lead_final;
                    $total_bids = AdvisorBids::where('advisor_id',$row->advisorId)->where('advisor_status', '=', 1)->count();
                    if($total_bids!=0){
                        $success_per = ($row->completed_leads / $total_bids) * 100;
                    }
                    $row->success_percent = $success_per; 
                    $company_admin = companies::where('company_admin',$row->advisorId)->first();
                    if(isset($company_admin) && $company_admin){
                        $row->role = "Admin";
                    }else{
                        $row->role = "Adviser";
                    }
                }
            }
            
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getAdvisorDetail($id){
        try {
            $query = new Self;
            $data['userDetails'] = $query->where('id','=',$id)->first();
            // echo json_encode($id);exit;
            $advisorProfile = AdvisorProfile::where('advisorId','=',$id)->first();
            if($advisorProfile){
                $company = companies::where('id',$advisorProfile->company_id)->first();
                if($company){
                    $advisorProfile->adviser_company_name = $company->company_name;
                }
                $postCode = PostalCodes::select('District','Country')->where('Postcode',$advisorProfile->post_code)->first();
                if($postCode){
                    $advisorProfile->district = $postCode->District;
                    $advisorProfile->country = $postCode->Country;
                }else{
                    $advisorProfile->district = "";
                    $advisorProfile->country = "";
                }
                $company_admin = companies::where('company_admin',$advisorProfile->advisorId)->first();
                if(isset($company_admin) && $company_admin){
                    $data['userDetails']->role = "Admin";
                }else{
                    $data['userDetails']->role = "Adviser";
                }
            }
            // ->with('notes')
            $data['userDetails'] = (object) $data['userDetails'];
            
            if($advisorProfile){
                $advisorProfile->notes = Notes::where('company_id',$advisorProfile->company_id)->get();
            }

            $data['invoice'] = DB::table('invoices')->where('advisor_id',$data['userDetails']->id)->where('month',date('m'))->first();
            $newTotal = 0;
            $discountTotal = 0;
            $bid_data = AdvisorBids::where('advisor_id',$data['userDetails']->id)->with('area')->get();
            foreach($bid_data as $pre){
                if(date("m",strtotime($pre->created_at))==date("m")){
                    $newTotal = $newTotal + $pre->cost_leads;
                }
                $date = date('Y-m-d', strtotime($pre->created_at . " +1 days"));
                if(date("Y-m-d",strtotime($pre->accepted_date))>$date){
                    $discountTotal = $discountTotal + $pre->cost_discounted;
                }
            }            
            $data['total_due'] = $newTotal - $discountTotal;
            $data['profile'] = $advisorProfile;
            $success_per = 0;
            $closedArr = array();
            $closed_count = 0;
            if($data['userDetails']){
                $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address','users.status as user_status', 'advisor_bids.advisor_id as advisor_id', 'companies.advisor_id as advisor_id')
                ->join('users', 'advice_areas.user_id', '=', 'users.id')
                ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
                ->where('advisor_bids.advisor_status', '=', 1)
                ->where('advisor_bids.advisor_id', '=', $data['userDetails']->id)
                ->count();
                // $row->live_leads = $advice_areaCount;
                // $data['userDetails']->accepted_leads = $advice_areaCount;
                
                // $live_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                // ->where('status', '=', 0)
                // ->where('advisor_status', '=', 1)
                // ->count();

                $live_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
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
                $data['userDetails']->live_leads = $not_responded;
                
                $accepted_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                ->count();
                $data['userDetails']->accepted_leads = $accepted_leads;

                $live_leads_data = User::getAdvisorLeadsData($data['userDetails']->id);
                // $data['userDetails']->live_leads = $live_leads_data['total_leads'];
                $data['userDetails']->eastimated_lead = $live_leads_data['eastimated_lead'];
                $data['userDetails']->cost_of_lead = $live_leads_data['cost_of_lead'];

                $hired_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                ->where('status', '=', 1)
                ->where('advisor_status', '=', 1)
                ->count();
                $data['userDetails']->hired_leads = $hired_leads;

                $completed_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                ->where('status', '=', 2)
                ->where('advisor_status', '=', 1)
                ->count();
                $data['userDetails']->completed_leads = $completed_leads;

                $lost_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                ->where('status', '=', 3)
                ->where('advisor_status', '=', 1)
                ->count();
                $data['userDetails']->lost_leads = $lost_leads;

                $closed = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                ->where('advisor_status', '=', 1)
                ->get();
                if(isset($closed) && count($closed)){
                    foreach($closed as $closed_data){
                        array_push($closedArr,$closed_data->area_id);
                    }
                }
                if(isset($closedArr) && count($closedArr)){
                    $closed_count = Advice_area::whereIn('id',$closedArr)->where('status',2)->count();
                }
                $data['userDetails']->closed = $closed_count;
                $not_proceed = 0;
                $get_not_proceed = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)->get();
                // echo json_encode($get_not_proceed);
                $data['userDetails']->not_proceed = 0;
                if(isset($get_not_proceed) && count($get_not_proceed)){
                    foreach($get_not_proceed as $not_proceed_data){
                        $check_proceed = AdvisorBids::where('area_id',$not_proceed_data->id)->where('status', '=', 0)->where('advisor_status', '=', 1)->count();
                        if($check_proceed){
                            $not_proceed = $not_proceed + 1;
                        }
                    }
                    // where('status', '=', 0)->where('advisor_status', '=', 1)->count()
                    $data['userDetails']->not_proceed = $not_proceed;
                }
                $value = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)->sum('cost_leads');
                $cost = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)->sum('cost_discounted');
                $data['userDetails']->value = $value;
                $data['userDetails']->cost = $cost;
                $total_bids = AdvisorBids::where('advisor_id',$data['userDetails']->id)->where('advisor_status', '=', 1)->count();
                if($total_bids!=0){
                    $success_per = ($data['userDetails']->completed_leads / $total_bids) * 100;
                }
                $data['userDetails']->success_percent = $success_per;
            }
            // exit;
            // echo json_encode($data);exit;
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getAdvisorLeadsData($id){
        $user = User::where('id',$id)->first();
        $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id','=',$user->id)->first();
        $requestTime = [];
        if($userPreferenceCustomer!=''){
            $ltv_max = $userPreferenceCustomer->ltv_max;
            $lti_max = $userPreferenceCustomer->lti_max;
        }else{
            $ltv_max = "";
            $lti_max = "";
        }
        $cost_of_lead = 0;
        $estimated_lead = 0;

        if(!empty($userPreferenceCustomer)) {
            if($userPreferenceCustomer->asap == 1) {
                $requestTime[] = "as soon as possible";
            }
            if($userPreferenceCustomer->next_3_month == 1) {
                $requestTime[] = "in the next 3 months";
            }
            if($userPreferenceCustomer->more_3_month == 1) {
                $requestTime[] = "in more than 3 months";
            }
        }
        
        // TODO: Ltv max and Lti Max need to check for filter
        $userPreferenceProduct = AdvisorPreferencesProducts::where('advisor_id','=',$id)->first();
        $service_type = array();
        if(!empty($userPreferenceProduct)) {
            if($userPreferenceProduct->remortgage == 1) {
                $service_type[] = "remortgage";
            }
            if($userPreferenceProduct->first_buyer == 1) {
                $service_type[]= "first time buyer";
            }
            if($userPreferenceProduct->next_buyer == 1) {
                $service_type[]= "next time buyer";
            }
            if($userPreferenceProduct->but_let == 1) {
                $service_type[]= "buy to let";
            }
            if($userPreferenceProduct->equity_release == 1) {
                $service_type[]= "equity release";
            }
            if($userPreferenceProduct->overseas == 1) {
                $service_type[]= "overseas";
            }
            if($userPreferenceProduct->self_build == 1) {
                $service_type[]= "self build";
            }
            if($userPreferenceProduct->mortgage_protection == 1) {
                $service_type[]= "mortgage protection";
            }
            if($userPreferenceProduct->secured_loan == 1) {
                $service_type[]= "secured loan";
            }
            if($userPreferenceProduct->bridging_loan == 1) {
                $service_type[]= "bridging loan";
            }
            if($userPreferenceProduct->commercial == 1) {
                $service_type[]= "commercial";
            }
            if($userPreferenceProduct->something_else == 1) {
                $service_type[]= "something else";
            }
        }
        // DB::enableQueryLog();
        
       $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address')
            ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
            ->where(function($query) use ($userPreferenceCustomer){
                if(!empty($userPreferenceCustomer)) {
                    if($userPreferenceCustomer->self_employed == 1){
                        $query->orWhere('advice_areas.self_employed','=',$userPreferenceCustomer->self_employed);
                    }
                    if($userPreferenceCustomer->non_uk_citizen == 1){
                        $query->orWhere('advice_areas.non_uk_citizen','=',$userPreferenceCustomer->non_uk_citizen);
                    }
                    if($userPreferenceCustomer->adverse_credit == 1){
                        $query->orWhere('advice_areas.adverse_credit','=',$userPreferenceCustomer->adverse_credit);
                    }
                    if($userPreferenceCustomer->fees_preference == 1){
                        $query->orWhere('advice_areas.fees_preference','=',$userPreferenceCustomer->fees_preference);
                    }
                }
        })->where(function($query) use ($requestTime){
                // if($requestTime != ""){
                //     $query->where('advice_areas.request_time','=',$requestTime);
                // }
                if(!empty($requestTime)) {
                    $query->where(function($q) use ($requestTime) {
                        foreach($requestTime as $rtime){
                            $q->orWhere('advice_areas.request_time',$rtime);
                        }
                    });
                }
        })->where(function($query) use ($service_type){
                if(!empty($service_type)) {
                    $query->where(function($q) use ($service_type) {
                        foreach($service_type as $sitem){
                            $q->orWhere('advice_areas.service_type',$sitem);
                        }
                    });
                }
            
        })->where(function($query) use ($ltv_max){
            if($ltv_max != "") {
               
                $query->where('advice_areas.ltv_max','<=',chop($ltv_max,"%"));
                $query->where('advice_areas.ltv_max','>',0);
            }
        })->where(function($query) use ($lti_max){
            if($lti_max != "") {
                //  echo chop($ltv_max,"%");die;
                $query->where('advice_areas.lti_max','<=',chop($lti_max,"x"));
                $query->where('advice_areas.lti_max','>',0);
            }
        })->whereNotIn('advice_areas.id',function($query) use ($user){
            $query->select('area_id')->from('advisor_bids')->where('advisor_id','=',$user->id);
        })->orderBy('advice_areas.id','DESC')->groupBy('advice_areas.'.'id')
        ->groupBy('advice_areas.'.'user_id')
        ->groupBy('advice_areas.'.'service_type')
        ->groupBy('advice_areas.'.'request_time')
        ->groupBy('advice_areas.'.'property')
        ->groupBy('advice_areas.'.'property_want')
        ->groupBy('advice_areas.'.'size_want')
        ->groupBy('advice_areas.'.'combined_income')
        ->groupBy('advice_areas.'.'description')
        ->groupBy('advice_areas.'.'occupation')
        ->groupBy('advice_areas.'.'contact_preference')
        ->groupBy('advice_areas.'.'advisor_preference')
        ->groupBy('advice_areas.'.'fees_preference')
        ->groupBy('advice_areas.'.'self_employed')
        ->groupBy('advice_areas.'.'non_uk_citizen')
        ->groupBy('advice_areas.'.'adverse_credit')
        ->groupBy('advice_areas.'.'contact_preference_face_to_face')
        ->groupBy('advice_areas.'.'contact_preference_online')
        ->groupBy('advice_areas.'.'contact_preference_telephone')
        ->groupBy('advice_areas.'.'contact_preference_evening_weekend')
        ->groupBy('advice_areas.'.'advisor_preference_local')
        ->groupBy('advice_areas.'.'advisor_preference_gender')
        ->groupBy('advice_areas.'.'status')
        ->groupBy('advice_areas.'.'combined_income_currency')
        ->groupBy('advice_areas.'.'property_currency')
        ->groupBy('advice_areas.'.'size_want_currency')
        ->groupBy('advice_areas.'.'advisor_id')
        ->groupBy('advice_areas.'.'close_type')
        ->groupBy('advice_areas.'.'need_reminder')
        ->groupBy('advice_areas.'.'initial_term')
        ->groupBy('advice_areas.'.'start_date')
        ->groupBy('advice_areas.'.'created_at')
        ->groupBy('advice_areas.'.'updated_at')
        ->groupBy('users.'.'name')
        ->groupBy('users.'.'email')
        ->groupBy('users.'.'address')
        ->groupBy('advice_areas.'.'ltv_max')
        ->groupBy('advice_areas.'.'lti_max')
        ->groupBy('advice_areas.'.'advisor_preference_language')->paginate();
        $bidCountArr = array();
        //$lastquery = DB::getQueryLog();
        //dd(end($lastquery));
        //echo '<pre>=';print_r($advice_area);die;
        foreach($advice_area as $key=> $item) {
            $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3)? 0:1;
            }
            $advice_area[$key]->totalBids = $bidCountArr;
            
            $costOfLead = ($item->size_want/100)*0.006;
            $time1 = Date('Y-m-d H:i:s');
            $time2 = Date('Y-m-d H:i:s',strtotime($item->created_at));
            $hourdiff = round((strtotime($time2) - strtotime($time1))/3600, 1);
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');
            if($hourdiff < 24) {
                $costOfLeadsStr = "".$item->size_want_currency.$amount;
                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr = "".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr = "".($amount-$newAmount)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 72) {
                $costOfLeadsStr = ""."Free";
                $costOfLeadsDropStr = "";
            }
            $cost_of_lead = $costOfLeadsStr;
            $advice_area[$key]->cost_of_lead = $costOfLeadsStr;
            $advice_area[$key]->cost_of_lead_drop = $costOfLeadsDropStr;
            $area_owner_details = User::where('id',$item->user_id)->first();
            $address = "";
            if(!empty($area_owner_details)) {
                $addressDetails = PostalCodes::where('Postcode','=',$area_owner_details->post_code)->first();
                if(!empty($addressDetails)) {
                    if($addressDetails->Country != ""){
                        $address = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
                        $address .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                        $address .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                    }
                    
                }
            }
            $lead_value = "";
            $main_value = ($item->size_want/100);
            $advisorDetaultValue = "";
            if($item->service_type=="remortgage") {
                $advisorDetaultValue = "remortgage";
            }else if($item->service_type=="first time buyer") {
                $advisorDetaultValue = "first_buyer";
            }else if($item->service_type=="next time buyer") {
                $advisorDetaultValue = "next_buyer";
            }else if($item->service_type=="buy to let") {
                $advisorDetaultValue = "but_let";
            }else if($item->service_type=="equity release") {
                $advisorDetaultValue = "equity_release";
            }else if($item->service_type=="overseas") {
                $advisorDetaultValue = "overseas";
            }else if($item->service_type=="self build") {
                $advisorDetaultValue = "self_build";
            }else if($item->service_type=="mortgage protection") {
                $advisorDetaultValue = "mortgage_protection";
            }else if($item->service_type=="secured loan") {
                $advisorDetaultValue = "secured_loan";
            }else if($item->service_type=="bridging loan") {
                $advisorDetaultValue = "bridging_loan";
            }else if($item->service_type=="commercial") {
                $advisorDetaultValue = "commercial";
            }else if($item->service_type=="something else") {
                $advisorDetaultValue = "something_else";
            }   
            $AdvisorPreferencesDefault = AdvisorPreferencesDefault::where('advisor_id','=',$user->id)->first();
            $advice_area[$key]->lead_address = $address;
            // echo json_encode($AdvisorPreferencesDefault->$advisorDetaultValue);
            $lead_value = ($main_value)*($AdvisorPreferencesDefault[$advisorDetaultValue]);
            $advice_area[$key]->lead_value = $item->size_want_currency.$lead_value;
            $estimated_lead = $lead_value;
        }
        // exit;
        $data['total_leads'] = $advice_area->count();
        $data['eastimated_lead'] = $estimated_lead;
        $data['cost_of_lead'] = $cost_of_lead;

        // echo json_encode($data);exit;
        return $data;
    }
}