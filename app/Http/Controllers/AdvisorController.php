<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use App\Models\DefaultPercent;
use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorEnquiries;
use App\Models\AdvisorOffers;
use App\Models\AdvisorPreferencesCustomer;
use App\Models\AdvisorPreferencesProducts;
use App\Models\AdvisorPreferencesDefault;
use App\Models\AdvisorProfile;
use App\Models\BillingAddress;
use App\Models\ChatChannel;
use App\Models\ChatModel;
use App\Models\companies;
use App\Models\CompanyTeamMembers;
use App\Models\LocationPreferences;
use App\Models\PostalCodes;
use App\Models\ReviewRatings;
use App\Models\Notifications;
use App\Models\Invoice;
use App\Models\ReviewSpam;
use App\Models\AdviceAreaSpam;
use App\Models\NeedSpam;
use PDF;
use JWTAuth;
use App\Models\User;
use App\Models\UserNotes;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Models\AppSettings;
use App\Models\AdviserProductPreferences;



class AdvisorController extends Controller
{
    protected $user;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['adviors'] = User::getAdvisors($post);
        // echo json_encode($data['adviors']);exit;
        return view('advisor.index',$data);
    }
    /**
     * Display the specified resource..
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $data = User::getAdvisorDetail($id);
        // echo json_encode($data);exit;
        $data['invoice'] = DB::table('invoices')->where('advisor_id',$id)->where('month',date('m'))->first();
        return view('advisor.show',$data);
    }

    public function downloadInvoice($id,$date) {
        $post = array();
        $post['date'] = $date;
        $user = User::where('id',$id)->first();
        $data['adviser'] = User::getAdvisorDetail($user->id);
        $data['site_address'] = DB::table('app_settings')->where('key','site_address')->first();
        $data['site_name'] = DB::table('app_settings')->where('key','mail_from_name')->first();
        $data['billing'] = DB::table('billing_addresses')->where('advisor_id',$user->id)->first();
        if($data['billing']){
            $data['billing']->value = $data['billing']->address_one; 
            if($data['billing']->address_two!=null){
                $data['billing']->value .= ", ".$data['billing']->address_two;
            }
            if($data['billing']->city!=null){
                $data['billing']->value .= ", ".$data['billing']->city;
            }
            if($data['billing']->post_code!=null){
                $data['billing']->value .= ", ".$data['billing']->post_code;
            }
        }
        $data['new_fees'] = array();
        $data['discount_credits'] = array();
        // $data['invoice']->discount_credit_arr = array();
        $spam_total = 0;
        if($data['adviser']){
            if(isset($post['date']) && $post['date']!=''){
                $explode = explode('-',$post['date']);
                $searchmonth = $explode[0];
                $searchyear = $explode[1];
                $data['invoice'] = DB::table('invoices')->where('advisor_id',$user->id)->where('month',$searchmonth)->where('year',$searchyear)->whereNull('deleted_at')->orderBy('id','DESC')->first();
            }else{
                $data['invoice'] = DB::table('invoices')->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->first();
            }
            
            if($data['invoice']){
                $summary = "";
                $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                $m = $data['invoice']->month;
                if($m==4 || $m==6 || $m==9 || $m==11){
                    $day = 30;
                }else if($m==2){
                    $day = 28;  
                }else{
                    $day = 31;
                }
                $fmonth = $m+1;
                $data['invoice']->month_check = $m;
                $summary = "01 ".$monthArr[$m-1]." ".date("Y")." - ".$day." ".$monthArr[$m-1]." ".date("Y");
                $data['invoice']->summary = $summary;
                $data['invoice']->show_date_month = $monthArr[$m-1]." ".date("Y");
                $data['invoice']->issued_date = "01/".$fmonth."/".date("Y");
                $data['invoice']->due_date = "14/".$fmonth."/".date("Y");
                $data['invoice']->invoice_data = json_decode($data['invoice']->invoice_data);
                $data['invoice']->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','!=',$data['invoice']->month)->where('advisor_id',$data['invoice']->advisor_id)->sum('total_due');
                $data['invoice']->paid_prevoius_invoice = DB::table('invoices')->where('is_paid','!=',0)->where('month','!=',$data['invoice']->month)->where('advisor_id',$data['invoice']->advisor_id)->sum('total_due');
                $data['invoice']->month_data = DB::table('invoices')->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->get(); 
                foreach($data['invoice']->month_data as $month_data){
                    $month_data->show_days = \Helpers::getMonth($month_data->month)." ".$month_data->year;
                }
                $data['invoice']->new_fees_arr = AdvisorBids::where('advisor_id',$data['invoice']->advisor_id)->with('area')->with('adviser')->get();
                // ->where('is_discounted',0)
                if(count($data['invoice']->new_fees_arr)){
                    foreach($data['invoice']->new_fees_arr as $new_bid){
                        $new_bid->cost_leads = number_format($new_bid->cost_leads,2);
                        if(isset($new_bid->area) && $new_bid->area){
                            $new_bid->area->user->advisor_profile = null;
                            if(isset($new_bid->area->user) && $new_bid->area->user){
                                $new_bid->area->user->advisor_profile = AdvisorProfile::where('advisorId',$new_bid->area->user->id)->first();
                            }
                        }
                        $new_bid->date = date("d-M-Y H:i",strtotime($new_bid->created_at));
                        if($new_bid->status==0){
                            $new_bid->status_type = "Live Lead";
                        }else if($new_bid->status==1){
                            $new_bid->status_type = "Hired";
                        }else if($new_bid->status==2){
                            $new_bid->status_type = "Completed";
                        }else if($new_bid->status==3){
                            $new_bid->status_type = "Lost";
                        }else if($new_bid->advisor_status==2){
                            $new_bid->status_type = "Not Proceeding";
                        }
                    }
                }
                
                $discount_cre = AdvisorBids::where('advisor_id',$data['invoice']->advisor_id)->where('is_discounted','!=',0)->with('area')->with('adviser')->get();
                if(count($discount_cre)){
                    foreach($discount_cre as $discount_bid){
                        $discount_bid->cost_leads = number_format($discount_bid->cost_leads,2);
                        $address = "";
                        if($discount_bid->area){
                            if(!empty($discount_bid->area->user)) {
                                $addressDetails = PostalCodes::where('Postcode',$discount_bid->area->user->post_code)->first();
                                if(!empty($addressDetails)) {
                                    if($addressDetails->Country != ""){
                                        $address = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                                        $address .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                                        $address .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                                    }
                                    
                                }
                            }
                        }
                        $discount_bid->area->address = $address;
                        // if(isset($discount_bid->area) && $discount_bid->area){
                        //     $discount_bid->area->user->advisor_profile = null;
                        //     if(isset($discount_bid->area->user) && $discount_bid->area->user){
                        //         $discount_bid->area->user->advisor_profile = AdvisorProfile::where('advisorId',$discount_bid->area->user->id)->first();
                        //     }
                        // }
                        $discount_bid->date = date("d-M-Y H:i",strtotime($discount_bid->created_at));
                        if($discount_bid->status==0){
                            $discount_bid->status_type = "Live Lead";
                        }else if($discount_bid->status==1){
                            $discount_bid->status_type = "Hired";
                        }else if($discount_bid->status==2){
                            $discount_bid->status_type = "Completed";
                        }else if($discount_bid->status==3){
                            $discount_bid->status_type = "Lost";
                        }else if($discount_bid->advisor_status==2){
                            $discount_bid->status_type = "Not Proceeding";
                        }
                        array_push($data['discount_credits'],$discount_bid);
                    }
                }

                $spam_refund = AdviceAreaSpam::where('user_id',$data['invoice']->advisor_id)->where('spam_status',1)->with('area')->get();
                foreach($spam_refund as $spam_refund_data){
                    $spam_refund_need = NeedSpam::where('adviser_id',$spam_refund_data->user_id)->where('area_id',$spam_refund_data->area_id)->first();
                    if($spam_refund_need){
                        $spam_bid = AdvisorBids::where('id',$spam_refund_need->bid_id)->with('area')->first();
                        if($spam_bid){
                            $baddress = "";
                            if($spam_bid->area){
                                if(!empty($spam_bid->area->user)) {
                                    $addressDetails = PostalCodes::where('Postcode',$spam_bid->area->user->post_code)->first();
                                    if(!empty($addressDetails)) {
                                        if($addressDetails->Country != ""){
                                            $baddress = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                                            $baddress .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                                            $baddress .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                                        }
                                        
                                    }
                                }
                            }
                            $spam_bid->area->address = $baddress;
                            if($spam_bid->status==0){
                                $spam_bid->status_type = "Live Lead";
                            }else if($spam_bid->status==1){
                                $spam_bid->status_type = "Hired";
                            }else if($spam_bid->status==2){
                                $spam_bid->status_type = "Completed";
                            }else if($spam_bid->status==3){
                                $spam_bid->status_type = "Lost";
                            }else if($spam_bid->advisor_status==2){
                                $spam_bid->status_type = "Not Proceeding";
                            }
                            $spam_bid->discount_cycle = "Refund";
                            $spam_bid->cost_leads = number_format($spam_bid->cost_leads,2);
                            $spam_bid->cost_discounted = number_format($spam_bid->cost_discounted,2);
                            // array_push($data['discount_credits'],$spam_bid);
                            $spam_bid->date = date("d-M-Y H:i",strtotime($spam_bid->created_at));
                            array_push($data['discount_credits'],$spam_bid);
                            if($spam_refund_need->cost_of_lead_discounted!=0){
                                $spam_total = $spam_total + $spam_refund_need->cost_of_lead_discounted;
                            }else{
                                $spam_total = $spam_total + $spam_refund_need->cost_of_lead;
                            }
                        }
                    }
                }
                // $discount_subtotal_to = $data['invoice']->discount_subtotal + $spam_total;
                // $data['invoice']->discount_subtotal = number_format($discount_subtotal_to,2);
                $data['invoice']->discount_credit_arr = $data['discount_credits'];
            }
        }
        $pdf = PDF::loadView('invoice.pdf_html_front.invoice-pdf', $data);
    
        return $pdf->download('invoice-'.$post['date'].'.pdf');
        // echo json_encode($data);exit;
        // return view('invoice.pdf_html_front.invoice-pdf',$data);
    }
    /**
     * Display Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceData($id,Request $request) {
        // $user = JWTAuth::parseToken()->authenticate();
        // $id = $user->id;
        $data['adviser'] = User::getAdvisorDetail($id);
        $data['site_address'] = DB::table('app_settings')->where('key','site_address')->first();
        $data['site_name'] = DB::table('app_settings')->where('key','mail_from_name')->first();
        $data['new_fees'] = array();
        if(isset($_GET['month']) && $_GET['month']!=''){
            $post['month'] = $_GET['month'];
        }else{
            $post['month'] = date('m');
        }
        if(isset($_GET['year']) && $_GET['year']!=''){
            $post['year'] = $_GET['year'];
        }else{
            $post['year'] = date('Y');
        }
        $data['discount_credits'] = array();
        if($data['adviser']){
            $data['invoice'] = DB::table('invoices')->where('advisor_id',$id)->where($post)->first();
            // echo json_encode($data);exit;
            if($data['invoice']){
                $data['invoice']->invoice_data = json_decode($data['invoice']->invoice_data);
                $data['invoice']->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','<',$data['invoice']->month)->where('advisor_id',$data['invoice']->advisor_id)->sum('total_due');
                $data['invoice']->paid_prevoius_invoice = DB::table('invoices')->where('is_paid',1)->where('month','<',$data['invoice']->month)->where('advisor_id',$data['invoice']->advisor_id)->sum('total_due');
                $data['invoice']->new_fees_arr = AdvisorBids::where('advisor_id',$data['invoice']->advisor_id)->where('is_discounted',0)->with('area')->with('adviser')->get();
                if(count($data['invoice']->new_fees_arr)){
                    foreach($data['invoice']->new_fees_arr as $new_bid){
                        $new_bid->date = date("d-M-Y H:i",strtotime($new_bid->created_at));
                        if($new_bid->status==0){
                            $new_bid->status_type = "Live Lead";
                        }else if($new_bid->status==1){
                            $new_bid->status_type = "Hired";
                        }else if($new_bid->status==2){
                            $new_bid->status_type = "Completed";
                        }else if($new_bid->status==3){
                            $new_bid->status_type = "Lost";
                        }else if($new_bid->advisor_status==2){
                            $new_bid->status_type = "Not Proceeding";
                        }
                    }
                }
                $data['invoice']->discount_credit_arr = AdvisorBids::where('advisor_id',$data['invoice']->advisor_id)->where('is_discounted','!=',0)->with('area')->with('adviser')->get();
                if(count($data['invoice']->discount_credit_arr)){
                    foreach($data['invoice']->discount_credit_arr as $discount_bid){
                        $discount_bid->date = date("d-M-Y H:i",strtotime($discount_bid->created_at));
                        if($discount_bid->status==0){
                            $discount_bid->status_type = "Live Lead";
                        }else if($discount_bid->status==1){
                            $discount_bid->status_type = "Hired";
                        }else if($discount_bid->status==2){
                            $discount_bid->status_type = "Completed";
                        }else if($discount_bid->status==3){
                            $discount_bid->status_type = "Lost";
                        }else if($discount_bid->advisor_status==2){
                            $discount_bid->status_type = "Not Proceeding";
                        }
                    }
                }
            }
        }
        // echo json_encode($data);exit;
        return view('advisor.invoice',$data);
    }

    
    /**
     * Update FCA the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateFCAStatus(Request $request){
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
                if($post['status']==1){
                    $postData['FCA_verified'] = date("Y-m-d H:i:s");
                    $postData['invalidate_fca'] = 0;
                    $advisor = AdvisorProfile::where('id',$post['id'])->first();
                    Notifications::create(array(
                        'type'=>'10', // 1:
                        'message'=>'FCA number has been confirmed. You are now able to see and buy leads', // 1:
                        'read_unread'=>'1', // 1:
                        'user_id'=>1,// 1:
                        'advisor_id'=>$advisor->advisorId, // 1:
                        'area_id'=>0,// 1:
                        'notification_to'=>1
                    ));
                }else{
                    $postData['FCA_verified'] = null;
                    $postData['invalidate_fca'] = 1;
                }
                $user = AdvisorProfile::where('id',$post['id'])->update($postData);
                if($user){
                    return response(\Helpers::sendSuccessAjaxResponse('FCA updated successfully.',$user));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
    /**
     * Update status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateAdvisorStatus(Request $request){
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
                $user = User::where('id',$post['id'])->update($post);
                if($post['status']==1){
                    $message = "activated";
                }else if($post['status']==0){
                    $message = "suspended";
                }else{
                    $message = "marked inactive"; 
                }
                if($user){
                    return response(\Helpers::sendSuccessAjaxResponse('Account '.$message.' successfully.',$user));
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
        User::where('id', '=', $advisor_id)->delete();
        AdvisorProfile::where('advisorId', '=', $advisor_id)->delete();
        AdvisorBids::where('advisor_id', '=', $advisor_id)->delete();
        AdvisorPreferencesCustomer::where('advisor_id', '=', $advisor_id)->delete();
        AdvisorPreferencesProducts::where('advisor_id', '=', $advisor_id)->delete();
        $data['message'] = 'Advisor deleted!';
        return redirect()->to('admin/advisors')->with('message', $data['message']);
    }

    /**
     * Update status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function resetPassword(Request $request){
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
                $data = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';
                $password = substr(str_shuffle($data), 0, 8);
                $postData['password'] = Hash::make($password);
                $user = User::where('id',$post['id'])->first();
                if($user){
                    $email_id = $user->email;
                    $data = array(             
                        'password'=>$password,
                        'user' =>$user
                    );
                    \Mail::send('emails.reset_password', $data, function($message){
                        $email = "pradosh.soni@gmail.com";
                        $subject = 'MortgageBox Reset Password';
                        $message->to($email, "Reset")->subject
                           ($subject);
                        $message->from("socialtechnofox@gmail.com","MortgageBox");
                    });
                    $userData = User::where('id',$post['id'])->update($postData);
                    return response(\Helpers::sendSuccessAjaxResponse('Password is reset and sent to email id.'));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse('Something went wrong please try again.'));
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
    function getReviewRating()
    {

        $user = JWTAuth::parseToken()->authenticate();
        $rating =  ReviewRatings::select('review_ratings.*', 'users.name', 'users.email', 'users.address')
            ->join('users', 'review_ratings.user_id', '=', 'users.id')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->where('review_ratings.status', '=', 0)
            ->get();

        $averageRating = ReviewRatings::avg('rating');

        $ratingExcellent =  ReviewRatings::where('review_ratings.rating', '<=', '5')
            ->where('review_ratings.rating', '>', '4')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingGreat =  ReviewRatings::where('review_ratings.rating', '<=', '4')
            ->where('review_ratings.rating', '>', '3')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingAverage =  ReviewRatings::where('review_ratings.rating', '<=', '3')
            ->where('review_ratings.rating', '>', '2')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingPoor =  ReviewRatings::where('review_ratings.rating', '<=', '2')
            ->where('review_ratings.rating', '>', '1')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
            ->where('review_ratings.rating', '>', '0')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        return response()->json([
            'status' => true,
            'data' => $rating,
            'avarageRating' => number_format((float)$averageRating, 2, '.', ''),
            'total' => count($rating),
            'ratingType' => array(
                'excellent' => $ratingExcellent,
                'great' => $ratingGreat,
                'average' => $ratingAverage,
                'poor' => $ratingPoor,
                'bad' => $ratingBad,
            )

        ], Response::HTTP_OK);
    }
    public function getAdvisorLinks()
    {
        $id = JWTAuth::parseToken()->authenticate();
        $advisor_data = AdvisorProfile::select('web_address', 'facebook', 'twitter', 'linkedin_link', 'updated_at','web_address_update','fb_update','twitter_update','linkedin_update')->where('advisorId', '=', $id->id)->first();
        if ($advisor_data) {
            if($advisor_data->web_address_update!=null){
                $advisor_data->web_address_update = date("d-m-Y H:i A",strtotime($advisor_data->web_address_update));
            }
            if($advisor_data->fb_update!=null){
                $advisor_data->fb_update = date("d-m-Y H:i A",strtotime($advisor_data->fb_update));
            }
            if($advisor_data->twitter_update!=null){
                $advisor_data->twitter_update = date("d-m-Y H:i A",strtotime($advisor_data->twitter_update));
            }
            if($advisor_data->linkedin_update!=null){
                $advisor_data->linkedin_update = date("d-m-Y H:i A",strtotime($advisor_data->linkedin_update));
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advisor_data
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function setAdvisorLinks(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        $update_arr = array();
        if(isset($post['web_address']) && $post['web_address']!=''){
            if($advisor_data->web_address!=''){
                if($post['web_address']!=$advisor_data->web_address){
                    $update_arr['web_address'] = $post['web_address'];
                    $update_arr['web_address_update'] = date("Y-m-d H:i:s");
                }
            }else{
                $update_arr['web_address'] = $post['web_address'];
                // $update_arr['web_address_update'] = date("Y-m-d H:i:s");
            }
        }
        if(isset($post['facebook']) && $post['facebook']!=''){
            if($advisor_data->facebook!=''){
                if($post['facebook']!=$advisor_data->facebook){
                    $update_arr['facebook'] = $post['facebook'];
                    $update_arr['fb_update'] = date("Y-m-d H:i:s");
                }
            }else{
                $update_arr['facebook'] = $post['facebook'];
                // $update_arr['fb_update'] = date("Y-m-d H:i:s");
            }
        }
        if(isset($post['twitter']) && $post['twitter']!=''){
            if($advisor_data->twitter!=''){
                if($post['twitter']!=$advisor_data->twitter){
                    $update_arr['twitter'] = $post['twitter'];
                    $update_arr['twitter_update'] = date("Y-m-d H:i:s");
                }
            }else{
                $update_arr['twitter'] = $post['twitter'];
                // $update_arr['twitter_update'] = date("Y-m-d H:i:s");
            }
        }
        if(isset($post['linkedin_link']) && $post['linkedin_link']!=''){
            if($advisor_data->linkedin_link!=''){
                if($post['linkedin_link']!=$advisor_data->linkedin_link){
                    $update_arr['linkedin_link'] = $post['linkedin_link'];
                    $update_arr['linkedin_update'] = date("Y-m-d H:i:s");
                }
            }else{
                $update_arr['linkedin_link'] = $post['linkedin_link'];
                // $update_arr['linkedin_update'] = date("Y-m-d H:i:s");
            }
        }
        $advisorDetails = AdvisorProfile::where('id', '=',$advisor_data->id)->update($update_arr);
        AdvisorProfile::where('company_id', $advisor_data->company_id)->update($update_arr);

        $advisor_data_after = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'Links updated successfully',
            'data' => $advisor_data_after
        ], Response::HTTP_OK);
    }

    public function getAdvisorProfileByAdvisorId($id)
    {
        JWTAuth::parseToken()->authenticate();
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id)->first();
        if($advisor_data){
            $advisor_data->is_make_enquiry_visible = 1;
            $avisor_bids_check = AdvisorBids::where('advisor_id',$id)->whereIn('status',[0,1])->orderBy('id','DESC')->first();
            $advisor_data->avisor_bids_check = $avisor_bids_check;
            if($avisor_bids_check){
                $area = Advice_area::where('id',$avisor_bids_check->area_id)->whereIn('advisor_id',[$id,0])->first();
                $advisor_data->area = $area;
                if($area){
                    if($area->area_status<3){
                        $advisor_data->is_make_enquiry_visible = 0;
                    }
                }
            }
            $advisor_data->profile_percent = 15;
            if($advisor_data->image!=''){
                $advisor_data->profile_percent = $advisor_data->profile_percent + 20;
            }
            if($advisor_data->web_address!=''){
                $advisor_data->profile_percent = $advisor_data->profile_percent + 20;
            }
            $advisor_data->completed_bid = AdvisorBids::where('advisor_id',$id)->where('status',2)->where('advisor_status',1)->count();
            $company = companies::where('id',$advisor_data->company_id)->first();
            if($company){
                if($advisor_data->short_description!=''){
                    if($company->company_about!=''){
                        $advisor_data->profile_percent = $advisor_data->profile_percent + 15;
                    }
                }
                $advisor_data->company_name = $company->company_name;
                $advisor_data->company_about = $company->company_about;
                $advisor_data->updated_by_name = "";
                if($company->updated_by!=0){
                    $updated_by_user = AdvisorProfile::where('advisorId',$company->updated_by)->first();
                    if($updated_by_user){
                        $advisor_data->updated_by_name = $updated_by_user->display_name;
                    }
                }
            }
            $company_team = CompanyTeamMembers::where('email',$advisor_data->email)->first();
            if($company_team){
                if($company_team->isCompanyAdmin==1){
                    $advisor_data->is_admin = 1;
                }else{
                    $advisor_data->is_admin = 0;
                    $adviser_company_admin = AdvisorProfile::where('advisorId',$company_team->advisor_id)->first();
                    if($adviser_company_admin){
                        if($adviser_company_admin->company_logo!=''){
                            $company_logo = $adviser_company_admin->company_logo;
                            $adviser_company_admin->company_logo = $company_logo;
                            if($advisor_data->image==''){
                                $advisor_data->image = $company_logo;
                            }
                            $advisor_data->company_logo = $company_logo;

                        }
                    }
                }
            }else{
                $advisor_data->is_admin = 2;
            }
            if($advisor_data->is_admin!=1){
                $company_logo = "";
                $company_ad = companies::where('id',$advisor_data->company_id)->first();
                if($company_ad){
                    $adviser_company_admin = AdvisorProfile::where('advisorId',$company_ad->company_admin)->first();
                    if($adviser_company_admin){
                        if($adviser_company_admin->company_logo!=''){
                            $company_logo = $adviser_company_admin->company_logo;
                            $adviser_company_admin->company_logo = $company_logo;
                        }
                    }
                }
            }
        }
        
        $last_activity = User::select('users.last_active')->where('id', '=', $id)->first();
        $offer_data = AdvisorOffers::where('advisor_id', '=', $id)->get();
        // if($advisor_data){
        //     if(count($offer_data)){
        //         $advisor_data->profile_percent = $advisor_data->profile_percent + 30;
        //         $advisor_data->offer = 1;
        //     }else{
        //         $advisor_data->offer = 0;
        //     }
        // }
        $countofCOunt = 0;
        $rating =  ReviewRatings::select('review_ratings.*', 'users.name', 'users.email', 'users.address')
            ->leftJoin('users', 'review_ratings.user_id', '=', 'users.id')
            ->leftJoin('review_spam', 'review_ratings.id', '=', 'review_spam.review_id')
            ->where('review_ratings.advisor_id', '=', $id)
            ->where('review_ratings.status',0)
            ->with('area')
            // ->where('review_spam.spam_status', '!=', 0)
            ->get();
        if(count($rating)){
            foreach($rating as $rating_data){
                $rating_reply = ReviewRatings::where('status',1)->where('parent_review_id',$rating_data->id)->first();
                if($rating_reply){
                    $rating_data->reply = $rating_reply->reply_reason;
                    $rating_data->is_replied = 1;
                    $rating_data->replied_on = date("d-m-Y H:i A",strtotime($rating_reply->replied_on));
                }else{
                    $rating_data->reply = "";
                    $rating_data->is_replied = 0;
                }
                $spam = ReviewSpam::where('review_id',$rating_data->id)->first();
                $rating_data->reason = "";
                $rating_data->spam_status = 2;
                $rating_data->is_spam = 0;
                if($spam){
                    $rating_data->reason = $spam->reason;
                    $rating_data->spam_status = $spam->spam_status;
                    $rating_data->is_spam = 1;
                    if($spam->spam_status!=1){
                		$countofCOunt = $countofCOunt + 1;
                    }
                }else{
                	$countofCOunt = $countofCOunt + 1;
                    $rating_data->reason = "";
                    $rating_data->spam_status = 2;
                    $rating_data->is_spam = 0;
                }
                
            }
        }

        $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
        ->Where('advisor_status','=',1)
        ->Where('advisor_id','=',$id)
        ->count();
        
        $averageRating = ReviewRatings::where('advisor_id', '=', $id)->where('status', '!=', 2)->avg('rating');
        if ($advisor_data) {
            $advisor_data->used_by  = $usedByMortage;
            $advisor_data->last_activity  = $last_activity->last_active;
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advisor_data,
                'offers' => $offer_data,
                'review_rating' => $rating,
                'avarageRating' => number_format((float)$averageRating, 1, '.', ''),
                'total' => $countofCOunt,

            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function getAdvisorProfileById($id)
    {
        $user = User::where('id',$id)->first();
        // echo json_encode($user);exit;
        if($user){
            if($user->user_role == 1) {
                $userDetails =  AdvisorProfile::where('advisorId', '=', $user->id)->first(); 
                if($userDetails){
                    $team_member = CompanyTeamMembers::where('email',$userDetails->email)->first();
                    // $userDetails->is_admin = $team_member;
                    if($team_member){
                        if($team_member->isCompanyAdmin==1){
                            $user->is_admin = 1;
                        }else{
                            $user->is_admin = 0;
                        }
                    }else{
                        $user->is_admin = 2;
                    }
                }
                $user->userDetails = $userDetails;
            }else{
                $user->userDetails = [];
            }
            return response()->json([
                'status' => true,
                'data' => $user
            ]);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function searchAdvisor(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        $advisor_data = AdvisorProfile::getSearchedAdvisor($post);
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }
    public function updateAdvisorAboutUs(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $advisorDetails = AdvisorProfile::where('advisorId', '=', $id->id)->update(
            [
                'short_description' => $request->short_description,
            ]
        );
        
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        // Update Compnay info for all advisers
        if($advisor_data){
            if($advisor_data->company_id > 0) {
                //     $advisorDetails = AdvisorProfile::where('company_id', '=', $advisor_data->company_id)->update(
                //     [
                //         'description' => $request->description,
                //         'description_updated' => Date('Y-m-d H:i:s'),
                //     ]
                // );
                $advisorDetails = companies::where('id', '=', $advisor_data->company_id)->update(
                    [
                        'company_about' => $request->company_about,
                        'updated_by' => $request->updated_by,
                        'updated_at' => Date('Y-m-d H:i:s'),
                    ]
                );
                $company = companies::where('id', '=', $advisor_data->company_id)->first();
                $advisor_data->company_about = "";
                $advisor_data->updated_by_user = "";
                if($company){
                    $advisor_data->company_about = $company->company_about;
                    if($company->updated_by!=0){
                        $updated_by_user = AdvisorProfile::where('advisorId', '=',$company->updated_by)->first();
                        if($updated_by_user){
                            $advisor_data->updated_by_user = $updated_by_user->display_name;
                        }
                    }
                }
            }
        }
        
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }
    public function updateAdvisorGeneralInfo(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        $data = $request->only('display_name');
        // if ($request->company_logo == "") {
        //     $request->company_logo = "";
        // }
        // if ($request->image == "") {
        //     $request->image = "";
        // }
        $arr = array();
    
        if(isset($request->email) && $request->email != ''){
            $emailExist = User::where('email',$request->email)->where('id','!=',$id->id)->count();
            if ($emailExist) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email is already exist',
                    'data' => $post
                ], Response::HTTP_OK);
            }
            $arr['email'] = $request->email;
        }

        
        if ($request->company_logo != "") {
            $explode = explode(":",$request->company_logo);
            if($explode[0]!='https'){
                if ($request->hasFile('company_logo')) {
                    $uploadFolder = 'advisor';
                    $image = $request->file('company_logo');
                    // $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $name = $request->file('company_logo')->getClientOriginalName();
                    $extension = $request->file('company_logo')->extension();
                    $originalString = str_replace("." . $extension, "", $name);
                    //$upfileName = preg_replace('/\s+/', '_', $originalString).".".$extension;
                    $upfileName = $name;
        
                    $num = 1;
        
        
                    while (Storage::exists("public/" . $uploadFolder . "/" . $upfileName)) {
                        $file_name = (string) $originalString . "-" . $num;
                        $upfileName = $file_name . "." . $extension;
                        $num++;
                    }
                    $image_uploaded_path = $image->storeAs($uploadFolder, $upfileName, 'public');
                    $request->company_logo = basename($image_uploaded_path);
        
        
                    $uploadedImageResponse = array(
                        "image_name" => basename($image_uploaded_path),
                        "image_url" => Storage::disk('public')->url($image_uploaded_path),
                        "mime" => $image->getClientMimeType()
                    );
                } else if ($request->company_logo != "") {
                    $request->company_logo =  str_replace("data:image/jpeg;base64,","",$request->company_logo);
                    $request->company_logo =  str_replace("data:image/png;base64,","",$request->company_logo);
                    $file = base64_decode($request->company_logo);
                    $folderName = 'advisor';
        
                    $safeName = $this->quickRandom(10) . '.' . 'png';
                    $destinationPath = public_path() . "/storage/" . $folderName;
                    file_put_contents($destinationPath . "/" . $safeName, $file);
        
                    //save new file path into db
                    $request->company_logo = $safeName;
                    $arr['company_logo'] = $request->company_logo;
                }
                $arr['company_logo'] = $request->company_logo;
            }
        }
        if ($request->image != "") {
            $explode1 = explode(":",$request->image);
            if($explode1[0]!='https'){
                if ($request->hasFile('image')) {
                    $uploadFolder = 'advisor';
                    $image = $request->file('image');
                    // $image_uploaded_path = $image->store($uploadFolder, 'public');
                    $name = $request->file('image')->getClientOriginalName();
                    $extension = $request->file('image')->extension();
                    $originalString = str_replace("." . $extension, "", $name);
                    //$upfileName = preg_replace('/\s+/', '_', $originalString).".".$extension;
                    $upfileName = $name;
        
                    $num = 1;
        
        
                    while (Storage::exists("public/" . $uploadFolder . "/" . $upfileName)) {
                        $file_name = (string) $originalString . "-" . $num;
                        $upfileName = $file_name . "." . $extension;
                        $num++;
                    }
                    $image_uploaded_path = $image->storeAs($uploadFolder, $upfileName, 'public');
                    $request->image = basename($image_uploaded_path);
        
        
                    $uploadedImageResponse = array(
                        "image_name" => basename($image_uploaded_path),
                        "image_url" => Storage::disk('public')->url($image_uploaded_path),
                        "mime" => $image->getClientMimeType()
                    );
                } else if ($request->image != "") {
                    $request->image =  str_replace("data:image/jpeg;base64,","",$request->image);
                    $request->image =  str_replace("data:image/png;base64,","",$request->image);
                    $file = base64_decode($request->image);
                    $folderName = 'advisor';
        
                    $safeName = $this->quickRandom(10) . '.' . 'png';
                    $destinationPath = public_path() . "/storage/" . $folderName;
                    file_put_contents($destinationPath . "/" . $safeName, $file);
        
                    //save new file path into db
                    $request->image = $safeName;
                    $arr['image'] = $request->image;
                }
            }
        }
        if ($request->display_name != "" && $request->display_name != "null") {
            $arr['display_name'] = $request->display_name;
        }
        if ($request->FCANumber != "" && $request->FCANumber != "null") {
            $arr['FCANumber'] = $request->FCANumber;
            $arr['invalidate_fca'] = 0;
        }
        if ($request->phone_number != "" && $request->phone_number != "null") {
            $arr['phone_number'] = $request->phone_number;
        }
        if ($request->city != "" && $request->city != "null"){
            $arr['city'] = $request->city;
        }
        if ($request->postcode != "" && $request->postcode != "null") {
            $arr['postcode'] = $request->postcode;
        }
        if ($request->role != "" && $request->role != "null") {
            $arr['role'] = $request->role;
        }

        if ($request->network != "" && $request->network != "null") {
            $arr['network'] = $request->network;
        }

        // if ($request->email != "" && $request->email != "null") {
        //     $arr['email'] = $request->email;
        // }

        if ($request->gender != "" && $request->gender != "null") {
            $arr['gender'] = $request->gender;
        }

        if ($request->language != "" && $request->language != "null") {
            $arr['language'] = $request->language;
        }

        if ($request->company_name != "" && $request->company_name != "null") {
            $arr['company_name'] = $request->company_name;
        }
        $advisorDetails = AdvisorProfile::where('advisorId', '=', $id->id)->update($arr);
        if ($request->email != "" && $request->email != "null") {
            User::where('id',$id->id)->update(['email'=>$request->email]);
        }
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        // echo json_encode($advisor_data);exit;
        if(isset($request->company_name) && $request->company_name!=''){
            companies::where('id',$advisor_data->company_id)->update(['company_name'=>$request->company_name]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $arr
        ], Response::HTTP_OK);
    }
    public  function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function getAdvisorProductPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->first();
        if (empty($notification)) {
            AdvisorPreferencesProducts::create([
                'advisor_id' => $user->id,
            ]);
        }
        $notification = AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    function updateAdvisorProductPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        if(isset($post) && !empty($post)){
            $mortgage_max_size = 0;
            $mortgage_min_size = 0;
            if(isset($post['mortgage_max_size']) && $post['mortgage_max_size']!=''){
                $mortgage_max_size = $post['mortgage_max_size'];
                unset($post['mortgage_max_size']);
            }
            if(isset($post['mortgage_min_size']) && $post['mortgage_min_size']!=''){
                $mortgage_min_size = $post['mortgage_min_size'];
                unset($post['mortgage_min_size']);
            }
            if($mortgage_max_size!=0 && $mortgage_min_size!=0){
                AdvisorProfile::where('advisorId',$user->id)->update(['mortgage_max_size'=>$mortgage_max_size,'mortgage_min_size'=>$mortgage_min_size]);
            }
            foreach($post as $key=>$value){
                $preference = AdviserProductPreferences::where('service_id',$key)->where('adviser_id',$user->id)->first();
                if($preference){
                    if($value==0){
                        AdviserProductPreferences::where('id',$preference->id)->delete();
                    }
                }else{
                    $productArr = array(
                        'service_id'=>$key,
                        'adviser_id'=>$user->id,
                        'created_at'=>date('Y-m-d H:i:s'),
                        'updated_at'=>date('Y-m-d H:i:s'),
                    );
                    AdviserProductPreferences::insertGetId($productArr);
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Successfully updated',
                'data' => []
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong please try again',
                'data' => $post
            ], Response::HTTP_OK);
        }
        // AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->update([
        //     "remortgage" => $request->remortgage,
        //     "first_buyer" => $request->first_buyer,
        //     "next_buyer" => $request->next_buyer,
        //     "but_let" => $request->but_let,
        //     "equity_release" => $request->equity_release,
        //     "overseas" => $request->overseas,
        //     "self_build" => $request->self_build,
        //     "mortgage_protection" => $request->mortgage_protection,
        //     "secured_loan" => $request->secured_loan,
        //     "bridging_loan" => $request->bridging_loan,
        //     "commercial" => $request->commercial,
        //     "something_else" => $request->something_else,
        //     "mortgage_min_size" => $request->mortgage_min_size,
        //     "mortgage_max_size" => $request->mortgage_max_size,

        // ]);
        // $notification = AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->first();
        
    }
    public function getAdvisorCustomerPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->first();
        if (empty($notification)) {

            AdvisorPreferencesCustomer::create([
                'advisor_id' => $user->id,
            ]);
        }
        $notification = AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    function updateAdvisorCustomerPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->update([
            "self_employed" => $request->self_employed,
            "non_uk_citizen" => $request->non_uk_citizen,
            "adverse_credit" => $request->adverse_credit,
            "ltv_max" => $request->ltv_max,
            "lti_max" => $request->lti_max,
            "asap" => $request->asap,
            "next_3_month" => $request->next_3_month,
            "more_3_month" => $request->more_3_month,
            "fees_preference" => $request->fees_preference,
        ]);
        $notification = AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    public function getAdvisorLocationPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $locations = AdvisorProfile::select(['postcode AS post_code', 'serve_range AS miles'])->where('advisorId', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }
    function updateAdvisorLocationPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorProfile::where('advisorId', '=', $user->id)->update([
            "postcode" => $request->post_code,
            "serve_range" => $request->miles,
        ]);
        $locations = AdvisorProfile::select(['postcode AS post_code', 'serve_range AS miles'])->where('advisorId', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }
    public function getAdvisorBillingAddress(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
        if (empty($locations)) {

            BillingAddress::create([
                'advisor_id' => $user->id,
            ]);
        }
        $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
        if($locations){
            $locations->post_code = strtoupper($locations->post_code);
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }
    function updateAdvisorBillingAddress(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        BillingAddress::where('advisor_id', '=', $user->id)->update([
            "contact_name" => $request->contact_name,
            "invoice_name" => $request->invoice_name,
            "address_one" => $request->address_one,
            "address_two" => $request->address_two,
            "city" => $request->city,
            "post_code" => $request->post_code,
            "contact_number" => $request->contact_number,
            "is_vat_registered" => $request->is_vat_registered,

        ]);
        $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
        if($locations){
            $locations->post_code = strtoupper($locations->post_code);
        }
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }

    public function getAdvisorFirstMessage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $firstMessage = AdvisorProfile::select('advisor_profiles.first_message')->where('advisorId', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $firstMessage
        ], Response::HTTP_OK);
    }
    function updateFirstMessage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorProfile::where('advisorId', '=', $user->id)->update([
            "first_message" => $request->first_message,

        ]);
        $firstMessage = AdvisorProfile::select('advisor_profiles.first_message')->where('advisorId', '=', $user->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $firstMessage
        ], Response::HTTP_OK);
    }
    
    function advisorTeam(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $company_team_id = 0;
        $company_data = CompanyTeamMembers::where('email', '=', $request->email)->first();
        if(isset($request->company_id) && $request->company_id!=''){
            $company = companies::where('id',$request->company_id)->first();
        }
        $checkUser = User::where('email','=',$request->email)->first();
        if (!empty($company_data)) {
            $company_team_data = CompanyTeamMembers::where('id',$company_data->id)->first();
            CompanyTeamMembers::where('id',$company_data->id)->update([
                'company_id' => $request->company_id,
                'advisor_id' => $user->id
            ]);

            if(!empty($checkUser)){
                AdvisorProfile::where('advisorId',$checkUser->id)->update([
                    'company_id' => $request->company_id
                ]);
            }
            $company_team_id = $company_data->id;
            
        }else{
            $profile = CompanyTeamMembers::create([
                'company_id' => $request->company_id,
                'name' => $request->name,
                'email' => $request->email,
                'advisor_id' => $user->id
            ]);
            $company_team_id = $profile->id;
        }
        
        
        if(!empty($checkUser)){
            $newArr = array(
                'name'=>ucfirst($request->name),
                'email'=>$request->email,
                'message_text' => 'Please be advised that '.$user->name.' has successfully added you to company '.$company->company_name
            );
            $c = \Helpers::sendEmail('emails.information',$newArr ,$request->email,ucfirst($request->name),'Join Company | Mortgagebox.co.uk','','');
            // $newArr = array(
            //     'name'=>ucfirst($request->name),
            //     'invited_by'=>ucfirst($user->name),
            //     'email'=>$request->email,
            //     'url' => config('constants.urls.team_email_verification_url')."".$this->getEncryptedId($company_team_id)
            // );
            // $c = \Helpers::sendEmail('emails.team_email_verification',$newArr ,$request->email,$request->name,'Join Company | Mortgagebox.co.uk','','');
        }else{
            $newArr = array(
                'name'=>ucfirst($request->name),
                'invited_by'=>ucfirst($user->name),
                'email'=>$request->email,
                'url' => config('constants.urls.team_signup_url')."?invitedBy=".$this->getEncryptedId($user->id)."&invitedToEmail=".urlencode($request->email)."&invitedForCompany=".$company->company_name
            );
            $c = \Helpers::sendEmail('emails.team_email_signup',$newArr ,$request->email,$request->name,'Mortgagebox.co.uk – '.$user->name.' has invited you to join','','');
        }
        //Send Email
        return response()->json([
            'status' => true,
            'message' => 'Team member added successfully',
            'data' => []
        ], Response::HTTP_OK);
    }

    public function verifyTeamEmail($id)
    {
        $team_id = $this->getDecryptedId($id);
        // return $team_id;
        $teamDetails = CompanyTeamMembers::where('id','=',$team_id)->first();
        if ($teamDetails) {
            CompanyTeamMembers::where('id','=',$team_id)->update([
            'is_joined' => "1"
            ]);
            AdvisorProfile::where('email','=',$teamDetails->email)->update([
                'company_id' => $teamDetails->company_id
            ]);
            $advisor = AdvisorProfile::where('email','=',$teamDetails->email)->first();
            $this->saveNotification(array(
                'type'=>'6', // 1:
                'message'=>'Invitation accepted by '.$advisor->display_name, // 1:
                'read_unread'=>'0', // 1:
                'user_id'=>$advisor->advisorId,// 1:
                'advisor_id'=>$teamDetails->advisor_id, // 1:
                'area_id'=>0,// 1:
                'notification_to'=>1
            ));
        }
        return redirect()->away(config('constants.urls.host_url'));
    }
    function updateTeam(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $company_data = CompanyTeamMembers::where('id', '=', $request->id)->first();
        if (empty($company_data)) {
            return response()->json([
                'status' => false,
                'message' => 'No record found',
                'data' => []
            ], Response::HTTP_OK);
        }
        $updatedData = array();
        $updatedData['company_id'] = $request->company_id;
        if (isset($request->name)) {
            $updatedData['name'] = $request->name;
        }
        if (isset($request->status)) {
            if($request->status==false){
                $updatedData['status'] = 0;
            }else{
                $updatedData['status'] = 1;
            }
        }
        $profile = CompanyTeamMembers::where('id', '=', $request->id)->update($updatedData);
        if($updatedData['status'] ==0){
            User::where('email',$company_data->email)->update(['status'=>2]);
        }else{
            User::where('email',$company_data->email)->update(['status'=>1]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Team member updated successfully',
            'data' => []
        ], Response::HTTP_OK);
    }
    function getAdvisorTeam($company_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $teams = CompanyTeamMembers::select('company_team_members.*')
            ->where('company_team_members.company_id', '=', $company_id)
            // ->where('company_team_members.advisor_id', '=', $user->id)
            ->join('companies', 'company_team_members.company_id', '=', 'companies.id')
           ->get();

        foreach ($teams as $key => $item) {
            $teamAdvisorDetails = User::where('users.email', '=', $item->email)->where('user_role', '=', 1)
                ->join('advisor_profiles', 'users.id', '=', 'advisor_profiles.advisorId')->first();
            $teams[$key]['advisorDetails'] = $teamAdvisorDetails;
        }
        
        if (count($teams) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $teams
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    function checkIfExistInAdvisorTeam($company_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $isExist = CompanyTeamMembers::where('company_id', $company_id)
            ->where('email', $user->email)->first();
        $roleInCompany = "Contact_Administrator";
        if ($isExist) {
            $roleInCompany = $isExist->isCompanyAdmin ? "Mortgage_Adviser" : "Contact_Administrator";
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $roleInCompany
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => $roleInCompany
            ], Response::HTTP_OK);
        }
    }
    public function deleteTeam(Request $request, $id)
    {

        $userDetails = JWTAuth::parseToken()->authenticate();
        $offers = CompanyTeamMembers::where('id', '=', $id)->delete();
        //User created, return success response
        $chatData = CompanyTeamMembers::get();
        return response()->json([
            'status' => true,
            'message' => 'Team member deleted successfully',
            'data' => $chatData
        ], Response::HTTP_OK);
    }
    function getDistanceRange($customerEasting, $advisorEasting, $customerNorthing, $advisorNorthing)
    {
        $C5 = pow(abs($advisorEasting - $customerEasting), 2);
        $D5 = pow(abs($advisorNorthing - $customerNorthing), 2);
        $distanceInMeter = pow(($C5 + $D5), 0.5);
        return $distanceInKm = floor($distanceInMeter / 1000);
    }
    function makeEnquiry(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        $userDetails = User::where('id', '=', $request->advisor_id)->first();
        $advisor = AdvisorProfile::where('advisorId', $request->advisor_id)->first();

       

            $ltv_max  = ($request->prop_value)/$request->mortgage_required;
            $lti_max  = ($request->prop_value)/$request->combined_income;

            $advice_area = Advice_area::create([
                'user_id' => $user->id,
                'service_type_id' => $request->need_advice,
                'request_time' => $request->how_soon,
                'property' => $request->prop_value,
                'size_want' => $request->mortgage_required,
                'combined_income' => $request->combined_income,
                'ltv_max'=>$ltv_max,
                'lti_max'=>$lti_max,
                'inquiry_adviser_id'=>$request->advisor_id,
                'inquiry_match_me'=>$request->match_me,
                'inquiry_description'=>$request->anything_else,
                'description'=>$request->anything_else,
                'combined_income_currency'=>'£',
                'property_currency'=>'£',
                'size_want_currency'=>'£'
            ]);
            $service_name = "";
            $service = ServiceType::where('id',$request->need_advice)->first();
            if($service){
                $service_name = $service->name;
            }
            $newArr = array(
                'name'=>$request->name,
                'recevier_name'=>$advisor->display_name,
                'email'=>$request->email,
                'mortgage_required' => number_format($post['mortgage_required'],0),
                'prop_value' => number_format($post['prop_value'],0),
                'combined_income' => number_format($post['combined_income'],0),
                'how_soon' => $post['how_soon'],
                'post_code' => $post['post_code'],
                'anything_else' => $post['anything_else'],
            );
            $c = \Helpers::sendEmail('emails.enquiry',$newArr ,$advisor->email,$advisor->display_name,'Mortgagebox.co.uk – New Enquiry from '.$request->name,'','');

            $this->saveNotification(array(
                'type'=>'9', // 1:
                'message'=>$request->name.' has directly contacted you regarding a '.$request->mortgage_required.' '.$service_name.' mortgage need',// 1:
                'read_unread'=>'0', // 1:
                'user_id'=>$user->id,// 1:
                'advisor_id'=>$request->advisor_id, // 1:
                'area_id'=>$advice_area->id,// 1:
                'notification_to'=>1
            ));
        

        return response()->json([
            'status' => true,
            'message' => 'Enquiry sent successfully!',
            'data' => []
        ], Response::HTTP_OK);
    }
    function advisorDashboard() {
        $userDetails = JWTAuth::parseToken()->authenticate();
        $userData = AdvisorProfile::where('advisorId',$userDetails->id)->first();
        $requestTime = [];
        $discount='';
        $ltv_max = 0;
        $lti_max = 0;
        $self = 0;
        $non_uk_citizen = 0;
        $adverse = 0;
        $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id',$userDetails->id)->first();
        if(!empty($userPreferenceCustomer)) {
            $ltv_max = $userPreferenceCustomer->ltv_max;
            $lti_max = $userPreferenceCustomer->lti_max;
            $self = $userPreferenceCustomer->self_employed;
            $non_uk_citizen = $userPreferenceCustomer->non_uk_citizen;
            $adverse = $userPreferenceCustomer->adverse_credit;
            if($userPreferenceCustomer->asap == 1) {
                $requestTime[] = "as soon as possible";
            }
            if($userPreferenceCustomer->next_3_month == 1) {
                $requestTime[] = "in the next 3 months";
            }
            if($userPreferenceCustomer->more_3_month == 1) {
                $requestTime[] = "in more than 3 months";
            }
        }
        $userPreferenceProduct = AdviserProductPreferences::where('adviser_id',$userDetails->id)->get();
        $service_type = array();
        if(!empty($userPreferenceProduct)) {
            foreach($userPreferenceProduct as $userPreferenceProduct_data){
                array_push($service_type,$userPreferenceProduct_data->service_id);
            }
            
        }
        if($userData){
            $userData->profile_percent = 15;
            if($userData->image!=''){
                $userData->profile_percent = $userData->profile_percent + 20;
            }
            if($userData->short_description!=''){
                $company = companies::where('id',$userData->company_id)->first();
                if($company){
                    $userData->company_about = $company->company_about;
                    if($company->company_about!=''){
                        $userData->profile_percent = $userData->profile_percent + 15;
                    }
                }
            }
            $offer_data = AdvisorOffers::where('advisor_id', '=', $userDetails->id)->get();
            if(count($offer_data)){
                $userData->profile_percent = $userData->profile_percent + 30;
                $userData->offer = 1;
            }else{
                $userData->offer = 0;
            }
            if($userData->web_address!=''){
                $userData->profile_percent = $userData->profile_percent + 20;
            }
        }
        
        $matched_last_hour = DB::table('advice_areas')
            ->where('created_at', '>=',DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))
            ->where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse)->whereIn('service_type_id',$service_type)->count();
            // orWhereIn('request_time',$requestTime)->
        $matched_last_today = Advice_area::where('created_at','>=', Carbon::today())->where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse)->whereIn('service_type_id',$service_type)->count();
        $matched_last_yesterday = Advice_area::where('created_at','>=', Carbon::yesterday())->where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse)->whereIn('service_type_id',$service_type)->count();
        $less_than_3_days = Advice_area::where('created_at','>=', Carbon::today()->subDays(3))->where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse)->whereIn('service_type_id',$service_type)->count();
        // $remortgage = Advice_area::where('service_type', '=', 'remortgage')->count();
        // $next_time_buyer = Advice_area::where('service_type', '=', 'first time buyer')->count();
        // $first_time_buyer = Advice_area::where('service_type', '=', 'next time buyer')->count();
        // $buy_to_let = Advice_area::where('service_type', '=', 'buy to let')->count();
        $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE  m.to_user_id = $userDetails->id AND m.to_user_id_seen = 0");
        
        $service = ServiceType::where('parent_id','!=',0)->where('status',1)->limit(4)->orderBy('sequence','ASC')->get();
        foreach($service as $service_data){
            $service_data->service_data_count = Advice_area::where('service_type_id', '=', $service_data->id)->where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse)->count();
        }
        $service_arr = array();
        if(count($service)){    
            $service_arr = array(
                $service[0]->name => $service[0]->service_data_count,
                $service[1]->name => $service[1]->service_data_count,
                $service[2]->name => $service[2]->service_data_count,
                $service[3]->name => $service[3]->service_data_count,
            );
        }
        $accepted_leads = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '!=', 3)
            ->where('advisor_status', '=', 1)
            ->where('is_discounted',0)
            ->where('free_introduction',0)
            ->sum('cost_leads');
        $hired_leads = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 1)
            ->where('advisor_status', '=', 1)
            ->sum('cost_leads');

        $accepted_leads_for_per = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '!=', 3)
            ->where('advisor_status', '=', 1)
            ->count();
        $hired_leads_for_per = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 1)
            ->where('advisor_status', '=', 1)
            ->count();
        $conversion_rate = 0;
        if($hired_leads_for_per!=0 && $accepted_leads_for_per!=0){
            $conversion_rate = ($hired_leads_for_per / $accepted_leads_for_per) * 100;
        }
        // $live_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
        //     ->where('status', '=', 0)
        //     ->where('advisor_status', '=', 1)
        //     ->where('created_at', '>', Carbon::today()->subDays(30))
        //     ->count();
        // $live_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 0)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(90))
        // ->count();

        // $live_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 0)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(365))
        // ->count();
        /************ This month COunt ************/
        $live_leads_months = 0;
        $accepted_months = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',0)->where('advisor_status',1)->where('created_at', '>', Carbon::today()->subDays(30))->get();
        if(count($accepted_months)){
            foreach($accepted_months as $accepted_months_data){
                $dataPurchased_months = Advice_area::where('id',$accepted_months_data->area_id)->where('advisor_id',0)->first();
                if($dataPurchased_months){
                    $live_leads_months = $live_leads_months + 1;
                }
            }
        }

        $hired_leads_months = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',1)->where('created_at', '>', Carbon::today()->subDays(30))->count();
        $completed_leads_months = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',2)->where('advisor_status',1)->where('created_at', '>', Carbon::today()->subDays(30))->count();
        $lost_leads_months = 0;
        $AllMyBids_months = AdvisorBids::where('advisor_id',$userDetails->id)->where('status','!=',1)->where('status','!=',2)->where('created_at', '>', Carbon::today()->subDays(30))->get();
        if(count($AllMyBids_months)){
            foreach($AllMyBids_months as $AllMyBids_months_data){
                $dataLost_months = Advice_area::where('id',$AllMyBids_months_data->area_id)->where('advisor_id','!=',$userDetails->id)->where('advisor_id','!=',0)->first();
                if($dataLost_months){
                    $lost_leads_months = $lost_leads_months + 1;
                }
            }
        }

        /************ This month COunt ************/

        /************ Quarter COunt ************/
        $live_leads_quarter = 0;
        $accepted_quarter = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',0)->where('advisor_status',1)->where('created_at', '>', Carbon::today()->subDays(90))->get();
        if(count($accepted_quarter)){
            foreach($accepted_quarter as $accepted_quarter_data){
                $dataPurchased_quarter = Advice_area::where('id',$accepted_quarter_data->area_id)->where('advisor_id',0)->first();
                if($dataPurchased_quarter){
                    $live_leads_quarter = $live_leads_quarter + 1;
                }
            }
        }

        $hired_leads_quarter = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',1)->where('created_at', '>', Carbon::today()->subDays(90))->count();
        $completed_leads_quarter = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',2)->where('advisor_status',1)->where('created_at', '>', Carbon::today()->subDays(90))->count();
        $lost_leads_quarter = 0;
        $AllMyBids_quarter = AdvisorBids::where('advisor_id',$userDetails->id)->where('status','!=',1)->where('status','!=',2)->where('created_at', '>', Carbon::today()->subDays(90))->get();
        if(count($AllMyBids_quarter)){
            foreach($AllMyBids_quarter as $AllMyBids_quarter_data){
                $dataLost_quarter = Advice_area::where('id',$AllMyBids_quarter_data->area_id)->where('advisor_id','!=',$userDetails->id)->where('advisor_id','!=',0)->first();
                if($dataLost_quarter){
                    $lost_leads_quarter = $lost_leads_quarter + 1;
                }
            }
        }

        /************ Quarter COunt ************/

        /************ Year COunt ************/
        $live_leads_year = 0;
        $accepted_year = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',0)->where('advisor_status',1)->where('created_at', '>', Carbon::today()->subDays(365))->get();
        if(count($accepted_year)){
            foreach($accepted_year as $accepted_year_data){
                $dataPurchased_year = Advice_area::where('id',$accepted_year_data->area_id)->where('advisor_id',0)->first();
                if($dataPurchased_year){
                    $live_leads_year = $live_leads_year + 1;
                }
            }
        }

        $hired_leads_year = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',1)->count();
        $completed_leads_year = AdvisorBids::where('advisor_id',$userDetails->id)->where('status',2)->where('advisor_status',1)->count();
        $lost_leads_year = 0;
        $AllMyBids_year = AdvisorBids::where('advisor_id',$userDetails->id)->where('status','!=',1)->where('status','!=',2)->get();
        if(count($AllMyBids_year)){
            foreach($AllMyBids_year as $AllMyBids_year_data){
                $dataLost_year = Advice_area::where('id',$AllMyBids_year_data->area_id)->where('advisor_id','!=',$userDetails->id)->where('advisor_id','!=',0)->first();
                if($dataLost_year){
                    $lost_leads_year = $lost_leads_year + 1;
                }
            }
        }

        /************ Year COunt ************/

        // $hired_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
        //     ->where('status', '=', 1)
        //     ->where('advisor_status', '=', 1)
        //     ->where('created_at', '>', Carbon::today()->subDays(30))
        //     ->count();
        // $hired_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 1)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(90))
        // ->count();

        // $hired_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 1)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(365))
        // ->count();

        // $completed_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
        //     ->where('status', '=', 2)
        //     ->where('advisor_status', '=', 1)
        //     ->where('created_at', '>', Carbon::today()->subDays(30))
        //     ->count();
        // $completed_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 2)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(90))
        // ->count();

        // $completed_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 2)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(365))
        // ->count();

        // $lost_leads_months = 0;
        // $checkBidMonth= AdvisorBids::where('advisor_id',$userDetails->id)->where('created_at', '>', Carbon::today()->subDays(30))->get();
        // if(count($checkBidMonth)){
        //     foreach($checkBidMonth as $checkBidMonth_data){
        //         $dataLostMonth = Advice_area::where('id',$checkBidMonth_data->area_id)->where('advisor_id','!=',$userDetails->id)->where('advisor_id','!=',0)->first();
        //         if($dataLostMonth){
        //             $lost_leads_months = $lost_leads_months + 1;
        //         }
        //     }
        // }

        // $lost_leads_quarter = 0;
        // $checkBidQuarter = AdvisorBids::where('advisor_id',$userDetails->id)->where('created_at', '>', Carbon::today()->subDays(90))->get();
        // if(count($checkBidQuarter)){
        //     foreach($checkBidQuarter as $checkBidQuarter_data){
        //         $dataLostQuarter = Advice_area::where('id',$checkBidQuarter_data->area_id)->where('advisor_id','!=',$userDetails->id)->where('advisor_id','!=',0)->first();
        //         if($dataLostQuarter){
        //             $lost_leads_quarter = $lost_leads_quarter + 1;
        //         }
        //     }
        // }

        // $lost_leads_year = 0;
        // $checkBidYear = AdvisorBids::where('advisor_id',$userDetails->id)->where('created_at', '>', Carbon::today()->subDays(365))->get();
        // if(count($checkBidYear)){
        //     foreach($checkBidYear as $checkBidYear_data){
        //         $dataLostYear = Advice_area::where('id',$checkBidYear_data->area_id)->where('advisor_id','!=',$userDetails->id)->where('advisor_id','!=',0)->first();
        //         if($dataLostYear){
        //             $lost_leads_year = $lost_leads_year + 1;
        //         }
        //     }
        // }

        // $lost_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
        //     ->where('status', '=', 3)
        //     ->where('advisor_status', '=', 1)
        //     ->where('created_at', '>', Carbon::today()->subDays(30))
        //     ->count();
        // $lost_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 3)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(90))
        // ->count();

        // $lost_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 3)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(365))
        // ->count();

        // $promotion = User::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 2)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(365))
        // ->count();
        $promotion = $userDetails->free_promotions;
        // if
        // if($userDetails->invite_count!=0){
        //     $promotion = $userDetails->free_promotions;
        //     // $app_settings = DB::table('app_settings')->where('key','no_of_free_leads_refer_friend')->first();
        //     // if($app_settings){
        //     //     $promotion = $userDetails->invite_count * $app_settings->value;
        //     // }
        // }

        $es_val = AdvisorBids::where('advisor_id','=',$userDetails->id)->where('status', '=', 2)->where('advisor_status', '=', 1)->get();
        $estimated = number_format(0.00,0);
        $area_arr = array();
        if(count($es_val)){
            foreach($es_val as $es_val_data){
                array_push($area_arr,$es_val_data->area_id);
            }
            if(count($area_arr)){
                $value_data = Advice_area::whereIn('id',$area_arr)->sum('size_want');
                $main_value = ($value_data/100);
                $advisorDetaultPercent = 0;
                $services = DB::table('app_settings')->where('key','estimate_calculation_percent')->first();
                if($services){
                    $advisorDetaultPercent = $services->value;
                }
                $lead_value = ($main_value)*($advisorDetaultPercent);
                $estimated = round($lead_value,0);
            }
            
        }
        // $row->value = json_encode($es_val);
        // $row->estimated_lead_value = $estimated;
        $total_due = DB::table('invoices')->where('is_paid',0)->where('advisor_id',$userDetails->id)->sum('total_due');
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => [
                'matched_card_one'=>array(
                    'last_hour'=>$matched_last_hour,
                    'today'=>$matched_last_today,
                    'yesterday'=>$matched_last_yesterday,
                    'less_than_3_days'=>$less_than_3_days,
                ),
                'matched_card_two'=>array(
                    'early_bid'=>'0',
                    '50_off'=>'0',
                    '70_off'=>'0',
                    'free'=>'0',
                ),
                'matched_card_three'=>$service,
                'accepted_card_one'=>array(
                    'live_leads'=>$live_leads_months,
                    'hired'=>$hired_leads_months,
                    'completed'=>$completed_leads_months,
                    'lost'=>$lost_leads_months,
                ),
                'accepted_card_two'=>array(
                    'live_leads'=>$live_leads_quarter,
                    'hired'=>$hired_leads_quarter,
                    'completed'=>$completed_leads_quarter,
                    'lost'=>$lost_leads_quarter,
                ),
                'accepted_card_three'=>array(
                    'live_leads'=>$live_leads_year,
                    'hired'=>$hired_leads_year,
                    'completed'=>$completed_leads_year,
                    'lost'=>$lost_leads_year,
                ),
                'performance'=>array(
                    'conversion_rate'=>number_format(round($conversion_rate),2),
                    'cost_of_leads'=>number_format(round($accepted_leads),2),
                    'estimated_revenue'=>number_format($estimated,0),
                ),
                'message_unread_count'=>$unread_count_total[0]->count_message,
                'notification_unread_count'=>0,
                'promotions'=>$promotion,
                'userDetails'=>$userData,
                'total_invoice'=>number_format(round($total_due),2)
            ]
        ], Response::HTTP_OK);
    }
    public function getAdvisorDefaultPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->first();
        if (empty($notification)) {
            AdvisorPreferencesDefault::create([
                'advisor_id' => $user->id,
            ]);
        }
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    function updateAdvisorDefaultPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        $result = ServiceType::where('status',1)->where('parent_id','!=','0')->get();
        if(count($result)){
            foreach($result as $row){
                foreach($post as $key => $value){
                    if($row->id==$key){
                        $check = DefaultPercent::where('service_id',$row->id)->where('adviser_id',$user->id)->first();
                        if(!$check){
                            $default = array(
                                'service_id'=>$row->id,
                                'adviser_id'=>$user->id,
                                'value_percent'=>$value,
                                'status'=>1,
                                'created_at'=>date('Y-m-d H:i:s'),
                            );
                            DefaultPercent::insertGetId($default);
                        }else{
                            DefaultPercent::where('id',$check->id)->update(['value_percent'=>$value,'updated_at'=>date('Y-m-d H:i:s')]);
                        }
                    }
                }
            }
        }
        // AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->update([
        //     "remortgage" => $request->remortgage,
        //     "first_buyer" => $request->first_buyer,
        //     "next_buyer" => $request->next_buyer,
        //     "but_let" => $request->but_let,
        //     "equity_release" => $request->equity_release,
        //     "overseas" => $request->overseas,
        //     "self_build" => $request->self_build,
        //     "mortgage_protection" => $request->mortgage_protection,
        //     "secured_loan" => $request->secured_loan,
        //     "bridging_loan" => $request->bridging_loan,
        //     "commercial" => $request->commercial,
        //     "something_else" => $request->something_else,
        // ]);
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification,
            'result'=>$result,
            'post'=> $post
        ], Response::HTTP_OK);
    }

    public function invoice(Request $request) {
        $users = User::where('user_role',1)->where('status',1)->get();
        // echo json_encode($users);exit;
        foreach($users as $row){
            $advisor_data = AdvisorProfile::where('advisorId', '=', $row->id)->first();
            $locations = BillingAddress::where('advisor_id', '=', $row->id)->first();
            $bill_to_address = "";
            if($locations){
                $bill_to_address = $locations->contact_name. '('.$locations->invoice_name.')';
                $bill_to_address .= "\n".$locations->address_one;
                $bill_to_address .= "\n".$locations->address_two;
                $bill_to_address .= "\n".$locations->contact_number;
                $bill_to_address .= "\n".$locations->city;
                $bill_to_address .= "\n".$locations->post_code;
            }
            $month = date('m');
            $year = date('Y');
            if(isset($request->selected_date) && $request->selected_date !="") {
                $month = date('m',strtotime($request->selected_date));
                $year = date('Y',strtotime($request->selected_date));
            }
            $invoice_detais = Invoice::where('advisor_id','=',$row->id)->where('month','=',$month)->where('year','=',$year)->where('is_paid','=','0')->first();
            $total_this_month_cost_of_leads_subtotal = AdvisorBids::where('advisor_id','=',$row->id)
            // ->where('status','>=',1)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"),$month)
            // ->where('created_at', date('m',strtotime($month)))
            ->where('is_paid_invoice','=',0)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%Y'))"),$year)
            // ->where('created_at',date('Y',strtotime($year)))
            ->sum('cost_leads');
            $cost_leads_this_month = AdvisorBids::select('advisor_bids.cost_leads','advisor_bids.accepted_date','advisor_bids.cost_discounted','advisor_bids.free_introduction','advice_areas.service_type','advice_areas.size_want_currency','advice_areas.size_want')->where('advisor_bids.advisor_id','=',$row->id)
            ->leftJoin('advice_areas','advisor_bids.area_id','advice_areas.id')
            ->where('advisor_bids.is_paid_invoice','=',0)
            ->where(DB::raw("(DATE_FORMAT(advisor_bids.created_at,'%m'))"),$month)
            ->where(DB::raw("(DATE_FORMAT(advisor_bids.created_at,'%Y'))"),$year)
            ->get();
            $cost_of_leads_of_the_monthArr = array();
            $discount_of_the_monthArr = array();
            $cost_of_lead = 0;
            foreach($cost_leads_this_month as $key=> $item) {
                $cost_of_leads_of_the_monthArr[$key]['message']='Amount for bid on '.$item->service_type.' of '.$item->size_want_currency.$item->size_want.' at '.Date('d-M-Y',strtotime($item->accepted_date)).'';
                $cost_of_leads_of_the_monthArr[$key]['cost']=($item->cost_leads!="")?$item->cost_leads:"0";
                $cost_of_lead = ($item->cost_leads!="")?$item->cost_leads:"0";
            }
            $total_this_month_discount_subtotal = AdvisorBids::where('advisor_id','=',$row->id)
            // ->where('status','!=',0)
            // ->whereMonth('created_at', $month)
            // ->whereYear('created_at', $year)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"),$month)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%Y'))"),$year)
            ->where('is_discounted','=',1)
            ->where('free_introduction',0)
            ->where('is_paid_invoice','=',0)
            ->sum('cost_discounted');
            $total_this_month_free_intro = AdvisorBids::where('advisor_id','=',$row->id)
            // ->where('status','>=',1)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"),$month)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%Y'))"),$year)
            // ->whereMonth('created_at', $month)
            // ->whereYear('created_at', $year)
            ->where('is_paid_invoice',0)
            ->where('free_introduction',1)
            ->sum('cost_discounted');
            $subtotal_of_discount_and_credit = $total_this_month_discount_subtotal+$total_this_month_free_intro;
            $total_dues = $total_this_month_cost_of_leads_subtotal-$subtotal_of_discount_and_credit;
            $total_amount = 0;
            $taxableAmount =  (1 + (20/100));
            $new_taxable_amount = $total_this_month_cost_of_leads_subtotal;
            $withoutTaxamount_new_taxable =  $new_taxable_amount / $taxableAmount;
            $tax_on_this_invoice_subtotal = $new_taxable_amount - $withoutTaxamount_new_taxable;

            $total_amount_final_subtotal = $new_taxable_amount - $tax_on_this_invoice_subtotal;
            // $vat_on_this_invoice_subtotal = $tax_on_this_invoice_subtotal;
            $vat_on_this_invoice_subtotal = 0;

            // $tax_on_this_invoice = (5/100)*$total_dues;
            // $vat_on_this_invoice = (20/100)*$total_dues;

            // $taxableAmount =  (1 + ($data['result']->tax_percent/100));
            // $withoutTaxamount =  $data['result']->amount / $taxableAmount;
            // $finalTaxAmount = $data['result']->amount - $withoutTaxamount;

            // $taxableAmount =  (1 + (20/100));
            $withoutTaxamount =  $total_dues / $taxableAmount;
            // $tax_on_this_invoice = $total_dues - $withoutTaxamount;
            $tax_on_this_invoice = 0;

            $total_amount_final = $total_dues - $tax_on_this_invoice;
            $vat_on_this_invoice = $tax_on_this_invoice;

            // $total_amount_final = $total_dues+$tax_on_this_invoice+$vat_on_this_invoice;
            $total_amount_final = number_format((float)($total_amount_final),2,'.','');
            $newFees = AdvisorBids::select('advisor_bids.*','users.name','advice_areas.property','advice_areas.service_type')->where('advisor_bids.advisor_id','=',$row->id)
            ->leftJoin('advice_areas','advisor_bids.area_id','advice_areas.id')
            ->leftJoin('users','advice_areas.user_id','users.id')
            // ->where('advisor_bids.status','>=',1)
            // ->whereMonth('advisor_bids.created_at', $month)
            ->where(DB::raw("(DATE_FORMAT(advisor_bids.created_at,'%m'))"),$month)
            ->where(DB::raw("(DATE_FORMAT(advisor_bids.created_at,'%Y'))"),$year)
            ->where('advisor_bids.is_paid_invoice','=',0)
            ->whereYear('advisor_bids.created_at', $year)
            ->get();

            $bidsId = AdvisorBids::where('advisor_id','=',$row->id)
            // ->where('status','>=',0)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%m'))"),$month)
            ->where(DB::raw("(DATE_FORMAT(created_at,'%Y'))"),$year)
            // ->whereMonth('created_at', $month)
            // ->whereYear('created_at', $year)
            ->where('is_paid_invoice','=',0)
            ->get();
            $bidArr = array();
            foreach($bidsId as $item) {
                array_push($bidArr,$item->id);
                // $bidArr[] = $item->id;
            }
            $newFeesArr = array();
            if(!empty($newFees)){
                foreach($newFees as $key=>$value) {
                    $show_status = "Live Leads"; 
                    $bidDetailsStatus = AdvisorBids::where('area_id',$value->area_id)->where('advisor_id','=',$row->id)->first();
                    if(!empty($bidDetailsStatus)) {
                        if($bidDetailsStatus->status==0 && $bidDetailsStatus->advisor_status==1) {
                            $show_status = "Not Proceeding"; 
                        }else if($bidDetailsStatus->status>0 && $bidDetailsStatus->advisor_status==1) {
                            $show_status = "Hired"; 
                        }else if($bidDetailsStatus->status==3 && $bidDetailsStatus->advisor_status==1) {
                            $show_status = "Lost"; 
                        }else if($bidDetailsStatus->status==2 && $bidDetailsStatus->advisor_status==1) {
                            $show_status = "Closed"; 
                        }else{
                            $show_status = "Live Leads";     
                        }
                    }else{
                        $show_status = "Live Leads";
                    }
                    $newFeesArr[$key]['date']=$value->accepted_date;
                    $newFeesArr[$key]['customer']=$value->name;
                    $newFeesArr[$key]['mortgage']=$value->property;
                    $newFeesArr[$key]['status']=$show_status;
                    $newFeesArr[$key]['free_type']=$value->service_type;
                    $newFeesArr[$key]['amount']=$value->cost_leads;
                }
            }
            $discountAndCreditArr = array();
            if(!empty($newFees))
            {
                foreach($newFees as $key=>$value) {
                    $show_status = "Live Leads"; 
                    $bidDetailsStatus = AdvisorBids::where('area_id',$value->area_id)->where('advisor_id','=',$row->id)->first();
                    if(!empty($bidDetailsStatus)) {
                    if($bidDetailsStatus->status==0 && $bidDetailsStatus->advisor_status==1) {
                        $show_status = "Not Proceeding"; 
                    }else if($bidDetailsStatus->status>0 && $bidDetailsStatus->advisor_status==1) {
                        $show_status = "Hired"; 
                    }else if($bidDetailsStatus->status==3 && $bidDetailsStatus->advisor_status==1) {
                        $show_status = "Lost"; 
                    }else if($bidDetailsStatus->status==2 && $bidDetailsStatus->advisor_status==1) {
                        $show_status = "Closed"; 
                    }else{
                        $show_status = "Live Leads";     
                    }
                    }else{
                    $show_status = "Live Leads"; 
                    }
                    $discountAndCreditArr[$key]['date']=$value->accepted_date;
                    $discountAndCreditArr[$key]['customer']=$value->name;
                    $discountAndCreditArr[$key]['mortgage']=$value->property;
                    $newFeesArr[$key]['status']=$show_status;
                    $discountAndCreditArr[$key]['free_type']=$value->service_type;
                    $discountAndCreditArr[$key]['amount']=$value->cost_discounted;
                }
                
            }
            $invoice_number = $this->quickRandom(10);
            $lastPayment = Invoice::where('advisor_id','=',$row->id)->orderBy('id','DESC')->limit(1,1)->first();
            $last_payment_invoice="";
            $last_invoice_amount="";
            $last_paid_date="";
            $last_is_paid="";
            $last_payment_date="";
            $last_transaction_id = "";
            if(!empty($lastPayment)) {
                $last_payment_invoice = $lastPayment['invoice_number'];
                $last_payment_date = $lastPayment['updated_at'];
                $last_transaction_id = $lastPayment['txt_id'];
                $amountData = json_decode($lastPayment['invoice_data'],true);
                $last_invoice_amount=$amountData["total_current_invoice_amount"];
                $last_paid_date=$lastPayment['updated_at'];
                $last_is_paid=$lastPayment['is_paid'];
            } 
            if(!$invoice_detais) {
                if(!empty($bidArr)) {
                    $invoice_IID= Invoice::create([
                        'cost_of_lead'=>$total_this_month_cost_of_leads_subtotal,
                        'subtotal'=>$total_this_month_cost_of_leads_subtotal,
                        'discount'=>$total_this_month_discount_subtotal,
                        'free_introduction'=>$total_this_month_free_intro,
                        'discount_subtotal'=>$subtotal_of_discount_and_credit,
                        'total_taxable_amount'=>$total_amount_final,
                        'vat'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                        'total_current_invoice'=>number_format((float)($total_dues),2,'.',''),
                        'total_due'=>$total_dues,
                        'invoice_number'=>$invoice_number,
                        'new_taxable_amount'=>number_format((float)($new_taxable_amount),2,'.',''),
                        'vat_amount'=>number_format((float)($vat_on_this_invoice_subtotal),2,'.',''),
                        'sub_total_without_tax'=>number_format((float)($total_amount_final_subtotal),2,'.',''),
                        'month'=>$month,
                        'year'=>$year,
                        'is_paid'=>0,
                        'advisor_id'=>$row->id,
                        'invoice_data'=>json_encode(array(
                            'new_fess'=>array('cost_of_leads_of_the_month'=>$cost_of_leads_of_the_monthArr,
                            'cost_of_leads_sub_total'=>$total_this_month_cost_of_leads_subtotal),
                            'discounts_and_credits'=>array('discount_subtotal'=>$total_this_month_discount_subtotal,
                            'free_introduction_subtotal'=>$total_this_month_free_intro,
                            'subtotal'=>$subtotal_of_discount_and_credit),
                            'total_dues'=>$total_dues,
                            'total_current_invoice_amount'=>number_format((float)($total_dues),2,'.',''),
                            'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                            'total_taxable_amount'=>$total_amount_final,
                            'new_fees_data'=>$newFeesArr,
                            'new_fees_total'=>$total_this_month_cost_of_leads_subtotal,
                            'discount_credit_data'=>$discountAndCreditArr,
                            'discount_credit_total'=>$total_this_month_discount_subtotal,
                            'new_taxable_amount'=>number_format((float)($new_taxable_amount),2,'.',''),
                            'vat_amount'=>number_format((float)($vat_on_this_invoice_subtotal),2,'.',''),
                            'sub_total_without_tax'=>number_format((float)($total_amount_final_subtotal),2,'.',''),
                            'invoice_number'=>$invoice_number,
                            'seller_address'=>'MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678',
                            'bill_to_address'=>$bill_to_address,
                            'bid_ids'=>implode(",",$bidArr)
                        )),
                        
                    ]);
                    $invoice_detais = Invoice::where('id','=',$invoice_IID->id)->first();
                    $total_data = json_decode($invoice_detais->invoice_data,true);
                    $total_data['invoice_data'] = $invoice_detais;
                    $this->saveNotification(array(
                        'type'=>'5', // 1:
                        'message'=>'Your invoice generated', // 1:
                        'read_unread'=>'0', // 1:
                        'user_id'=>1,// 1:
                        'advisor_id'=>$row->id, // 1:
                        'area_id'=>0,// 1:
                        'notification_to'=>0
                    ));
                    // $total_data['invoice_id']=$invoice_detais->id;
                    // $total_data['last_payment_invoice']=$last_payment_invoice;
                    // $total_data['last_invoice_amount']=$last_invoice_amount;
                    // $total_data['last_payment_date']=$last_payment_date;
                    // $total_data['last_transaction_id']=$last_transaction_id;
                    // $total_data['invoice_number']=$invoice_number;
                    // return response()->json([
                    //     'status' => true,
                    //     'message' => 'success',
                    //     'data' => $total_data
                    // ], Response::HTTP_OK);
                    // return "Invoice generated successfully";
                }else{
                    // echo "333";echo "<br>";

                    $lastPayment = Invoice::where('advisor_id','=',$row->id)->orderBy('id','DESC')->limit(1,1)->first();
                    $last_payment_invoice="";
                    $last_payment_date="";
                    $last_transaction_id = "";
                    $last_invoice_amount="";
                    $last_paid_date="";
                    $last_is_paid="";
                    $seller_address='MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678';
                    $bill_to_address=$bill_to_address;
                    if(!empty($lastPayment)) {
                        $last_payment_invoice = $lastPayment['invoice_number'];
                        $last_payment_date = $lastPayment['updated_at'];
                        $last_transaction_id = $lastPayment['txt_id'];
                        $amountData = json_decode($lastPayment['invoice_data'],true);
                        $last_invoice_amount=$amountData["total_current_invoice_amount"];
                        $last_paid_date=$lastPayment['updated_at'];
                        $last_is_paid=$lastPayment['is_paid'];
                    } 
                    // return response()->json([
                    //     'status' => true,
                    //     'message' => 'No invoice pending',
                    //     'data' => [
                    //         'last_payment_invoice'=>$last_payment_invoice,
                    //         'last_payment_date'=>$last_payment_date,
                    //         'last_transaction_id'=>$last_transaction_id,
                    //         'seller_address'=>$seller_address,
                    //         'bill_to_address'=>$bill_to_address,
                    //         'last_invoice_amount'=>$last_invoice_amount,
                    //         'last_paid_date'=>$last_paid_date,
                    //         'last_is_paid'=>$last_is_paid
                    //     ]
                    // ], Response::HTTP_OK);
                }
            }else{
                // echo "0000";echo "<br>";

                if(isset($bill_to_address) && $bill_to_address!=''){
                    $bill_to_address = $bill_to_address;
                }else{
                    $bill_to_address = '';
                }
                Invoice::where('id','=',$invoice_detais->id)->update([
                    'cost_of_lead'=>$total_this_month_cost_of_leads_subtotal,
                    'subtotal'=>$total_this_month_cost_of_leads_subtotal,
                    'discount'=>$total_this_month_discount_subtotal,
                    'free_introduction'=>$total_this_month_free_intro,
                    'discount_subtotal'=>$subtotal_of_discount_and_credit,
                    'total_current_invoice'=>number_format((float)($total_dues),2,'.',''),
                    'vat'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                    'total_taxable_amount'=>$total_amount_final,
                    'new_taxable_amount'=>number_format((float)($new_taxable_amount),2,'.',''),
                    'vat_amount'=>number_format((float)($vat_on_this_invoice_subtotal),2,'.',''),
                    'sub_total_without_tax'=>number_format((float)($total_amount_final_subtotal),2,'.',''),
                    'total_due'=>$total_dues,
                    'month'=>$month,
                    'year'=>$year,
                    'is_paid'=>0,
                    'advisor_id'=>$row->id,
                    'invoice_data'=>json_encode(array(
                    'new_fess'=>array('cost_of_leads_of_the_month'=>$cost_of_leads_of_the_monthArr,
                    'cost_of_leads_sub_total'=>$total_this_month_cost_of_leads_subtotal),
                    'discounts_and_credits'=>array('discount_subtotal'=>$total_this_month_discount_subtotal,
                    'free_introduction_subtotal'=>$total_this_month_free_intro,
                    'subtotal'=>$subtotal_of_discount_and_credit),
                    'total_dues'=>$total_dues,
                    'total_current_invoice_amount'=>number_format((float)($total_dues),2,'.',''),
                    'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                    'total_taxable_amount'=>$total_amount_final,
                    'new_fees_data'=>$newFeesArr,
                    'new_fees_total'=>$total_this_month_cost_of_leads_subtotal,
                    'discount_credit_data'=>$discountAndCreditArr,
                    'discount_credit_total'=>$total_this_month_discount_subtotal,
                    'new_taxable_amount'=>number_format((float)($new_taxable_amount),2,'.',''),
                    'vat_amount'=>number_format((float)($vat_on_this_invoice_subtotal),2,'.',''),
                    'sub_total_without_tax'=>number_format((float)($total_amount_final_subtotal),2,'.',''),

                    'seller_address'=>'MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678',
                    'bill_to_address'=>$bill_to_address,
                    'bid_ids'=>implode(",",$bidArr)
                    )),
                    
                ]);
                $this->saveNotification(array(
                    'type'=>'5', // 1:
                    'message'=>'Your invoice generated', // 1:
                    'read_unread'=>'0', // 1:
                    'user_id'=>1,// 1:
                    'advisor_id'=>$row->id, // 1:
                    'area_id'=>0,// 1:
                    'notification_to'=>0
                ));
                $invoice_detais = Invoice::where('advisor_id','=',$row->id)->where('month','=',$month)->where('year','=',$year)->first();
                $total_data = json_decode($invoice_detais->invoice_data,true);
                $total_data['invoice_data'] = $invoice_detais;
                $total_data['invoice_data']->discount_subtotal = 0.00;
                $total_data['invoice_data']->new_fees_arr = array();
                $total_data['invoice_data']->discount_credit_arr = array();
                if($total_data['invoice_data']){
                    $total_data['invoice_data']->discount_subtotal = $total_data['invoice_data']->discount + $total_data['invoice_data']->free_introduction;
                    $total_data['invoice_data']->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','<',$total_data['invoice_data']->month)->where('advisor_id',$total_data['invoice_data']->advisor_id)->sum('total_due');
                    $total_data['invoice_data']->paid_prevoius_invoice = DB::table('invoices')->where('is_paid',1)->where('month','<',$total_data['invoice_data']->month)->where('advisor_id',$total_data['invoice_data']->advisor_id)->sum('total_due');
                    $total_data['new_fees_arr'] = AdvisorBids::where('advisor_id',$total_data['invoice_data']->advisor_id)->where('is_discounted',0)->with('area')->with('adviser')->get();
                    if(count($total_data['new_fees_arr'])){
                        foreach($total_data['new_fees_arr'] as $new_bid){
                            $new_bid->date = date("d-M-Y H:i",strtotime($new_bid->created_at));
                            if($new_bid->status==0){
                                $new_bid->status_type = "Live Lead";
                            }else if($new_bid->status==1){
                                $new_bid->status_type = "Hired";
                            }else if($new_bid->status==2){
                                $new_bid->status_type = "Completed";
                            }else if($new_bid->status==3){
                                $new_bid->status_type = "Lost";
                            }else if($new_bid->advisor_status==2){
                                $new_bid->status_type = "Not Proceeding";
                            }
                        }
                    }
                    $total_data['discount_credit_arr'] = AdvisorBids::where('advisor_id',$total_data['invoice_data']->advisor_id)->where('is_discounted','!=',0)->with('area')->with('adviser')->get();
                    if(count($total_data['new_fees_arr'])){
                        foreach($total_data['discount_credit_arr'] as $discount_bid){
                            $discount_bid->date = date("d-M-Y H:i",strtotime($discount_bid->created_at));
                            if($discount_bid->status==0){
                                $discount_bid->status_type = "Live Lead";
                            }else if($discount_bid->status==1){
                                $discount_bid->status_type = "Hired";
                            }else if($discount_bid->status==2){
                                $discount_bid->status_type = "Completed";
                            }else if($discount_bid->status==3){
                                $discount_bid->status_type = "Lost";
                            }else if($discount_bid->advisor_status==2){
                                $discount_bid->status_type = "Not Proceeding";
                            }
                            
                        }
                    }
                }
                $total_data['month_data'] = Invoice::where('advisor_id','=',$row->id)->get(); 
                foreach($total_data['month_data'] as $month_data){
                    $month_data->show_days = \Helpers::getMonth($month_data->month)." ".$month_data->year;
                }
                $total_data['invoice_id']=$invoice_detais->id;
                $total_data['invoice_number']=$invoice_detais->invoice_number;
                $total_data['last_payment_invoice']=$last_payment_invoice;
                $total_data['last_payment_date']=$last_payment_date;
                $total_data['last_transaction_id']=$last_transaction_id;
                $total_data['seller_address']='MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678';
                $total_data['bill_to_address']=$bill_to_address;
                $total_data['last_invoice_amount']=$last_invoice_amount;
                $total_data['last_paid_date']=$last_paid_date;
                $total_data['last_is_paid']=$last_is_paid;
                
                // return response()->json([
                //     'status' => true,
                //     'message' => 'success',
                //     'data' => $total_data
                // ], Response::HTTP_OK);
            }
        }
        return "Invoice generated successfully";
        exit;
    }

    public function saveNotification($data) {
        $notification = Notifications::create($data);
        if($notification) {
            return true;
        }else {
            return false;
        }
    }
