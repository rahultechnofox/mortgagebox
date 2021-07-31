<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvisorPreferencesDefault extends Model
{
    use HasFactory;
    protected $fillable = [
        'remortgage','first_buyer','next_buyer','but_let','equity_release','overseas','self_build','mortgage_protection','secured_loan','bridging_loan','commercial','something_else','advisor_id'
    ];
}
