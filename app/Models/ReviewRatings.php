<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReviewRatings extends Model
{
    use HasFactory;
    protected $fillable = [
        'advisor_id','rating','reviews','user_id','status','parent_review_id','reply_reason','spam_reason','review_title','parent_review_id','reply_reason','spam_reason','reviewer_name','area_id','replied_on'
    ];
    public function user(){
        return $this->hasOne('App\Models\User',"id","user_id");
    }
    public function area(){
        return $this->hasOne('App\Models\Advice_area',"id","area_id")->with('service');
    }
}
