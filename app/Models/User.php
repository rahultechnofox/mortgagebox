<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
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
}