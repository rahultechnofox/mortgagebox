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
}
