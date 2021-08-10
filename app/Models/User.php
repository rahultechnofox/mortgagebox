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
    public static function getLists($search){
        try {
            $query = new Self;
            // echo json_encode($search);exit;
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
            $data = $query->where('user_role','=',0)->orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getAdvisors($search){
        try {
            $query = new Self;
            // echo json_encode($search);exit;
            // if(isset($search['search']) && $search['search']!=''){
            //     $query = $query->where('name', 'like', '%' .strtolower($search['search']). '%')->orWhere('email', 'like', '%' .strtolower($search['search']). '%')->orWhere('post_code', 'like', '%' .strtolower($search['search']). '%');
            // }
            // if(isset($search['email_status']) && $search['email_status']!=''){
            //     $query = $query->where('email_status',$search['email_status']);
            // }
            // if(isset($search['status']) && $search['status']!=''){
            //     $query = $query->where('status',$search['status']);
            // }
            // if(isset($search['created_at']) && $search['created_at']!=''){
            //     $query = $query->whereDate('created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            // }
            $data = $query->select('advisor_profiles.*','users.email_verified_at','users.email_status')->where('users.user_role','=',1)
            ->leftJoin('advisor_profiles', 'users.id', '=', 'advisor_profiles.advisorId')
            ->orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
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

    public static function getAdvisorDetail($id){
        try {
            $query = new Self;
            $data['userDetails'] = $query->where('id','=',$id)->first();
            // echo json_encode($id);exit;
            $advisorProfile = AdvisorProfile::where('advisorId','=',$id)->first();
            // ->with('notes')
            $data['userDetails'] = (object) $data['userDetails'];
            $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advisor_id')
            ->join('users', 'advice_areas.user_id', '=', 'users.id')
            ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
            ->where('advisor_bids.advisor_status', '=', 1)
            ->where('advisor_bids.advisor_id', '=', $id)
            ->count();
        
            $data['userDetails']->accepted_leads = $advice_areaCount;
            
            $live_leads = AdvisorBids::where('advisor_id','=',$id)
            ->where('status', '=', 0)
            ->where('advisor_status', '=', 1)
            ->count();
            $data['userDetails']->live_leads = $live_leads;

            $hired_leads = AdvisorBids::where('advisor_id','=',$id)
            ->where('status', '=', 1)
            ->where('advisor_status', '=', 1)
            ->count();
            $data['userDetails']->hired_leads = $hired_leads;

            $completed_leads = AdvisorBids::where('advisor_id','=',$id)
            ->where('status', '=', 2)
            ->where('advisor_status', '=', 1)
            ->count();
            $data['userDetails']->completed_leads = $completed_leads;
            $lost_leads = AdvisorBids::where('advisor_id','=',$id)
            ->where('status', '=', 3)
            ->where('advisor_status', '=', 1)
            ->count();
            $data['userDetails']->lost_leads = $lost_leads;
            $closed = AdvisorBids::where('advisor_id','=',$id)
            ->where('status', '=', 3)
            ->where('advisor_status', '=', 2)
            ->count();
            $data['userDetails']->closed = $closed;
            $data['profile'] = $advisorProfile;
            $value = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)->sum('cost_leads');
            $cost = AdvisorBids::where('advisor_id','=',$data['userDetails']->id)->sum('cost_discounted');
            $data['userDetails']->value = $value;
            $data['userDetails']->cost = $cost;
            if($data['profile']){
                $data['profile']->notes = Notes::where('company_id',$data['profile']->company_id)->get();
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}