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

    public static function getNeedList($search){
        try {
            $query = new Self;
            $userId = array();
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
            // if(isset($search['email_status']) && $search['email_status']!=''){
            //     $query = $query->where('users.email_status',$search['email_status']);
            // }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('advice_areas.status',$search['status']);
            }
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('advice_areas.created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }
            $advice_area = $query->select('advice_areas.*','users.name','users.email')->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
            ->orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
            foreach ($advice_area as $key => $item) {
                $offer_count = AdvisorBids::where('area_id','=',$item->id)->count();
                $active_bids = AdvisorBids::where('area_id','=',$item->id)->where('status',1)->where('advisor_status',1)->count();
                $bidDetails = AdvisorBids::where('area_id','=',$item->id)->where('status','>','0')->first();
                if(!empty($bidDetails)) {
                    $advice_area[$key]->bid_status = $bidDetails->status;
                }else{
                    $advice_area[$key]->bid_status ="N/A";
                }
                $advice_area[$key]->offer_count = $offer_count;
                $advice_area[$key]->active_bids = $active_bids;
            }
            $data['userDetails'] = $advice_area;
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
