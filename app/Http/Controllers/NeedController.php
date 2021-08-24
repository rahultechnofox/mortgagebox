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
        $adviceBid = AdvisorBids::where('area_id',$needDetails->id)->get();
        $adviceBidCount = AdvisorBids::where('area_id',$needDetails->id)->count();
        $needDetails->totalBids = $adviceBidCount;
        if(count($adviceBid)>0){
            
            foreach($adviceBid as $advice_data){
                
                $advisors = AdvisorProfile::where('advisorId',$advice_data->advisor_id)->first();
                if($advisors){
                    $advice_data->adviser_name = $advisors->display_name;
                }else{
                    $advice_data->adviser_name = "";
                }
                $costOfLead = ($needDetails->size_want/100)*0.006;
                $time1 = Date('Y-m-d H:i:s',strtotime($advice_data->created_at));
                $time2 = Date('Y-m-d H:i:s',strtotime($advice_data->accepted_date));
                $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
                $costOfLeadsStr = "";
                $costOfLeadsDropStr = "";
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
                $advice_data->leads_status = $costOfLeadsStr;
                $advice_data->cost_of_lead_drop = $costOfLeadsDropStr;
                
            }

        }
        $needDetails->cost_of_lead = $costOfLeadsStr;
        $needDetails->cost_of_lead_drop = $costOfLeadsDropStr;
        $needDetails->bids = $adviceBid;
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
