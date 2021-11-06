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
}
