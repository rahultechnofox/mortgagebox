<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Advice_area extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'service_type_id', 'service_type','request_time', 'property','property_want', 'size_want','combined_income', 'description','occupation', 'contact_preference', 'advisor_preference', 'fees_preference','self_employed','non_uk_citizen','adverse_credit','contact_preference_face_to_face','contact_preference_online','contact_preference_telephone','contact_preference_evening_weekend','advisor_preference_local','advisor_preference_gender','advisor_preference_language','status','combined_income_currency','property_currency','size_want_currency','close_type','advisor_id','need_reminder','initial_term','start_date','ltv_max','lti_max','inquiry_adviser_id','inquiry_match_me','inquiry_description'
    ];

    public function user(){
        return $this->hasOne('App\Models\User',"id","user_id");
    }
    public function advisor_profile(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","user_id");
    }
    public function service(){
        return $this->hasOne('App\Models\ServiceType',"id","service_type_id");
    }
    public function total_bid_count(){
        return $this->hasMany('App\Models\AdvisorBids',"area_id","id");
    }
    public function total_lost_bid_count(){
        return $this->hasMany('App\Models\AdvisorBids',"area_id","id")->where('status',0);
    }
    
    public static function getNeedList($search){
        try {
            $query = new Self;
            $userId = array();
            $perpage = config('constants.paginate.num_per_page');
            if(isset($search['search']) && $search['search']!=''){
                $user = User::where('name', 'like', '%' .strtolower($search['search']). '%')->get();
                if(count($user)){
                    foreach($user as $row){
                        array_push($userId,$row->id);
                    }
                }
            }
            if(count($userId)){
                $query = $query->whereIn('advice_areas.user_id',$userId);
            }
            if(isset($search['service_id']) && $search['service_id']!=''){
                $query = $query->where('advice_areas.service_type_id',$search['service_id']);
            }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('advice_areas.status',$search['status']);
            }
            if(isset($search['per_page']) && $search['per_page']!=''){
                $perpage = $search['per_page'];
            }
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('advice_areas.created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }
            if(isset($search['area_id']) && count($search['area_id'])){
                $query = $query->whereIn('advice_areas.id',$search['area_id']);
            }
            $advice_area = $query->select('advice_areas.*','users.name','users.email')->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
            ->with('service')->orderBy('id','DESC')->paginate($perpage);
            $count = 0;
            // echo json_encode($advice_area);exit;
            foreach ($advice_area as $key => $item) {
                // echo json_encode($item->id);
                $advisers = AdvisorBids::where('area_id',$item->id)->get();
                // $review = ReviewRatings::where('area_id',$item->id)->first();
                // if($review){
                //     $advice_area[$key]->offer_count = $review;
                // }
                // echo json_encode($advisers);
                $date = date('Y-m-d H:i:s');
                // echo json_encode($date);
                $offer_count = count($advisers);
                foreach($advisers as $adviser_data){
                    $user_data_active = User::where('id',$adviser_data->advisor_id)->first();
                    if($user_data_active){
                        $active_time = date('Y-m-d H:i:s',strtotime('+5 minutes', strtotime($user_data_active->last_active)));
                        // echo json_encode($active_time);
                        if($date>=date('Y-m-d H:i:s',strtotime($user_data_active->last_active)) && $date<$active_time){
                            $count = $count+1;
                        }
                    }
                }
                $item->active_count = $count;
                $count = 0;
                $active_bids = AdvisorBids::where('area_id','=',$item->id)->where('status',1)->where('advisor_status',1)->count();
                $bidDetails = AdvisorBids::where('area_id','=',$item->id)->where('status','>','0')->first();
                if(!empty($bidDetails)) {
                    $advice_area[$key]->bid_status = $bidDetails->status;
                }else{
                    $advice_area[$key]->bid_status ="N/A";
                }
                $advice_area[$key]->offer_count = $offer_count;
                $advice_area[$key]->active_bids = $active_bids;
                $advice_area[$key]->selected_pro = AdvisorBids::where('area_id',$item->id)->where('advisor_bids.advisor_status',1)->where('advisor_bids.status','!=',0)->leftJoin('users', 'advisor_bids.advisor_id', '=', 'users.id')->select('advisor_bids.*','users.name as advisor_name')->first();
                if($advice_area[$key]->close_type!=0){
                    if($advice_area[$key]->close_type==1){
                        $advice_area[$key]->close_type="Someone not on Mortgagebox";
                    }else if($advice_area[$key]->close_type==12){
                        $advice_area[$key]->close_type="In the end I didn’t need a mortgage adviser";
                    }else{
                        $advice_area[$key]->close_type="--";
                    }
                }else if($advice_area[$key]->advisor_id!=0){
                    $user = AdvisorProfile::where('advisorId',$advice_area[$key]->advisor_id)->first();
                    if($user){
                        $advice_area[$key]->close_type=$user->display_name;
                    }else{
                        $advice_area[$key]->close_type="--";
                    }
                    
                }else{
                    $advice_area[$key]->close_type="--";
                }
                $rating =  ReviewRatings::select('review_ratings.*')->where('review_ratings.area_id',$item->id)->where('review_ratings.status',0)->first();
                $averageRating = ReviewRatings::where('review_ratings.area_id',$item->id)->where('review_ratings.status', '=', 0)->avg('rating');

                $advice_area[$key]->avarageRating = number_format((float)$averageRating, 2, '.', '');
                $advice_area[$key]->rating = $rating;
            }
            // exit;
            $data['userDetails'] = $advice_area;
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getMatchNeedFilter($search){
        try {
            $query = new Self;
            $advisorAreaArr = array();

            $AdviceAreaIds = array();
            $final_date = array();
            $datesAreaIds = array();
            $statusIds = array();
            $mortgageValueIds = array();
            $finalAreaArr = array();
            $preferencesIds = array();
            $promotionIds = array();
            $ltv_max = "";
            $lti_max = "";

            $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id','=',$search['user_id'])->first();
            if($userPreferenceCustomer){
                $ltv_max = $userPreferenceCustomer->ltv_max;
                $lti_max = $userPreferenceCustomer->lti_max;
            }
            
            if(isset($search['advice_area']) && count($search['advice_area'])){
                $advisorAreaArr = array(-1);
                $default = Advice_area::whereIn('service_type_id',$search['advice_area'])->where('status',1)->get();
                if(count($default)){
                    foreach($default as $default_data){
                        array_push($advisorAreaArr,$default_data->id);
                    }
                }
            }

            if(isset($search['fees_preference']) && count($search['fees_preference'])){
                $preferencesIds = array(-1);
                for($i=0;$i<count($search['fees_preference']);$i++){
                    if($search['fees_preference'][$i]=="no_fee") {
                        $preference = Advice_area::where('fees_preference',0)->where('status',1)->get();
                        foreach($preference as $preference_data){
                            array_push($preferencesIds,$preference_data->id);
                        }
                    }else{  
                        $preference_no = Advice_area::where('fees_preference',1)->where('status',1)->get();
                        foreach($preference_no as $preference_no_data){
                            array_push($preferencesIds,$preference_no_data->id);
                        }
                    }
                }

                if(count($advisorAreaArr)>0){
                    $advisorAreaArr = array_intersect($advisorAreaArr, $preferencesIds);
                }else{
                    $advisorAreaArr = array_unique($preferencesIds);
                }
            }

            if(isset($search['mortgage_value']) && count($search['mortgage_value'])){
                $mortgageValueIds = array(-1);
                for($i=0;$i<count($search['mortgage_value']);$i++){
                    $explode = explode("_",$search['mortgage_value'][$i]);
                    if($explode[0]>0){
                        $explode[0] = (int)$explode[0]."000";
                    }
                    if($explode[1]>0){
                        $explode[1] = (int)$explode[1]."000";
                    }
                    
                    $ad = Advice_area::where('size_want','>',$explode[0])->where('size_want','<=',$explode[1])->get();
                    if(count($ad)){
                        foreach($ad as $ad_data){
                            array_push($mortgageValueIds,$ad_data->id);
                        }
                    }
                }
                if(count($advisorAreaArr)>0){
                    $advisorAreaArr = array_intersect($advisorAreaArr, $mortgageValueIds);
                }else{
                    $advisorAreaArr = array_unique($mortgageValueIds);
                }
            }

            if(isset($search['lead_submitted']) && count($search['lead_submitted'])){
                $datesAreaIds = array(-1);
                foreach($search['lead_submitted'] as $item){
                    if($item=='today'){
                        $today = Advice_area::where('created_at', '>=',Carbon::today())->get();
                        if(count($today)){
                            foreach($today as $today_data){
                                array_push($datesAreaIds,$today_data->id);
                            }
                        }
                    }
                    if($item=='yesterday'){
                        $yesterday = Advice_area::where('created_at','>=', Carbon::yesterday())->where('created_at', '<=',Carbon::today())->get();
                        if(count($yesterday)){
                            foreach($yesterday as $yesterday_data){
                                array_push($datesAreaIds,$yesterday_data->id);
                            }
                        }
                    }
                    if($item=='last_hour'){
                        $last_hour = Advice_area::where('created_at','>=' ,date("Y-m-d H:i:s", strtotime('-1 hour')))->get();
                        if(count($last_hour)){
                            foreach($last_hour as $last_hour_data){
                                array_push($datesAreaIds,$last_hour_data->id);
                            }
                        }
                    }
                    if($item=='less_3_days'){
                        $three_days = Advice_area::where('created_at', '>', Carbon::today()->subDays(3))->get();
                        if(count($three_days)){
                            foreach($three_days as $three_days_data){
                                array_push($datesAreaIds,$three_days_data->id);
                            }
                        }
                    }
                    if($item=='less_3_week'){
                        $three_week = Advice_area::where('created_at', '>', Carbon::today()->subDays(7))->get();
                        if(count($three_week)){
                            foreach($three_week as $three_week_data){
                                array_push($datesAreaIds,$three_week_data->id);
                            }
                        }
                    }
                }
               // echo json_encode($datesAreaIds);exit;
                if(count($advisorAreaArr)>0){
                    $advisorAreaArr = array_intersect($advisorAreaArr, $datesAreaIds);
                }else{
                    $advisorAreaArr = array_unique($datesAreaIds);
                }
            }

            if(isset($search['status']) && count($search['status'])){
                $statusIds = array(-1);
                foreach($search['status'] as $status){
                    if($status=='read'){
                        $area_read = AdviceAreaRead::where('adviser_id',$search['user_id'])->get();
                        if(count($area_read)){
                            foreach($area_read as $area_read_data){
                                array_push($statusIds,$area_read_data->area_id);
                            }
                        }
                    }
                    if($status=='unread'){
                        $aStatus = array(-1);
                        $area = AdviceAreaRead::where('adviser_id',$search['user_id'])->get();
                        if(count($area)){
                            foreach($area as $default_data){
                                array_push($aStatus,$default_data->area_id);
                            }
                        }
                        if(count($aStatus)){
                            $area_id = Advice_area::whereNotIn('id',$aStatus)->get();
                            if(count($area_id)){
                                foreach($area_id as $area_id_data){
                                    array_push($statusIds,$area_id_data->id);
                                }
                            }
                        }
                    }
                    if($status=='not-interested'){
                        $area_intrest = AdvisorBids::where('advisor_id',$search['user_id'])->where('advisor_status',2)->get();
                        if(count($area_intrest)){
                            foreach($area_intrest as $area_intrest_data){
                                array_push($statusIds,$area_intrest_data->area_id);
                            }
                        }
                    }
                }
                
                if(count($advisorAreaArr)>0){
                    $advisorAreaArr = array_intersect($advisorAreaArr, $statusIds);
                }else{
                    $advisorAreaArr = array_unique($statusIds);
                }
            }
            if(isset($search['promotion']) && count($search['promotion'])){
                $promotionIds = array(-1);
                foreach($search['promotion'] as $item){
                    if($item=='none'){
                        $none = AdvisorBids::where('is_discounted', 0)->get();
                        if(count($none)){
                            foreach($none as $none_data){
                                array_push($promotionIds,$none_data->area_id);
                            }
                        }
                    }
                    if($item=='75-off'){
                        $third = AdvisorBids::where('is_discounted', 1)->where('discount_cycle', "Third cycle")->get();
                        if(count($third)){
                            foreach($third as $third_data){
                                array_push($promotionIds,$third_data->area_id);
                            }
                        }
                    }
                    if($item=='50-off'){
                        $half = AdvisorBids::where('is_discounted', 1)->where('discount_cycle', "Second cycle")->get();
                        if(count($half)){
                            foreach($half as $half_data){
                                array_push($promotionIds,$half_data->area_id);
                            }
                        }
                    }
                    if($item=='free'){
                        $free = AdvisorBids::where('is_discounted', 1)->where('discount_cycle', "Fourth cycle")->get();
                        if(count($free)){
                            foreach($free as $free_data){
                                array_push($promotionIds,$free_data->area_id);
                            }
                        }
                    }
                }
                if(count($advisorAreaArr)>0){
                    $advisorAreaArr = array_intersect($advisorAreaArr, $promotionIds);
                }else{
                    $advisorAreaArr = array_unique($promotionIds);
                }
            }
            if(count($advisorAreaArr)){
                $query = $query->whereIn('id',$advisorAreaArr);
            }
            $data = $query->with('service')->orderBy('id','DESC')->paginate();
            if(count($data)){
                foreach($data as $key=> $item) {
                    $item->created_at_need = date("d-m-Y H:i",strtotime($item->created_at));
                    $bidCountArr = array();
                    $user = User::where('id',$item->user_id)->first();
                    if($user){
                        $item->name = $user->name;
                        $item->email = $user->email;
                        $item->address = $user->address;
                    }
                    $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
                    foreach($adviceBid as $bid) {
                        $bidCountArr[] = ($bid->status == 3)? 0:1;
                    }
                    $data[$key]->totalBids = $bidCountArr;

                    $costOfLead = ($item->size_want/100)*0.006;
                    $time1 = Date('Y-m-d H:i:s');
                    $time2 = Date('Y-m-d H:i:s',strtotime($item->created_at));
                    $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
                    $costOfLeadsStr = "";
                    $costOfLeadsDropStr = "";
                    $leadSummary = "";
                    $amount = number_format((float)$costOfLead, 2, '.', '');
                    if($hourdiff < 24) {
                        $costOfLeadsStr = " ".$item->size_want_currency.$amount;
                        $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$amount;
                        $leadSummary = "This lead will cost ".$item->size_want_currency.$amount;

                        $in = 24-$hourdiff;
                        $hrArr = explode(".",$in);
                        $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                    }
                    if($hourdiff > 24 && $hourdiff < 48) {
                        $costOfLeadsStr = " ".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                        $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                        $in = 48-$hourdiff;
                        $newAmount = (75 / 100) * $amount;
                        $hrArr = explode(".",$in);
                        $leadSummary = "This lead will cost ".$item->size_want_currency.($amount/2);

                        $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                    }
                    if($hourdiff > 48 && $hourdiff < 72) {
                        $newAmount = (75 / 100) * $amount;
                        $costOfLeadsStr = " ".$item->size_want_currency.($amount-$newAmount)." (Save 75%, was ".$item->size_want_currency.$amount.")";
                        $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.($amount-$newAmount)." (Save 75%, was ".$item->size_want_currency.$amount.")";
                        $leadSummary = "This lead will cost ".$item->size_want_currency.($amount-$newAmount);

                        $in = 72-$hourdiff;
                        $hrArr = explode(".",$in);
                        $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                    }
                    if($hourdiff > 72) {
                        $costOfLeadsStr = ""."Free";
                        $costOfLeadsStrWithCostOflead = "Cost of lead "."Free";
                        $leadSummary = "This lead is free";

                        $costOfLeadsDropStr = "";
                    }
                    if($user->free_promotions>0){
                        $costOfLeadsStr = " ".$item->size_want_currency."0 - free introduction (Save 100%, was ".$item->size_want_currency.$amount.")";
                        $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency."0 - free introduction (Save 100%, was ".$item->size_want_currency.$amount.")";
                        $leadSummary = "This lead is free";
                    }
                    $data[$key]->is_accepted = 0;
                    
                    $data[$key]->cost_of_lead = $costOfLeadsStr;
                    $data[$key]->cost_of_lead_with_cost = $costOfLeadsStrWithCostOflead;

                    $data[$key]->cost_of_lead_drop = $costOfLeadsDropStr;
                    $data[$key]->lead_summary = $leadSummary;
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
                    $advisorDetaultPercent = 0;
                    if($item->service_type_id!=0){
                        $services = DefaultPercent::where('adviser_id',$search['user_id'])->where('service_id',$item->service_type_id)->first();
                        if($services){
                            $advisorDetaultPercent = $services->value_percent;
                        }
                    }
                    $lead_value = ($main_value)*($advisorDetaultPercent);
                    $data[$key]->lead_value = $item->size_want_currency.$lead_value;
                    $data[$key]->lead_address = $address;
                }
            }
            return $data;
        }catch (\Exception $e) {
            // echo json_encode($e->getMessage());exit;
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getAcceptedLeads($search){
        try {
            $query = new Self;
            $advice_arr = array();
            if(isset($search['lead']) && $search['lead']!=''){
                $advice_arr = array(-1);
                if($search['lead']=='live_leads'){
                    $accepted = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',0)->get();
                    // where('advisor_status',1)->
                    if(count($accepted)){
                        foreach($accepted as $accepted_data){
                            $dataPurchased = Advice_area::where('id',$accepted_data->area_id)->where('advisor_id',0)->first();
                            if($dataPurchased){
                                if(!in_array($dataPurchased->id,$advice_arr)){
                                    array_push($advice_arr,$dataPurchased->id);
                                }
                                // array_push($status_arr,$dataPurchased->id);
                            }
                        }
                    }
                    // $accepted = AdvisorBids::where('status',0)->where('advisor_status',1)->where('advisor_id','=',$search['user_id'])->get();
                    // if(count($accepted)){
                    //     foreach($accepted as $accepted_data){
                    //         if(!in_array($accepted_data->area_id,$advice_arr)){
                    //             array_push($advice_arr,$accepted_data->area_id);
                    //         }
                    //     }
                    // }
                }else if($search['lead']=='hired'){
                    $hired = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',1)->get();
                    if(count($hired)){
                        foreach($hired as $hired_data){
                            if(!in_array($hired_data->area_id,$advice_arr)){
                                array_push($advice_arr,$hired_data->area_id);
                            }
                            // array_push($status_arr,$hired_data->area_id);
                        }
                    }
                    // $accepted = AdvisorBids::where('status',1)->where('advisor_id',$search['user_id'])->get();
                    // if(count($accepted)){
                    //     foreach($accepted as $accepted_data){
                    //         if(!in_array($accepted_data->area_id,$advice_arr)){
                    //             array_push($advice_arr,$accepted_data->area_id);
                    //         }
                    //     }
                    // }
                }else if($search['lead']=='completed'){
                    $completed = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',2)->where('advisor_status',1)->get();
                    if(count($completed)){
                        foreach($completed as $completed_data){
                            if(!in_array($completed_data->area_id,$advice_arr)){
                                array_push($advice_arr,$completed_data->area_id);
                            }
                            // array_push($status_arr,$hired_data->area_id);
                        }
                    }
                    // $accepted = AdvisorBids::where('status',2)->where('advisor_status',1)->where('advisor_id','=',$search['user_id'])->get();
                    // if(count($accepted)){
                    //     foreach($accepted as $accepted_data){
                    //         if(!in_array($accepted_data->area_id,$advice_arr)){
                    //             array_push($advice_arr,$accepted_data->area_id);
                    //         }
                    //     }
                    // }
                }else if($search['lead']=='lost'){
                    $AllMyBids = AdvisorBids::where('advisor_id',$search['user_id'])->where('status','!=',1)->where('status','!=',2)->get();
                    if(count($AllMyBids)){
                        foreach($AllMyBids as $bids){
                            $dataLost = Advice_area::where('id',$bids->area_id)->where('advisor_id','!=',$search['user_id'])->where('advisor_id','!=',0)->first();
                            if($dataLost){
                                if(!in_array($dataLost->id,$advice_arr)){
                                    array_push($advice_arr,$dataLost->id);
                                }
                                // array_push($status_arr,$bids->area_id);
                            }else{
                                $dataLostManual = Advice_area::where('id',$bids->area_id)->where('advisor_id',$search['user_id'])->where('area_status',4)->first();
                                if($dataLostManual){
                                    if(!in_array($dataLostManual->id,$advice_arr)){
                                        array_push($advice_arr,$dataLostManual->id);
                                    }
                                }
                            }
                        }
                    }
                    // $AllMyBids = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',0)->get();
                    // if(count($AllMyBids)){
                    //     foreach($AllMyBids as $AllMyBids_data){
                    //         if(!in_array($AllMyBids_data->area_id,$advice_arr)){
                    //             array_push($advice_arr,$AllMyBids_data->area_id);
                    //         }
                    //     }
                    // }
                }   
            }
            if(isset($search['time']) && $search['time']!=''){
                if($search['time']=='this_month'){
                    $query = $query->where('advice_areas.created_at','>=',Carbon::today()->subDays(30));
                }else if($search['time']=='quarter'){
                    $query = $query->where('advice_areas.created_at','>=',Carbon::today()->subDays(183));
                }else if($search['time']=='year'){
                    $query = $query->where('advice_areas.created_at','>=',Carbon::today()->subDays(365));
                }
            }

            if(isset($search['status']) && count($search['status'])>0){
                $status_arr = array(-1);                
                foreach($search['status'] as $status_data){
                    if($status_data=='accepted'){
                        $accepted = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',0)->get();
                        // where('advisor_status',1)->
                        if(count($accepted)){
                            foreach($accepted as $accepted_data){
                                $dataPurchased = Advice_area::where('id',$accepted_data->area_id)->where('advisor_id',0)->first();
                                if($dataPurchased){
                                    array_push($status_arr,$dataPurchased->id);
                                }
                            }
                        }
                        // $status_need = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',0)->get();
                        // if(count($status_need)){
                        //     foreach($status_need as $status_need_data){
                        //         array_push($status_arr,$status_need_data->area_id);
                        //     }
                        // }
                    }
                    if($status_data=='sole_adviser' || $status_data=='hired'){
                        $hired = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',1)->get();
                        if(count($hired)){
                            foreach($hired as $hired_data){
                                array_push($status_arr,$hired_data->area_id);
                            }
                        }
                    }
                    if($status_data=='completed'){
                        $completed = AdvisorBids::where('advisor_id',$search['user_id'])->where('status',2)->where('advisor_status',1)->get();
                        if(count($completed)){
                            foreach($completed as $hired_data){
                                array_push($status_arr,$hired_data->area_id);
                            }
                        }
                    }
    
                    if($status_data=='lost'){
                        $AllMyBidsLost = AdvisorBids::where('advisor_id',$search['user_id'])->where('status','!=',1)->where('status','!=',2)->get();
                        if(count($AllMyBidsLost)){
                            foreach($AllMyBidsLost as $bidsLost){
                                $dataLost = Advice_area::where('id',$bidsLost->area_id)->where('advisor_id','!=',$search['user_id'])->where('advisor_id','!=',0)->first();
                                if($dataLost){
                                    array_push($status_arr,$dataLost->id);
                                }else{
                                    $dataLostManual = Advice_area::where('id',$bidsLost->area_id)->where('advisor_id',$search['user_id'])->where('area_status',4)->first();
                                    if($dataLostManual){
                                        if(!in_array($dataLostManual->id,$status_arr)){
                                            array_push($status_arr,$dataLostManual->id);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if($status_data=='no_response'){
                        $response = Advice_area::where('advisor_id',0)->where('created_at','<',date("Y-m-d H:i:s",strtotime("- 14 days")))->get();
                        foreach($response as $response_data){
                            $accepted = AdvisorBids::where('advisor_id',$search['user_id'])->where('area_id',$response_data->id)->where('status',0)->where('advisor_status',1)->count();
                            $hired = AdvisorBids::where('area_id',$response_data->id)->where('status',1)->count();
                            
                            $channelIds = array(-1);
                            $channelID = ChatChannel::where('advicearea_id',$response_data->id)->orderBy('id','DESC')->get();
                            foreach ($channelID as $chanalesR) {
                                array_push($channelIds, $chanalesR->id);
                            }                    
                            $chatCount = ChatModel::whereIn('channel_id',$channelIds)->count();;
    
                            if($chatCount==0 && $hired==0 && $accepted>0){
                                array_push($status_arr,$response_data->id);
                            }
                        }
                        $response_another = Advice_area::where('advisor_id',$search['user_id'])->where('area_status',5)->get();
                        if(count($response_another)){
                            foreach($response_another as $response_another_data){
                                $accepted_another = AdvisorBids::where('advisor_id',$search['user_id'])->where('area_id',$response_another_data->id)->where('status',4)->where('advisor_status',1)->first();
                                if($accepted_another){
                                    if(!in_array($response_another_data->id,$status_arr)){
                                        array_push($status_arr,$response_another_data->id);
                                    }
                                }
                            }
                        }
                    }
                    
                }
                // echo json_encode($status_arr);exit;
                if(count($advice_arr)>0){
                    $advice_arr = array_intersect($advice_arr, $status_arr);
                }else{
                    $advice_arr = array_unique($status_arr);
                }
            }
            $query =  $query->select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advisor_id')
            ->join('users', 'advice_areas.user_id', '=', 'users.id')
            ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
            // ->where('advisor_bids.advisor_status', '=', 1)
            ->where('advisor_bids.advisor_id', '=',$search['user_id']);

            if(count($advice_arr)>0){
                $query = $query->whereIn('advice_areas.id',$advice_arr);
            }

            $advice_area =  $query->with('total_bid_count')->with('total_lost_bid_count')
            ->with('service')
            ->orderBy('id','DESC')
            ->paginate();
            // ->get();
            // paginate()
            return $advice_area;
        }catch (\Exception $e) {
            // echo json_encode($e->getMessage());exit;
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}