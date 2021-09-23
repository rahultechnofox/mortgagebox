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
}
