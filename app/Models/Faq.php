<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Faq extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "faq";

    protected $fillable = [
        'name', 'status'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['deleted_at'];

    public function faq_category(){
        return $this->hasOne('App\Models\FaqCategory',"id","faq_category_id");
    }
    public function audience(){
        return $this->hasOne('App\Models\Audience',"id","audience_id");
    }
    public static function getFaq($search){
        try {
            $query = new Self;
            if(isset($search['name']) && $search['name']!=''){
                $query = $query->where('question', 'like', '%' .strtolower($search['name']). '%');
            }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('status',$search['status']);
            }
            $data = $query->with('faq_category')->with('audience')->orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
