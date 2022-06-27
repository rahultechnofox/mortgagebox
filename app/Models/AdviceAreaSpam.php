<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class AdviceAreaSpam extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "advice_areas_spam";

    protected $fillable = [
        'spam_status','reason','user_id','area_id'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['deleted_at'];

    public function area(){
        return $this->hasOne('App\Models\Advice_area',"id","area_id")->with('user')->with('service');
    }

    public function user(){
        return $this->hasOne('App\Models\User',"id","user_id");
    }
    public static function getSpamNeed($search){
        try {
            $query = new Self;
            // if(isset($search['name']) && $search['name']!=''){
            //     $query = $query->where('name', 'like', '%' .strtolower($search['name']). '%');
            // }
            // if(isset($search['status']) && $search['status']!=''){
            //     $query = $query->where('status',$search['status']);
            // }
            $data = $query->with('area')->with('user')->orderBy('created_at','DESC')->paginate(config('constants.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
