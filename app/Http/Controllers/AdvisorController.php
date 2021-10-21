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
                }else{
                    $postData['FCA_verified'] = null;
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
        $advisor_data = AdvisorProfile::select('web_address', 'facebook', 'twitter', 'linkedin_link', 'updated_at')->where('advisorId', '=', $id->id)->first();
        if ($advisor_data) {
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
        $advisorDetails = AdvisorProfile::select('web_address', 'facebook', 'twitter', 'linkedin_link', 'updated_at')->where('advisorId', '=', $id->id)->update(
            [
                'web_address' => $request->web_address,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'linkedin_link' => $request->linkedin_link,
            ]
        );
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'Links updated successfully',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }

    public function getAdvisorProfileByAdvisorId($id)
    {
        JWTAuth::parseToken()->authenticate();
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id)->first();
        if($advisor_data){
            $company = companies::where('id',$advisor_data->company_id)->first();
            if($company){
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

        $rating =  ReviewRatings::select('review_ratings.*', 'users.name', 'users.email', 'users.address')
            ->leftJoin('users', 'review_ratings.user_id', '=', 'users.id')
            ->leftJoin('review_spam', 'review_ratings.id', '=', 'review_spam.review_id')
            ->where('review_ratings.advisor_id', '=', $id)
            ->where('review_ratings.status', '!=', 2)
            ->with('area')
            // ->where('review_spam.spam_status', '!=', 0)
            ->get();
        if(count($rating)){
            foreach($rating as $rating_data){
                $spam = ReviewSpam::where('review_id',$rating_data->id)->first();
                $rating_data->reason = "";
                $rating_data->spam_status = 2;
                $rating_data->is_spam = 0;
                if($spam){
                    $rating_data->reason = $spam->reason;
                    $rating_data->spam_status = $spam->spam_status;
                    $rating_data->is_spam = 1;
                }else{
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
        
        $averageRating = ReviewRatings::where('advisor_id', '=', $id)->where('review_ratings.status', '!=', 2)->avg('rating');

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
                'total' => count($rating),

            ], Response::HTTP_OK);
        } else {
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
        $post_code = "";
        $advice_area = "";
        $mortgage_value = "";
        $how_soon = "";
        $mortgage_value = "";
        $local_advisor = "";
        $gender = "";
        $language = "";
        if (isset($request->post_code)) {
            $post_code = $request->post_code;
        }
        if (isset($request->advice_area)) {
            $advice_area = $request->advice_area;
        }
        if (isset($request->how_soon)) {
            $how_soon = $request->how_soon;
        }
        if (isset($request->mortgage_value)) {
            $mortgage_value = $request->mortgage_value;
        }
        if (isset($request->local_advisor)) {
            $local_advisor = $request->local_advisor;
        }
        if (isset($request->gender)) {
            $gender = $request->genders;
        }
        if (isset($request->language)) {
            $language = $request->language;
        }
        
        $advisor_data = array();

        $sql = "SELECT  ap.* from advisor_profiles as ap left join advisor_preferences_customers as apc  on ap.advisorId = apc.advisor_id";
        $sql .= " left join advisor_preferences_products as app on ap.advisorId = app.advisor_id";
        // $sql .= " left join review_ratings as rr on ap.advisorId = rr.user_id";
        $sql .= " where ";
       
        if (!empty($request->how_soon)) {
            foreach ($request->how_soon as $key => $column_name) {
                if ($key === array_key_first($request->how_soon)) {
                    $sql .= " apc." . $column_name . " = 1 ";
                } else {
                    $sql .= " OR apc." . $column_name . " = 1 ";
                }
            }
        }
        if (!empty($request->advice_area)) {
            foreach ($request->advice_area as $key => $column_name) {
                if ($key === array_key_first($request->advice_area)) {
                    if (!empty($request->how_soon)) {
                        $sql .= "OR app." . $column_name . " = 1 ";
                    } else {
                        $sql .= " app." . $column_name . " = 1 ";
                    }
                } else {
                    $sql .= " OR app." . $column_name . " = 1 ";
                }
            }
        }

        // if (!empty($request->mortgage_value)) {
        //     foreach ($request->mortgage_value as $key => $column_name) {
        //         if ($key === array_key_first($request->mortgage_value)) {
        //             if (!empty($request->how_soon) || !empty($request->advice_area) ) {

        //                 $sql .= "OR app." . $column_name . " = 1 ";
        //             } else {
        //                 $sql .= " app." . $column_name . " = 1 ";
        //             }
        //         } else {
        //             $sql .= " OR app." . $column_name . " = 1 ";
        //         }
        //     }
        // }
        if (isset($request->gender)) {
            if (!empty($request->how_soon) || !empty($request->advice_area)) {
                $sql .= " AND ap.gender  = '" . $request->gender . "'";
            } else {
                $sql .= " ap.gender  =  '" . $request->gender . "'";
            }
        }
        if (isset($request->language)) {
            if (!empty($request->how_soon) || !empty($request->advice_area)) {
                $sql .= " AND ap.language  =  '" . $request->language . "'";
            } else {
                $sql .= " ap.language  =  '" . $request->language . "'";
            }
        }
        if (isset($request->local_advisor) && $request->local_advisor !=0 ) {
            if (!empty($request->how_soon) || !empty($request->advice_area)) {
                $sql .= " AND apc.non_uk_citizen  =  '" . $request->local_advisor . "'";
            } else {
                $sql .= " apc.non_uk_citizen  = '" . $request->local_advisor . "'";
            }
        }
        if ($post_code != "") {
            if (!empty($request->how_soon) || !empty($request->advice_area)|| isset($request->language) || isset($request->gender) ||  $request->local_advisor !=0) {
             $sql .= "  AND ap.postcode  =  '" . $post_code . "'";
            }else{
                $sql .= "   ap.postcode  =  '" . $post_code . "'";  
            }
        }
        
        $advisor_data = DB::select($sql);

        $getCustomerPostalDetails = PostalCodes::where('Postcode', '=', $post_code)->first();
        $dataArray = array();
        if ($advisor_data) {
            foreach ($advisor_data as $key => $item) {
                $rating =  ReviewRatings::select('review_ratings.*')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->where('review_ratings.status', '=', 0)
                    ->get();

                // if ($item->postcode != "") {
                    // $getAdvisorPostalDetails = PostalCodes::where('Postcode', '=', $item->postcode)->first();
                    // if (!empty($getAdvisorPostalDetails)) {
                        // $customerEasting = $getCustomerPostalDetails->Easting;
                        // $advisorEasting = $getAdvisorPostalDetails->Easting;
                        // $customerNorthing = $getCustomerPostalDetails->Northing;
                        // $advisorNorthing = $getAdvisorPostalDetails->Northing;
                        // $distance = $this->getDistanceRange($customerEasting, $advisorEasting, $customerNorthing, $advisorNorthing);
                        // $item->distance = $distance;
                        // if ($item->serve_range > $distance) {

                        //     unset($advisor_data[$key]);
                        // } else {

                            $rating =  ReviewRatings::select('review_ratings.*')
                                ->where('review_ratings.advisor_id', '=', $item->advisorId)
                                ->where('review_ratings.status', '=', 0)
                                ->get();

                            $averageRating = ReviewRatings::where('review_ratings.advisor_id', '=', $item->advisorId)->where('review_ratings.status', '=', 0)->avg('rating');


                            $ratingGreat =  ReviewRatings::where('review_ratings.rating', '<=', '4')
                                ->where('review_ratings.rating', '>', '3')
                                ->where('review_ratings.advisor_id', '=', $item->advisorId)
                                ->count();

                            $ratingAverage =  ReviewRatings::where('review_ratings.rating', '<=', '3')
                                ->where('review_ratings.rating', '>', '2')
                                ->where('review_ratings.advisor_id', '=', $item->advisorId)
                                ->count();

                            $ratingPoor =  ReviewRatings::where('review_ratings.rating', '<=', '2')
                                ->where('review_ratings.rating', '>', '1')
                                ->where('review_ratings.advisor_id', '=', $item->advisorId)
                                ->count();
                $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
                    ->where('review_ratings.rating', '>', '0')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->count();

                            $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
                                ->where('review_ratings.rating', '>', '0')
                                ->where('review_ratings.advisor_id', '=', $item->advisorId)
                                ->count();

                            $item->avarageRating = number_format((float)$averageRating, 2, '.', '');
                            $item->rating = [
                                'total' => count($rating),
                            ];
                            $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
                            ->Where('advisor_status','=',1)
                            ->Where('advisor_id','=',$item->advisorId)
                            ->count();
                            $item->used_by  = $usedByMortage;
                            $dataArray[] = $item;
                        // }
                    // } else {
                    //     unset($advisor_data[$key]);
                    // }
                // } else {
                //     unset($advisor_data[$key]);
                // }
           }

            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $dataArray
                // 'current_page' => $advisor_data->currentPage(),
                // 'first_page_url' => $advisor_data->url(1),
                // 'last_page_url' => $advisor_data->url($advisor_data->lastPage()),
                // 'per_page' => $advisor_data->perPage(),
                // 'next_page_url' => $advisor_data->nextPageUrl(),
                // 'prev_page_url' => $advisor_data->previousPageUrl(),
                // 'total' => $advisor_data->total(),
                // 'total_on_current_page' => $advisor_data->count(),
                // 'has_more_page' => $advisor_data->hasMorePages(),
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_OK);
        }
    }
    public function updateAdvisorAboutUs(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        // $advisorDetails = AdvisorProfile::where('advisorId', '=', $id->id)->update(
        //     [
        //         'short_description' => $request->short_description,
        //         'description' => $request->description,
        //         'description_updated' => Date('Y-m-d H:i:s'),
        //     ]
        // );
        
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
        $data = $request->only('display_name');
        if ($request->company_logo == "") {
            $request->company_logo = "";
        }
        if ($request->image == "") {
            $request->image = "";
        }

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
        }

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
        }
        $arr = array();
        if ($request->company_logo != "") {
            $arr['company_logo'] = $request->company_logo;
        }
        if ($request->image != "") {
            $arr['image'] = $request->image;
        }
        if ($request->display_name != "") {
            $arr['display_name'] = $request->display_name;
        }
        if ($request->FCANumber != "") {
            $arr['FCANumber'] = $request->FCANumber;
        }
        if ($request->phone_number != "") {
            $arr['phone_number'] = $request->phone_number;
        }
        if ($request->city != "") {
            $arr['city'] = $request->city;
        }
        if ($request->postcode != "") {
            $arr['postcode'] = $request->postcode;
        }
        if ($request->role != "") {
            $arr['role'] = $request->role;
        }

        if ($request->network != "") {
            $arr['network'] = $request->network;
        }

        if ($request->email != "") {
            $arr['email'] = $request->email;
        }

        if ($request->gender != "") {
            $arr['gender'] = $request->gender;
        }

        if ($request->language != "") {
            $arr['language'] = $request->language;
        }

        if ($request->company_name != "") {
            $arr['company_name'] = $request->company_name;
        }

        $advisorDetails = AdvisorProfile::where('advisorId', '=', $id->id)->update($arr);
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        // echo json_encode($advisor_data);exit;
        if(isset($request->company_name) && $request->company_name!=''){
            companies::where('id',$advisor_data->company_id)->update(['company_name'=>$request->company_name]);
        }
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $advisor_data
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
        $company_data = CompanyTeamMembers::where('email', '=', $request->email)->first();
        if(isset($request->company_id) && $request->company_id!=''){
            $company = companies::where('id',$request->company_id)->first();
        }
        $checkUser = User::where('email','=',$request->email)->first();
        if (!empty($company_data)) {
            CompanyTeamMembers::where('id',$company_data->id)->update([
                'company_id' => $request->company_id,
                'advisor_id' => $user->id
            ]);

            if(!empty($checkUser)){
                AdvisorProfile::where('advisorId',$checkUser->id)->update([
                    'company_id' => $request->company_id
                ]);
            }
            
        }else{
            $profile = CompanyTeamMembers::create([
                'company_id' => $request->company_id,
                'name' => $request->name,
                'email' => $request->email,
                'advisor_id' => $user->id
            ]);
        }
        
        
        if(!empty($checkUser)){
            $newArr = array(
                'name'=>ucfirst($request->name),
                'invited_by'=>ucfirst($user->name),
                'email'=>$request->email,
                'url' => config('constants.urls.team_email_verification_url')."".$this->getEncryptedId($request->user_id)
            );
            $c = \Helpers::sendEmail('emails.team_email_verification',$newArr ,$request->email,$request->name,'Join Company | Mortgagebox.co.uk','','');
        }else{
            $newArr = array(
                'name'=>ucfirst($request->name),
                'invited_by'=>ucfirst($user->name),
                'email'=>$request->email,
                'url' => config('constants.urls.team_signup_url')."?invitedBy=".$this->getEncryptedId($user->id)."&invitedToEmail=".urlencode($request->email)."&invitedForCompany=".$company->company_name
            );
            $c = \Helpers::sendEmail('emails.team_email_signup',$newArr ,$request->email,$request->name,'Invitation | Mortgagebox.co.uk','','');
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
        $teamDetails = CompanyTeamMembers::where('id','=',$team_id)->first();
        if (!empty($teamDetails)) {
            CompanyTeamMembers::where('id','=',$team_id)->update([
            'is_joined' => "1"
            ]);
            AdvisorProfile::where('email','=',$teamDetails->email)->update([
                'company_id' => $teamDetails->company_id
            ]);
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
        $userDetails = User::where('id', '=', $request->advisor_id)->first();
       

            $ltv_max  = ($request->prop_value)/$request->mortgage_required;
            $lti_max  = ($request->prop_value)/$request->combined_income;

            $advice_area = Advice_area::create([
                'user_id' => $user->id,
                'service_type' => $request->need_advice,
                'request_time' => $request->how_soon,
                'property' => $request->prop_value,
                'size_want' => $request->mortgage_required,
                'combined_income' => $request->combined_income,
                'ltv_max'=>$ltv_max,
                'lti_max'=>$lti_max,
                'inquiry_adviser_id'=>$request->advisor_id,
                'inquiry_match_me'=>$request->match_me,
                'inquiry_description'=>$request->anything_else,
                'description'=>$request->anything_else

            ]);

            $this->saveNotification(array(
                'type'=>'9', // 1:
                'message'=>'You have invited for new bid from '.$request->name, // 1:
                'read_unread'=>'0', // 1:
                'user_id'=>$user->id,// 1:
                'advisor_id'=>$request->advisor_id, // 1:
                'area_id'=>$advice_area->id,// 1:
                'notification_to'=>1
            ));
            
        
        $msg = "";
        $msg .= "<table>"; 


        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Name";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->name;
        $msg .= "</td>"; 
        $msg .= "</tr>"; 

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Email";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->email;
        $msg .= "</td>"; 
        $msg .= "</tr>";
        
        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Mortgage Required";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->mortgage_required;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Property Value";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->prop_value;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Combined Income";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->combined_income;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "How soon do you need the mortgage?";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->how_soon;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Postcode";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->post_code;
        $msg .= "</td>"; 
        $msg .= "</tr>";
        
        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Is there anything else you feel is important?";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->anything_else;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "</table>"; 

        $userDetails = User::where('id', '=', $request->advisor_id)->first();

        $headers = "From: mbox@technofox.co.in\r\n";
        $headers .= "Reply-To: mbox@technofox.co.in\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        mail($userDetails->email, "New Enquiry", $msg,$headers);
        //for discussion
        // $newArr = array(
        //     'name'=>$request->name,
        //     'email'=>$request->email,
        //     'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
        // );
        // $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');

        return response()->json([
            'status' => true,
            'message' => 'Enquiry sent successfully!',
            'data' => []
        ], Response::HTTP_OK);
    }
    function advisorDashboard() {
        $userDetails = JWTAuth::parseToken()->authenticate();
        $matched_last_hour = DB::table('advice_areas')
            ->where('created_at', '>=',DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))
            ->count();
        $matched_last_today = Advice_area::whereDate('created_at', Carbon::today())->count();
        $matched_last_yesterday = Advice_area::whereDate('created_at', Carbon::yesterday())->count();
        $less_than_3_days = Advice_area::where('created_at', '>', Carbon::yesterday()->subDays(3))->where('created_at', '<', Carbon::today())->count();
        // $remortgage = Advice_area::where('service_type', '=', 'remortgage')->count();
        // $next_time_buyer = Advice_area::where('service_type', '=', 'first time buyer')->count();
        // $first_time_buyer = Advice_area::where('service_type', '=', 'next time buyer')->count();
        // $buy_to_let = Advice_area::where('service_type', '=', 'buy to let')->count();
        $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE  m.to_user_id = $userDetails->id AND m.to_user_id_seen = 0");
        
        $service = ServiceType::where('parent_id','!=',0)->where('status',1)->limit(4)->orderBy('sequence','ASC')->get();
        foreach($service as $service_data){
            $service_data->service_data_count = Advice_area::where('service_type_id', '=', $service_data->id)->count();
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
        $live_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 0)
            ->where('advisor_status', '=', 1)
            ->where('created_at', '>', Carbon::today()->subDays(30))
            ->count();
        $live_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 0)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(90))
        ->count();

        $live_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 0)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(365))
        ->count();

        $hired_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 1)
            ->where('advisor_status', '=', 1)
            ->where('created_at', '>', Carbon::today()->subDays(30))
            ->count();
        $hired_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 1)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(90))
        ->count();

        $hired_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 1)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(365))
        ->count();

        $completed_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 2)
            ->where('advisor_status', '=', 1)
            ->where('created_at', '>', Carbon::today()->subDays(30))
            ->count();
        $completed_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 2)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(90))
        ->count();

        $completed_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 2)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(365))
        ->count();

        // $promotion = User::where('advisor_id','=',$userDetails->id)
        // ->where('status', '=', 2)
        // ->where('advisor_status', '=', 1)
        // ->where('created_at', '>', Carbon::today()->subDays(365))
        // ->count();
        $promotion = 0;
        if($userDetails->invite_count!=0){
            $app_settings = DB::table('app_settings')->where('key','no_of_free_leads_refer_friend')->first();
            if($app_settings){
                $promotion = $userDetails->invite_count * $app_settings->value;
            }
        }
        
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
                ),
                'accepted_card_two'=>array(
                    'live_leads'=>$live_leads_quarter,
                    'hired'=>$hired_leads_quarter,
                    'completed'=>$completed_leads_quarter,
                ),
                'accepted_card_three'=>array(
                    'live_leads'=>$live_leads_year,
                    'hired'=>$hired_leads_year,
                    'completed'=>$completed_leads_year,
                ),
                'performance'=>array(
                    'conversion_rate'=>round($conversion_rate, 2),
                    'cost_of_leads'=>round($accepted_leads, 2),
                    'estimated_revenue'=>round($hired_leads, 2),
                ),
                'message_unread_count'=>$unread_count_total[0]->count_message,
                'notification_unread_count'=>0,
                'promotions'=>$promotion

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
    // Function for invoice generate
    // public function invoice(Request $request) {
    //     // return "Invoice generated successfully";
    //     $user = JWTAuth::parseToken()->authenticate();
    //     $advisor_data = AdvisorProfile::where('advisorId', '=', $user->id)->first();
    //     $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
    //     if($locations){
    //         $bill_to_address = $locations->contact_name. '('.$locations->invoice_name.')';
    //         $bill_to_address .= "\n".$locations->address_one;
    //         $bill_to_address .= "\n".$locations->address_two;
    //         $bill_to_address .= "\n".$locations->contact_number;
    //         $bill_to_address .= "\n".$locations->city;
    //         $bill_to_address .= "\n".$locations->post_code;
    //     }
    //     $month = date('m');
    //     $year = date('Y');
    //     if(isset($request->selected_date) && $request->selected_date !="") {
    //         $month = date('m',strtotime($request->selected_date));
    //         $year = date('Y',strtotime($request->selected_date));
    //     }
    //     $invoice_detais = Invoice::where('advisor_id','=',$user->id)->where('month','=',$month)->where('year','=',$year)->where('is_paid','=','0')->first();
    //     $total_this_month_cost_of_leads_subtotal = AdvisorBids::where('advisor_id','=',$user->id)
    //     ->where('status','>=',1)
    //     ->whereMonth('created_at', $month)
    //     ->where('is_paid_invoice','=',0)
    //     ->whereYear('created_at', $year)
    //     ->sum('cost_leads');
    //     $cost_leads_this_month = AdvisorBids::select('advisor_bids.cost_leads','advisor_bids.accepted_date','advisor_bids.cost_discounted','advisor_bids.free_introduction','advice_areas.service_type','advice_areas.size_want_currency','advice_areas.size_want')->where('advisor_bids.advisor_id','=',$user->id)
    //     ->leftJoin('advice_areas','advisor_bids.area_id','advice_areas.id')
    //     ->where('advisor_bids.status','>=',1)
    //     ->where('advisor_bids.is_paid_invoice','=',0)
    //     ->whereMonth('advisor_bids.created_at', $month)
    //     ->whereYear('advisor_bids.created_at', $year)
    //     ->get();
    //     $cost_of_leads_of_the_monthArr = array();
    //     $discount_of_the_monthArr = array();
    //     $cost_of_lead = 0;
    //     foreach($cost_leads_this_month as $key=> $item) {
    //         $cost_of_leads_of_the_monthArr[$key]['message']='Amount for bid on '.$item->service_type.' of '.$item->size_want_currency.$item->size_want.' at '.Date('d-M-Y',strtotime($item->accepted_date)).'';
    //         $cost_of_leads_of_the_monthArr[$key]['cost']=($item->cost_leads!="")?$item->cost_leads:"0";
    //         $cost_of_lead = ($item->cost_leads!="")?$item->cost_leads:"0";
    //     }
    //     $total_this_month_discount_subtotal = AdvisorBids::where('advisor_id','=',$user->id)
    //     ->where('status','>=',1)
    //     ->whereMonth('created_at', $month)
    //     ->whereYear('created_at', $year)
    //     ->where('is_paid_invoice','=',0)
    //     ->sum('cost_discounted');
    //     $total_this_month_free_intro = AdvisorBids::where('advisor_id','=',$user->id)
    //     ->where('status','>=',1)
    //     ->whereMonth('created_at', $month)
    //     ->whereYear('created_at', $year)
    //     ->where('is_paid_invoice','=',0)
    //     ->where('free_introduction','=',1)
    //     ->sum('cost_discounted');
    //     $subtotal_of_discount_and_credit = $total_this_month_discount_subtotal+$total_this_month_free_intro;
    //     $total_dues = $total_this_month_cost_of_leads_subtotal-$subtotal_of_discount_and_credit;
    //     $total_amount = 0;
    //     $tax_on_this_invoice = (5/100)*$total_dues;
    //     $vat_on_this_invoice = (20/100)*$total_dues;
    //     $total_amount_final = $total_dues+$tax_on_this_invoice+$vat_on_this_invoice;
    //     $total_amount_final = number_format((float)($total_amount_final),2,'.','');
    //     $newFees = AdvisorBids::select('advisor_bids.*','users.name','advice_areas.property','advice_areas.service_type')->where('advisor_bids.advisor_id','=',$user->id)
    //     ->leftJoin('advice_areas','advisor_bids.area_id','advice_areas.id')
    //     ->leftJoin('users','advice_areas.user_id','users.id')
    //     ->where('advisor_bids.status','>=',1)
    //     ->whereMonth('advisor_bids.created_at', $month)
    //     ->where('advisor_bids.is_paid_invoice','=',0)
    //     ->whereYear('advisor_bids.created_at', $year)
    //     ->get();

    //     $bidsId = AdvisorBids::where('advisor_id','=',$user->id)
    //     ->where('status','>=',1)
    //     ->whereMonth('created_at', $month)
    //     ->whereYear('created_at', $year)
    //     ->where('is_paid_invoice','=',0)
    //     ->get();
    //     $bidArr = array();
    //     foreach($bidsId as $item) {
    //         $bidArr[] = $item->id;
    //     }
    //     $newFeesArr = array();
    //     if(!empty($newFees)){
    //         foreach($newFees as $key=>$value) {
    //             $show_status = "Live Leads"; 
    //             $bidDetailsStatus = AdvisorBids::where('area_id',$value->area_id)->where('advisor_id','=',$user->id)->first();
    //             if(!empty($bidDetailsStatus)) {
    //                 if($bidDetailsStatus->status==0 && $bidDetailsStatus->advisor_status==1) {
    //                     $show_status = "Not Proceeding"; 
    //                 }else if($bidDetailsStatus->status>0 && $bidDetailsStatus->advisor_status==1) {
    //                     $show_status = "Hired"; 
    //                 }else if($bidDetailsStatus->status==3 && $bidDetailsStatus->advisor_status==1) {
    //                     $show_status = "Lost"; 
    //                 }else if($bidDetailsStatus->status==2 && $bidDetailsStatus->advisor_status==1) {
    //                     $show_status = "Closed"; 
    //                 }else{
    //                     $show_status = "Live Leads";     
    //                 }
    //             }else{
    //                 $show_status = "Live Leads";
    //             }
    //             $newFeesArr[$key]['date']=$value->accepted_date;
    //             $newFeesArr[$key]['customer']=$value->name;
    //             $newFeesArr[$key]['mortgage']=$value->property;
    //             $newFeesArr[$key]['status']=$show_status;
    //             $newFeesArr[$key]['free_type']=$value->service_type;
    //             $newFeesArr[$key]['amount']=$value->cost_leads;
    //         }
    //     }
    //     $discountAndCreditArr = array();
    //     if(!empty($newFees))
    //     {
    //         foreach($newFees as $key=>$value) {
    //             $show_status = "Live Leads"; 
    //             $bidDetailsStatus = AdvisorBids::where('area_id',$value->area_id)->where('advisor_id','=',$user->id)->first();
    //             if(!empty($bidDetailsStatus)) {
    //             if($bidDetailsStatus->status==0 && $bidDetailsStatus->advisor_status==1) {
    //                 $show_status = "Not Proceeding"; 
    //             }else if($bidDetailsStatus->status>0 && $bidDetailsStatus->advisor_status==1) {
    //                 $show_status = "Hired"; 
    //             }else if($bidDetailsStatus->status==3 && $bidDetailsStatus->advisor_status==1) {
    //                 $show_status = "Lost"; 
    //             }else if($bidDetailsStatus->status==2 && $bidDetailsStatus->advisor_status==1) {
    //                 $show_status = "Closed"; 
    //             }else{
    //                 $show_status = "Live Leads";     
    //             }
    //             }else{
    //             $show_status = "Live Leads"; 
    //             }
    //             $discountAndCreditArr[$key]['date']=$value->accepted_date;
    //             $discountAndCreditArr[$key]['customer']=$value->name;
    //             $discountAndCreditArr[$key]['mortgage']=$value->property;
    //             $newFeesArr[$key]['status']=$show_status;
    //             $discountAndCreditArr[$key]['free_type']=$value->service_type;
    //             $discountAndCreditArr[$key]['amount']=$value->cost_discounted;
    //         }
            
    //     }
    //     $invoice_number = $this->quickRandom(10);
    //     $lastPayment = Invoice::where('advisor_id','=',$user->id)->orderBy('id','DESC')->limit(1,1)->first();
    //     $last_payment_invoice="";
    //     $last_invoice_amount="";
    //     $last_paid_date="";
    //     $last_is_paid="";
    //     $last_payment_date="";
    //     $last_transaction_id = "";
    //     if(!empty($lastPayment)) {
    //         $last_payment_invoice = $lastPayment['invoice_number'];
    //         $last_payment_date = $lastPayment['updated_at'];
    //         $last_transaction_id = $lastPayment['txt_id'];
    //         $amountData = json_decode($lastPayment['invoice_data'],true);
    //         $last_invoice_amount=$amountData["total_current_invoice_amount"];
    //         $last_paid_date=$lastPayment['updated_at'];
    //         $last_is_paid=$lastPayment['is_paid'];
    //     } 
    //     if(empty($invoice_detais)) {
    //         if(!empty($bidArr)) {
    //             $invoice_IID= Invoice::create([
    //                 'invoice_data'=>json_encode(array(
    //                     'new_fess'=>array('cost_of_leads_of_the_month'=>$cost_of_leads_of_the_monthArr,
    //                     'cost_of_leads_sub_total'=>$total_this_month_cost_of_leads_subtotal),
    //                     'discounts_and_credits'=>array('discount_subtotal'=>$total_this_month_discount_subtotal,
    //                     'free_introduction_subtotal'=>$total_this_month_free_intro,
    //                     'subtotal'=>$subtotal_of_discount_and_credit),
    //                     'total_dues'=>$total_dues,
    //                     'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
    //                     'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
    //                     'total_current_invoice_amount'=>$total_amount_final,
    //                     'new_fees_data'=>$newFeesArr,
    //                     'new_fees_total'=>$total_this_month_cost_of_leads_subtotal,
    //                     'discount_credit_data'=>$discountAndCreditArr,
    //                     'discount_credit_total'=>$total_this_month_discount_subtotal,
    //                     'invoice_number'=>$invoice_number,
    //                     'seller_address'=>'MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678',
    //                     'bill_to_address'=>$bill_to_address,
    //                     'bid_ids'=>implode(",",$bidArr)
    //                 )),
    //                 'cost_of_lead'=>$total_this_month_cost_of_leads_subtotal,
    //                 'subtotal'=>$total_this_month_cost_of_leads_subtotal,
    //                 'discount'=>$total_this_month_discount_subtotal,
    //                 'free_introduction'=>0,
    //                 'discount_subtotal'=>$subtotal_of_discount_and_credit,
    //                 'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
    //                 'vat'=>number_format((float)($vat_on_this_invoice),2,'.',''),
    //                 'total_current_invoice'=>$total_amount_final,
    //                 'total_due'=>$total_dues,
    //                 'invoice_number'=>$invoice_number,
    //                 'month'=>$month,
    //                 'year'=>$year,
    //                 'is_paid'=>0,
    //                 'advisor_id'=>$user->id
    //             ]);
    //             $invoice_detais = Invoice::where('id','=',$invoice_IID->id)->first();
    //             $total_data = json_decode($invoice_detais->invoice_data,true);
    //             $total_data['invoice_data'] = $invoice_detais;
    //             $total_data['invoice_id']=$invoice_detais->id;
    //             $total_data['last_payment_invoice']=$last_payment_invoice;
    //             $total_data['last_invoice_amount']=$last_invoice_amount;
    //             $total_data['last_payment_date']=$last_payment_date;
    //             $total_data['last_transaction_id']=$last_transaction_id;
    //             $total_data['invoice_number']=$invoice_number;
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'success',
    //                 'data' => $total_data
    //             ], Response::HTTP_OK);
    //         }else{
    //             $lastPayment = Invoice::where('advisor_id','=',$user->id)->orderBy('id','DESC')->limit(1,1)->first();
    //             $last_payment_invoice="";
    //             $last_payment_date="";
    //             $last_transaction_id = "";
    //             $last_invoice_amount="";
    //             $last_paid_date="";
    //             $last_is_paid="";
    //             $seller_address='MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678';
    //             $bill_to_address=$bill_to_address;
    //             if(!empty($lastPayment)) {
    //                 $last_payment_invoice = $lastPayment['invoice_number'];
    //                 $last_payment_date = $lastPayment['updated_at'];
    //                 $last_transaction_id = $lastPayment['txt_id'];
    //                 $amountData = json_decode($lastPayment['invoice_data'],true);
    //                 $last_invoice_amount=$amountData["total_current_invoice_amount"];
    //                 $last_paid_date=$lastPayment['updated_at'];
    //                 $last_is_paid=$lastPayment['is_paid'];
    //             } 
    //             return response()->json([
    //                 'status' => true,
    //                 'message' => 'No invoice pending',
    //                 'data' => [
    //                     'last_payment_invoice'=>$last_payment_invoice,
    //                     'last_payment_date'=>$last_payment_date,
    //                     'last_transaction_id'=>$last_transaction_id,
    //                     'seller_address'=>$seller_address,
    //                     'bill_to_address'=>$bill_to_address,
    //                     'last_invoice_amount'=>$last_invoice_amount,
    //                     'last_paid_date'=>$last_paid_date,
    //                     'last_is_paid'=>$last_is_paid
    //                 ]
    //             ], Response::HTTP_OK);
    //         }
    //     }else{
    //         if(isset($bill_to_address) && $bill_to_address!=''){
    //             $bill_to_address = $bill_to_address;
    //         }else{
    //             $bill_to_address = '';
    //         }
    //         Invoice::where('id','=',$invoice_detais->id)->update([
    //             'invoice_data'=>json_encode(array(
    //             'new_fess'=>array('cost_of_leads_of_the_month'=>$cost_of_leads_of_the_monthArr,
    //             'cost_of_leads_sub_total'=>$total_this_month_cost_of_leads_subtotal),
    //             'discounts_and_credits'=>array('discount_subtotal'=>$total_this_month_discount_subtotal,
    //             'free_introduction_subtotal'=>$total_this_month_free_intro,
    //             'subtotal'=>$subtotal_of_discount_and_credit),
    //             'total_dues'=>$total_dues,
    //             'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
    //             'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
    //             'total_current_invoice_amount'=>$total_amount_final,
    //             'new_fees_data'=>$newFeesArr,
    //             'new_fees_total'=>$total_this_month_cost_of_leads_subtotal,
    //             'discount_credit_data'=>$discountAndCreditArr,
    //             'discount_credit_total'=>$total_this_month_discount_subtotal,

    //             'seller_address'=>'MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678',
    //             'bill_to_address'=>$bill_to_address,
    //             'bid_ids'=>implode(",",$bidArr)
    //             )),
    //             'cost_of_lead'=>$cost_of_lead,
    //             'subtotal'=>$total_this_month_cost_of_leads_subtotal,
    //             'discount'=>$total_this_month_discount_subtotal,
    //             'free_introduction'=>0,
    //             'discount_subtotal'=>$subtotal_of_discount_and_credit,
    //             'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
    //             'vat'=>number_format((float)($vat_on_this_invoice),2,'.',''),
    //             'total_current_invoice'=>$total_amount_final,
    //             'total_due'=>$total_dues,
    //             'month'=>$month,
    //             'year'=>$year,
    //             'is_paid'=>0,
    //             'advisor_id'=>$user->id
    //         ]);
    //         $invoice_detais = Invoice::where('advisor_id','=',$user->id)->where('month','=',$month)->where('year','=',$year)->first();
    //         $total_data = json_decode($invoice_detais->invoice_data,true);
    //         $total_data['invoice_data'] = $invoice_detais;
    //         $total_data['invoice_data']->discount_subtotal = 0.00;
    //         $total_data['invoice_data']->new_fees_arr = array();
    //         $total_data['invoice_data']->discount_credit_arr = array();
    //         if($total_data['invoice_data']){
    //             $total_data['invoice_data']->discount_subtotal = $total_data['invoice_data']->discount + $total_data['invoice_data']->free_introduction;
    //             $total_data['invoice_data']->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','<',$total_data['invoice_data']->month)->where('advisor_id',$total_data['invoice_data']->advisor_id)->sum('total_due');
    //             $total_data['invoice_data']->paid_prevoius_invoice = DB::table('invoices')->where('is_paid',1)->where('month','<',$total_data['invoice_data']->month)->where('advisor_id',$total_data['invoice_data']->advisor_id)->sum('total_due');
    //             $total_data['new_fees_arr'] = AdvisorBids::where('advisor_id',$total_data['invoice_data']->advisor_id)->where('is_discounted',0)->with('area')->with('adviser')->get();
    //             if(count($total_data['new_fees_arr'])){
    //                 foreach($total_data['new_fees_arr'] as $new_bid){
    //                     $new_bid->date = date("d-M-Y H:i",strtotime($new_bid->created_at));
    //                     if($new_bid->status==0){
    //                         $new_bid->status_type = "Live Lead";
    //                     }else if($new_bid->status==1){
    //                         $new_bid->status_type = "Hired";
    //                     }else if($new_bid->status==2){
    //                         $new_bid->status_type = "Completed";
    //                     }else if($new_bid->status==3){
    //                         $new_bid->status_type = "Lost";
    //                     }else if($new_bid->advisor_status==2){
    //                         $new_bid->status_type = "Not Proceeding";
    //                     }
    //                 }
    //             }
    //             $total_data['discount_credit_arr'] = AdvisorBids::where('advisor_id',$total_data['invoice_data']->advisor_id)->where('is_discounted','!=',0)->with('area')->with('adviser')->get();
    //             if(count($total_data['new_fees_arr'])){
    //                 foreach($total_data['discount_credit_arr'] as $discount_bid){
    //                     $discount_bid->date = date("d-M-Y H:i",strtotime($discount_bid->created_at));
    //                     if($discount_bid->status==0){
    //                         $discount_bid->status_type = "Live Lead";
    //                     }else if($discount_bid->status==1){
    //                         $discount_bid->status_type = "Hired";
    //                     }else if($discount_bid->status==2){
    //                         $discount_bid->status_type = "Completed";
    //                     }else if($discount_bid->status==3){
    //                         $discount_bid->status_type = "Lost";
    //                     }else if($discount_bid->advisor_status==2){
    //                         $discount_bid->status_type = "Not Proceeding";
    //                     }
    //                 }
    //             }
    //         }
    //         $total_data['month_data'] = Invoice::where('advisor_id','=',$user->id)->get(); 
    //         foreach($total_data['month_data'] as $month_data){
    //             $month_data->show_days = \Helpers::getMonth($month_data->month)." ".$month_data->year;
    //         }
    //         $total_data['invoice_id']=$invoice_detais->id;
    //         $total_data['invoice_number']=$invoice_detais->invoice_number;
    //         $total_data['last_payment_invoice']=$last_payment_invoice;
    //         $total_data['last_payment_date']=$last_payment_date;
    //         $total_data['last_transaction_id']=$last_transaction_id;
    //         $total_data['seller_address']='MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678';
    //         $total_data['bill_to_address']=$bill_to_address;
    //         $total_data['last_invoice_amount']=$last_invoice_amount;
    //         $total_data['last_paid_date']=$last_paid_date;
    //         $total_data['last_is_paid']=$last_is_paid;
    //         return response()->json([
    //             'status' => true,
    //             'message' => 'success',
    //             'data' => $total_data
    //         ], Response::HTTP_OK);
    //     }
        
    // }

    public function invoice(Request $request) {
        $users = User::where('user_role',1)->where('status',1)->get();
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
            ->where('status','>=',1)
            ->whereMonth('created_at', $month)
            ->where('is_paid_invoice','=',0)
            ->whereYear('created_at', $year)
            ->sum('cost_leads');
            $cost_leads_this_month = AdvisorBids::select('advisor_bids.cost_leads','advisor_bids.accepted_date','advisor_bids.cost_discounted','advisor_bids.free_introduction','advice_areas.service_type','advice_areas.size_want_currency','advice_areas.size_want')->where('advisor_bids.advisor_id','=',$row->id)
            ->leftJoin('advice_areas','advisor_bids.area_id','advice_areas.id')
            ->where('advisor_bids.status','>=',1)
            ->where('advisor_bids.is_paid_invoice','=',0)
            ->whereMonth('advisor_bids.created_at', $month)
            ->whereYear('advisor_bids.created_at', $year)
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
            ->where('status','>=',1)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('is_paid_invoice','=',0)
            ->sum('cost_discounted');
            $total_this_month_free_intro = AdvisorBids::where('advisor_id','=',$row->id)
            ->where('status','>=',1)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('is_paid_invoice','=',0)
            ->where('free_introduction','=',1)
            ->sum('cost_discounted');
            $subtotal_of_discount_and_credit = $total_this_month_discount_subtotal+$total_this_month_free_intro;
            $total_dues = $total_this_month_cost_of_leads_subtotal-$subtotal_of_discount_and_credit;
            $total_amount = 0;
            $tax_on_this_invoice = (5/100)*$total_dues;
            $vat_on_this_invoice = (20/100)*$total_dues;
            $total_amount_final = $total_dues+$tax_on_this_invoice+$vat_on_this_invoice;
            $total_amount_final = number_format((float)($total_amount_final),2,'.','');
            $newFees = AdvisorBids::select('advisor_bids.*','users.name','advice_areas.property','advice_areas.service_type')->where('advisor_bids.advisor_id','=',$row->id)
            ->leftJoin('advice_areas','advisor_bids.area_id','advice_areas.id')
            ->leftJoin('users','advice_areas.user_id','users.id')
            ->where('advisor_bids.status','>=',1)
            ->whereMonth('advisor_bids.created_at', $month)
            ->where('advisor_bids.is_paid_invoice','=',0)
            ->whereYear('advisor_bids.created_at', $year)
            ->get();

            $bidsId = AdvisorBids::where('advisor_id','=',$row->id)
            ->where('status','>=',1)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('is_paid_invoice','=',0)
            ->get();
            $bidArr = array();
            foreach($bidsId as $item) {
                $bidArr[] = $item->id;
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
            if(empty($invoice_detais)) {
                if(!empty($bidArr)) {
                    $invoice_IID= Invoice::create([
                        'cost_of_lead'=>$total_this_month_cost_of_leads_subtotal,
                        'subtotal'=>$total_this_month_cost_of_leads_subtotal,
                        'discount'=>$total_this_month_discount_subtotal,
                        'free_introduction'=>0,
                        'discount_subtotal'=>$subtotal_of_discount_and_credit,
                        'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
                        'vat'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                        'total_current_invoice'=>$total_amount_final,
                        'total_due'=>$total_dues,
                        'invoice_number'=>$invoice_number,
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
                            'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
                            'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                            'total_current_invoice_amount'=>$total_amount_final,
                            'new_fees_data'=>$newFeesArr,
                            'new_fees_total'=>$total_this_month_cost_of_leads_subtotal,
                            'discount_credit_data'=>$discountAndCreditArr,
                            'discount_credit_total'=>$total_this_month_discount_subtotal,
                            'invoice_number'=>$invoice_number,
                            'seller_address'=>'MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678',
                            'bill_to_address'=>$bill_to_address,
                            'bid_ids'=>implode(",",$bidArr)
                        )),
                        
                    ]);
                    $invoice_detais = Invoice::where('id','=',$invoice_IID->id)->first();
                    $total_data = json_decode($invoice_detais->invoice_data,true);
                    $total_data['invoice_data'] = $invoice_detais;
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
                    return "Invoice generated successfully";
                }else{
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
                    return response()->json([
                        'status' => true,
                        'message' => 'No invoice pending',
                        'data' => [
                            'last_payment_invoice'=>$last_payment_invoice,
                            'last_payment_date'=>$last_payment_date,
                            'last_transaction_id'=>$last_transaction_id,
                            'seller_address'=>$seller_address,
                            'bill_to_address'=>$bill_to_address,
                            'last_invoice_amount'=>$last_invoice_amount,
                            'last_paid_date'=>$last_paid_date,
                            'last_is_paid'=>$last_is_paid
                        ]
                    ], Response::HTTP_OK);
                }
            }else{
                if(isset($bill_to_address) && $bill_to_address!=''){
                    $bill_to_address = $bill_to_address;
                }else{
                    $bill_to_address = '';
                }
                Invoice::where('id','=',$invoice_detais->id)->update([
                    'cost_of_lead'=>$cost_of_lead,
                    'subtotal'=>$total_this_month_cost_of_leads_subtotal,
                    'discount'=>$total_this_month_discount_subtotal,
                    'free_introduction'=>0,
                    'discount_subtotal'=>$subtotal_of_discount_and_credit,
                    'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
                    'vat'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                    'total_current_invoice'=>$total_amount_final,
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
                    'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
                    'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                    'total_current_invoice_amount'=>$total_amount_final,
                    'new_fees_data'=>$newFeesArr,
                    'new_fees_total'=>$total_this_month_cost_of_leads_subtotal,
                    'discount_credit_data'=>$discountAndCreditArr,
                    'discount_credit_total'=>$total_this_month_discount_subtotal,

                    'seller_address'=>'MortgageBox\n\n123 High Street, Imaginary town\nSurrey TW12 2AA, United Kingdom\n\nThis is not a payment address\nVAT Number: GB1234567890\nCompany number 12345678',
                    'bill_to_address'=>$bill_to_address,
                    'bid_ids'=>implode(",",$bidArr)
                    )),
                    
                ]);
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
                return "Invoice generated successfully";
                // return response()->json([
                //     'status' => true,
                //     'message' => 'success',
                //     'data' => $total_data
                // ], Response::HTTP_OK);
            }
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
}
