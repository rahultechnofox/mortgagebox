<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingAddress extends Model
{
    use HasFactory;
    protected $fillable = [
        'contact_name','invoice_name','address_one','address_two','city','post_code','contact_number','is_vat_registered','advisor_id'
    ];
}
