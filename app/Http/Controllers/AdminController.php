<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorProfile;
use App\Models\AdvisorPreferencesCustomer;
use App\Models\AdvisorPreferencesProducts;
use App\Models\companies;
use App\Models\CompanyTeamMembers;
use App\Models\StaticPage;
use App\Models\ServiceType;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminController extends Controller
{
    public function index(){
        $data['customer'] = User::where('user_role',0)->count(); 
        $data['adviser'] = User::where('user_role',1)->count(); 
        $data['companies'] = companies::count(); 
        $data['need'] = Advice_area::count(); 
        // echo json_encode($data);exit;
        return view('dashboard.index',$data);
    }
}
