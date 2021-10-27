<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
                
            }
            // exit;
            $data['userDetails'] = $advice_area;
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}