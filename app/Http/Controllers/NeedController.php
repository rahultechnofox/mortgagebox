<?php

namespace App\Http\Controllers;
use DB;
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
use App\Models\AppSettings;
use App\Models\AdviceAreaSpam;
use App\Models\NeedSpam;
use App\Models\Notifications;
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
        $live_arr = array();
        $closedArr = array();
        if(isset($post['advisor_id']) && $post['advisor_id']!=''){
            if(isset($post['area_status']) && $post['area_status']!=''){
                if($post['area_status']=='active'){
                    $accepted_leads = AdvisorBids::where('advisor_id',$post['advisor_id'])->get();
                    foreach($accepted_leads as $accepted_leads_data){
                        array_push($live_arr,$accepted_leads_data->area_id);
                    }
                }else if($post['area_status']=='closed'){
                    $closed = AdvisorBids::where('advisor_id',$post['advisor_id'])->where('advisor_status', '=', 1)->get();
                    if(isset($closed) && count($closed)){
                        foreach($closed as $closed_data){
                            // array_push($live_arr,$closed_data->area_id);
                            array_push($closedArr,$closed_data->area_id);
                        }
                    }
                    if(isset($closedArr) && count($closedArr)){
                        $closed_count = Advice_area::whereIn('id',$closedArr)->where('status',2)->get();
                        if(count($closed_count)){
                            foreach($closed_count as $closed_count_data){
                                array_push($live_arr,$closed_count_data->id);
                            }
                        }
                    }
                }else if($post['area_status']=='live'){
                    $live_leads = AdvisorBids::where('advisor_id',$post['advisor_id'])->where('status', '=', 0)->where('advisor_status', '=', 1)->get();
                    foreach($live_leads as $live_leads_data){
                        array_push($live_arr,$live_leads_data->area_id);
                    }
                }else if($post['area_status']=='hired'){
                    $hired_leads = AdvisorBids::where('advisor_id',$post['advisor_id'])->where('status', '=', 1)->where('advisor_status', '=', 1)->get();
                    foreach($hired_leads as $hired_leads_data){
                        array_push($live_arr,$hired_leads_data->area_id);
                    }
                }else if($post['area_status']=='completed'){
                    $completed_leads = AdvisorBids::where('advisor_id',$post['advisor_id'])->where('status', '=', 2)->where('advisor_status', '=', 1)->get();
                    foreach($completed_leads as $completed_leads_data){
                        array_push($live_arr,$completed_leads_data->area_id);
                    }
                }else if($post['area_status']=='not_proceeding'){
                    $get_not_proceed = AdvisorBids::where('advisor_id',$post['advisor_id'])->get();
                    if(isset($get_not_proceed) && count($get_not_proceed)){
                        foreach($get_not_proceed as $not_proceed_data){
                            $check_proceed = AdvisorBids::where('area_id',$not_proceed_data->id)->where('status', '=', 0)->where('advisor_status', '=', 1)->get();
                            if(count($check_proceed)){
                                foreach($check_proceed as $check_proceed_data){
                                    array_push($live_arr,$check_proceed_data->area_id);
                                }
                            }
                        }
                    }
                }else if($post['area_status']=='lost'){
                    $lost_leads = AdvisorBids::where('advisor_id',$post['advisor_id'])->where('status', '=', 3)->where('advisor_status', '=', 1)->get();
                    // echo json_encode($lost_leads);exit;
                    foreach($lost_leads as $lost_leads_data){
                        array_push($live_arr,$lost_leads_data->area_id);
                    }
                }
            }
        }

        if(isset($live_arr) && count($live_arr)){
            $post['area_id'] = $live_arr;
        }
        $advice_area = Advice_area::getNeedList($post);
        // echo json_encode($advice_area);exit;
        $data = $advice_area;   
        $data['services'] = ServiceType::where('parent_id','!=',0)->where('status',1)->get();
        $data['entry_count'] = config('constants.paginate.num_per_page');
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
        ->with('service')
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
            $hourdiff = round((strtotime($time2) - strtotime($time1))/3600, 1);
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
            $main_value = ($needDetails->size_want/100);
            $advisorDetaultValue = "";
            $advisorDetaultPercent = 0;
            $setting = DB::table('app_settings')->where('key','estimate_calculation_percent')->first();
            if($setting){
                $lead_value = ($main_value)*($setting->value);
                $needDetails->lead_value = $needDetails->size_want_currency.round($lead_value,2);
            }else{
                $needDetails->lead_value = 0;
            }

            $needDetails->selected_pro = AdvisorBids::where('area_id',$needDetails->id)->where('advisor_bids.advisor_status',1)->where('advisor_bids.status','!=',0)->leftJoin('users', 'advisor_bids.advisor_id', '=', 'users.id')->select('advisor_bids.*','users.name as advisor_name')->first();
            if($needDetails->close_type!=0){
                if($needDetails->close_type==1){
                    $needDetails->close_type="Someone not on Mortgagebox";
                }else if($needDetails->close_type==12){
                    $needDetails->close_type="In the end I didn’t need a mortgage adviser";
                }else{
                    $needDetails->close_type="--";
                }
            }else if($needDetails->advisor_id!=0){
                $user = AdvisorProfile::where('advisorId',$needDetails->advisor_id)->first();
                if($user){
                    $needDetails->close_type=$user->display_name;
                }else{
                    $needDetails->close_type="--";
                }
                
            }else{
                $needDetails->close_type="--";
            }
            // $advisorDetaultPercent = 0;
            // if($item->service_type_id!=0){
            //     $services = DefaultPercent::where('adviser_id',$user->id)->where('service_id',$item->service_type_id)->first();
            //     if($services){
            //         $advisorDetaultPercent = $services->value_percent;
            //     }
            // }
            // $lead_value = ($main_value)*($advisorDetaultPercent);
            // $advice_area[$key]->lead_value = $item->size_want_currency.$lead_value;
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

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function need_spam(Request $request){
        $post = $request->all();
        $advice_area = AdviceAreaSpam::getSpamNeed($post);
        // $data = $advice_area;   
        $data['result'] = $advice_area;
        $data['services'] = ServiceType::where('parent_id','!=',0)->where('status',1)->get();
        $data['entry_count'] = config('constants.paginate.num_per_page');
        // echo json_encode($data);exit;
        return view('need_spam.index',$data);
    }

    /**
     * Take decision status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function refundPayment(Request $request){
        try {
            $post = $request->all();
            $validate = [
                'id' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                unset($post['_token']);
                $user = AdviceAreaSpam::where('id',$post['id'])->update(['spam_status'=>$post['spam_status']]);
                if($post['spam_status']==1){
                    if($user){
                        $need = AdviceAreaSpam::where('id',$post['id'])->first();
                        if($need){
                            $need_bid = AdvisorBids::where('area_id',$need->area_id)->where('advisor_id',$need->user_id)->first();
                            if($need_bid){
                                $refund = array(
                                    'area_id'=>$need->area_id,
                                    'adviser_id'=>$need->user_id,
                                    'bid_id'=>$need_bid->id,
                                    'month'=>date('m',strtotime($need_bid->created_at)),
                                    'cost_of_lead'=>$need_bid->cost_leads,
                                    'cost_of_lead_discounted'=>$need_bid->cost_discounted,
                                    'refund_status'=>1,
                                    'created_at'=>date('Y-m-d H:i:s'),
                                );
                                NeedSpam::insertGetId($refund);
                                if($need_bid->free_introduction==1){
                                    $adviser_data = User::where('id',$need->user_id)->first();
                                    if($adviser_data){
                                        $free_lead = $adviser_data->free_promotions + 1;
                                        User::where('id',$need->user_id)->update(['free_promotions'=>$free_lead]);
                                    }
                                }
                            }
                            $this->saveNotification(array(
                                'type'=>'0', // 1:
                                'message'=>'Refund for a bid', // 1:
                                'read_unread'=>'0', // 1:
                                'user_id'=>1,// 1:
                                'advisor_id'=>$need->user_id, // 1:
                                'area_id'=>$need->area_id,// 1:
                                'notification_to'=>0
                            ));
                        }
                    }
                }
                $need = AdviceAreaSpam::where('id',$post['id'])->first();
                if($need){
                    $area = Advice_area::where('id',$need->area_id)->first();
                    if($area){
                        $userdata = User::where('id',$area->user_id)->first();
                        if($userdata){
                            $adviser = AdvisorProfile::where('advisorId',$need->user_id)->first();
                            if($adviser){
                                if($post['spam_status']==1){
                                    $message = "Admin is agree with your spam marked request.";
                                    $newArr = array(
                                        'name'=>$adviser->display_name,
                                        'email'=>$adviser->email,
                                        'message_text' => 'Admin is agree with your spam marked request.'
                                    );
                                }else{
                                    $message = "Admin is disagree with your spam marked request.";
                                    $newArr = array(
                                        'name'=>$adviser->display_name,
                                        'email'=>$adviser->email,
                                        'message_text' => 'Admin is disagree with your spam marked request.'
                                    );
                                }
                                $this->saveNotification(array(
                                    'type'=>'0', // 1:
                                    'message'=>$message, // 1:
                                    'read_unread'=>'0', // 1:
                                    'user_id'=>1,// 1:
                                    'advisor_id'=>$need->user_id, // 1:
                                    'area_id'=>$need->area_id,// 1:
                                    'notification_to'=>1
                                ));
                                
                                $c = \Helpers::sendEmail('emails.information',$newArr ,$adviser->email,$adviser->display_name,'Mortgagebox.co.uk – '.$adviser->display_name,'','');
                            }
                        }
                    }
                }
                if($user){
                    return response(\Helpers::sendSuccessAjaxResponse('Status updated successfully.',$user));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }

    public function saveNotification($data) {
        $notification = Notifications::create($data);
        if($notification) {
            return true;
        }else {
            return false;
        }
    }
}
