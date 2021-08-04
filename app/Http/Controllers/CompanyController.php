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

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $model){
        $companyDetails = companies::orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
        return view('company.index',['companyDetails'=>$companyDetails]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function destroy($advisor_id) {
        companies::where('id', '=', $id)->delete();
        CompanyTeamMembers::where('company_id', '=', $id)->delete();
        $data['message'] = 'Company deleted!';
        return redirect()->to('admin/companies')->with('message', $data['message']);
    }
}
