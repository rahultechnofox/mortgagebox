<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorPreferencesProducts extends Model
{
    use HasFactory;
    protected $fillable = [
        'remortgage','first_buyer','next_buyer','but_let','equity_release','overseas','self_build','mortgage_protection','secured_loan','bridging_loan','commercial','something_else','mortgage_min_size','mortgage_max_size','advisor_id'
    ];
}
