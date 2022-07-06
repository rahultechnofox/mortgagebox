<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ReviewSpam extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "review_spam";

    protected $fillable = [
        'spam_status','reason','user_id','review_id'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['deleted_at'];

    public function adviser(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","user_id");
    }
    public function review(){
        return $this->hasOne('App\Models\ReviewRatings',"id","review_id")->with('user');
    }
    public static function getReviewSpam($search){
        try {
            $query = new Self;
            // if(isset($search['name']) && $search['name']!=''){
            //     $query = $query->where('question', 'like', '%' .strtolower($search['name']). '%');
            // }
            $data = $query->orderBy('id','DESC')->with('adviser')->with('review')->paginate(config('constants.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
