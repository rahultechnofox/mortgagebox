<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaticPage extends Model
{
    use HasFactory;
    protected $fillable = [
        'page_name', 'slug', 'page_content','status'
    ];

    public static function getPages($search){
        try {
            $query = new Self;
            if(isset($search['name']) && $search['name']!=''){
                $query = $query->where('page_name', 'like', '%' .strtolower($search['name']). '%');
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
