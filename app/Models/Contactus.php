<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Contactus extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "contactus";

    protected $fillable = [
        'name', 'email','mobile','message','is_replied'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['deleted_at'];

    public static function getContactUs($search){
        try {
            $query = new Self;
            if(isset($search['name']) && $search['name']!=''){
                $query = $query->where('name', 'like', '%' .strtolower($search['name']). '%')->orWhere('email', 'like', '%' .strtolower($search['email']). '%')->orWhere('mobile', 'like', '%' .strtolower($search['mobile']). '%')->orWhere('message', 'like', '%' .strtolower($search['message']). '%');
            }
            // if(isset($search['status']) && $search['status']!=''){
            //     $query = $query->where('status',$search['status']);
            // }
            $data = $query->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
