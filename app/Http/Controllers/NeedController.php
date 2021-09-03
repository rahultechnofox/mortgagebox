<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorProfile;
use App\Models\AdvisorPreferencesCustomer;
use App\Models\AdvisorPreferencesProducts;
use App\Models\AdvisorPreferencesDefault;

use App\Models\companies;
use App\Models\CompanyTeamMembers;
use App\Models\StaticPage;
use App\Models\ServiceType;
use App\Models\UserNotes;


use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class NeedController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $advice_area = Advice_area::getNeedList($post);
        $data = $advice_area;   
        // echo json_encode($data);exit;
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
        $costOfLeadsStr = "";
        $costOfLeadsDropStr = "";
        $bidCountArr = array();
        $adviceBids = AdvisorBids::where('area_id',$needDetails->id)->get();
        $adviceBidCount = AdvisorBids::where('area_id',$needDetails->id)->count();
        $needDetails->totalBids = $adviceBidCount;
        $bidCountArr = array();
        if($needDetails!=''){
            $adviceBid = AdvisorBids::where('area_id',$needDetails->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3)? 0:1;
            }
            $needDetails->totalBids = $bidCountArr;
            $costOfLead = ($needDetails->size_want/100)*0.006;
            $time1 = Date('Y-m-d H:i:s');
            $time2 = Date('Y-m-d H:i:s',strtotime($needDetails->created_at));
            $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
            $costOfLeadsStr1 = "";
            $costOfLeadsDropStr1 = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');

            if($hourdiff < 24) {
                $costOfLeadsStr1 = "".$needDetails->size_want_currency.$amount;
                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr1 = "Cost of lead drops to ".$needDetails->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr1 = "".$needDetails->size_want_currency.($amount/2)." (Save 50%, was ".$needDetails->size_want_currency.$amount.")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr1 = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr1 = "".($amount-$newAmount)." (Save 50%, was ".$needDetails->size_want_currency.$amount.")";
                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr1 = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 72) {
                $costOfLeadsStr1 = ""."Free";
                $costOfLeadsDropStr1 = "";
            }
            $needDetails->cost_of_lead = $costOfLeadsStr1;
            $needDetails->cost_of_lead_drop = $costOfLeadsDropStr1;
            // $lead_value = "";
            // $main_value = ($needDetails->size_want/100);
            // $advisorDetaultValue = "";
            // if($needDetails->service_type=="remortgage") {
            //     $advisorDetaultValue = "remortgage";
            // }else if($needDetails->service_type=="first time buyer") {
            //     $advisorDetaultValue = "first_buyer";
            // }else if($needDetails->service_type=="next time buyer") {
            //     $advisorDetaultValue = "next_buyer";
            // }else if($needDetails->service_type=="buy to let") {
            //     $advisorDetaultValue = "but_let";
            // }else if($needDetails->service_type=="equity release") {
            //     $advisorDetaultValue = "equity_release";
            // }else if($needDetails->service_type=="overseas") {
            //     $advisorDetaultValue = "overseas";
            // }else if($needDetails->service_type=="self build") {
            //     $advisorDetaultValue = "self_build";
            // }else if($needDetails->service_type=="mortgage protection") {
            //     $advisorDetaultValue = "mortgage_protection";
            // }else if($needDetails->service_type=="secured loan") {
            //     $advisorDetaultValue = "secured_loan";
            // }else if($needDetails->service_type=="bridging loan") {
            //     $advisorDetaultValue = "bridging_loan";
            // }else if($needDetails->service_type=="commercial") {
            //     $advisorDetaultValue = "commercial";
            // }else if($needDetails->service_type=="something else") {
            //     $advisorDetaultValue = "something_else";
            // }  
            // $AdvisorPreferencesDefault = AdvisorPreferencesDefault::where('advisor_id','=',$needDetails->user_id)->first();
            // // echo json_encode($AdvisorPreferencesDefault);exit;
            // $lead_value = ($main_value)*($AdvisorPreferencesDefault->$advisorDetaultValue);
            // $needDetails->lead_value = $lead_value;
        }
        if(count($adviceBids)>0){
            
            foreach($adviceBids as $advice_data){
                
                $advisors = AdvisorProfile::where('advisorId',$advice_data->advisor_id)->first();
                if($advisors){
                    $advice_data->adviser_name = $advisors->display_name;
                }else{
                    $advice_data->adviser_name = "";
                }
                $costOfLead = ($needDetails->size_want/100)*0.006;
                $time1 = Date('Y-m-d H:i:s',strtotime($advice_data->created_at));
                $time2 = Date('Y-m-d H:i:s',strtotime($advice_data->accepted_date));
                $hourdiff = round((strtotime($time2) - strtotime($time1))/3600, 1);
                $costOfLeadsStr = "";
                $costOfLeadsDropStr = "";
                $priceDrop = '0.00';
                $final_amount_after_discount = '';

                $amount = number_format((float)$costOfLead, 2, '.', '');
                if(!empty($advice_data)) {
                    $advice_data->bid_status =  ($advice_data->status == 2)? "Closed":"Active";
                }else {
                    $advice_data->bid_status =  "Active";
                }
                if($hourdiff < 24) {
                    $costOfLeadsStr = "".$needDetails->size_want_currency.$amount;
                    $in = 24-$hourdiff;
                    $hrArr = explode(".",$in);
                    $priceDrop = '0.00';
                    $final_amount_after_discount = $amount;
                    $costOfLeadsDropStr = "1st Cycle - Full Cost";
                }
                if($hourdiff > 24 && $hourdiff < 48) {
                    $costOfLeadsStr = "2nd Cycle Saved 50%, was".$needDetails->size_want_currency.($amount/2);
                    $in = 48-$hourdiff;
                    $newAmount = (50 / 100) * $amount;
                    $hrArr = explode(".",$in);
                    $priceDrop = $newAmount;
                    $final_amount_after_discount = $amount-$newAmount;

                    $costOfLeadsDropStr = "2nd Cycle Saved 50%, was ".$needDetails->size_want_currency.($amount-$newAmount);
                }
                if($hourdiff > 48 && $hourdiff < 72) {
                    $newAmount = (75 / 100) * $amount;
                    $costOfLeadsStr = "".($amount-$newAmount)." (Save 75%, was ".$needDetails->size_want_currency.$amount.")";
                    $in = 72-$hourdiff;
                    $hrArr = explode(".",$in);
                    $final_amount_after_discount = $amount-$newAmount;
                    $priceDrop = $newAmount;
                    $costOfLeadsDropStr = "3rd Cycle Saved 75% was ".$needDetails->size_want_currency.($amount-$newAmount);
                }
                if($hourdiff > 72) {
                    $costOfLeadsStr = "4th Cycle Saved 100%";
                    $costOfLeadsDropStr = "4th Cycle Saved 100%";
                    $priceDrop = $amount;
                    $final_amount_after_discount = "0.00";

                }
                // if($hourdiff < 24) {
                //     $costOfLeadsStr = "".$needDetails->size_want_currency.$amount;
                //     $in = 24-$hourdiff;
                //     $hrArr = explode(".",$in);
                //     $costOfLeadsDropStr = "Cost of lead drops to ".$needDetails->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                // }
                // if($hourdiff > 24 && $hourdiff < 48) {
                //     $costOfLeadsStr = "".$needDetails->size_want_currency.($amount/2)." (Save 50%, was ".$needDetails->size_want_currency.$amount.")";
                //     $in = 48-$hourdiff;
                //     $newAmount = (50 / 100) * $amount;
                //     $hrArr = explode(".",$in);
                //     $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                // }
                // if($hourdiff > 48 && $hourdiff < 72) {
                //     $newAmount = (75 / 100) * $amount;
                //     $costOfLeadsStr = "".($amount-$newAmount)." (Save 75%, was ".$needDetails->size_want_currency.$amount.")";
                //     $in = 72-$hourdiff;
                //     $hrArr = explode(".",$in);
                //     $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                // }
                // if($hourdiff > 72) {
                //     $costOfLeadsStr = ""."Free";
                //     $costOfLeadsDropStr = "";
                // }
                $advice_data->leads_status = $costOfLeadsStr;
                $advice_data->cost_of_lead_drop = $costOfLeadsDropStr;
                $advice_data->price_drop = $priceDrop;
                $advice_data->final_amount_after_discount = $final_amount_after_discount;
                
            }
        }
        // $needDetails->cost_of_lead = $costOfLeadsStr;
        // $needDetails->cost_of_lead_drop = $costOfLeadsDropStr;
        $needDetails->bids = $adviceBids;
        $needDetails->notes = UserNotes::where('advice_id',$needDetails->id)->get();
        // echo json_encode($needDetails);exit;
        return view('need_list.show',['needDetails'=>$needDetails]);
    }
    /**
     * Update status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateNeedStatus(Request $request){
        try {
            $post = $request->all();
            $validate = [
                'id' => 'required',
                'status' => 'required'
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                unset($post['_token']);
                $user = Advice_area::where('id',$post['id'])->update($post);
                if($user){
                    return response(\Helpers::sendSuccessAjaxResponse('Status changed successfully.',$user));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
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