// pay invoice
   public function payInvoice(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $amount = $request->amount;
        $invoice_tbl_id = $request->id;
        $invoice_number = $request->invoice_number;
        
   }
   function getEncryptedId($id)
    {
        // Store the cipher method 
        $ciphering = "AES-256-CTR";
        // Use OpenSSl Encryption method 
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;
        // Non-NULL Initialization Vector for encryption 
        $encryption_iv = 'a9qDc#G@9$bOpPnR';
        // Store the encryption key 
        $encryption_key = "&*(#Pp@IND";
        // Use openssl_encrypt() function to encrypt the data 
        return base64_encode(openssl_encrypt($id, $ciphering, $encryption_key, $options, $encryption_iv));
    }

    function getDecryptedId($id)
    {
        $id = base64_decode($id);
        // Store the cipher method 
        $ciphering = "AES-256-CTR";
        // Use OpenSSl Encryption method 
        $iv_length = openssl_cipher_iv_length($ciphering);
        $options = 0;
        // Non-NULL Initialization Vector for encryption 
        $encryption_iv = 'a9qDc#G@9$bOpPnR';
        // Store the encryption key 
        $encryption_key = "&*(#Pp@IND";
        // Use openssl_decrypt() function to decrypt the data 
        return openssl_decrypt($id, $ciphering, $encryption_key, $options, $encryption_iv);
    }

    //Today match leads summary cron job
    function matchLeadsSummaryCron(){
        $users = User::where('status',1)->get();
        foreach($users as $user){
            $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id','=',$user->id)->first();
            $requestTime = [];
            $ltv_max ="";
            $lti_max ="";
            if($userPreferenceCustomer){
                $ltv_max = $userPreferenceCustomer->ltv_max;
                $lti_max = $userPreferenceCustomer->lti_max;
                if(!empty($userPreferenceCustomer)) {
                    if($userPreferenceCustomer->asap == 1) {
                        $requestTime[] = "as soon as possible";
                    }
                    if($userPreferenceCustomer->next_3_month == 1) {
                        $requestTime[] = "in the next 3 months";
                    }
                    if($userPreferenceCustomer->more_3_month == 1) {
                        $requestTime[] = "in more than 3 months";
                    }
                }
            }
            
            
            // TODO: Ltv max and Lti Max need to check for filter
            $userPreferenceProduct = AdviserProductPreferences::where('adviser_id','=',$user->id)->get();
            $service_type = array();
            if(!empty($userPreferenceProduct)) {
                foreach($userPreferenceProduct as $userPreferenceProduct_data){
                    array_push($service_type,$userPreferenceProduct_data->service_id);
                }
            }
            
            $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address')->with('service')
                ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
                ->leftJoin('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
                ->where(function($query) use ($userPreferenceCustomer){
                    if(!empty($userPreferenceCustomer)) {
                        if($userPreferenceCustomer->self_employed == 1){
                            $query->orWhere('advice_areas.self_employed','=',$userPreferenceCustomer->self_employed);
                        }
                        if($userPreferenceCustomer->non_uk_citizen == 1){
                            $query->orWhere('advice_areas.non_uk_citizen','=',$userPreferenceCustomer->non_uk_citizen);
                        }
                        if($userPreferenceCustomer->adverse_credit == 1){
                            $query->orWhere('advice_areas.adverse_credit','=',$userPreferenceCustomer->adverse_credit);
                        }
                        if($userPreferenceCustomer->fees_preference == 1){
                            $query->orWhere('advice_areas.fees_preference','=',$userPreferenceCustomer->fees_preference);
                        }
                    }
            })->where(function($query) use ($requestTime){
                    if(!empty($requestTime)) {
                        $query->where(function($q) use ($requestTime) {
                            foreach($requestTime as $rtime){
                                $q->orWhere('advice_areas.request_time',$rtime);
                            }
                        });
                    }
            })
            ->where(function($query) use ($service_type){
                    if(!empty($service_type)) {
                        $query->where(function($q) use ($service_type) {
                            foreach($service_type as $sitem){
                                $q->orWhere('advice_areas.service_type_id',$sitem);
                            }
                        });
                    }
                
            })
            ->where(function($query) use ($ltv_max){
                if($ltv_max != "") {
                
                    $query->where('advice_areas.ltv_max','<=',chop($ltv_max,"%"));
                    $query->where('advice_areas.ltv_max','>',0);
                }
            })->where(function($query) use ($lti_max){
                if($lti_max != "") {
                    $query->where('advice_areas.lti_max','<=',chop($lti_max,"x"));
                    $query->where('advice_areas.lti_max','>',0);
                }
            })->whereNotIn('advice_areas.id',function($query) use ($user){
                $query->select('area_id')->from('advisor_bids')->where('advisor_id','=',$user->id);
            })->where('advice_areas.start_date',date('Y-m-d H:i:s'))->orderBy('advice_areas.id','DESC')->with('total_bid_count')->groupBy('advice_areas.'.'id')
            ->groupBy('advice_areas.'.'user_id')
            ->groupBy('advice_areas.'.'service_type')
            ->groupBy('advice_areas.'.'request_time')
            ->groupBy('advice_areas.'.'property')
            ->groupBy('advice_areas.'.'property_want')
            ->groupBy('advice_areas.'.'size_want')
            ->groupBy('advice_areas.'.'combined_income')
            ->groupBy('advice_areas.'.'description')
            ->groupBy('advice_areas.'.'occupation')
            ->groupBy('advice_areas.'.'contact_preference')
            ->groupBy('advice_areas.'.'advisor_preference')
            ->groupBy('advice_areas.'.'fees_preference')
            ->groupBy('advice_areas.'.'self_employed')
            ->groupBy('advice_areas.'.'non_uk_citizen')
            ->groupBy('advice_areas.'.'adverse_credit')
            ->groupBy('advice_areas.'.'contact_preference_face_to_face')
            ->groupBy('advice_areas.'.'contact_preference_online')
            ->groupBy('advice_areas.'.'contact_preference_telephone')
            ->groupBy('advice_areas.'.'contact_preference_evening_weekend')
            ->groupBy('advice_areas.'.'advisor_preference_local')
            ->groupBy('advice_areas.'.'advisor_preference_gender')
            ->groupBy('advice_areas.'.'status')
            ->groupBy('advice_areas.'.'combined_income_currency')
            ->groupBy('advice_areas.'.'property_currency')
            ->groupBy('advice_areas.'.'size_want_currency')
            ->groupBy('advice_areas.'.'advisor_id')
            ->groupBy('advice_areas.'.'close_type')
            ->groupBy('advice_areas.'.'need_reminder')
            ->groupBy('advice_areas.'.'initial_term')
            ->groupBy('advice_areas.'.'start_date')
            ->groupBy('advice_areas.'.'created_at')
            ->groupBy('advice_areas.'.'updated_at')
            ->groupBy('users.'.'name')
            ->groupBy('users.'.'email')
            ->groupBy('users.'.'address')
            ->groupBy('advice_areas.'.'ltv_max')
            ->groupBy('advice_areas.'.'lti_max')
            ->groupBy('advice_areas.'.'advisor_preference_language')->orderBy('advice_areas.id','DESC')->count();
            $bidCountArr = array();
            if($advice_area>0){
                $this->saveNotification(array(
                    'type'=>'2', // 1:
                    'message'=>$advice_area.' Leads matched today', // 1:
                    'read_unread'=>'0', // 1:
                    'user_id'=>1,// 1:
                    'advisor_id'=>$user->id, // 1:
                    'area_id'=>0,// 1:
                    'notification_to'=>0
                ));
            }
        }
        return "Summary of today's matching leads";
        exit;
    }

    public function checkMail(Request $request) {
        $newArrDec = array(
            'name'=>"Test",
            'email'=>'rahul@technofox.com',
            'message_text' => 'You have lost this bid other advisor is selected for this bid.'
        );
        $c = \Helpers::sendEmail('emails.information',$newArrDec ,'akshitamishra08@gmail.com','Test','MortgageBox Test Mail','','');
    }
}
