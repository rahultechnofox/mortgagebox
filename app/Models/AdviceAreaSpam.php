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
}
