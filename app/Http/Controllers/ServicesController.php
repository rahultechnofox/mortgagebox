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

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $model){
        $pages = ServiceType::orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));;
        return view('services.index',['page_list'=>$pages]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function destroy($advisor_id) {
        ServiceType::where('id', '=', $page_id)->delete();
        $data['message'] = 'Service deleted!';
        return redirect()->to('admin/services')->with('message', $data['message']);
    }
}
