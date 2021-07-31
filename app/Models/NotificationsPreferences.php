<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationsPreferences extends Model
{
    use HasFactory;
    protected $fillable = [
        'new_lead', 'newslatter','direct_contact','monthly_invoice','direct_message','accept_offer','decline_offer','lead_match','review','promotional','user_id'
    ];
}
