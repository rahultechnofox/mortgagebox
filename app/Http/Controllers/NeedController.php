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

class NeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(User $model){
        $advice_area = Advice_area::select('advice_areas.*','users.name','users.email')->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
        ->orderBy('id','DESC')->paginate(config('constant.paginate.num_per_page'));
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
        $data['userDetails'] = $advice_area;
        echo json_encode($data['userDetails']);exit;    
        return view('need_list.index',$data);
    }
    /**
     * Display the specified resource..
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $needDetails = Advice_area::select('advice_areas.*','users.name','user_notes.notes')->where('advice_areas.id','=',$id)
        ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
        ->leftJoin('user_notes', 'advice_areas.id', '=', 'user_notes.advice_id')
        ->first();
        $bidCountArr = array();
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
        
        return view('need_list.show',['needDetails'=>$needDetails]);
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function destroy($advisor_id) {
        Advice_area::where('id', '=', $need_id)->delete();
        AdvisorBids::where('area_id', '=', $need_id)->delete();
        $data['message'] = 'Need deleted!';
        return redirect()->to('admin/need')->with('message', $data['message']);
    }
}
