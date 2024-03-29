<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class FaqCategory extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = "faq_category";
    protected $fillable = [
        'name','audience', 'status'
    ];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['deleted_at'];

    

    public static function getFaqCategories($search){
        try {
            $query = new Self;
            if(isset($search['name']) && $search['name']!=''){
                $query = $query->where('name', 'like', '%' .strtolower($search['name']). '%');
            }
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('status',$search['status']);
            }
            $data = $query->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
