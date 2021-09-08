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
    public static function getLists($search){
        try {
            $query = new Self;
            if(isset($search['search']) && $search['search']!=''){
                $query = $query->where('name', 'like', '%' .strtolower($search['search']). '%')->orWhere('email', 'like', '%' .strtolower($search['search']). '%')->orWhere('post_code', 'like', '%' .strtolower($search['search']). '%');
            }
            if(isset($search['email_status']) && $search['email_status']!=''){
                $query = $query->where('email_status',$search['email_status']);
            }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('status',$search['status']);
            }
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }
            $data = $query->where('user_role','=',0)->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
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
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('users.status',$search['status']);
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
            foreach($data as $row){
                $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address','users.status as user_status', 'advisor_bids.advisor_id as advisor_id', 'companies.advisor_id as advisor_id')
                ->join('users', 'advice_areas.user_id', '=', 'users.id')
                ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
                ->where('advisor_bids.advisor_status', '=', 1)
                ->where('advisor_bids.advisor_id', '=', $row->advisorId)
                ->count();
                // $row->live_leads = $advice_areaCount;
                $row->accepted_leads = $advice_areaCount;
                
                $live_leads = AdvisorBids::where('advisor_id','=',$row->advisorId)
                ->where('status', '=', 0)
                ->where('advisor_status', '=', 1)
                ->count();
                $row->live_leads = $live_leads;

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
                $row->value = $value;
                $row->cost = $cost;
                $total_bids = AdvisorBids::where('advisor_id',$row->advisorId)->where('advisor_status', '=', 1)->count();
                if($total_bids!=0){
                    $success_per = ($row->completed_leads / $total_bids) * 100;
                }
                $row->success_percent = $success_per; 
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
            if($data['userDetails']){
                $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address','users.status as user_status', 'advisor_bids.advisor_id as advisor_id', 'companies.advisor_id as advisor_id')
                ->join('users', 'advice_areas.user_id', '=', 'users.id')
                ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
                ->where('advisor_bids.advisor_status', '=', 1)
                ->where('advisor_bids.advisor_id', '=', $data['userDetails']->id)
                ->count();
                // $row->live_leads = $advice_areaCount;
                $data['userDetails']->accepted_leads = $advice_areaCount;
                
                $live_leads = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)
                ->where('status', '=', 0)
                ->where('advisor_status', '=', 1)
                ->count();
                $data['userDetails']->live_leads = $live_leads;

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
            
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}