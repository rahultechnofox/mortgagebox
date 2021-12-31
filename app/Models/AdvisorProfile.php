<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorProfile extends Model
{
    use HasFactory;
    protected $fillable = [
         'display_name','tagline','FCANumber','company_name','phone_number','address_line1','address_line2','city','postcode','web_address','facebook','twitter','about_us','role','image','short_description','status','advisorId','serve_range','linkedin_link','description','company_logo','network','email','gender','language','company_id','stripe_customer_id','FCA_verified','mortgage_min_size','mortgage_max_size'
    ];
    
    public static function getNeedList($search){
        try {
            $query = new Self;
            if(isset($search['search']) && $search['search']!=''){
                $query = $query->where('advisor_profiles.name', 'like', '%' .strtolower($search['search']). '%');
            }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('advisor_profiles.status',$search['status']);
            }
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('advisor_profiles.created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }
            // echo json_encode($search);exit;
            $data = $query->select('advisor_profiles.*','users.email_verified_at','users.email_status')->where('users.user_role','=',1)
            ->leftJoin('advisor_profiles', 'users.id', '=', 'advisor_profiles.advisorId')
            ->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            // echo json_encode($data);exit;

            foreach($data as $row){
                $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advisor_id')
                ->join('users', 'advice_areas.user_id', '=', 'users.id')
                ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
                ->where('advisor_bids.advisor_status', '=', 1)
                ->where('advisor_bids.advisor_id', '=', $row->id)
                ->count();
            
                $row->accepted_leads = $advice_areaCount;
                
                $live_leads = AdvisorBids::where('advisor_id','=',$row->id)
                ->where('status', '=', 0)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->live_leads = $live_leads;

                $hired_leads = AdvisorBids::where('advisor_id','=',$row->id)
                ->where('status', '=', 1)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->hired_leads = $hired_leads;

                $completed_leads = AdvisorBids::where('advisor_id','=',$row->id)
                ->where('status', '=', 2)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->completed_leads = $completed_leads;

                $lost_leads = AdvisorBids::where('advisor_id','=',$row->id)
                ->where('status', '=', 3)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->lost_leads = $lost_leads;
                $value = AdvisorBids::where('advisor_id','=',$row->id)->sum('cost_leads');
                $cost = AdvisorBids::where('advisor_id','=',$row->id)->sum('cost_discounted');
                $row->value = $value;
                $row->cost = $cost;
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getSearchedAdvisor($search){
        try {
            $query = new Self;
            $advisorArr = array();
            $AdviceAreaIds = array();
            $mortgageValueIds = array();
            if(isset($search['advice_area']) && count($search['advice_area'])){
                $AdviceAreaIds = array(-1);
                $default = DefaultPercent::whereIn('service_id',$search['advice_area'])->where('status',1)->get();
                if(count($default)){
                    foreach($default as $default_data){
                        if(!in_array($default_data->adviser_id,$AdviceAreaIds)){
                            array_push($AdviceAreaIds,$default_data->adviser_id);
                        }
                    }
                }
            }

            if(isset($search['post_code']) && $search['post_code']!=''){
                $query = $query->where('postcode',$search['post_code']);
            }

            if(isset($search['gender']) && $search['gender']!=''){
                $query = $query->where('gender',$search['gender']);
            }

            if(isset($search['language']) && $search['language']!=''){
                $query = $query->where('language',$search['language']);
            }

            if(isset($search['how_soon']) && count($search['how_soon'])){
                $advisorArr = array(-1);
                for($i=0;$i<count($search['how_soon']);$i++){
                    if($search['how_soon'][$i]=='more_3_month'){
                        $ad_customer = AdvisorPreferencesCustomer::where('more_3_month',1)->get();
                        if(count($ad_customer)){
                            foreach($ad_customer as $ad_customer_data){
                                if(!in_array($ad_customer_data->advisor_id,$advisorArr)){
                                    array_push($advisorArr,$ad_customer_data->advisor_id);
                                }
                            }
                        }
                    }
                    if($search['how_soon'][$i]=='next_3_month'){
                        $ad_customer_next = AdvisorPreferencesCustomer::where('next_3_month',1)->get();
                        if(count($ad_customer_next)){
                            foreach($ad_customer_next as $ad_customer_next_data){
                                if(!in_array($ad_customer_next_data->advisor_id,$advisorArr)){
                                    array_push($advisorArr,$ad_customer_next_data->advisor_id);
                                }
                            }
                        }
                    }
                    if($search['how_soon'][$i]=='asap'){
                        $ad_customer_asap = AdvisorPreferencesCustomer::where('asap',1)->get();
                        if(count($ad_customer_asap)){
                            foreach($ad_customer_asap as $ad_customer_asap_data){
                                if(!in_array($ad_customer_asap_data->advisor_id,$advisorArr)){
                                    array_push($advisorArr,$ad_customer_asap_data->advisor_id);
                                }
                            }
                        }
                    }
                }
            }
            if(isset($search['mortgage_value']) && count($search['mortgage_value'])){
                $mortgageValueIds = array(-1);
                for($i=0;$i<count($search['mortgage_value']);$i++){
                    $explode = explode("_",$search['mortgage_value'][$i]);
                    if($explode[0]>0){
                        $explode[0] = $explode[0]."000";
                    }
                    if($explode[1]>0){
                        $explode[1] = $explode[1]."000";
                    }
                    $ad = AdvisorProfile::where('mortgage_min_size','>',$explode[0])->where('mortgage_max_size','<=',$explode[1])->get();
                    if(count($ad)){
                        foreach($ad as $ad_data){
                            if(!in_array($ad_data->id,$mortgageValueIds)){
                                array_push($mortgageValueIds,$ad_data->id);
                            }
                        }
                    }
                }
            }

            if(count($advisorArr) && count($AdviceAreaIds) && count($mortgageValueIds)){
                $advisorArr = array_intersect($advisorArr, $AdviceAreaIds, $mortgageValueIds);
            }else if(count($advisorArr) && count($AdviceAreaIds)){
                $advisorArr = array_intersect($advisorArr, $AdviceAreaIds);
            }else if(count($advisorArr) && count($mortgageValueIds)){
                $advisorArr = array_intersect($advisorArr, $mortgageValueIds);
            }else if(!count($advisorArr) && count($mortgageValueIds)){
                $advisorArr = $mortgageValueIds;
            }else if(!count($advisorArr) && count($AdviceAreaIds)){
                $advisorArr = $AdviceAreaIds;
            }else if(!count($mortgageValueIds) && count($AdviceAreaIds)){
                $advisorArr = array_intersect($AdviceAreaIds, $mortgageValueIds);
            }

            if(count($advisorArr)){
                $query = $query->whereIn('id',$advisorArr);
            }
            $data = $query->orderBy('id','DESC')->groupBy('id')->get();
            $getCustomerPostalDetails = PostalCodes::where('Postcode', '=', $search['post_code'])->first();
            $dataArray = array();
            if (count($data)) {
                foreach ($data as $key => $item) {
                    $rating =  ReviewRatings::select('review_ratings.*')
                        ->where('review_ratings.advisor_id', '=', $item->advisorId)
                        ->where('review_ratings.status', '=', 0)
                        ->get();

                    $averageRating = ReviewRatings::where('review_ratings.advisor_id', '=', $item->advisorId)->where('review_ratings.status', '=', 0)->avg('rating');

                    $ratingGreat =  ReviewRatings::where('review_ratings.rating', '<=', '4')
                        ->where('review_ratings.rating', '>', '3')
                        ->where('review_ratings.advisor_id', '=', $item->advisorId)
                        ->count();

                    $ratingAverage =  ReviewRatings::where('review_ratings.rating', '<=', '3')
                        ->where('review_ratings.rating', '>', '2')
                        ->where('review_ratings.advisor_id', '=', $item->advisorId)
                        ->count();

                    $ratingPoor =  ReviewRatings::where('review_ratings.rating', '<=', '2')
                        ->where('review_ratings.rating', '>', '1')
                        ->where('review_ratings.advisor_id', '=', $item->advisorId)
                        ->count();
                    $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
                        ->where('review_ratings.rating', '>', '0')
                        ->where('review_ratings.advisor_id', '=', $item->advisorId)
                        ->count();

                    $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
                        ->where('review_ratings.rating', '>', '0')
                        ->where('review_ratings.advisor_id', '=', $item->advisorId)
                        ->count();

                    $item->avarageRating = number_format((float)$averageRating, 2, '.', '');
                    $item->rating = [
                        'total' => count($rating),
                    ];
                    $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
                    ->Where('advisor_status','=',1)
                    ->Where('advisor_id','=',$item->advisorId)
                    ->count();
                    $item->used_by  = $usedByMortage;
                    $dataArray[] = $item;
                }
            }
            return $dataArray;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
