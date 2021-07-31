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
    public function index(User $model)
    {
        return view('dashboard');
    }
    public function users(User $model)
    {
        $userDetails = User::where('user_role','=',0)->get();
        return view('users',['userDetails'=>$userDetails]);
    }
    public function viewCustomer($id) {
        $userDetails = User::where('id','=',$id)->first();
        
        $advice_area =  Advice_area::select('advice_areas.*')
            ->where('advice_areas.user_id', '=', $id)
            ->get();
        $userDetails->total_needs = count($advice_area);
        $adviceBidClosed = 0;
        $adviceBidActive = 0;
        $pendingBidCount = 0;
         
        foreach($advice_area as $items) {
            $adviceBidCl= AdvisorBids::where('area_id',$items->id)->where('status','=','2')->get();
            $adviceBidClosed = $adviceBidClosed+count($adviceBidCl);
            $adviceBidAc= AdvisorBids::where('area_id',$items->id)->where('status','=','1')->get();
            $adviceBidActive = $adviceBidActive+count($adviceBidAc);
            $pendingCount= AdvisorBids::where('area_id',$items->id)->where('status','=','0')->get();
            $pendingBidCount = $pendingBidCount+count($pendingCount);
            //$adviceBidActive[] = AdvisorBids::where('area_id',$items->id)->where('status','=','1')->get();
        }
        $userDetails->closed = $adviceBidClosed;
        $userDetails->active_bid = $adviceBidActive;
        $userDetails->pending_bid = $pendingBidCount;

        return view('view_customer',['userDetails'=>$userDetails]);
    }
    public function Advisors(User $model)
    {
        $userDetails = User::select('advisor_profiles.*','users.email_verified_at')->where('users.user_role','=',1)
        ->leftJoin('advisor_profiles', 'users.id', '=', 'advisor_profiles.advisorId')
        ->get();
        foreach($userDetails as $k=>$v) {
            $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advisor_id')
            ->join('users', 'advice_areas.user_id', '=', 'users.id')
            ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
            ->where('advisor_bids.advisor_status', '=', 1)
            ->where('advisor_bids.advisor_id', '=', $v->advisorId)
            ->get();
            $userDetails[$k]->acceptedLeads = count($advice_area);

            $live_leads = AdvisorBids::where('advisor_id','=',$v->advisorId)
            ->where('status', '=', 0)
            ->where('advisor_status', '=', 1)
            ->count();
            $userDetails[$k]->live_leads = $live_leads;

            $hired_leads = AdvisorBids::where('advisor_id','=',$v->advisorId)
            ->where('status', '=', 1)
            ->where('advisor_status', '=', 1)
            ->count();
            $userDetails[$k]->hired_leads = $hired_leads;

            $completed_leads = AdvisorBids::where('advisor_id','=',$v->advisorId)
            ->where('status', '=', 2)
            ->where('advisor_status', '=', 1)
            ->count();
            $userDetails[$k]->completed_leads = $completed_leads;

        }
        

        return view('advisors',['userDetails'=>$userDetails]);
    }
    public function needList() {
        $advice_area = Advice_area::select('advice_areas.*','users.name','users.email')->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
        ->get();
        foreach ($advice_area as $key => $item) {
            $offer_count = AdvisorBids::where('area_id','=',$item->id)->count();
            $bidDetails = AdvisorBids::where('area_id','=',$item->id)->where('status','>','0')->first();
            if(!empty($bidDetails)) {
                $advice_area[$key]->bid_status = $bidDetails->status;
            }else{
                $advice_area[$key]->bid_status ="N/A";
            }
            
            $advice_area[$key]->offer_count = $offer_count;
            
        }
        return view('need_list',['userDetails'=>$advice_area]);
    }
    public function viewNeeds($id) {
        $needDetails = Advice_area::select('advice_areas.*','users.name','user_notes.notes')->where('advice_areas.id','=',$id)
        ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
        ->leftJoin('user_notes', 'advice_areas.id', '=', 'user_notes.advice_id')
        ->first();
        $bidCountArr = array();
        // foreach($needDetails as $key=> $item) {
            $adviceBid = AdvisorBids::where('area_id',$needDetails->id)->where('status','>','0')->where('status','<','3')->first();
            $adviceBidCount = AdvisorBids::where('area_id',$needDetails->id)->count();
            if(!empty($adviceBid)) {
                $needDetails->bid_status =  ($adviceBid->status == 2)? "Closed":"Active";
            }else {
                $needDetails->bid_status =  "Active";
            }
            
            $needDetails->totalBids = $adviceBidCount;
            $costOfLead = ($needDetails->size_want/100)*0.006;
            $time1 = Date('Y-m-d H:i:s');
            $time2 = Date('Y-m-d H:i:s',strtotime($needDetails->created_at));
            $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');
            if($hourdiff < 24) {
                $costOfLeadsStr = "".$needDetails->size_want_currency.$amount;
                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".$needDetails->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr = "".$needDetails->size_want_currency.($amount/2)." (Save 50%, was ".$needDetails->size_want_currency.$amount.")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr = "".($amount-$newAmount)." (Save 50%, was ".$needDetails->size_want_currency.$amount.")";
                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 72) {
                $costOfLeadsStr = ""."Free";
                $costOfLeadsDropStr = "";
            }
            
            $needDetails->cost_of_lead = $costOfLeadsStr;
            $needDetails->cost_of_lead_drop = $costOfLeadsDropStr;
        // }
        return view('view_needs',['needDetails'=>$needDetails]);
    }
    public function viewAdvisor($id) {
        $userDetails = User::where('id','=',$id)->first();
        $advisorProfile = AdvisorProfile::where('advisorId','=',$id)->first();
        $userDetails = (object) $userDetails;
        $advice_areaCount =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advisor_id')
        ->join('users', 'advice_areas.user_id', '=', 'users.id')
        ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
        ->where('advisor_bids.advisor_status', '=', 1)
        ->where('advisor_bids.advisor_id', '=', $id)
        ->count();
      
        $userDetails->acceptedLeads = $advice_areaCount;
         
        $live_leads = AdvisorBids::where('advisor_id','=',$id)
        ->where('status', '=', 0)
        ->where('advisor_status', '=', 1)
        ->count();
        $userDetails->live_leads = $live_leads;

        $hired_leads = AdvisorBids::where('advisor_id','=',$id)
        ->where('status', '=', 1)
        ->where('advisor_status', '=', 1)
        ->count();
        $userDetails->hired_leads = $hired_leads;

        $completed_leads = AdvisorBids::where('advisor_id','=',$id)
        ->where('status', '=', 2)
        ->where('advisor_status', '=', 1)
        ->count();
        $userDetails->completed_leads = $completed_leads;
        $lost_leads = AdvisorBids::where('advisor_id','=',$id)
        ->where('status', '=', 3)
        ->where('advisor_status', '=', 1)
        ->count();
        $userDetails->lost_leads = $lost_leads;

        return view('view_advisor',['userDetails'=>$userDetails,'profile'=>$advisorProfile]);
    }
    function deleteCustomer($customer_id) {
        User::where('id', '=', $customer_id)->delete();
        Advice_area::where('user_id', '=', $customer_id)->delete();
        $data['message'] = 'Customer deleted!';
        return redirect()->to('admin/users')->with('message', $data['message']);
    }
    function deleteAdvisor($advisor_id) {
        User::where('id', '=', $advisor_id)->delete();
        AdvisorProfile::where('advisorId', '=', $advisor_id)->delete();
        AdvisorBids::where('advisor_id', '=', $advisor_id)->delete();
        AdvisorPreferencesCustomer::where('advisor_id', '=', $advisor_id)->delete();
        AdvisorPreferencesProducts::where('advisor_id', '=', $advisor_id)->delete();
        $data['message'] = 'Advisor deleted!';
        return redirect()->to('admin/advisors')->with('message', $data['message']);
    }
    function deleteNeed($need_id) {
        Advice_area::where('id', '=', $need_id)->delete();
        AdvisorBids::where('area_id', '=', $need_id)->delete();
        $data['message'] = 'Need deleted!';
        return redirect()->to('admin/needList')->with('message', $data['message']);
    }
    public function companyList()
    {
        $companyDetails = companies::get();
        return view('company_list',['companyDetails'=>$companyDetails]);
    }
    public function viewCompany($id)
    {
        $companyDetail = companies::where('id','=',$id)->first();
        $companyTeamDetail = CompanyTeamMembers::select('company_team_members.*')
        ->where('company_team_members.company_id',$id)
        // ->leftJoin('users', 'company_team_members.email', '=', 'users.email')
       ->get();
       
        return view('view_company',['companyDetail'=>$companyDetail,'team'=>$companyTeamDetail]);
    }
    public function deleteCompany($id) {
        companies::where('id', '=', $id)->delete();
        CompanyTeamMembers::where('company_id', '=', $id)->delete();
        $data['message'] = 'Company deleted!';
        return redirect()->to('admin/companies')->with('message', $data['message']);
    }
    
    public function pages()
    {
        $pages = StaticPage::get();
        return view('pages',['page_list'=>$pages]);
    }
    public function addPage(Request $request ) {
        return view('add_page');
    }
    public function savePage(Request $request ) {
        $validated = $request->validate([
            'page_name' => 'required',
            'page_content' => 'required',
        ]);
       $slug =  str_replace(" ","-",$request->page_name);
       $slug = strtolower($slug);
        $page = StaticPage::create([
            'page_name' => $request->page_name,
            'slug' => $slug,
            'page_content' => $request->page_content
        ]);
        $data['message'] = $request->page_name.' added successfully!';
        return redirect()->to('admin/pages')->with('message', $data['message']);
    }
    public function editPage($id) {
        $pages = StaticPage::where('id','=',$id)->first();
        return view('edit_page',['pageDetails'=>$pages]);
    }
    public function updatePage(Request $request) {
        $validated = $request->validate([
            'page_name' => 'required',
            'page_content' => 'required',
        ]);
       $slug =  str_replace(" ","-",$request->page_name);
       $slug = strtolower($slug);
        $page = StaticPage::where('id','=',$request->id)->update([
            'page_name' => $request->page_name,
            'slug' => $slug,
            'page_content' => $request->page_content
        ]);
        $data['message'] = $request->page_name.' updated successfully!';
        return redirect()->to('admin/pages')->with('message', $data['message']);
    }
    function deletePage($page_id) {
        StaticPage::where('id', '=', $page_id)->delete();
        $data['message'] = 'Page deleted!';
        return redirect()->to('admin/pages')->with('message', $data['message']);
    }
    public function pageStatus($status,$page_id) {
        $page = StaticPage::where('id','=',$page_id)->update([
            'status' => $status
        ]);
        $data['message'] = 'Status Updated Successfully!';
        return redirect()->to('admin/pages')->with('message', $data['message']);
    }
    //services
    public function services()
    {
        $pages = ServiceType::get();
        return view('services',['page_list'=>$pages]);
    }
    public function addService(Request $request ) {
        return view('add_service');
    }
    public function saveService(Request $request ) {
        $validated = $request->validate([
            'name' => 'required',
        ]);
        
        $page = ServiceType::create([
            'name' => $request->name,
        ]);
        $data['message'] = 'Service added successfully!';
        return redirect()->to('admin/services')->with('message', $data['message']);
    }
    public function editService($id) {
        $pages = ServiceType::where('id','=',$id)->first();
        return view('edit_service',['pageDetails'=>$pages]);
    }
    public function updateService(Request $request) {
        $validated = $request->validate([
            'name' => 'required',
        ]);
        $page = ServiceType::where('id','=',$request->id)->update([
            'name' => $request->name,            
        ]);
        $data['message'] = 'Service updated successfully!';
        return redirect()->to('admin/services')->with('message', $data['message']);
    }
    function deleteService($page_id) {
        ServiceType::where('id', '=', $page_id)->delete();
        $data['message'] = 'Service deleted!';
        return redirect()->to('admin/services')->with('message', $data['message']);
    }
    public function serviceStatus($status,$page_id) {
        $page = ServiceType::where('id','=',$page_id)->update([
            'status' => $status
        ]);
        $data['message'] = 'Service Updated Successfully!';
        return redirect()->to('admin/services')->with('message', $data['message']);
    }
}
