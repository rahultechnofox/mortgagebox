<?php

namespace App\Http\Controllers;

use App\Models\Advice_area;
use App\Models\DefaultPercent;
use App\Models\AdvisorBids;
use App\Models\AdvisorOffers;
use App\Models\AdvisorProfile;
use App\Models\ChatChannel;
use App\Models\ChatModel;
use App\Models\companies;
use App\Models\NotificationsPreferences;
use App\Models\Notifications;
use App\Models\PostalCodes;
use App\Models\AdviceAreaRead;
use App\Models\ReviewRatings;
use App\Models\AdvisorPreferencesCustomer;
use App\Models\AdvisorPreferencesProducts;
use App\Models\ServiceType;
use App\Models\AdvisorPreferencesDefault;
use App\Models\AdviceAreaSpam;
use App\Models\StaticPage;
use App\Models\ReviewSpam;
use JWTAuth;
use App\Models\User;
use App\Models\UserNotes;
use App\Models\CompanyTeamMembers;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Contactus;
use App\Models\NeedSpam;
use App\Models\AdviserProductPreferences;
use App\Models\PaymentLog;
use App\Models\Payment;
use DateTime;
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
use PDF;
use Illuminate\Pagination\LengthAwarePaginator;
use Stripe;

class ApiController extends Controller
{
    protected $user;
    public function register(Request $request)
    {
        //Validate data
        $data = $request->only('name', 'email', 'password');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()], 200);
        }

        //Request is valid, create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);
        $userArr = (array)$user;
        // Mail::send('register_mail', $userArr, function($message) use ($userArr) {
        //     $dataUser = User::find($userArr);
        //     $message->to($dataUser->email);
        //     $message->subject('Welcome Mail');
        // });
        $dataUser = User::find($user);
        // $msg = "";
        // $msg .= "Welcome\n\n";
        // $msg .= "Hello ".ucfirst($request->name)."\n\n";
        // $msg .= "<p>Finding the right mortgage should be easy, but too often it's a hassle. Some mortgage web-sites / advisors aren't as helpful or that easy to use. And how can you be sure you've been given the best deal when you only use one?</p>\n\n";
        // $msg .= "<p>That's why we launched mortgagebox. To give you choice by matching you to five expert mortgage advisers, based on your mortgage needs, who then contoct you initially through mortgagebox</p>\n\n";
        // $msg .= "<p>Meet/talk/message the advisers and then choose the one best suited to your needs. This could be based on product, speed of execution, service offered, lack of fees or how well you gel with the adviser</p>\n\n";
        // $msg .= "<p>We've created a free account for you to manage your mortgage need. Please click the link below to activate your account and start finding your mortgage advisers</p>\n\n";
        // $msg .= "<p>We've created a free account for you to manage your mortgage need. Please click the link below to activate your account and start finding your mortgage advisers</p>\n\n";
        // $msg .= "<a href='".config('constants.urls.email_verification_url')."'>Activate Account</a>\n\n";
        // $msg .= "Best wishes\n\n";
        // $msg .= "The Mortgagebox team\n\n";

        // $msg .= $this->getEncryptedId($user->id);
        // $msg = wordwrap($msg, 70);
        // mail($request->email, "Welcome to Mortgagebox.co.uk", $msg);
        $newArr = array(
            'name'=>$request->name,
            'email'=>$request->email,
            'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
        );
        $c = \Helpers::sendEmail('emails.customer_signup',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');
        //User created, return success response
        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'data' => $dataUser
        ], Response::HTTP_OK);
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');

        //valid credential
        $validator = Validator::make($credentials, [
            'email' => 'required|email',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()], 200);
        }

        //Request is validated
        //Crean token
        try {
            $user =  USER::where('email', '=', $request->email)->where('status',"!=",2)->first();
            if($user){
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Login credentials are invalid.',
                    ], 400);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Login credentials are invalid.'
                ]);
            }
            
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'status' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        $user->profile_percent = 15;
        if($user->user_role == 1) {
            $userDetails =  AdvisorProfile::where('advisorId', '=', $user->id)->first(); 
            if($userDetails){
                if($userDetails->image!=''){
                    $user->profile_percent = $user->profile_percent + 20;
                }
                if($userDetails->short_description!=''){
                    $company = companies::where('id',$userDetails->company_id)->first();
                    if($company){
                        if($company->company_about!=''){
                            $user->profile_percent = $user->profile_percent + 15;
                        }
                    }
                }
                $offer_data = AdvisorOffers::where('advisor_id', '=', $user->id)->get();
                if(count($offer_data)){
                    $user->profile_percent = $user->profile_percent + 30;
                    $user->offer = 1;
                }else{
                    $user->offer = 0;
                }
                if($userDetails->web_address!=''){
                    $user->profile_percent = $user->profile_percent + 20;
                }
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
        $user->slug = $this->getEncryptedId($user->id);
        $user->is_invoice_remaining = 0;
        $unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','<',date('m'))->where('advisor_id',$user->id)->get();
        if(count($unpaid_prevoius_invoice)){
            // return response()->json(['user' => $unpaid_prevoius_invoice]);
            $user->is_invoice_remaining = 1;
        }else{
            $unpaid_prevoius_invoice_check = DB::table('invoices')->where('is_paid',0)->where('month',date('m'))->where('advisor_id',$user->id)->first();
            
            if($unpaid_prevoius_invoice_check){
                $unpaid_prevoius_invoice_check->current = date('Y-m-d H:i:s');
                $unpaid_prevoius_invoice_check->after = date('Y-m-d H:i:s',strtotime('+14 Days',strtotime($unpaid_prevoius_invoice_check->created_at)));

                if(date('Y-m-d H:i:s') >= date('Y-m-d H:i:s',strtotime('+14 Days',strtotime($unpaid_prevoius_invoice_check->created_at)))){
                    $user->is_invoice_remaining = 1;
                }
            }
            // return response()->json(['user' => $unpaid_prevoius_invoice_check]);
        }
        User::where('id',$user->id)->update(['last_active'=>date('Y-m-d H:i:s')]);
        //Token created, return with success response and jwt token
        return response()->json([
            'status' => true,
            'token' => $token,
            'data' => $user
        ]);
    }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['error' => $validator->messages()], 200);
        }

        //Request is validated, do logout        
        try {
            JWTAuth::invalidate($request->token);

            return response()->json([
                'status' => true,
                'message' => 'User has been logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry, user cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function get_user(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        unset($user->nationality);
        unset($user->fca_number);
        unset($user->company_name);

        $advice_area = Advice_area::where('user_id', '=', $user->id)->get();
        if (!$advice_area) {
            $advice_area = array();
        } else {
            unset($advice_area->user_id);
        }
        $user->slug = $this->getEncryptedId($user->id);
        return response()->json(['user' => $user]);
    }


    public function get_user_profile(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if($user->user_role==1){
            $user->userDetails = AdvisorProfile::where('advisorId',$user->id)->first();
            if($user->userDetails){
                $user->userDetails->profile_percent = 15;
                if($user->userDetails->image!=''){
                    $user->userDetails->profile_percent = $user->userDetails->profile_percent + 20;
                }
                if($user->userDetails->web_address!=''){
                    $user->userDetails->profile_percent = $user->userDetails->profile_percent + 20;
                }
                if($user->userDetails->short_description!=''){
                    $company = companies::where('id',$user->userDetails->company_id)->first();
                    if($company){
                        if($company->company_about!=''){
                            $user->userDetails->profile_percent = $user->userDetails->profile_percent + 15;
                        }
                    }
                }
                $offer_data = AdvisorOffers::where('advisor_id', '=', $user->id)->get();
                if(count($offer_data)){
                    $user->userDetails->profile_percent = $user->userDetails->profile_percent + 30;
                    $user->userDetails->offer = 1;
                }else{
                    $user->userDetails->offer = 0;
                }
                $team_member = CompanyTeamMembers::where('email',$user->userDetails->email)->first();
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
            $user->slug = $this->getEncryptedId($user->id);
            $user->is_invoice_remaining = 0;
            $unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('total_due','!=',0)->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->first();

            if($unpaid_prevoius_invoice){
                // return response()->json(['user' => $unpaid_prevoius_invoice]);
                $user->is_invoice_remaining = 1;
            }
        }
        return response()->json(['user' => $user]);
    }
    public function updateAccount(Request $request)
    {
        $userDetails = JWTAuth::parseToken()->authenticate();

        $data = $request->only('name', 'post_code', 'email');
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'post_code' => 'required',
            'email' => 'required',
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()], 200);
        }

        if ($userDetails->email != $request->email) {
            $user = $userDetails->update([
                'name' => $request->name,
                'post_code' => $request->post_code,
                'email' => $request->email,
                'email_status' => 0,
            ]);
            // $msg = "You have successfully changed your email.\n Please verfiy your account by click below link ";
            // $msg .= config('constants.urls.email_verification_url');

            // $msg .= $this->getEncryptedId($userDetails->id);
            // $msg = wordwrap($msg, 70);
            // mail($request->email, "Email Verification", $msg);
            $newArr = array(
                'name'=>$request->name,
                'email'=>$request->email,
                'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($userDetails->id)
            );
            $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Email Verification','','');
        } else {
            $user = $userDetails->update([
                'name' => $request->name,
                'post_code' => $request->post_code,
                'email' => $request->email,
            ]);
        }


        return response()->json([
            'status' => true,
            'message' => 'Account updated successfully',
        ], Response::HTTP_OK);
    }

    public function changePassword(Request $request)
    {
        $userDetailsGet = JWTAuth::parseToken()->authenticate();
        $data = $request->only('old_password', 'new_password');
        $validator = Validator::make($data, [
            'old_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()], 200);
        }
        //  $userDetails = User::find($userDetailsGet);

        if (empty($userDetailsGet)) {
            return response()->json(['status' => false, 'error' => 'User is not exists'], 200);
        }
        if (!Hash::check($request->old_password, $userDetailsGet->password)) {
            return response()->json(['status' => false, 'error' => 'Old password is wrong'], 200);
        }
        $userDetailsGet->update(['password' => bcrypt($request->new_password)]);
        return response()->json([
            'status' => true,
            'message' => 'Password changed successfully',

        ], Response::HTTP_OK);
    }

    public function addAdviceArea(Request $request)
    {
        if ($request->user_id == 0 || $request->user_id == "") {

            $data = $request->only('name', 'email', 'password');
            $validator = Validator::make($data, [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|max:50'
            ]);
            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['status' => false, 'error' => $validator->messages()], 200);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'post_code' => $request->post_code,
                'password' => bcrypt($request->password)
            ])->id;

            $request->user_id = $user;
            // $msg = "";
            // $msg .= "Welcome\n\n";
            // $msg .= "Hello ".ucfirst($request->name)."\n\n";
            // $msg .= "<p>Finding the right mortgage should be easy, but too often it's a hassle. Some mortgage web-sites / advisors aren't as helpful or that easy to use. And how can you be sure you've been given the best deal when you only use one?</p>\n\n";
            // $msg .= "<p>That's why we launched mortgagebox. To give you choice by matching you to five expert mortgage advisers, based on your mortgage needs, who then contoct you initially through mortgagebox</p>\n\n";
            // $msg .= "<p>Meet/talk/message the advisers and then choose the one best suited to your needs. This could be based on product, speed of execution, service offered, lack of fees or how well you gel with the adviser</p>\n\n";
            // $msg .= "<p>We've created a free account for you to manage your mortgage need. Please click the link below to activate your account and start finding your mortgage advisers</p>\n\n";
            // $msg .= "<p>We've created a free account for you to manage your mortgage need. Please click the link below to activate your account and start finding your mortgage advisers</p>\n\n";
            // $msg .= "<a href='".config('constants.urls.email_verification_url')."'>Activate Account</a>\n\n";
            // $msg .= "Best wishes\n\n";
            // $msg .= "The Mortgagebox team\n\n";
            // $msg = "You have successfully created account.\n Please verfiy your account by click below link ";
            // $msg .= config('constants.urls.email_verification_url');

            //$msg .= $this->getEncryptedId($request->user_id);
          //  $msg = wordwrap($msg, 70);

            // mail($request->email, "Welcome to Mortgagebox.co.uk", $msg);
            // $newArr = array(
            //     'name'=>$request->name,
            //     'email'=>$request->email,
            //     'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
            // );
            // $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');
            $newArr = array(
                'name'=>$request->name,
                'email'=>$request->email,
                'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
            );
            $c = \Helpers::sendEmail('emails.customer_signup',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');

            $credentials = $request->only('email', 'password');
            try {
                $token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp]);

                JWTAuth::authenticate($token);
            } catch (Exception $e) {
                return response()->json([
                    'status' => false,
                    'token' => $token,

                ], Response::HTTP_OK);
            }
        }
        JWTAuth::parseToken()->authenticate();
        // $user = Advice_area::create([
        //     'user_id' => $request->user_id,
        //     'service_type' => $request->service_type,
        //     'request_time' => $request->request_time,
        //     'property' => $request->property,
        //     'property_want' => $request->property_want,
        //     'size_want' => $request->size_want,
        //     'combined_income' => $request->combined_income,
        //     'description' => $request->description,
        //     'occupation' => $request->occupation,
        //     'contact_preference' => $request->contact_preference,
        //     'advisor_preference' => $request->advisor_preference,
        //     'fees_preference' => $request->fees_preference,
        // ]);
            $ltv_max  = ($request->property)/$request->size_want;
            $lti_max  = ($request->property)/$request->combined_income;

        Advice_area::create([
            'user_id' => $request->user_id,
            'service_type' => $request->service_type,
            'request_time' => $request->request_time,
            'property' => $request->property,
            'property_want' => $request->property_want,
            'size_want' => $request->size_want,
            'combined_income' => $request->combined_income,
            'description' => $request->description,
            'occupation' => $request->occupation,
            'contact_preference' => $request->contact_preference,
            'advisor_preference' => $request->advisor_preference,
            'fees_preference' => $request->fees_preference,
            'combined_income_currency' => $request->combined_income_currency,
            'property_currency' => $request->property_currency,
            'self_employed' => $request->self_employed,
            'non_uk_citizen' => $request->non_uk_citizen,
            'adverse_credit' => $request->adverse_credit,
            'contact_preference_face_to_face' => $request->contact_preference_face_to_face,
            'contact_preference_online' => $request->contact_preference_online,
            'contact_preference_telephone' => $request->contact_preference_telephone,
            'contact_preference_evening_weekend' => $request->contact_preference_evening_weekend,
            'advisor_preference_local' => $request->advisor_preference_local,
            'advisor_preference_language' => $request->advisor_preference_language,
            'size_want_currency' => $request->size_want_currency,
            'advisor_preference_gender' => $request->advisor_preference_gender,
            'ltv_max' => $ltv_max,
            'lti_max' => $lti_max

        ]);
        
        //User created, return success response
        return response()->json([
            'status' => true,
            'message' => 'Advice area added successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
    
    public function advisorRegister(Request $request)
    {
        //Validate data
        $post = $request->all();
        // echo json_encode($post);exit;
        $data = $request->only('name', 'email', 'password');
        $credentials = $request->only('email', 'password');
        // $post = $request->all();
        // echo json_encode($post);exit;
        $validator = Validator::make($data, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6|max:50'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()], 200);
        }
        $description = "";
        if(isset($request->company_name) && $request->company_name!=''){
            $company_data = companies::where('company_name', '=', $request->company_name)->first();
            $company_id = 0;
            
            if (!empty($company_data)) {
                $parentAdvisor = AdvisorProfile::where('company_id', '=', $company_data->id)->first();
                if(!empty($parentAdvisor)) {
                    $description = $parentAdvisor->description;
                }
                $company_id = $company_data->id;
            } else {
                $company_data_new = companies::create([
                    'company_name' => $request->company_name
                ]);
                $company_id = $company_data_new->id;
            }
        }
        //Request is valid, create new user
        $user_data = array(
            'name' => $request->name,
            'email' => $request->email,
            'user_role' => '1',
            'nationality' => $request->nationality,
            'address' => $request->address,
            'fca_number' => $request->fca_number,
            'company_name' => $request->company_name,
            'post_code' => $request->post_code,
            'password' => bcrypt($request->password)
        );
        if(isset($request->invitedBy) && $request->invitedBy!=''){
            $user_data['invited_by'] = $this->getDecryptedId($request->invitedBy);
        }
        $user = User::create($user_data);
        $free_promotions = 0;
        if($user){
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Credentials are invalid.',
                ], 400);
            }
            // $token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp]);
            // return response()->json([
            //     'data'=>$credentials,
            //     'status' => false,
            //     // 'checkkkk' => $checkkkk,
            //     'token' => $token,
            // ], Response::HTTP_OK);
            // $checkkkk = JWTAuth::authenticate($token);
            // try {
                

                
            // } catch (Exception $e) {
            //     return response()->json([
            //         'data'=>$e,
            //         'status' => false,
            //         'token' => $token,

            //     ], Response::HTTP_OK);
            // }
            // $user->profile_percent = 15;
            $userData = User::where('id',$user->id)->first();
            $check_promotion_advisor = DB::table('app_settings')->where('key','new_adviser_status')->where('value','1')->first();
            if($check_promotion_advisor){
                $free_promotion_value = DB::table('app_settings')->where('key','no_of_free_leads_adviser')->first();
                if($free_promotion_value){
                    $free_promotions = $free_promotion_value->value;
                }
            }
            User::where('id',$user->id)->update(['free_promotions'=>$free_promotions]);
            // $getuserData = User::where('id',$user->id)->first();
            // $free_promotions = $getuserData->free_promotions;

            $userData->profile_percent = 15;
            if($userData->user_role == 1) {
                $userDetails =  AdvisorProfile::where('advisorId', '=', $user->id)->first(); 
                if($userDetails){
                    if($userDetails->image!=''){
                        $userData->profile_percent = $userData->profile_percent + 20;
                    }
                    if($userDetails->short_description!=''){
                        $company = companies::where('id',$userDetails->company_id)->first();
                        if($company){
                            if($company->company_about!=''){
                                $userData->profile_percent = $userData->profile_percent + 15;
                            }
                        }
                    }
                    $offer_data = AdvisorOffers::where('advisor_id', '=', $user->id)->get();
                    if(count($offer_data)){
                        $userData->profile_percent = $userData->profile_percent + 30;
                        $userData->offer = 1;
                    }else{
                        $userData->offer = 0;
                    }
                    if($userDetails->web_address!=''){
                        $userData->profile_percent = $userData->profile_percent + 20;
                    }
                    $team_member = CompanyTeamMembers::where('email',$userDetails->email)->first();
                    // $userDetails->is_admin = $team_member;
                    if($team_member){
                        if($team_member->isCompanyAdmin==1){
                            $userData->is_admin = 1;
                        }else{
                            $userData->is_admin = 0;
                        }
                    }else{
                        $userData->is_admin = 2;
                    }
                }
                $userData->userDetails = $userDetails;
            }else{
                $userData->userDetails = [];
            }
            $userData->slug = $this->getEncryptedId($user->id);
            $userData->is_invoice_remaining = 0;
            $unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','<',date('m'))->where('advisor_id',$user->id)->get();
            if(count($unpaid_prevoius_invoice)){
                // return response()->json(['user' => $unpaid_prevoius_invoice]);
                $userData->is_invoice_remaining = 1;
            }else{
                $unpaid_prevoius_invoice_check = DB::table('invoices')->where('is_paid',0)->where('month',date('m'))->where('advisor_id',$user->id)->first();
                
                if($unpaid_prevoius_invoice_check){
                    $unpaid_prevoius_invoice_check->current = date('Y-m-d H:i:s');
                    $unpaid_prevoius_invoice_check->after = date('Y-m-d H:i:s',strtotime('+14 Days',strtotime($unpaid_prevoius_invoice_check->created_at)));

                    if(date('Y-m-d H:i:s') >= date('Y-m-d H:i:s',strtotime('+14 Days',strtotime($unpaid_prevoius_invoice_check->created_at)))){
                        $userData->is_invoice_remaining = 1;
                    }
                }
                // return response()->json(['user' => $unpaid_prevoius_invoice_check]);
            }
            User::where('id',$user->id)->update(['last_active'=>date('Y-m-d H:i:s')]);
        }
        if(isset($user->invited_by) && $user->invited_by!='' && $request->type!='invite_team'){
            $invited_count = User::where('invited_by',$user->invited_by)->count();
            $invitedByUser = User::where('id',$user->invited_by)->first();
            $check_promotion = DB::table('app_settings')->where('key','friend_active')->where('value','1')->first();
            if($check_promotion){
                $check_promotion_value = DB::table('app_settings')->where('key','no_of_free_leads_refer_friend')->first();
                if($check_promotion_value){
                    $free_promotions = $check_promotion_value->value;
                    if($invitedByUser->free_promotions!=0){
                        $free_promotions = $free_promotions + $invitedByUser->free_promotions;
                    }
                    User::where('id',$user->invited_by)->update(['invite_count'=>$invited_count,'free_promotions'=>$free_promotions]);
                }else{
                    User::where('id',$user->invited_by)->update(['invite_count'=>$invited_count]);

                }
            }
            $invited_by_user = AdvisorProfile::where('advisorId',$user->invited_by)->first();
            if($invited_by_user){
                $this->saveNotification(array(
                    'type'=>'1', // 1:
                    'message'=>'You have got free introduction from refering advisor', // 1:
                    'read_unread'=>'0', // 1:
                    'user_id'=>$user->id,// 1:
                    'advisor_id'=>$invited_by_user->advisorId, // 1:
                    'area_id'=>0,// 1:
                    'notification_to'=>1
                ));
                $newArr = array(
                    'name'=>$user->display_name,
                    'email'=>$user->email,
                    'message_text' => 'You have got free introduction from refering advisor'
                );
                $c = \Helpers::sendEmail('emails.information',$newArr ,$user->email,$user->name,'MortgageBox Free introduction','','');
            }
        }
        $advisor_id = $user->id;
        $company_id_for_ad = 0;
        if(isset($company_id) && $company_id!=0){
            $company_id_for_ad = $company_id;
        }
        //Request is valid, create new user
        $profile = AdvisorProfile::create([
            'advisorId' => $advisor_id,
            'FCANumber' => $request->fca_number,
            'address_line1' => $request->address,
            'display_name' => $request->name,
            'company_name' => $request->company_name,
            'serve_range' => $request->serve_range,
            'company_id' => $company_id_for_ad,
            'email' => $request->email,
            'postcode' => $request->post_code,
            'description' => $description,
            'mortgage_min_size'=>1,
            'mortgage_max_size'=>1000000
        ]);
        $serviceType = ServiceType::where('parent_id','!=',0)->where('status',1)->get();
        if(count($serviceType)){
            foreach($serviceType as $serviceType_data){
                $default_arr = array(
                    'service_id'=>$serviceType_data->id,
                    'adviser_id'=>$advisor_id,
                    'value_percent'=>0.30,
                    'status'=>1,
                    'created_at'=>date('Y-m-d H:i:s'),
                );
                DefaultPercent::insertGetId($default_arr);
                $productPrefernce = array(
                    'service_id'=>$serviceType_data->id,
                    'adviser_id'=>$advisor_id,
                    'created_at'=>date('Y-m-d H:i:s'),
                );
                AdviserProductPreferences::insertGetId($productPrefernce);
            }
        }
        if(isset($company_id) && $company_id!=0){
            $iscompany_admin = 0;
            $company_team = CompanyTeamMembers::where('email',$request->email)->first();
            $company_team_name = companies::where('id',$company_id)->first();
            // companies::where('id',$company_id)->update(array('company_admin'=>$advisor_id));
            if($company_team){
                $teamArr = array(
                    'name' => $request->name,
                    'status'=>1,
                    'is_joined'=>1,
                    'updated_at'=>date('Y-m-d H:i:s')
                );
                CompanyTeamMembers::where('id',$company_team->id)->update($teamArr);
                $iscompany_admin = 0;
            }else{
                $teamArr = array(
                    'company_id' => $company_id,
                    'name' => $request->name,
                    'email' => $request->email,
                    'advisor_id' => $user->id,
                    'isCompanyAdmin'=>1,
                    'status'=>1,
                    'is_joined'=>1,
                    'created_at'=>date('Y-m-d H:i:s')
                );
                CompanyTeamMembers::insertGetId($teamArr);
                $iscompany_admin = 1;
            }
            if(isset($iscompany_admin) && $iscompany_admin!=1){
                if(isset($user_data['invited_by']) && $user_data['invited_by']!=''){
                    $invited_user_data = AdvisorProfile::where('advisorId',$user_data['invited_by'])->first();
                    $this->saveNotification(array(
                        'type'=>'6', // 1:
                        'message'=> $request->name.' is now team member of your company', // 1:
                        'read_unread'=>'0', // 1:
                        'user_id'=>$user->id,// 1:
                        'advisor_id'=>$user_data['invited_by'], // 1:
                        'area_id'=>0,// 1:
                        'notification_to'=>1
                    ));
                    $newArr1 = array(
                        'name'=>$invited_user_data->name,
                        'email'=>$invited_user_data->email,
                        'message_text' => $request->name.' is now team member of your company ',
                    );
                    $c = \Helpers::sendEmail('emails.information',$newArr1 ,$invited_user_data->email,$invited_user_data->name,'MortgageBox Join Company','','');
                }
               
            }
            // $this->saveNotification(array(
            //     'type'=>'1', // 1:
            //     'message'=>'Your invitation is accepted by team member '.$request->name, // 1:
            //     'read_unread'=>'0', // 1:
            //     'user_id'=>$advisor_id,// 1:
            //     'advisor_id'=>$user->id, // 1:
            //     'area_id'=>0,// 1:
            //     'notification_to'=>1
            // ));
            // $newArr1 = array(
            //     'name'=>$user->name,
            //     'email'=>$user->email,
            //     'message_text' => 'Your invitation is accepted by team member '.$request->name
            // );
            // $c = \Helpers::sendEmail('emails.information',$newArr1 ,$user->email,$user->name,'MortgageBox Invitation Accept','','');
        }
        // Set Defaul prefrances
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $advisor_id)->first();
        if (empty($notification)) {
            AdvisorPreferencesDefault::create([
                'advisor_id' => $user->id,
            ]);
        }
        
        $notification2 = AdvisorPreferencesCustomer::where('advisor_id', '=', $advisor_id)->first();
        if (empty($notification2)) {
            AdvisorPreferencesCustomer::create([
                'advisor_id' => $user->id,
            ]);
        }
        $newArr2 = array(
            'name'=>$request->name,
            'email'=>$request->email,
            'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($advisor_id)
        );
        $c = \Helpers::sendEmail('emails.email_verification',$newArr2 ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');
        //User created, return success response
        
        
        return response()->json([
            'status' => true,
            'message' => 'Advisor created successfully',
            'data' => $userData,
            'token'=> $token
        ], Response::HTTP_OK);
    }
    public function updateAdvisorProfile(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $data = $request->only('display_name');
        $validator = Validator::make($data, [
            'display_name' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['status' => false, 'error' => $validator->messages()], 200);
        }

        if ($request->hasFile('image')) {
            $validator = Validator::make($request->all(), [
                'image' => 'required|image:jpeg,png,jpg,gif,svg|max:2048'
            ]);
            if ($validator->fails()) {
                return response()->json(['status' => false, 'error' =>  $validator->messages()], 200);
            }

            $uploadFolder = 'users';
            $image = $request->file('image');
            $image_uploaded_path = $image->store($uploadFolder, 'public');
            $request->image = basename($image_uploaded_path);
            $uploadedImageResponse = array(
                "image_name" => basename($image_uploaded_path),
                "image_url" => Storage::disk('public')->url($image_uploaded_path),
                "mime" => $image->getClientMimeType()
            );
        }
        $advisorDetails = AdvisorProfile::where('advisorId', '=', $request->advisorId)->update(
            [
                'display_name' => $request->display_name,
                'tagline' => $request->tagline,
                'FCANumber' => $request->FCANumber,
                'invalidate_fca' => 0,
                'phone_number' => $request->phone_number,
                'address_line1' => $request->address_line1,
                'address_line2' => $request->address_line2,
                'city' => $request->city,
                'postcode' => $request->postcode,
                'web_address' => $request->web_address,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'about_us' => $request->about_us,
                'role' => $request->role,
                'short_description' => $request->short_description,
                'description' => $request->description,
                'advisorId' => $request->advisorId,
                'image' => $request->image,
            ]
        );
        $advisor_data = AdvisorProfile::where('advisorId', '=', $request->advisorId)->first();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }
    public function getAdvisorProfile()
    {
        $id = JWTAuth::parseToken()->authenticate();
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        if ($advisor_data) {

            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advisor_data,

            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }

    public function getAdvisorDetails($id)
    {
        $user_id = $this->getDecryptedId($id);
        $user = User::where('id',$user_id)->first();
        if($user) {
            $user->advisor_data = AdvisorProfile::where('advisorId', '=', $user_id)->first();
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $user,

            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function addNewAdviceArea(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user != "") {
            $ltv_max  = ($request->property)/$request->size_want;
            $lti_max  = ($request->property)/$request->combined_income;

            $area = Advice_area::create([
                'user_id' => $user->id,
                'service_type_id' => (int)$request->service_type_id,
                // 'service_type' => $request->service_type,
                'request_time' => $request->request_time,
                'property' => $request->property,
                'property_want' => $request->property_want,
                'size_want' => $request->size_want,
                'combined_income' => $request->combined_income,
                'description' => $request->description,
                'occupation' => $request->occupation,
                'contact_preference' => $request->contact_preference,
                'advisor_preference' => $request->advisor_preference,
                'fees_preference' => $request->fees_preference,
                'combined_income_currency' => $request->combined_income_currency,
                'property_currency' => $request->property_currency,
                'self_employed' => $request->self_employed,
                'non_uk_citizen' => $request->non_uk_citizen,
                'adverse_credit' => $request->adverse_credit,
                'contact_preference_face_to_face' => $request->contact_preference_face_to_face,
                'contact_preference_online' => $request->contact_preference_online,
                'contact_preference_telephone' => $request->contact_preference_telephone,
                'contact_preference_evening_weekend' => $request->contact_preference_evening_weekend,
                'advisor_preference_local' => $request->advisor_preference_local,
                'advisor_preference_language' => $request->advisor_preference_language,
                'size_want_currency' => $request->size_want_currency,
                'advisor_preference_gender' => $request->advisor_preference_gender,
                'ltv_max'=>$ltv_max,
                'lti_max'=>$lti_max

            ]);
            //User created, return success response
            // $asap = 0;
            // $next_3_month = 0;
            // $more_3_month = 0;
            // if($request->request_time=='as soon as possible'){
            //     $asap = 1;
            // }else if($request->request_time=='in the next 3 months'){
            //     $next_3_month = 1;
            // }else if($request->request_time=='in more than 3 months'){
            //     $more_3_month = 1;
            // }
            // $userIdsArr = array();
            // $userService = AdviserProductPreferences::where('service_id',$request->service_type_id)->get(); 
            // if(count($userService)){
            //     $advisorId = array(-1);
            //     foreach($userService as $userService_data){
            //         array_push($advisorId,$userService_data->adviser_id);
            //     }
            //     if(count($userIdsArr)>0){
            //         $userIdsArr = array_intersect($userIdsArr, $advisorId);
            //     }else{
            //         $userIdsArr = array_unique($advisorId);
            //     }
            // }
            // $userCustomer = AdvisorPreferencesCustomer::where('self_employed',$request->self_employed)->where('non_uk_citizen',$request->non_uk_citizen)->where('adverse_credit',$request->adverse_credit)->where('asap',$asap)->where('next_3_month',$next_3_month)->where('more_3_month',$more_3_month)->where('fees_preference',$request->fees_preference)->get(); 
            // if(count($userCustomer)){
            //     $advisorCustomerId = array(-1);
            //     foreach($userCustomer as $userCustomer_data){
            //         array_push($advisorCustomerId,$userCustomer_data->advisor_id);
            //     }
            //     if(count($userIdsArr)>0){
            //         $userIdsArr = array_intersect($userIdsArr, $advisorCustomerId);
            //     }else{
            //         $userIdsArr = array_unique($advisorCustomerId);
            //     }
            // }

            // $advisor = User::whereIn('id',$userIdsArr)->where('user_role',1)->where('post_code',$user->post_code)->get();
            // foreach($advisor as $advisor_data){
            //     $newArrDec = array(
            //         'name'=>$advisor_data->name,
            //         'email'=>$advisor_data->email,
            //         'message_text' => 'One lead is matched with your profile go and checkout it.'
            //     );
            //     $c = \Helpers::sendEmail('emails.information',$newArrDec ,$advisor_data->email,$advisor_data->name,'MortgageBox Lead Match','','');
            // }
            return response()->json([
                'status' => true,
                'message' => 'Advice area added successfully',
                'area_id'=>$area->id,

            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'not found',

            ], Response::HTTP_OK);
        }
    }

    public function sendNewLeadMatchMail(Request $request,$id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user != "") {
            $area = Advice_area::where('id',$id)->first();
            //User created, return success response
            $asap = 0;
            $next_3_month = 0;
            $more_3_month = 0;
            if($area){
                if($area->request_time=='as soon as possible'){
                    $asap = 1;
                }else if($area->request_time=='in the next 3 months'){
                    $next_3_month = 1;
                }else if($area->request_time=='in more than 3 months'){
                    $more_3_month = 1;
                }
                $userIdsArr = array();
                $userService = AdviserProductPreferences::where('service_id',$area->service_type_id)->get(); 
                if(count($userService)){
                    $advisorId = array(-1);
                    foreach($userService as $userService_data){
                        array_push($advisorId,$userService_data->adviser_id);
                    }
                    if(count($userIdsArr)>0){
                        $userIdsArr = array_intersect($userIdsArr, $advisorId);
                    }else{
                        $userIdsArr = array_unique($advisorId);
                    }
                }
                $userCustomer = AdvisorPreferencesCustomer::where('self_employed',$area->self_employed)->where('non_uk_citizen',$area->non_uk_citizen)->where('adverse_credit',$area->adverse_credit)->where('asap',$asap)->where('next_3_month',$next_3_month)->where('more_3_month',$more_3_month)->where('fees_preference',$request->fees_preference)->get(); 
                if(count($userCustomer)){
                    $advisorCustomerId = array(-1);
                    foreach($userCustomer as $userCustomer_data){
                        array_push($advisorCustomerId,$userCustomer_data->advisor_id);
                    }
                    if(count($userIdsArr)>0){
                        $userIdsArr = array_intersect($userIdsArr, $advisorCustomerId);
                    }else{
                        $userIdsArr = array_unique($advisorCustomerId);
                    }
                }
    
                $advisor = User::whereIn('id',$userIdsArr)->where('user_role',1)->where('post_code',$user->post_code)->get();
                foreach($advisor as $advisor_data){
                    $newArrDec = array(
                        'name'=>$advisor_data->name,
                        'email'=>$advisor_data->email,
                        'message_text' => 'One lead is matched with your profile go and checkout it.'
                    );
                    $c = \Helpers::sendEmail('emails.information',$newArrDec ,$advisor_data->email,$advisor_data->name,'MortgageBox Lead Matched','','');
                }
                
            }
            return response()->json([
                'status' => true,
                'message' => 'Mail sent successfully',
                'data'=>$area

            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'not found',

            ], Response::HTTP_OK);
        }
    }

    public function getUsersAdviceArea()
    {
        $id = JWTAuth::parseToken()->authenticate();
        // $advice_area = Advice_area::where('user_id', '=', $id->id)->get();
        $advice_area = Advice_area::where('user_id', '=', $id->id)->orderBy('id', 'DESC')->with('service')->get();
        // ->leftJoin('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.id')
        
        // $advice_area =  DB::select('SELECT advice_areas.*,advisor_bids.status as bid_status FROM advice_areas  LEFT JOIN advisor_bids ON advice_areas.id = advisor_bids.area_id where user_id ='.$id->id.'');
        foreach ($advice_area as $key => $item) {
            $reviewed = 0;
            // $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->id AND m.to_user_id != $id->id AND m.to_user_id_seen = 0");
            $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->id AND m.to_user_id = $id->id AND m.to_user_id_seen = 0");
            // $channelDetails = ChatChannel::where('advicearea_id',$item->id)->where('to_user_id',$id->id)->first();
            // $channelExist = ChatChannel::where('advicearea_id',$item->id)->where('from_user_id',$id->id)->first();
            // echo json_encode();exit;
            // $chat_count = 0;
            // $channel_id  = 0;
            // $channel_name = "channel" . "-" . $request->advicearea_id . "-" . $request->from_user_id . "-" . $request->to_user_id;
            // $channelDetails =  ChatChannel::where('from_user_id', '=', $request->from_user_id)->where('to_user_id', '=', $request->to_user_id)->first();
            // $channelExist =  ChatChannel::where('from_user_id', '=', $request->to_user_id)->where('to_user_id', '=', $request->from_user_id)->first();
            // if (!empty($channel)) {
            //     $chat_count = ChatModel::where('channel_id', '=', $channel->id)->where('to_user_id', '=', $id->id)->where('to_user_id_seen', '=', 0)->orderBy('id', 'asc')->count();
            // }
            // if (!empty($channelDetails) && !empty($channelExist)) {
            //     if (!empty($channelDetails)) {
            //         $channel_id = $channelDetails->id;
            //     } else if (!empty($channelExist)) {
            //         $channel_id = $channelExist->id;
            //     }
            //     $chat_count = ChatModel::where('channel_id', '=', $channel_id)->where('to_user_id_seen', '=', 0)->orderBy('id', 'asc')->count();
            // }
    
            //For get unseen messages counts Only
            // if ($request->type && $request->type == 1) {
            // } else {
            //     $chatData = ChatModel::where('channel_id', '=', $channel_id)->orderBy('id', 'asc')->get();
            //     if (count($chatData)) {
            //         ChatModel::where('to_user_id', '=', $user->id)->where('channel_id', '=', $channel_id)->where('to_user_id_seen', '=', 0)->update(
            //             [
            //                 'to_user_id_seen' => 1
            //             ]
    
            //         );
            //     }
            // }

            //For get unseen messages counts Only
            // if ($request->type && $request->type == 1) {
                
            // } else {
            //     $chatData = ChatModel::where('channel_id', '=', $channel_id)->orderBy('id', 'asc')->get();
            //     if (count($chatData)) {
            //         ChatModel::where('to_user_id', '=', $user->id)->where('channel_id', '=', $channel_id)->where('to_user_id_seen', '=', 0)->update(
            //             [
            //                 'to_user_id_seen' => 1
            //             ]

            //         );
            //     }
            // }
            // return response()->json([
            //     'status' => true,
            //     'channel' => ['channel_id' => $channel_id, 'channel_name' => $channel_name],
            //     'data' => $chatData
            // ], Response::HTTP_OK);
            // $advice_area[$key]->unread_message_count = $chat_count;
            
            $advice_area[$key]->unread_message_count = $unread_count_total[0]->count_message;
            $offer_count = AdvisorBids::where('area_id','=',$item->id)->count();
            $advice_area[$key]->offer_count = $offer_count;
            $bidDetailsStatus = AdvisorBids::where('area_id',$item->id)->first();
            $advice_area[$key]->bid_data = $bidDetailsStatus;
            if($item->area_status=='0'){
                $show_status = "Matching";
            }else if($item->area_status=='1'){
                $show_status = "Matched";
            }else if($item->area_status=='2'){
                $show_status = "Adviser Selected";
            }else if($item->area_status=='3'){
                $show_status = "Completed";
            }else if($item->area_status=='4'){
                $show_status = "Closed";
            }else if($item->area_status=='5'){
                $show_status = "Not Proceeding";
            }
            $advice_area[$key]->show_status = $show_status;
            $reviewed = 0;
            if($show_status == "Completed"){
                $review = ReviewRatings::where('user_id',$id->id)->where('area_id',$item->id)->first();
                if($review){
                    $reviewed = 1;
                }
            }
            $advice_area[$key]->reviewed = $reviewed;
        }
        if ($advice_area) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advice_area
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function getAdviceAreaById($id)
    {
        JWTAuth::parseToken()->authenticate();
        $advice_area = Advice_area::where('id',$id)->with('service')->first();
        $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $advice_area->id AND m.to_user_id_seen = 0");
        $advice_area->unread_message_count = $unread_count_total[0]->count_message;

        if ($advice_area) {
            $advice_area->created_at_need = date("d-m-Y H:i",strtotime($advice_area->created_at));
            $advice_area->offer_count = AdvisorBids::where('area_id',$id)->count();
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advice_area,
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    // add user's notes by advice id
    public function addUserNotes(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $obj = UserNotes::create([
            'user_id' => $user->id,
            'notes' => $request->notes,
            'advice_id' => $request->advice_id,
        ]);
        //User created, return success response
        return response()->json([
            'status' => true,
            'data' =>$obj,
            'message' => 'Notes added successfully',

        ], Response::HTTP_OK);
    }

    public function getAdviceNotesByAdviceId($advice_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $adviceNotes = UserNotes::where('advice_id', '=', $advice_id)->get();
        if (count($adviceNotes) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $adviceNotes
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    
    public function getAdviceNotesOfUserByAdviceId($advice_id,$user_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $adviceNotes = UserNotes::where('advice_id', '=', $advice_id)->where('user_id', '=', $user_id)->get();
        if (count($adviceNotes) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $adviceNotes
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_OK);
        }
    }

    public function updateNotes(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $advisorDetails = UserNotes::where('id', '=', $request->id)->update([
            'notes' => $request->notes,
        ]);
        return response()->json([
            'status' => true,
            'message' => 'Notes updated successfully',
        ], Response::HTTP_OK);
    }

    public function closeAdviceAreaNeed(Request $request)
    {

        $user = JWTAuth::parseToken()->authenticate();
        $update_arr = array(
            'status' => 2,
            'area_status'=>4,
            'close_type' => $request->close_type,
            'advisor_id' => $request->advisor_id,
            'need_reminder' => $request->need_reminder,
            'initial_term' => $request->initial_term,
            'start_date' => $request->start_date,
        );
        if(isset($request->initial_term) && $request->initial_term!=''){
            $update_arr['initial_term_number'] = $request->initial_term_number;
        }
        $advice_area = Advice_area::where('id', '=', $request->id)->update($update_arr);
        if ($advice_area == 1) {
            return response()->json([
                'status' => true,
                'message' => 'Advice area closed',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    
    public function verifyEmail($id)
    {
        $user_id = $this->getDecryptedId($id);
        $userDetails = User::find($user_id);

        if (!empty($userDetails)) {
            $result = $userDetails->update([
                'email_status' => 1,
                'email_verified_at' => Date('Y-m-d H:i:s'),
            ]);
            if($userDetails->user_role==0){
                return redirect()->away(config('constants.urls.host_url')."?type=Activate");
            }else{
                return redirect()->away(config('constants.urls.host_url')."/adviser?type=Activate");
            }
            
        }
        
    }
    // MARK: Function for forgot password
    public function forgotPassword(Request $request)
    {
        $email = $request->email;
        $userDetails = User::where('email', '=', $email)->first();
        if (!empty($userDetails)) {
            $password = $this->generateRandomString(10);
            $userDetails->update([
                'password' => bcrypt($password)
            ]);
            $data['email'] = $email;
            $newArr = array(
                'name'=>$userDetails->name,
                'email'=>$email,
                'password' => $password
            );
            $c = \Helpers::sendEmail('emails.reset_password',$newArr ,$email,$userDetails->name,'Password reset','','');
            return response()->json([
                'status' => true,
                'message' => 'Password is sent to your registered email.',
            ], Response::HTTP_OK);
        } else {

            return response()->json([
                'status' => false,
                'message' => 'Email is not exist.',
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function resendActivationMail(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $post = $request->all();
        if(isset($request->email) && $request->email != ''){
            $emailExist = User::where('email',$request->email)->where('id','!=',$user->id)->count();
            if ($emailExist) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email is already exist',
                    'data' => $post
                ], Response::HTTP_OK);
            }
            $advisorDetails = AdvisorProfile::where('advisorId', '=', $user->id)->update(['email'=>$request->email]);
        }
        // $msg = "To verify your email \n Please click below link ";
        // $msg .= config('constants.urls.email_verification_url');

        // $msg .= $this->getEncryptedId($user->id);
        // $msg = wordwrap($msg, 70);
        // mail($user->email, "Email Verification", $msg);
        $newArr = array(
            'name'=>$user->name,
            'email'=>$user->email,
            'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($user->id)
        );
        $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$user->email,$user->name,'Email Verification','','');
        return response()->json([
            'status' => true,
            'message' => 'Activation link sent successfully',
        ], Response::HTTP_OK);
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


    function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function matchLeads(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id','=',$user->id)->first();
        $requestTime = [];
        $timeVal='';
        $service=0;
        $discountArr=array();
        $timeArr=array();

        $discount='';
        $ltv_max = $userPreferenceCustomer->ltv_max;
        $lti_max = $userPreferenceCustomer->lti_max;
        $self = 0;
        $non_uk_citizen = 0;
        $adverse = 0;
        if(!empty($userPreferenceCustomer)) {
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
        if(isset($_GET['service_id']) && $_GET['service_id']!=''){
            $service = $_GET['service_id'];
        }
        
        if(isset($_GET['discount']) && $_GET['discount']!=''){
            $discount = $_GET['discount'];
            $discountArr=array(-1);
            if($discount=='50%_off'){
                $bid = AdvisorBids::where('discount_cycle','Second cycle')->get();
                foreach($bid as $bid_data){
                    if(!in_array($bid_data->area_id,$discountArr)){
                        array_push($discountArr,$bid_data->area_id);
                    }
                }
            }
            if($discount=='75%_off'){
                $bidTh = AdvisorBids::where('discount_cycle','Third cycle')->get();
                foreach($bid as $bidTh_data){
                    if(!in_array($bidTh_data->area_id,$discountArr)){
                        array_push($discountArr,$bidTh_data->area_id);
                    }
                }
            }
            if($discount=='free'){
                $bidFou = AdvisorBids::where('discount_cycle','Fourth cycle')->get();
                foreach($bidFou as $bidFou_data){
                    if(!in_array($bidFou_data->area_id,$discountArr)){
                        array_push($discountArr,$bidFou_data->area_id);
                    }
                }
            }
        }

        if(isset($_GET['time']) && $_GET['time']!=''){
            $timeVal = $_GET['time'];
            $timeArr = array(-1);
            if($timeVal!='') {
                if($timeVal=='last_hour'){
                    $last = Advice_area::where('created_at','>=',DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))->get();
                    foreach($last as $last_data){
                        array_push($timeArr,$last_data->id);
                    }
                }else if($timeVal=='today'){
                    $today = Advice_area::where('created_at','>=',Carbon::today())->get();
                    foreach($today as $today_data){
                        array_push($timeArr,$today_data->id);
                    }
                }else if($timeVal=='yesterday'){
                    $yesterday = Advice_area::where('created_at','>=',Carbon::yesterday())->get();
                    foreach($yesterday as $yesterday_data){
                        array_push($timeArr,$yesterday_data->id);
                    }
                }else if($timeVal=='less_than_3_days'){
                    $less_3 = Advice_area::where('created_at','>=', Carbon::today()->subDays(3))->get();
                    foreach($less_3 as $less_3_data){
                        array_push($timeArr,$less_3_data->id);
                    }
                }
            }
            if(count($discountArr)>0){
                $discountArr = array_intersect($discountArr, $timeArr);
            }else{
                $discountArr = array_unique($timeArr);
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
        $advice_area_arr = array(-1);
        $advice_meQ = Advice_area::where('inquiry_adviser_id',$user->id);
        if(count($discountArr)>0){
            $advice_meQ = $advice_meQ->whereIn('id',$discountArr);
        }
        $advice_me = $advice_meQ->get();
        foreach($advice_me as $advice_me_data){
            array_push($advice_area_arr,$advice_me_data->id);
        }

        $adviceQ = Advice_area::where('inquiry_adviser_id','!=',0)->where('inquiry_adviser_id','!=',$user->id)->where('inquiry_match_me',1)->where('area_status',0);
        if(count($discountArr)>0){
            $adviceQ = $adviceQ->whereIn('id',$discountArr);
        }
        $advice = $adviceQ->get();
        foreach($advice as $advice_data){
            $bid = AdvisorBids::where('area_id',$advice_data->id)->count();
            if($bid<3){
                if(!in_array($advice_data->id,$advice_area_arr)){
                    array_push($advice_area_arr,$advice_data->id);
                }
            }
        }
        if(count($advice_area_arr)){
            $advice_area_arr = array_unique($advice_area_arr);
        }
        //this is code of search area id array
        // 
        $queryModel = 'App\Models\Advice_area';
        if($self!=0 && $non_uk_citizen!=0 && $adverse!=0){
            if($self==1){
                $queryModel::where('self_employed',1);
            }
            if($non_uk_citizen==1){
                $queryModel::where('non_uk_citizen',1);
            }
            if($adverse==1){
                $queryModel::where('adverse_credit',1);
            }
            // $queryModel::where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse);
        }
        $advice_areaQuery =  $queryModel::select('advice_areas.*', 'users.name', 'users.email', 'users.address')->with('service')
            ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
            ->leftJoin('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
            ->where('area_status',0)->where('inquiry_adviser_id',0)->where(function($query) use ($requestTime){
                // ->where('self_employed',$self)->where('non_uk_citizen',$non_uk_citizen)->where('adverse_credit',$adverse)
                // if($requestTime != ""){
                //     $query->where('advice_areas.request_time','=',$requestTime);
                // }
                if(!empty($requestTime)) {
                    $query->where(function($q) use ($requestTime) {
                        foreach($requestTime as $rtime){
                            $q->orWhere('advice_areas.request_time',$rtime);
                        }
                    });
                }
        })->where(function($query) use ($service_type){
                if(!empty($service_type)) {
                    $query->where(function($q) use ($service_type) {
                        foreach($service_type as $sitem){
                            $q->orWhere('advice_areas.service_type_id',$sitem);
                        }
                    });
                }
            
        })->where(function($query) use ($ltv_max){
            if($ltv_max != "") {
               
                $query->where('advice_areas.ltv_max','<=',chop($ltv_max,"%"));
                $query->where('advice_areas.ltv_max','>',0);
            }
        })->where(function($query) use ($lti_max){
            if($lti_max != "") {
                //  echo chop($ltv_max,"%");die;
                $query->where('advice_areas.lti_max','<=',chop($lti_max,"x"));
                $query->where('advice_areas.lti_max','>',0);
            }
        })->where(function($query) use ($service){
            if($service != 0) {
                $query->where('advice_areas.service_type_id',$service);
            }
        })->whereNotIn('advice_areas.id',function($query) use ($user){
            $query->select('area_id')->from('advisor_bids')->where('advisor_id','=',$user->id);
        });

        if(count($discountArr)>0){
            $advice_areaQuery = $advice_areaQuery->whereIn('advice_areas.id',$discountArr);
        }

        $advice_area = $advice_areaQuery->orWhereIn('advice_areas.id',$advice_area_arr)->orderBy('advice_areas.id','DESC')->with('total_bid_count')->groupBy('advice_areas.'.'id')
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
        ->groupBy('advice_areas.'.'advisor_preference_language')->orderBy('id','DESC')->get();
        // ->where('advisor_bids.status',0)
        $bidCountArr = array();
        //$lastquery = DB::getQueryLog();
        //dd(end($lastquery));
        //echo '<pre>=';print_r($advice_area);die;
        $getLeads = array();
        $check_data = 0;
        foreach($advice_area as $key=> $item) {
            $item->created_at_need = date("d-m-Y H:i",strtotime($item->created_at));
            $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3 )? 0:1;
            }
            $advice_area[$key]->totalBids = $bidCountArr;
            $advice_area[$key]->total_bids_count = count($item->total_bid_count);
            $costOfLead = ($item->size_want/100)*0.006;
            $time1 = Date('Y-m-d H:i:s');
            $time2 = Date('Y-m-d H:i:s',strtotime($item->created_at));
            $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
            $costOfLeadsStrWithCostOflead = "";
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $leadSummary = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');
            if($hourdiff < 24) {
                $costOfLeadsStr = " ".$item->size_want_currency.round($amount);
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.round($amount);
                $leadSummary = "This lead will cost ".$item->size_want_currency.round($amount);

                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.(round($amount/2))." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr = " ".$item->size_want_currency.(round($amount/2))." (Save 50%, was ".$item->size_want_currency.round($amount).")";
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.(round($amount/2))." (Save 50%, was ".$item->size_want_currency.round($amount).")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $leadSummary = "This lead will cost ".$item->size_want_currency.(round($amount/2));

                $costOfLeadsDropStr = "Cost of lead drops to ".(round($amount-$newAmount))." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr = " ".$item->size_want_currency.(round($amount-$newAmount))." (Save 75%, was ".$item->size_want_currency.round($amount).")";
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.(round($amount-$newAmount))." (Save 75%, was ".$item->size_want_currency.round($amount).")";
                $leadSummary = "This lead will cost ".$item->size_want_currency.(round($amount-$newAmount));

                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 72) {
                $costOfLeadsStr = ""."Free";
                $costOfLeadsStrWithCostOflead = "Cost of lead "."Free";
                $leadSummary = "This lead is free";

                $costOfLeadsDropStr = "";
            }
            if($user->free_promotions>0){
                $costOfLeadsStr = " ".$item->size_want_currency."0 - free introduction (Save 100%, was ".$item->size_want_currency.$amount.")";
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency."0 - free introduction (Save 100%, was ".$item->size_want_currency.round($amount).")";
                $leadSummary = "This lead is free";
            }
            $advice_area[$key]->is_accepted = 0;
            
            $advice_area[$key]->cost_of_lead = $costOfLeadsStr;
            $advice_area[$key]->cost_of_lead_with_cost = $costOfLeadsStrWithCostOflead;

            $advice_area[$key]->cost_of_lead_drop = $costOfLeadsDropStr;
            $advice_area[$key]->lead_summary = $leadSummary;

            $area_owner_details = User::where('id',$item->user_id)->first();
            $address = "";
            if(!empty($area_owner_details)) {
                $addressDetails = PostalCodes::where('Postcode','=',$area_owner_details->post_code)->first();
                if(!empty($addressDetails)) {
                    if($addressDetails->Country != ""){
                        $address = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
                        $address .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                        $address .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                    }
                    
                }
            }
            $lead_value = 0;
            $main_value = ($item->size_want/100);
            $advisorDetaultValue = "";
            $advisorDetaultPercent = 0;
            if($item->service_type_id!=0){
                $services = DefaultPercent::where('adviser_id',$user->id)->where('service_id',$item->service_type_id)->first();
                if($services){
                    $advisorDetaultPercent = $services->value_percent;
                }
            }
            $lead_value = ($main_value)*($advisorDetaultPercent);
            $advice_area[$key]->lead_value = $item->size_want_currency.number_format((int)round($lead_value),0);
            // if($item->service_type=="remortgage") {
            //     $advisorDetaultValue = "remortgage";
            // }else if($item->service_type=="first time buyer") {
            //     $advisorDetaultValue = "first_buyer";
            // }else if($item->service_type=="next time buyer") {
            //     $advisorDetaultValue = "next_buyer";
            // }else if($item->service_type=="buy to let") {
            //     $advisorDetaultValue = "but_let";
            // }else if($item->service_type=="equity release") {
            //     $advisorDetaultValue = "equity_release";
            // }else if($item->service_type=="overseas") {
            //     $advisorDetaultValue = "overseas";
            // }else if($item->service_type=="self build") {
            //     $advisorDetaultValue = "self_build";
            // }else if($item->service_type=="mortgage protection") {
            //     $advisorDetaultValue = "mortgage_protection";
            // }else if($item->service_type=="secured loan") {
            //     $advisorDetaultValue = "secured_loan";
            // }else if($item->service_type=="bridging loan") {
            //     $advisorDetaultValue = "bridging_loan";
            // }else if($item->service_type=="commercial") {
            //     $advisorDetaultValue = "commercial";
            // }else if($item->service_type=="something else") {
            //     $advisorDetaultValue = "something_else";
            // }   
            // $AdvisorPreferencesDefault = AdvisorPreferencesDefault::where('advisor_id','=',$user->id)->first();
            $advice_area[$key]->lead_address = $address;
            // $lead_value = ($main_value)*($AdvisorPreferencesDefault->$advisorDetaultValue);
            // $advice_area[$key]->lead_value = $item->size_want_currency.$lead_value;
            $advice_area[$key]->is_read = 0;
            $read = AdviceAreaRead::where('area_id',$item->id)->where('adviser_id','=',$user->id)->first();
            if($read){
                $advice_area[$key]->is_read = 1;
            }
            if($advice_area[$key]->total_bids_count<5){
                array_push($getLeads,$item);
            }
        }
        // $check_data = count($advice_area) - count($getLeads);
        $dataCheck = $this->arrayPaginator($getLeads,$request);
        return response()->json([
            'status' => true,
            'data' => $dataCheck->items(),
            'current_page' => $dataCheck->currentPage(),
            'first_page_url' => $dataCheck->url(1),
            'last_page_url' => $dataCheck->url($dataCheck->lastPage()),
            'per_page' => $dataCheck->perPage(),
            'next_page_url' => $dataCheck->nextPageUrl(),
            'prev_page_url' => $dataCheck->previousPageUrl(),
            'total' => $dataCheck->total(),
            'total_on_current_page' => $dataCheck->count(),
            'has_more_page' => $dataCheck->hasMorePages(),
            // 'current_page' => $dataCheck['current_page'],
            // 'first_page_url' => $dataCheck['first_page_url'],
            // 'last_page_url' => $dataCheck['last_page_url'],
            // 'per_page' => $dataCheck['per_page'],
            // 'next_page_url' => $dataCheck['next_page_url'],
            // 'prev_page_url' => $dataCheck['prev_page_url'],
            // 'total' => $dataCheck['total'],
            // 'total_on_current_page' => $dataCheck->last_page_url,
            // 'has_more_page' => $dataCheck->last_page_url,
        ], Response::HTTP_OK);
        // return response()->json([
        //     'status' => true,
        //     'pagei'=>$dataCheck,
        //     'check_data'=>$getLeads,
        //     'data' => $getLeads,
        //     'current_page' => $advice_area->currentPage(),
        //     'first_page_url' => $advice_area->url(1),
        //     'last_page_url' => $advice_area->url($advice_area->lastPage()),
        //     'per_page' => $advice_area->perPage(),
        //     'next_page_url' => $advice_area->nextPageUrl(),
        //     'prev_page_url' => $advice_area->previousPageUrl(),
        //     'total' => $advice_area->total(),
        //     'total_on_current_page' => $advice_area->count(),
        //     'has_more_page' => $advice_area->hasMorePages(),
        // ], Response::HTTP_OK);
    }

    public function arrayPaginator($array, $request)
    {
        $post = $request->all();
        $per_page_number = 15;
        $page = (isset($post['page']) && !empty($post['page'])) ? $post['page'] : 1;
        $perPage = (isset($post['perpage']))?$post['perpage']:$per_page_number;
        $offset = ($page * $perPage) - $perPage;
        $sliceArray = array_slice($array, $offset, $perPage, true);
        $finalArray = array();
        foreach ($sliceArray as $row) {
            array_push($finalArray, $row);
        }

        return new LengthAwarePaginator($finalArray, count($array), $perPage, $page,['path' => $request->url(), 'query' => $request->query()]);
    }

    function getNeedDetails(Request $request)
    {
        $post = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        $advisor = AdvisorProfile::where('advisorId',$user->id)->first();
        $bidCountArr = array();
        $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address')->with('service')
            ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')->where('advice_areas.id',$post['area_id'])->first();
        if($advice_area){
            $adviceBid = AdvisorBids::where('area_id',$advice_area->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3)? 0:1;
            }
            $advice_area->totalBids = $bidCountArr;
            
            $costOfLead = ($advice_area->size_want/100)*0.006;
            $time1 = Date('Y-m-d H:i:s');
            $time2 = Date('Y-m-d H:i:s',strtotime($advice_area->created_at));
            $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');
            if($hourdiff < 24) {
                $costOfLeadsStr = "".$advice_area->size_want_currency.$amount;
                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".$advice_area->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr = "".$advice_area->size_want_currency.($amount/2)." (Save 50%, was ".$advice_area->size_want_currency.$amount.")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr = "".($amount-$newAmount)." (Save 50%, was ".$advice_area->size_want_currency.$amount.")";
                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 72) {
                $costOfLeadsStr = ""."Free";
                $costOfLeadsDropStr = "";
            }
            $advice_area->is_accepted = 0;
            
            $advice_area->cost_of_lead = $costOfLeadsStr;
            $advice_area->cost_of_lead_drop = $costOfLeadsDropStr;
            $area_owner_details = User::where('id',$advice_area->user_id)->first();
            $address = "";
            if(!empty($area_owner_details)) {
                $addressDetails = PostalCodes::where('Postcode','=',$area_owner_details->post_code)->first();
                if(!empty($addressDetails)) {
                    if($addressDetails->Country != ""){
                        $address = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
                        $address .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                        $address .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                    }
                    
                }
            }
            $lead_value = "";
            $main_value = ($advice_area->size_want/100);
            $advisorDetaultValue = "";
            $advisorDetaultPercent = 0;
            if($advice_area->service_type_id!=0){
                $services = DefaultPercent::where('adviser_id',$user->id)->where('service_id',$advice_area->service_type_id)->first();
                if($services){
                    $advisorDetaultPercent = $services->value_percent;
                }else{
                    $advisorDetaultPercent = 0.30;
                }
            }else{
                $advisorDetaultPercent = 0.30;
            }
            $lead_value = ($main_value)*($advisorDetaultPercent);
            $advice_area->lead_value = $advice_area->size_want_currency.$lead_value;
            $advice_area->lead_address = $address;    
            $advice_area->is_read = 0;
            $read = AdviceAreaRead::where('area_id',$advice_area->id)->where('adviser_id','=',$user->id)->first();
            if($read){
                $advice_area->is_read = 1;
            }

            $channelIds = array(-1);
            $channelID = ChatChannel::where('advicearea_id',$advice_area->id)->orderBy('id','DESC')->get();
            foreach ($channelID as $chanalesR) {
                array_push($channelIds, $chanalesR->id);
            }
            $advice_area->last_notes = UserNotes::where('advice_id', '=', $advice_area->id)->where('user_id',$user->id)->get();
            $last_chat_data = ChatModel::whereIn('channel_id',$channelIds)->take(5)->orderBy('id','DESC')->with('from_user')->with('to_user')->get();
            if(isset($last_chat_data) && count($last_chat_data)){
                 foreach($last_chat_data as $chat){
                    $chat->show_name = "";
                    if($chat->from_user_id==$user->id){
                        if(isset($chat->from_user) && $chat->from_user){
                            $chat->show_name = "You";
                        }else{
                            $chat->show_name = $chat->from_user->name;
                        }
                    }else{
                        $chat->show_name = $chat->from_user->name;
                    }
                    // if($chat->to_user_id==$user->id){
                    //     if(isset($chat->to_user) && $chat->to_user){
                    //         $chat->from_user->show_name = $chat->from_user->name;
                    //     }
                    // }
                     if(date('Y-m-d')==date("Y-m-d",strtotime($chat->created_at))){
                        $chat->date_time = date("H:i",strtotime($chat->created_at));
                    }else{
                        $chat->date_time = date("d M Y H:i",strtotime($chat->created_at));
                    }
                 }
            }

            $advice_area->spam_info = AdviceAreaSpam::where('area_id',$advice_area->id)->where('user_id','=',$user->id)->first();
            
            $advice_area->last_chat = $last_chat_data;
        }
        return response()->json([
            'status' => true,
            'data' => $advice_area,
        ], Response::HTTP_OK);
    }


    function searchMortgageNeeds(Request $request){
        try{
            $user = JWTAuth::parseToken()->authenticate();
            $post = $request->all();
            $post['user_id'] = $user->id;
            $advice_area = Advice_area::getMatchNeedFilter($post);
            //echo json_encode($advice_area);exit;
            return response()->json([
                'status' => true,
                'data' => $advice_area->items(),
                'current_page' => $advice_area->currentPage(),
                'first_page_url' => $advice_area->url(1),
                'last_page_url' => $advice_area->url($advice_area->lastPage()),
                'per_page' => $advice_area->perPage(),
                'next_page_url' => $advice_area->nextPageUrl(),
                'prev_page_url' => $advice_area->previousPageUrl(),
                'total' => $advice_area->total(),
                'total_on_current_page' => $advice_area->count(),
                'has_more_page' => $advice_area->hasMorePages(),
            ], Response::HTTP_OK);
        }catch (\Exception $e) {
            // echo json_encode($e->getMessage());exit;
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    function searchAcceptedNeeds(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $message = $request->message;
        $status = $request->status;
        $advisor_id = $request->advice_area;
        $search = $request->search;
        $lead_submitted = $request->lead_submitted;
        $prospect = $request->prospect;
        
        $advisorAreaArr = array();
        
        if(isset($request->message) && count($request->message)>0){
            $advisorAreaArr = array(-1);
            foreach($request->message as $messages){
                if($messages=='unread'){
                    $chat = ChatModel::where('to_user_id',$user->id)->where('to_user_id_seen',0)->get();
                    foreach($chat as $chat_data){
                        $channel = ChatChannel::where('id',$chat_data->channel_id)->first();
                        if($channel){
                            array_push($advisorAreaArr,$channel->advicearea_id);
                        }
                    }
                }
            }
        }

        if(isset($request->status) && count($request->status)>0){
            $status_arr = array(-1);
            $no_res = array();
            $lost = array();
            $accept = array();
            $response = Advice_area::where('advisor_id',0)->get();
            foreach($response as $response_data){
                $accepted = AdvisorBids::where('advisor_id',$user->id)->where('area_id',$response_data->id)->where('status',0)->count();
                $hired = AdvisorBids::where('area_id',$response_data->id)->where('status',1)->count();
                if($accepted>0 && $hired==0){
                    array_push($no_res,$response_data->id);
                }
            }
            $AllMyBids = AdvisorBids::where('advisor_id',$user->id)->where('status',0)->get();
            if(count($AllMyBids)){
                foreach($AllMyBids as $bids){
                    $dataLost = Advice_area::where('id',$bids->area_id)->where('advisor_id','!=',$user->id)->first();
                    if($dataLost){
                        array_push($lost,$bids->area_id);
                    }
                }
            }
            $status_need = AdvisorBids::where('advisor_id',$user->id)->where('status',0)->where('advisor_status',1)->get();
            if(count($status_need)){
                foreach($status_need as $status_need_data){
                    array_push($accept,$status_need_data->area_id);
                }
            }
            foreach($request->status as $status_data){
                if($status_data=='accepted'){
                    $status_need = AdvisorBids::where('advisor_id',$user->id)->where('status',0)->where('advisor_status',1)->whereNotIn('area_id',$no_res)->whereNotIn('area_id',$lost)->get();
                    if(count($status_need)){
                        foreach($status_need as $status_need_data){
                            array_push($status_arr,$status_need_data->area_id);
                        }
                    }
                }
                if($status_data=='sole_adviser' || $status_data=='hired'){
                    $hired = AdvisorBids::where('advisor_id',$user->id)->where('status',1)->get();
                    if(count($hired)){
                        foreach($hired as $hired_data){
                            array_push($status_arr,$hired_data->area_id);
                        }
                    }
                }
                if($status_data=='completed'){
                    $completed = AdvisorBids::where('advisor_id',$user->id)->where('status',2)->where('advisor_status',1)->get();
                    if(count($completed)){
                        foreach($completed as $hired_data){
                            array_push($status_arr,$hired_data->area_id);
                        }
                    }
                }

                if($status_data=='lost'){
                    $AllMyBids = AdvisorBids::where('advisor_id',$user->id)->whereNotIn('area_id',$accept)->whereNotIn('area_id',$no_res)->where('status',0)->get();
                    if(count($AllMyBids)){
                        foreach($AllMyBids as $bids){
                            $dataLost = Advice_area::where('id',$bids->area_id)->where('advisor_id','!=',$user->id)->first();
                            if($dataLost){
                                array_push($status_arr,$bids->area_id);
                            }
                        }
                    }
                }
                if($status_data=='no_response'){

                    $response = Advice_area::where('advisor_id',0)->where('created_at','<',date("Y-m-d H:i:s",strtotime("- 14 days")))->get();
                    foreach($response as $response_data){
                        $accepted = AdvisorBids::where('advisor_id',$user->id)->where('area_id',$response_data->id)->where('status',0)->where('advisor_status',1)->count();
                        $hired = AdvisorBids::where('area_id',$response_data->id)->where('status',1)->count();
                        
                        $channelIds = array(-1);
                        $channelID = ChatChannel::where('advicearea_id',$response_data->id)->orderBy('id','DESC')->get();
                        foreach ($channelID as $chanalesR) {
                            array_push($channelIds, $chanalesR->id);
                        }                    
                        $chatCount = ChatModel::whereIn('channel_id',$channelIds)->count();;

                        if($chatCount==0 && $hired==0 && $accepted>0){
                            array_push($status_arr,$response_data->id);
                        }
                    }
                }
                
            }
            if(count($advisorAreaArr)>0){
                $advisorAreaArr = array_intersect($advisorAreaArr, $status_arr);
            }else{
                $advisorAreaArr = array_unique($status_arr);
            }
        }

        if(isset($request->advice_area) && count($request->advice_area)>0){
            $adviser_arr = array(-1);
            foreach($request->advice_area as $advisor_ids){
                $advisor_data = AdvisorBids::where('advisor_bids.advisor_id',$advisor_ids)->where('status',3)->where('advisor_status',2)->get();
                foreach($advisor_data as $advisor_profile_data){
                    array_push($adviser_arr,$advisor_profile_data->area_id);
                }
            }
            
            if(count($advisorAreaArr)>0){
                $advisorAreaArr = array_intersect($advisorAreaArr, $adviser_arr);
            }else{
                $advisorAreaArr = array_unique($adviser_arr);
            }
        }

        $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advice_areas.advisor_id')
        ->join('users', 'advice_areas.user_id', '=', 'users.id')
        ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
        ->where('advisor_bids.advisor_status', '=', 1)
        ->where(function($query) use ($search){
            if($search != "") {
                $query->orWhere('advice_areas.service_type', 'like', '%' . $search . '%');
                $query->orWhere('advice_areas.description', 'like', '%' . $search . '%');
                $query->orWhere('advice_areas.request_time', 'like', '%' . $search . '%');
                $query->orWhere('advice_areas.advisor_preference_language', 'like', '%' . $search . '%');
            }            
        })
        ->where(function($query) use ($lead_submitted){
            if(!empty($lead_submitted)) {
                $query->where(function($q) use ($lead_submitted) {
                    foreach($lead_submitted as $item ){
                        if($item=="three_month"){
                            $q->where('advice_areas.created_at','>=',date("Y-m-d",strtotime("- 3 month")));
                        }else if($item=="six_month") {
                            $q->where('advice_areas.created_at','>=',date("Y-m-d",strtotime("- 6 month")));
                        }else if($item=="last_year") {
                            $q->where('advice_areas.created_at','>=',date("Y-m-d",strtotime("- 12 month")));
                        }else if($item=="this_year") {
                            $q->where('advice_areas.created_at','>=',date("Y").'-01-01');
                        } 
                    }
                });
            }
        });
        if(count($advisorAreaArr)){
            $advice_area = $advice_area->whereIn('advice_areas.id',$advisorAreaArr);
        }

        $advice_area =  $advice_area->groupBy('advice_areas.'.'id')->with('service')->orderBy('advice_areas.id','DESC')->paginate();

        $bidCountArr = array();
        foreach($advice_area as $key=> $item) {
            $item->created_at_need = date("d-m-Y H:i",strtotime($item->created_at));
            $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3)? 0:1;
            }
            $adviceBidMainStatus = AdvisorBids::where('area_id',$item->id)->where('status','>','0')->orderBy('status','ASC')->first();
            if(!empty($adviceBidMainStatus)) {
                 $advice_area[$key]->bid_status = $adviceBidMainStatus->status;
            }else{
                 $advice_area[$key]->bid_status = 0;
            }
            $advice_area[$key]->totalBids = $bidCountArr;
            $advice_area[$key]->is_accepted = 1;
            $costOfLead = ($item->size_want/100)*0.006;
            $time1 = Date('Y-m-d H:i:s');
            $time2 = Date('Y-m-d H:i:s',strtotime($item->created_at));
            $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');
            if($hourdiff < 24) {
                $costOfLeadsStr = "".$item->size_want_currency.$amount;
                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr = "".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr = "".($amount-$newAmount)." (Save 50%, was ".$item->size_want_currency.$amount.")";
                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            }
            if($hourdiff > 72) {
                $costOfLeadsStr = ""."Free";
                $costOfLeadsDropStr = "";
            }
            
            $advice_area[$key]->cost_of_lead = $costOfLeadsStr;
            $advice_area[$key]->cost_of_lead_drop = $costOfLeadsDropStr;
            $area_owner_details = User::where('id',$item->user_id)->first();
            $address = "";
            if(!empty($area_owner_details)) {
                $addressDetails = PostalCodes::where('Postcode','=',$area_owner_details->post_code)->first();
                if(!empty($addressDetails)) {
                    if($addressDetails->Country != ""){
                        $address = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                        $address .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                        $address .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                    }
                    
                }
            }
            $lead_value = "";
            $main_value = ($item->size_want/100);
            $advisorDetaultValue = "";
            if($item->service_type=="remortgage") {
                $advisorDetaultValue = "remortgage";
            }else if($item->service_type=="first time buyer") {
                $advisorDetaultValue = "first_buyer";
            }else if($item->service_type=="next time buyer") {
                $advisorDetaultValue = "next_buyer";
            }else if($item->service_type=="buy to let") {
                $advisorDetaultValue = "but_let";
            }else if($item->service_type=="equity release") {
                $advisorDetaultValue = "equity_release";
            }else if($item->service_type=="overseas") {
                $advisorDetaultValue = "overseas";
            }else if($item->service_type=="self build") {
                $advisorDetaultValue = "self_build";
            }else if($item->service_type=="mortgage protection") {
                $advisorDetaultValue = "mortgage_protection";
            }else if($item->service_type=="secured loan") {
                $advisorDetaultValue = "secured_loan";
            }else if($item->service_type=="bridging loan") {
                $advisorDetaultValue = "bridging_loan";
            }else if($item->service_type=="commercial") {
                $advisorDetaultValue = "commercial";
            }else if($item->service_type=="something else") {
                $advisorDetaultValue = "something_else";
            }   
            $AdvisorPreferencesDefault = AdvisorPreferencesDefault::where('advisor_id','=',$user->id)->first();
            
            $advice_area[$key]->lead_address = $address;
            $lead_value = ($main_value)*($AdvisorPreferencesDefault->$advisorDetaultValue);
            $advice_area[$key]->lead_value = $item->size_want_currency.number_format($lead_value,0);
            $bidDetailsStatus = AdvisorBids::where('area_id',$item->id)->where('advisor_id','=',$user->id)->first();
            $bidDetailsStatus = AdvisorBids::where('area_id',$item->id)->where('advisor_id','=',$user->id)->first();
            // $show_status = 'Accepted';
            $show_status = 'Purchased';

            if($bidDetailsStatus){
                if($bidDetailsStatus->status==0 && $bidDetailsStatus->advisor_status==1){
                    // $show_status = "Accepted";
                    $show_status = "Purchased";

                }
                if($bidDetailsStatus->status==1 && $bidDetailsStatus->advisor_status==1){
                    $show_status = "Hired"; 
                }
                if($bidDetailsStatus->status==2 && $bidDetailsStatus->advisor_status==1){
                    $show_status = "Completed"; 
                }
                
                $checkBid = AdvisorBids::where('advisor_id',$user->id)->where('area_id',$item->id)->count();
                if($checkBid>0){
                    $dataLost = Advice_area::where('id',$item->id)->where('advisor_id','!=',$user->id)->where('advisor_id','!=',0)->first();
                    if($dataLost){
                        $show_status = "Lost";
                    }
                }

                if($item->created_at<date("Y-m-d H:i:s",strtotime("- 14 days")) && ($show_status=='Purchased' || $show_status=='')){
                    $channelIds = array(-1);
                    $channelID = ChatChannel::where('advicearea_id',$item->id)->orderBy('id','DESC')->get();
                    foreach ($channelID as $chanalesR) {
                        array_push($channelIds, $chanalesR->id);
                    }                    
                    $chatCount = ChatModel::whereIn('channel_id',$channelIds)->count();;
                    if($chatCount==0){
                        $show_status = "No Response";
                    }
                }
            }
            if(isset($advice_area[$key])){
                if(isset($advice_area[$key]->show_status))
                    $advice_area[$key]->show_status = (isset($show_status))?$show_status:'';
            }

            $channelIds = array(-1);
            $channelID = ChatChannel::where('advicearea_id',$item->id)->orderBy('id','DESC')->get();
            foreach ($channelID as $chanalesR) {
                array_push($channelIds, $chanalesR->id);
            }
            $advice_area[$key]->last_notes = UserNotes::where('advice_id', '=', $item->id)->where('user_id',$user->id)->get();
            $last_chat_data = ChatModel::whereIn('channel_id',$channelIds)->take(5)->orderBy('id','DESC')->with('from_user')->with('to_user')->get();
            if(isset($last_chat_data) && count($last_chat_data)){
                 foreach($last_chat_data as $chat){
                    $chat->show_name = "";
                    if($chat->from_user_id==$user->id){
                        if(isset($chat->from_user) && $chat->from_user){
                            $chat->show_name = "You";
                        }else{
                            $chat->show_name = $chat->from_user->name;
                        }
                    }else{
                        $chat->show_name = $chat->from_user->name;
                    }
                     if(date('Y-m-d')==date("Y-m-d",strtotime($chat->created_at))){
                        $chat->date_time = date("H:i",strtotime($chat->created_at));
                    }else{
                        $chat->date_time = date("d M Y H:i",strtotime($chat->created_at));
                    }
                 }
            }

            $advice_area[$key]->spam_info = AdviceAreaSpam::where('area_id',$item->id)->where('user_id','=',$user->id)->first();
            
            $advice_area[$key]->last_chat = $last_chat_data;
        }

        return response()->json([
            'status' => true,
            'data' => $advice_area->items(),
            'current_page' => $advice_area->currentPage(),
            'first_page_url' => $advice_area->url(1),
            'last_page_url' => $advice_area->url($advice_area->lastPage()),
            'per_page' => $advice_area->perPage(),
            'next_page_url' => $advice_area->nextPageUrl(),
            'prev_page_url' => $advice_area->previousPageUrl(),
            'total' => $advice_area->total(),
            'total_on_current_page' => $advice_area->count(),
            'has_more_page' => $advice_area->hasMorePages(),
        ], Response::HTTP_OK);
    }

    function acceptRejectBid(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $bidCount = AdvisorBids::where('area_id','=',$request->area_id)->count();
        $advisorCountBid = AdvisorBids::where('area_id','=',$request->area_id)->where('advisor_id','=',$user->id)->count();
        if($advisorCountBid > 0) {
            return response()->json([
                'status' => false,
                'message' => 'You already placed bid on this',
            ], Response::HTTP_OK);
        }else{
            if($bidCount < 5) {
                if ($request->advisor_status == 1) {
                    $advisorAreaDetails = Advice_area::where('id',$request->area_id)->first();
                    $costOfLead = ($advisorAreaDetails->size_want/100)*0.006;
                    $time1 = Date('Y-m-d H:i:s');
                    $time2 = Date('Y-m-d H:i:s',strtotime($advisorAreaDetails->created_at));
                    $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
                    $discounted_price = 0;
                    $is_discount = 0;
                    $discounted_cycle = "";
                    $amount = number_format((float)$costOfLead, 2, '.', '');
                    if($hourdiff < 24) {
                        $discounted_price = 0;
                        $discounted_cycle = "First cycle";
                        $is_discount = 0;
                    }
                    if($hourdiff > 24 && $hourdiff < 48) {
                        $discounted_price = number_format((float)($amount/2), 2, '.', '');
                        $discounted_cycle = "Second cycle";
                        $is_discount = 1;
                    }
                    if($hourdiff > 48 && $hourdiff < 72) {
                        $newAmount = (75 / 100) * $amount;
                        $discounted_price = number_format((float)($newAmount), 2, '.', '');
                        $discounted_cycle = "Third cycle";
                        $is_discount = 1;
                    }
                    if($hourdiff > 72) {
                        $discounted_price = number_format((float)($costOfLead), 2, '.', '');
                        $discounted_cycle = "Fourth cycle";
                        $is_discount = 1;
                    }
                    $free_into = 0;
                    if(isset($request->advisor_id) && $request->advisor_id){
                        $userData = User::where('id',$request->advisor_id)->first();
                        if($userData){
                            if($userData->free_promotions!=0){
                                $free_into = 1;
                                $discounted_cycle = "Free Introduction";
                                $is_discount = 1;
                                $discounted_price = number_format((float)($costOfLead), 2, '.', '');
                                $userData->free_promotions = $userData->free_promotions-1;
                                User::where('id',$request->advisor_id)->update(['free_promotions'=>$userData->free_promotions]);
                            }
                        }
                    }   
                    $bid_arr = array(
                        'discount_cycle'=>$discounted_cycle,
                        'is_discounted'=>$is_discount,
                        'advisor_id' => $request->advisor_id,
                        'area_id' => $request->area_id,
                        'advisor_status' => $request->advisor_status,
                        'cost_leads'=>$amount,
                        'cost_discounted'=>$discounted_price,
                        'free_introduction'=>$free_into,
                        'bid_created_date'=>$advisorAreaDetails->created_at
                    );
                    $advice_area = AdvisorBids::create($bid_arr);
                    if($advice_area){
                        $area = Advice_area::where('id',$request->area_id)->first();
                        if($area && $area->status==0){
                            Advice_area::where('id',$request->area_id)->update(['area_status'=>1]);
                        }
                    }
                   
                    $this->saveNotification(array(
                        'type'=>'1', // 1:
                        'message'=>'A new bid placed', // 1:
                        'read_unread'=>'0', // 1:
                        'user_id'=>$advisorAreaDetails->user_id,// 1:
                        'advisor_id'=>$request->advisor_id, // 1:
                        'area_id'=>$request->area_id,// 1:
                        'notification_to'=>0
                    ));
                    return response()->json([
                        'status' => true,
                        'message' => 'Lead purchased successfully',
                        'data'=>$bid_arr
                    ], Response::HTTP_OK);
                } else if ($request->advisor_status == 2) {
                    $advice_area = AdvisorBids::create([
                        'advisor_id' => $request->advisor_id,
                        'area_id' => $request->area_id,
                        'advisor_status' => $request->advisor_status,
                    ]);
                    $this->saveNotification(array(
                        'type'=>'1', // 1:
                        'message'=>'Not interest ', // 1:
                        'read_unread'=>'0', // 1:
                        'user_id'=>$user->id,// 1:
                        'advisor_id'=>$request->advisor_id, // 1:
                        'area_id'=>$request->area_id// 1:
                    ));
                    return response()->json([
                        'status' => true,
                        'message' => 'Mark as not intrested',
                    ], Response::HTTP_OK);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Maximum bids are placed.',
                ], Response::HTTP_OK);
            }
        }
        
        
    }
    public function inviteUsers(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $adviser = AdvisorProfile::where('advisorId',$user->id)->first();
        $post = $request->all();
        if (isset($request->emails) && !empty($request->emails)) {
            foreach($request->emails as $email_id){
                $invitedUrl = "";
                if(isset($post['user_role'])){
                    if($post['user_role']==1){
                        $invitedUrl = config('constants.urls.host_url');
                        $invitedUrl = $invitedUrl."/invite-advisor/" . $this->getEncryptedId($user->id)."?invitedToEmail=".$email_id;
                    }else{
                        $invitedUrl = config('constants.urls.host_url');
                        $invitedUrl = $invitedUrl."/invite/" . $this->getEncryptedId($user->id)."?invitedToEmail=".$email_id;
                    }
                }
                $newArr = array(
                    'name'=>$user->name,
                    'email'=>$email_id,
                    'url' => $invitedUrl
                );
                if($adviser){
                    $c = \Helpers::sendEmail('emails.invitation',$newArr ,$email_id,$user->name,$adviser->display_name.' has invited you to join Mortgagebox.co.uk','','');
                }else{
                    $c = \Helpers::sendEmail('emails.invitation',$newArr ,$email_id,$user->name,$user->name.' has invited you to join Mortgagebox.co.uk','','');
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Invite sent on mentioned emails.',
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'emails is required field.',
            ], Response::HTTP_OK);
        }
    }

    public function getAdviseAreaBid($id, $status)
    {
        $UserDetails = JWTAuth::parseToken()->authenticate();
        //$advice_area = AdvisorBids::where('area_id', '=', $id)->where('status', '=', $status)->get();
        $area = Advice_area::where('id',$id)->first();
        if($area){
            if($area->advisor_id!=0){
                if($status!=1){
                    $advice_area =  AdvisorBids::select('advisor_bids.*', 'users.name', 'users.email', 'users.address', 'advisor_profiles.display_name', 'advisor_profiles.tagline', 'advisor_profiles.FCANumber', 'advisor_profiles.company_name', 'advisor_profiles.phone_number', 'advisor_profiles.address_line1', 'advisor_profiles.address_line2', 'advisor_profiles.city', 'advisor_profiles.postcode', 'advisor_profiles.web_address', 'advisor_profiles.facebook', 'advisor_profiles.image', 'advisor_profiles.short_description')
                    ->join('users', 'advisor_bids.advisor_id', '=', 'users.id')
                    ->join('advisor_profiles', 'advisor_bids.advisor_id', '=', 'advisor_profiles.advisorId')
                    ->where('advisor_bids.area_id',$id)
                    ->whereNotIn('advisor_bids.status',[1,2])
                    // ->where('advisor_bids.advisor_status',1)
                    // ->where('advisor_bids.advisor_status', '=', $status)
                    ->get();
                }else{
                    $advice_area =  AdvisorBids::select('advisor_bids.*', 'users.name', 'users.email', 'users.address', 'advisor_profiles.display_name', 'advisor_profiles.tagline', 'advisor_profiles.FCANumber', 'advisor_profiles.company_name', 'advisor_profiles.phone_number', 'advisor_profiles.address_line1', 'advisor_profiles.address_line2', 'advisor_profiles.city', 'advisor_profiles.postcode', 'advisor_profiles.web_address', 'advisor_profiles.facebook', 'advisor_profiles.image', 'advisor_profiles.short_description')
                    ->join('users', 'advisor_bids.advisor_id', '=', 'users.id')
                    ->join('advisor_profiles', 'advisor_bids.advisor_id', '=', 'advisor_profiles.advisorId')
                    ->where('advisor_bids.area_id', '=', $id)
                    ->where('advisor_bids.status', $status)
                    ->where('advisor_bids.advisor_status',1)
                    // ->where('advisor_bids.advisor_status', '=', $status)
                    ->get();
                }
            }else{
                $advice_area =  AdvisorBids::select('advisor_bids.*', 'users.name', 'users.email', 'users.address', 'advisor_profiles.display_name', 'advisor_profiles.tagline', 'advisor_profiles.FCANumber', 'advisor_profiles.company_name', 'advisor_profiles.phone_number', 'advisor_profiles.address_line1', 'advisor_profiles.address_line2', 'advisor_profiles.city', 'advisor_profiles.postcode', 'advisor_profiles.web_address', 'advisor_profiles.facebook', 'advisor_profiles.image', 'advisor_profiles.short_description')
                ->join('users', 'advisor_bids.advisor_id', '=', 'users.id')
                ->join('advisor_profiles', 'advisor_bids.advisor_id', '=', 'advisor_profiles.advisorId')
                ->where('advisor_bids.area_id', '=', $id)
                ->where('advisor_bids.advisor_status', '=', $status)
                ->get();
            }
            if (count($advice_area)) {  
                if($area->advisor_id!=0){
                    if($status!=1){
                        $checkStatus = AdvisorBids::where('area_id',$id)->whereNotIn('status',[1,2])->count();
                    }else{
                        $checkStatus = AdvisorBids::where('area_id',$id)->where('status',1)->where('advisor_status',1)->count();
                    }
                }else{
                    $checkStatus = AdvisorBids::where('area_id',$id)->where('status',$status)->count();
                }              
                
                foreach ($advice_area as $key => $item) {
                    $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->area_id  AND m.to_user_id_seen = 0 AND m.to_user_id = $UserDetails->id AND m.from_user_id=$item->advisor_id");
                
                    $advice_area[$key]->unread_message_count = $unread_count_total[0]->count_message;
                    $last_activity = User::select('users.last_active')->where('id', '=', $item->advisor_id)->first();
                    $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
                    
                    ->Where('advisor_status','=',1)
                    ->Where('advisor_id','=',$item->advisor_id)
                    ->count();
                    $advice_area[$key]->used_by = $usedByMortage;
                    $advice_area[$key]->last_activity = $last_activity->last_active;
                    $advice_area[$key]->response_time = $this->getAdvisorResponseTime($item->advisor_id);
                    $rating =  ReviewRatings::select('review_ratings.*')
                    ->where('review_ratings.advisor_id', '=', $item->advisor_id)
                    ->where('review_ratings.status', '=', 0)
                    ->get();

                    $averageRating = ReviewRatings::where('review_ratings.advisor_id', '=', $item->advisor_id)->where('review_ratings.status', '=', 0)->avg('rating');

                    $advice_area[$key]->avarageRating = number_format((float)$averageRating, 2, '.', '');
                    $advice_area[$key]->rating = [
                        'total' => count($rating),
                    ];

                    if($advice_area[$key]->status==1 && $area->advisor_id==$advice_area[$key]->advisor_id){
                        // $advice_area[$key]->status_name = "Purchased";
                        $advice_area[$key]->status_name = "Adviser Selected";
                    }else if($advice_area[$key]->status==0 && $area->advisor_id!=$advice_area[$key]->advisor_id){
                        $advice_area[$key]->status_name = "Lost";
                    }
                    if($checkStatus!=0){
                        $advice_area[$key]->is_bided = 1;
                    }else{
                        $advice_area[$key]->is_bided = 0;
                    }
                    $itemComplete = AdvisorBids::orWhere('status',2)->where('advisor_status',1)->where('advisor_id',$item->advisor_id)->count();
                    $advice_area[$key]->total_completed_bids = $itemComplete;
                }
                return response()->json([
                    'status' => true,
                    'message' => 'success',
                    'data' => $advice_area
                ], Response::HTTP_OK);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Area Not found',
                    'data' => []
                ], Response::HTTP_UNAUTHORIZED);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Area Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
        // if($status!=1){
        //     $advice_area =  AdvisorBids::select('advisor_bids.*', 'users.name', 'users.email', 'users.address', 'advisor_profiles.display_name', 'advisor_profiles.tagline', 'advisor_profiles.FCANumber', 'advisor_profiles.company_name', 'advisor_profiles.phone_number', 'advisor_profiles.address_line1', 'advisor_profiles.address_line2', 'advisor_profiles.city', 'advisor_profiles.postcode', 'advisor_profiles.web_address', 'advisor_profiles.facebook', 'advisor_profiles.image', 'advisor_profiles.short_description')
        //     ->join('users', 'advisor_bids.advisor_id', '=', 'users.id')
        //     ->join('advisor_profiles', 'advisor_bids.advisor_id', '=', 'advisor_profiles.advisorId')
        //     ->where('advisor_bids.area_id',$id)
        //     ->whereNotIn('advisor_bids.status',[1,2])
        //     // ->where('advisor_bids.advisor_status',1)
        //     // ->where('advisor_bids.advisor_status', '=', $status)
        //     ->get();

        //     if ($advice_area) {
                
        //         $checkStatus = AdvisorBids::where('area_id',$id)->whereNotIn('status',[1,2])->count();
        //         foreach ($advice_area as $key => $item) {
        //             $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->area_id  AND m.to_user_id_seen = 0 AND m.to_user_id = $UserDetails->id AND m.from_user_id=$item->advisor_id");
                
        //             $advice_area[$key]->unread_message_count = $unread_count_total[0]->count_message;
        //             $last_activity = User::select('users.last_active')->where('id', '=', $item->advisor_id)->first();
        //             $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
                    
        //             ->Where('advisor_status','=',1)
        //             ->Where('advisor_id','=',$item->advisor_id)
        //             ->count();
        //             $advice_area[$key]->used_by = $usedByMortage;
        //             $advice_area[$key]->last_activity = $last_activity->last_active;
        //             $advice_area[$key]->response_time = $this->getAdvisorResponseTime($item->advisor_id);
        //             $rating =  ReviewRatings::select('review_ratings.*')
        //             ->where('review_ratings.advisor_id', '=', $item->advisor_id)
        //             ->where('review_ratings.status', '=', 0)
        //             ->get();

        //             $averageRating = ReviewRatings::where('review_ratings.advisor_id', '=', $item->advisor_id)->where('review_ratings.status', '=', 0)->avg('rating');

        //             $advice_area[$key]->avarageRating = number_format((float)$averageRating, 2, '.', '');
        //             $advice_area[$key]->rating = [
        //                 'total' => count($rating),
        //             ];

        //             if($advice_area[$key]->status==1){
        //                 $advice_area[$key]->status_name = "Purchased";
        //             }else{
        //                 $advice_area[$key]->status_name = "Offer Declined";
        //             }
        //             if($checkStatus!=0){
        //                 $advice_area[$key]->is_bided = 1;
        //             }else{
        //                 $advice_area[$key]->is_bided = 0;
        //             }
        //             $itemComplete = AdvisorBids::orWhere('status',2)->where('advisor_status',1)->where('advisor_id',$item->advisor_id)->count();
        //             $advice_area[$key]->total_completed_bids = $itemComplete;
        //         }
        //         return response()->json([
        //             'status' => true,
        //             'message' => 'success',
        //             'data' => $advice_area
        //         ], Response::HTTP_OK);
        //     } else {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Not found',
        //             'data' => []
        //         ], Response::HTTP_UNAUTHORIZED);
        //     }
        // }else{
        //     $advice_area =  AdvisorBids::select('advisor_bids.*', 'users.name', 'users.email', 'users.address', 'advisor_profiles.display_name', 'advisor_profiles.tagline', 'advisor_profiles.FCANumber', 'advisor_profiles.company_name', 'advisor_profiles.phone_number', 'advisor_profiles.address_line1', 'advisor_profiles.address_line2', 'advisor_profiles.city', 'advisor_profiles.postcode', 'advisor_profiles.web_address', 'advisor_profiles.facebook', 'advisor_profiles.image', 'advisor_profiles.short_description')
        //     ->join('users', 'advisor_bids.advisor_id', '=', 'users.id')
        //     ->join('advisor_profiles', 'advisor_bids.advisor_id', '=', 'advisor_profiles.advisorId')
        //     ->where('advisor_bids.area_id', '=', $id)
        //     ->where('advisor_bids.status', $status)
        //     ->where('advisor_bids.advisor_status',1)
        //     // ->where('advisor_bids.advisor_status', '=', $status)
        //     ->get();

        //     if ($advice_area) {
                
        //         $checkStatus = AdvisorBids::where('area_id',$id)->where('status',1)->where('advisor_status',1)->count();
        //         foreach ($advice_area as $key => $item) {
        //             $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->area_id  AND m.to_user_id_seen = 0 AND m.to_user_id = $UserDetails->id AND m.from_user_id=$item->advisor_id");
                
        //             $advice_area[$key]->unread_message_count = $unread_count_total[0]->count_message;
        //             $last_activity = User::select('users.last_active')->where('id', '=', $item->advisor_id)->first();
        //             $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
                    
        //             ->Where('advisor_status','=',1)
        //             ->Where('advisor_id','=',$item->advisor_id)
        //             ->count();
        //             $advice_area[$key]->used_by = $usedByMortage;
        //             $advice_area[$key]->last_activity = $last_activity->last_active;
        //             $advice_area[$key]->response_time = $this->getAdvisorResponseTime($item->advisor_id);
        //             $rating =  ReviewRatings::select('review_ratings.*')
        //             ->where('review_ratings.advisor_id', '=', $item->advisor_id)
        //             ->where('review_ratings.status', '=', 0)
        //             ->get();

        //             $averageRating = ReviewRatings::where('review_ratings.advisor_id', '=', $item->advisor_id)->where('review_ratings.status', '=', 0)->avg('rating');

        //             $advice_area[$key]->avarageRating = number_format((float)$averageRating, 2, '.', '');
        //             $advice_area[$key]->rating = [
        //                 'total' => count($rating),
        //             ];

        //             if($advice_area[$key]->status==1){
        //                 $advice_area[$key]->status_name = "Purchased";
        //             }else{
        //                 $advice_area[$key]->status_name = "Lost";
        //             }
        //             if($checkStatus!=0){
        //                 $advice_area[$key]->is_bided = 1;
        //             }else{
        //                 $advice_area[$key]->is_bided = 0;
        //             }
        //             $itemComplete = AdvisorBids::orWhere('status',2)->where('advisor_status',1)->where('advisor_id',$item->advisor_id)->count();
        //             $advice_area[$key]->total_completed_bids = $itemComplete;
        //         }
        //         return response()->json([
        //             'status' => true,
        //             'message' => 'success',
        //             'data' => $advice_area
        //         ], Response::HTTP_OK);
        //     } else {
        //         return response()->json([
        //             'status' => false,
        //             'message' => 'Not found',
        //             'data' => []
        //         ], Response::HTTP_UNAUTHORIZED);
        //     }
        // }
        
    }

    public function startChat(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $chatData = array();
        $channel_id  = 0;
        if(!isset($request->advicearea_id)){
            $channel_name = "channel" . "-" .$request->from_user_id . "-" . $request->to_user_id;
            $channelDetails =  ChatChannel::where('id',$request->channel_id)->first();
            // $getchatDetail = ChatChannel::where('from_user_id', $request->from_user_id)->where('to_user_id', $request->to_user_id)->get();
            // $channelDetails = $getchatDetail[0];
            // $channelExist =  ChatChannel::where('from_user_id', $request->to_user_id)->where('to_user_id', '=', $request->from_user_id)->first();
        }else{
            $channel_name = "channel" . "-" . $request->advicearea_id . "-" . $request->from_user_id . "-" . $request->to_user_id;
            $channelDetails =  ChatChannel::where('advicearea_id',$request->advicearea_id)->where('from_user_id', '=', $request->from_user_id)->where('to_user_id', '=', $request->to_user_id)->orderBy('id','DESC')->first();
            $channelExist =  ChatChannel::where('advicearea_id',$request->advicearea_id)->where('from_user_id', '=', $request->to_user_id)->where('to_user_id', '=', $request->from_user_id)->first();
        }
        
        if (empty($channelDetails) && empty($channelExist)) {
            $channel = ChatChannel::create([
                'from_user_id' => $request->from_user_id,
                'to_user_id' => $request->to_user_id,
                'channel_name' => $channel_name,
                'advicearea_id' => $request->advicearea_id
            ]);
            $channel_id  = $channel->id;
        } else {
            if (!empty($channelDetails)) {
                $channel_id = $channelDetails->id;
                $channel_name = $channelDetails->channel_name;
            } else if (!empty($channelExist)) {
                $channel_id = $channelExist->id;
                $channel_name = $channelExist->channel_name;
            }
        }

        //For get unseen messages counts Only
        if ($request->type && $request->type == 1) {
            $chatData = ChatModel::where('channel_id', '=', $channel_id)->where('to_user_id', '=', $user->id)->where('to_user_id_seen', '=', 0)->orderBy('id', 'asc')->count();
        } else {
            $chatData = ChatModel::where('channel_id', '=', $channel_id)->orderBy('id', 'asc')->get();
            if (count($chatData)) {
                ChatModel::where('to_user_id', '=', $user->id)->where('channel_id', '=', $channel_id)->where('to_user_id_seen', '=', 0)->update(
                    [
                        'to_user_id_seen' => 1
                    ]

                );
            }
        }
        // $newArr = array(
        //     'name'=>$user->name,
        //     'email'=>$user->email,
        //     'message_text' => "You have received the following message from ".$user->name
        // );
        // $display_name = "";
        // $advisor_data = AdvisorProfile::where('advisorId',$user->id)->first();
        // if($advisor_data){
        //     $display_name = $advisor_data->display_name;
        // }
        // $c = \Helpers::sendEmail('emails.information',$newArr ,$user->email,$display_name,'Mortgagebox.co.uk – Message received from '.$user->name,'','');
        return response()->json([
            'status' => true,
            'channel' => ['channel_id' => $channel_id, 'channel_name' => $channel_name],
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    public function sendMessage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $chatData = array();
        $advisor = AdvisorProfile::where('advisorId',$request->to_user_id)->first();
        $advisor_user = User::where('id',$request->to_user_id)->first();
        $message = ChatModel::create([
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'channel_id' => $request->channel_id,
            'text' => $request->text,

        ]);
        $message_id  = $message->id;

        $chatData = ChatModel::where('id', '=', $message_id)->orderBy('id', 'desc')->first();
        $channel = ChatChannel::where('id', '=', $request->channel_id)->first();
        $area_id = 0;
        if($channel){
            $area_id = $channel->area_id;
        }
        if($area_id=='' || $area_id==null){
            $area_id = 0;
        }
        
        $message = 'New message arrived from '.$user->name;
        // $this->saveNotification(array(
        //     'type'=>'1', // 1:
        //     'message'=>'New message arrived from '.$user->name, // 1:
        //     'read_unread'=>'0', // 1:
        //     'user_id'=>$request->from_user_id,// 1:
        //     'advisor_id'=>$request->to_user_id, // 1:
        //     'area_id'=>$area_id,// 1:
        //     'notification_to'=>1
        // ));
        $display_name = "";
        if($advisor){
            $display_name = $advisor->display_name;
        }else{
            $display_name = $advisor_user->name;

        }
        $newArr = array(
            'name'=>$display_name,
            // 'name'=>$display_name,
            'email'=>$advisor_user->email,
            'message_text' => $message,
            'sent_message' => $request->text
        );
        $c = \Helpers::sendEmail('emails.information',$newArr ,$advisor_user->email,$display_name,'New message received from '.$user->name,'','');
        return response()->json([
            'status' => true,
            'channel' => $request->channel_id,
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    function advisorAcceptedLeads(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $post['user_id'] = $user->id;
        if(isset($_GET['lead']) && $_GET['lead']){
            $post['lead'] = $_GET['lead'];
        }
        if(isset($_GET['time']) && $_GET['time']){
            $post['time'] = $_GET['time'];
        }
        if(isset($request->status) && $request->status!=''){
            $explode = explode(",",$request->status);
            $post['status'] = $explode;
        }
        $advice_area =  Advice_area::getAcceptedLeads($post);
        // return response()->json([
        //     'status' => true,
        //     'data' => $advice_area,
        // ], Response::HTTP_OK);
        $bidCountArr = array();
        foreach($advice_area as $key=> $item) {
            $item->created_at_need = date("d-m-Y H:i",strtotime($item->created_at));
            $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                if($advice_area[$key]->area_status==2){
                    // if($bid->status == 3){
                    //     array_push($bidCountArr,0);
                    // }elseif($bid->status == 0){
                    //     array_push($bidCountArr,0);
                    // }else{
                    //     array_push($bidCountArr,1);
                    // }

                    $bidCountArr[] = ($bid->status == 3 || $bid->status == 0)? 0:1;
                    // $bidCountArr[] = [1,2,3,4,5];
                }else{
                    $bidCountArr[] = ($bid->status == 3)? 0:1;
                }
            }
            $adviceBidMainStatus = AdvisorBids::where('area_id',$item->id)->where('status','>','0')->orderBy('status','ASC')->first();
            if(!empty($adviceBidMainStatus)) {
                 $advice_area[$key]->bid_status = $adviceBidMainStatus->status;
            }else{
                 $advice_area[$key]->bid_status = 0;
            }
            $advice_area[$key]->totalBids = $bidCountArr;
            $advice_area[$key]->total_bids_count = count($item->total_bid_count);
            $advice_area[$key]->total_lost_bids_count = count($item->total_lost_bid_count);
            if($item->advisor_id!=0){
                $advice_area[$key]->total_lost_bids_count = count($item->total_lost_bid_count);
            }
            $advice_area[$key]->is_accepted = 1;
            $costOfLead = ($item->size_want/100)*0.006;
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $MyBid = AdvisorBids::where('area_id',$item->id)->where('advisor_id',$user->id)->first();
            // if($MyBid){
            //     if($MyBid->discount_cycle=='First cycle'){
            //         $costOfLeadsStr = "".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($MyBid->cost_leads/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            //     }
            //     if($MyBid->discount_cycle=='Second cycle'){
            //         $costOfLeadsStr = "".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($MyBid->cost_leads/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            //     }
            //     if($MyBid->discount_cycle=='Third cycle'){
            //         $costOfLeadsStr = "".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($MyBid->cost_leads/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            //     }
            //     if($MyBid->discount_cycle=='Fourth cycle'){
            //         $costOfLeadsStr = "".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($MyBid->cost_leads/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            //     }
            //     if($MyBid->discount_cycle=='Free Introduction'){
            //         $costOfLeadsStr = "".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$MyBid->cost_leads;
            //         $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($MyBid->cost_leads/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            //     }
            // }
            // if($hourdiff < 24) {
            //     $costOfLeadsStr = " ".$item->size_want_currency.$amount;
            //     $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.$amount;

            //     $in = 24-$hourdiff;
            //     $hrArr = explode(".",$in);
            //     $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.($amount/2)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            // }
            // if($hourdiff > 24 && $hourdiff < 48) {
            //     $costOfLeadsStr = " ".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
            //     $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.($amount/2)." (Save 50%, was ".$item->size_want_currency.$amount.")";
            //     $in = 48-$hourdiff;
            //     $newAmount = (75 / 100) * $amount;
            //     $hrArr = explode(".",$in);
            //     $costOfLeadsDropStr = "Cost of lead drops to ".($amount-$newAmount)." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            // }
            // if($hourdiff > 48 && $hourdiff < 72) {
            //     $newAmount = (75 / 100) * $amount;
            //     $costOfLeadsStr = " ".$item->size_want_currency.($amount-$newAmount)." (Save 75%, was ".$item->size_want_currency.$amount.")";
            //     $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.($amount-$newAmount)." (Save 75%, was ".$item->size_want_currency.$amount.")";

            //     $in = 72-$hourdiff;
            //     $hrArr = explode(".",$in);
            //     $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
            // }
            // if($hourdiff > 72) {
            //     $costOfLeadsStr = ""."Free";
            //     $costOfLeadsStrWithCostOflead = "Cost of lead "."Free";

            //     $costOfLeadsDropStr = "";
            // }
            // if($user->free_promotions>0){
            //     $costOfLeadsStr = " ".$item->size_want_currency."0 - free introduction (Save 100%, was ".$item->size_want_currency.$amount.")";
            //     $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency."0 - free introduction (Save 100%, was ".$item->size_want_currency.$amount.")";

            // }

            $time2 = Date('Y-m-d H:i:s',strtotime($item->created_at));
            $time1 = Date('Y-m-d H:i:s',strtotime($item->bid_created_date));
            $hourdiff = round((strtotime($time1) - strtotime($time2))/3600, 1);
            $costOfLeadsStr = "";
            $costOfLeadsDropStr = "";
            $amount = number_format((float)$costOfLead, 2, '.', '');
            if($hourdiff < 24) {
                $costOfLeadsStr = "".$item->size_want_currency.round($amount);
                $in = 24-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".$item->size_want_currency.(round($amount/2))." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.round($amount);
            }
            if($hourdiff > 24 && $hourdiff < 48) {
                $costOfLeadsStr = "".$item->size_want_currency.(round($amount/2))." (Saved 50%, was ".$item->size_want_currency.round($amount).")";
                $in = 48-$hourdiff;
                $newAmount = (75 / 100) * $amount;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to ".(round($amount-$newAmount))." in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.(round($amount/2))." (Saved 50%, was ".$item->size_want_currency.round($amount).")";
            }
            if($hourdiff > 48 && $hourdiff < 72) {
                $newAmount = (75 / 100) * $amount;
                $costOfLeadsStr = "".(round($amount-$newAmount))." (Saved 75%, was ".$item->size_want_currency.round($amount).")";
                $in = 72-$hourdiff;
                $hrArr = explode(".",$in);
                $costOfLeadsDropStr = "Cost of lead drops to Free in ".(isset($hrArr[0])? $hrArr[0]."h":'0h')." ".(isset($hrArr[1])? $hrArr[1]."m":'0m');
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency.(round($amount-$newAmount))." (Saved 75%, was ".$item->size_want_currency.round($amount).")";
            }
            if($hourdiff > 72) {
                $costOfLeadsStr = ""."Free";
                $costOfLeadsDropStr = "";
                $costOfLeadsStrWithCostOflead = "Cost of lead Free";
            }
            if($MyBid->discount_cycle=='Free Introduction'){
                $costOfLeadsStr = " ".$item->size_want_currency."0 - free introduction (Saved 100%, was ".$item->size_want_currency.round($amount).")";
                $costOfLeadsStrWithCostOflead = "Cost of lead ".$item->size_want_currency."0 - free introduction (Saved 100%, was ".$item->size_want_currency.round($amount).")";
            }
            
            $advice_area[$key]->cost_of_lead = $costOfLeadsStr;
            $advice_area[$key]->cost_of_lead_drop = $costOfLeadsDropStr;
            $advice_area[$key]->cost_of_lead_with_cost = $costOfLeadsStrWithCostOflead;
            $area_owner_details = User::where('id',$item->user_id)->first();
            $address = "";
            if(!empty($area_owner_details)) {
                $addressDetails = PostalCodes::where('Postcode','=',$area_owner_details->post_code)->first();
                if(!empty($addressDetails)) {
                    if($addressDetails->Country != ""){
                        $address = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
                        $address .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                        $address .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                    }
                    
                }
            }
            $lead_value = 0;
            $main_value = ($item->size_want/100);
            $advisorDetaultValue = "";
            $advisorDetaultPercent = 0;
            if($item->service_type_id!=0){
                $services = DefaultPercent::where('adviser_id',$user->id)->where('service_id',$item->service_type_id)->first();
                if($services){
                    $advisorDetaultPercent = $services->value_percent;
                }
            }
            $lead_value = ($main_value)*($advisorDetaultPercent);
            $advice_area[$key]->lead_value = $item->size_want_currency.number_format((int)round($lead_value),0);
                        
            $advice_area[$key]->lead_address = $address;
            $show_status = ""; 
            $bidDetailsStatus = AdvisorBids::where('area_id',$item->id)->where('advisor_id',$user->id)->first();
            if($bidDetailsStatus){
                if($bidDetailsStatus->status==0 && $bidDetailsStatus->advisor_status==1){
                    $show_status = "Purchased";
                }
                if($bidDetailsStatus->status==1 && $bidDetailsStatus->advisor_status==1){
                    $show_status = "Hired"; 
                }
                if($bidDetailsStatus->status==2 && $bidDetailsStatus->advisor_status==1){
                    $show_status = "Completed"; 
                }
                // if($bidDetailsStatus->status==4 && $bidDetailsStatus->advisor_status==1){
                //     $show_status = "Completed"; 
                // }

                $checkBid = AdvisorBids::where('advisor_id',$user->id)->where('area_id',$item->id)->count();
                if($checkBid>0){
                    $dataLost = Advice_area::where('id',$item->id)->where('advisor_id','!=',$user->id)->where('advisor_id','!=',0)->first();
                    if($dataLost){
                        $show_status = "Lost";
                    }else{
                        $dataLostManual = Advice_area::where('id',$item->id)->where('advisor_id',$user->id)->where('area_status',4)->first();
                        if($dataLostManual){
                            $show_status = "Lost";
                        }
                    }
                }

                if($item->created_at<date("Y-m-d H:i:s",strtotime("- 14 days")) && ($show_status=='Purchased' || $show_status=='') ){
                    $channelIds = array(-1);
                    $channelID = ChatChannel::where('advicearea_id',$item->id)->orderBy('id','DESC')->get();
                    foreach ($channelID as $chanalesR) {
                        array_push($channelIds, $chanalesR->id);
                    }                    
                    $chatCount = ChatModel::whereIn('channel_id',$channelIds)->count();
                    if($chatCount==0){
                        $show_status = "No Response";
                    }else{
                        if($item->area_status==5){
                            $show_status = "No Response";
                        }
                    }
                }else{
                    if($item->area_status==5){
                        $show_status = "No Response";
                    }
                }
            }
            
            $advice_area[$key]->show_status = (isset($show_status))?$show_status:'';
            $channelIds = array(-1);
            $channelID = ChatChannel::where('advicearea_id',$item->id)->orderBy('id','DESC')->get();
            foreach ($channelID as $chanalesR) {
                array_push($channelIds, $chanalesR->id);
            }
            $advice_area[$key]->last_notes = UserNotes::where('advice_id', '=', $item->id)->where('user_id',$user->id)->get();
            $last_chat_data = ChatModel::whereIn('channel_id',$channelIds)->take(5)->orderBy('id','DESC')->with('from_user')->with('to_user')->get();
            if(isset($last_chat_data) && count($last_chat_data)){
                 foreach($last_chat_data as $chat){
                    $chat->show_name = "";
                    if($chat->from_user_id==$user->id){
                        if(isset($chat->from_user) && $chat->from_user){
                            $chat->show_name = "You";
                        }else{
                            $chat->show_name = $chat->from_user->name;
                        }
                    }else{
                        $chat->show_name = $chat->from_user->name;
                    }
                    // if($chat->to_user_id==$user->id){
                    //     if(isset($chat->to_user) && $chat->to_user){
                    //         $chat->from_user->show_name = $chat->from_user->name;
                    //     }
                    // }
                     if(date('Y-m-d')==date("Y-m-d",strtotime($chat->created_at))){
                        $chat->date_time = date("H:i",strtotime($chat->created_at));
                    }else{
                        $chat->date_time = date("d M Y H:i",strtotime($chat->created_at));
                    }
                 }
            }

            $advice_area[$key]->spam_info = AdviceAreaSpam::where('area_id',$item->id)->where('user_id','=',$user->id)->first();
            
            $advice_area[$key]->last_chat = $last_chat_data;
        }

        return response()->json([
            'status' => true,
            // 'data' => $advice_area,
            'data' => $advice_area->items(),
            'current_page' => $advice_area->currentPage(),
            'first_page_url' => $advice_area->url(1),
            'last_page_url' => $advice_area->url($advice_area->lastPage()),
            'per_page' => $advice_area->perPage(),
            'next_page_url' => $advice_area->nextPageUrl(),
            'prev_page_url' => $advice_area->previousPageUrl(),
            'total' => $advice_area->total(),
            'total_on_current_page' => $advice_area->count(),
            'has_more_page' => $advice_area->hasMorePages(),
        ], Response::HTTP_OK);
    }

    function getRecentMessages()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $showChats = array();
        $channel_id  = 0;
        $chatData = ChatModel::where('from_user_id',$user->id)->orWhere('to_user_id',$user->id)->with('from_user')->with('to_user')->orderBy('id','DESC')->groupBy('channel_id')->get();
        
        foreach($chatData as $chatData_data){
            $channel_data = ChatChannel::where('id',$chatData_data->channel_id)->first();
            if($channel_data){
                $chatData_data->area_data = Advice_area::where('id',$channel_data->advicearea_id)->with('service')->first();
                if($chatData_data->area_data){
                    $chatData_data->area_data->size_want = number_format($chatData_data->area_data->size_want,0);
                }
            }
            $chatData_data->showChats = ChatModel::where('channel_id', '=', $chatData_data->channel_id)->orderBy('id', 'DESC')->first();
            if($chatData_data->showChats!=''){
                $chatData_data->lastMessage = $chatData_data->showChats->text;
            }else{
                $chatData_data->lastMessage = "";
            }
            
        }
        return response()->json([
            'status' => true,
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    function seenMessages(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $advisorDetails = ChatModel::where('to_user_id', '=', $user->id)->where('channel_id', '=', $request->channel_id)->where('to_user_id_seen', '=', 0)->update(
            [
                'to_user_id_seen' => 1
            ]

        );
        return response()->json([
            'status' => true,
        ], Response::HTTP_OK);
    }

    function sendAttachment(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $message_type = 2;
        if ($request->hasFile('image')) {
            $images = $request->file('image');
            $request->image = time() . rand() .'.'.$images->getClientOriginalExtension();
            $destinationPath = public_path('/upload/chat/');
            $images->move($destinationPath, $request->image);
            if($images->getClientOriginalExtension()=='jpg' || $images->getClientOriginalExtension()=='jpeg' || $images->getClientOriginalExtension()=='png' || $images->getClientOriginalExtension()=='gif'){
                $message_type = 1;
            }else if($images->getClientOriginalExtension()=='xml'){
                $message_type = 2;
            }else if($images->getClientOriginalExtension()=='pdf'){
                $message_type = 3;
            }else if($images->getClientOriginalExtension()=='csv'){
                $message_type = 4;
            }else if($images->getClientOriginalExtension()=='zip'){
                $message_type = 6;
            }else{
                $message_type = 7;
            }
            // $uploadFolder = 'chat';
            // $image = $request->file('image');
            // // $image_uploaded_path = $image->store($uploadFolder, 'public');
            // $name = $request->file('image')->getClientOriginalName();
            // $extension = $request->file('image')->extension();
            // $originalString = str_replace("." . $extension, "", $name);
            // //$upfileName = preg_replace('/\s+/', '_', $originalString).".".$extension;
            // $upfileName = $name;

            // $num = 1;


            // while (Storage::exists("public/" . $uploadFolder . "/" . $upfileName)) {
            //     $file_name = (string) $originalString . "-" . $num;
            //     $upfileName = $file_name . "." . $extension;
            //     $num++;
            // }
            // $image_uploaded_path = $image->storeAs($uploadFolder, $upfileName, 'public');
            // $request->image = basename($image_uploaded_path);


            // $uploadedImageResponse = array(
            //     "image_name" => basename($image_uploaded_path),
            //     "image_url" => Storage::disk('public')->url($image_uploaded_path),
            //     "mime" => $image->getClientMimeType()
            // );

            // if ($uploadedImageResponse['mime'] == "image/png" || $uploadedImageResponse['mime'] == "image/jpeg" || $uploadedImageResponse['mime'] == "image/jpg" || $uploadedImageResponse['mime'] == "image/gif") {
            //     $message_type = 1;
            // } else if ($uploadedImageResponse['mime'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
            //     $message_type = 2;
            // } else if ($uploadedImageResponse['mime'] == "application/pdf") {
            //     $message_type = 3;
            // } else if ($uploadedImageResponse['mime'] == "application/csv") {
            //     $message_type = 4;
            // } else if ($uploadedImageResponse['mime'] == "application/zip") {
            //     $message_type = 6;
            // } else {
            //     $message_type = 7;
            // }
        }
        // $uploadedImageResponse['image_url'];
        $chatData = array();
        $message = ChatModel::create([
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'channel_id' => $request->channel_id,
            'attachment' => $request->image,
            'message_type' => $message_type

        ]);
        $message_id  = $message->id;

        $chatData = ChatModel::where('id', '=', $message_id)->orderBy('id', 'desc')->first();
        return response()->json([
            'status' => true,
            'channel' => $request->channel_id,
            'data' => $chatData,
            'image_data'=>$images
        ], Response::HTTP_OK);
    }

    public function addOffer(Request $request)
    {
        $userDetails = JWTAuth::parseToken()->authenticate();

        $offers = AdvisorOffers::create([
            'advisor_id' => $userDetails->id,
            'offer' => $request->offer,
            'description' => $request->description,
            'status' => 1
        ])->id;
        //User created, return success response
        $chatData = AdvisorOffers::get();
        return response()->json([
            'status' => true,
            'message' => 'Offer added successfully',
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    public function editOffer(Request $request, $id)
    {

        $userDetails = JWTAuth::parseToken()->authenticate();

        $offers = AdvisorOffers::where('offer_id', '=', $id)->update([
            'advisor_id' => $userDetails->id,
            'offer' => $request->offer,
            'description' => $request->description,
            'status' => 1
        ]);


        //User created, return success response
        $chatData = AdvisorOffers::get();
        return response()->json([
            'status' => true,
            'message' => 'Offer updated successfully',
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    public function deleteOffer(Request $request, $id)
    {

        $userDetails = JWTAuth::parseToken()->authenticate();
        $offers = AdvisorOffers::where('offer_id', '=', $id)->delete();
        //User created, return success response
        $chatData = AdvisorOffers::get();
        return response()->json([
            'status' => true,
            'message' => 'Offer deleted successfully',
            'data' => $chatData
        ], Response::HTTP_OK);
    }
    //for user

    function getNotificationPreferences()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = NotificationsPreferences::where('user_id', '=', $user->id)->first();
        if (empty($notification)) {
            NotificationsPreferences::create([
                'new_lead'=>0,
                'newslatter'=>1,
                'direct_contact'=>1,
                'monthly_invoice'=>1,
                'direct_message'=>1,
                'accept_offer'=>1,
                'decline_offer'=>1,
                'lead_match'=>1,
                'review'=>1,
                'promotional'=>1,
                'user_id' => $user->id
            ]);
        }
        $notification = NotificationsPreferences::where('user_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }

    function updateNotificationPreferences(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        NotificationsPreferences::where('user_id', '=', $user->id)->update([
            'new_lead' => $request->new_lead,
            'newslatter' => $request->newslatter,
            'direct_contact' => $request->direct_contact,
            'monthly_invoice' => $request->monthly_invoice,
            'direct_message' => $request->direct_message,
            'accept_offer' => $request->accept_offer,
            'decline_offer' => $request->decline_offer,
            'lead_match' => $request->lead_match,
            'review' => $request->review,
            'promotional' => $request->promotional

        ]);
        $notification = NotificationsPreferences::where('user_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }

    public function setRecentMessagesOfChatToRead(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        ChatModel::where('to_user_id', '=', $user->id)->where('channel_id', '=', $request->channel_id)->where('to_user_id_seen', '=', 0)->update(
            [
                'to_user_id_seen' => 1
            ]
        );

        return response()->json([
            'status' => true,
            'data' => []
        ], Response::HTTP_OK);
    }

    public function setRecentMessagesOfAllChatToRead(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        ChatModel::where('to_user_id', '=', $user->id)->where('to_user_id_seen', '=', 0)->update(
            [
                'to_user_id_seen' => 1
            ]
        );

        return response()->json([
            'status' => true,
            'data' => []
        ], Response::HTTP_OK);
    }

    public function selectOrDeclineOffer($id,$status) {
        //1:accepted, 3: rejected
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorBids::where('id', '=', $id)->update([
            'status'=>$status
        ]);
        $bidDetails = AdvisorBids::where('id', '=', $id)->first();
        $message = "";
        if($status==1){
            $advice_area = Advice_area::where('id',$bidDetails->area_id)->first();
            $service = ServiceType::where('id',$advice_area->service_type_id)->first();
            Advice_area::where('id',$bidDetails->area_id)->update(['area_status'=>2,'advisor_id'=>$bidDetails->advisor_id]);
            $message = "Offer accepted";
            $this->saveNotification(array(
                'type'=>'1', // 1:
                'message'=>'Your bid accepted by customer '.$user->name, // 1:
                'read_unread'=>'0', // 1:
                'user_id'=>$user->id,// 1:
                'advisor_id'=>$bidDetails->advisor_id, // 1:
                'area_id'=>$bidDetails->area_id,// 1:
                'notification_to'=>1
            ));
            $advisor = AdvisorProfile::where('advisorId',$bidDetails->advisor_id)->first();
            $newArr = array(
                'name'=>$advisor->display_name,
                'email'=>$advisor->email,
                'message_text' => 'Congratulations, you have been selected by '.$user->name.' to arrange their £'.number_format($advice_area->size_want).' '.$service->name.' mortgage. This is required '.$advice_area->request_time,
                'url'=>config('constants.urls.host_url')."/adviser?type=Login",
                'btn_text'=>'Respond'
            );
            $c = \Helpers::sendEmail('emails.information',$newArr ,$advisor->email,$advisor->display_name,'Mortgagebox.co.uk – £'.number_format($advice_area->size_want).' Lead won from '.$user->name,'','');
            $bids = AdvisorBids::where('area_id',$bidDetails->area_id)->where('advisor_id','!=',$bidDetails->advisor_id)->get();
            if(count($bids)){
                foreach($bids as $bids_data){
                    $advisorDecline = AdvisorProfile::where('advisorId',$bids_data->advisor_id)->first();
                    $this->saveNotification(array(
                        'type'=>'2', // 1:
                        'message'=>'Your offer to help '.$user->name.' has unfortunately been unsuccessful', // 1:
                        'read_unread'=>'0', // 1:
                        'user_id'=>$user->id,// 1:
                        'advisor_id'=>$bids_data->advisor_id, // 1:
                        'area_id'=>$bidDetails->area_id,// 1:
                        'notification_to'=>1
                    ));
                    $newArrDec = array(
                        'name'=>$advisorDecline->display_name,
                        'email'=>$advisorDecline->email,
                        'message_text' => 'Your offer to help '.$user->name.' has unfortunately been unsuccessful'
                    );
                    $c = \Helpers::sendEmail('emails.information',$newArrDec ,$advisorDecline->email,$advisorDecline->display_name,'MortgageBox Lost Bid','','');
                }
            }
        }else{
            $message = "Offer declined";
            $advisor = AdvisorProfile::where('advisorId',$bidDetails->advisor_id)->first();
            $this->saveNotification(array(
                'type'=>'2', // 1:
                'message'=>'Your bid declined by customer '.$user->name, // 1:
                'read_unread'=>'0', // 1:
                'user_id'=>$user->id,// 1:
                'advisor_id'=>$bidDetails->advisor_id, // 1:
                'area_id'=>$bidDetails->area_id,// 1:
                'notification_to'=>1
            ));
            $newArr = array(
                'name'=>$advisor->display_name,
                'email'=>$advisor->email,
                'message_text' => 'Your bid declined by customer '.$user->name
            );
            $c = \Helpers::sendEmail('emails.information',$newArr ,$advisor->email,$advisor->display_name,'MortgageBox Bid Declined','','');
        }
        return response()->json([
            'status' => true,
            'message' => $message,
        ], Response::HTTP_OK);
    }

    public function saveNotification($data) {
        $notification = Notifications::create($data);
        if($notification) {
            return true;
        }else {
            return false;
        }
    }

    public function saveCard(Request $request) {
        require_once(public_path().'/stripe/init.php');
        $user = JWTAuth::parseToken()->authenticate();
        try {
            // $stripe_obj = new Stripe();
            $stripe_obj = \Stripe\Stripe::setApiKey('sk_test_3eGdHm9suYV79NBBzmCOWcsN');
            $stripe = new \Stripe\StripeClient(
                'sk_test_3eGdHm9suYV79NBBzmCOWcsN'
            );
        
            $token = $stripe->tokens->create([
                'card' => [
                    'number' => $request->account_number,
                    'exp_month' => $request->exp_month,
                    'exp_year' => $request->exp_year,
                    'cvc' => $request->cvc,
                ],
            ]);
            if($token   ){
                $customer = Stripe\Customer::create(array(
                    'name'=>($user->name) ? $user->name : '',
                    'email'=>($user->email) ? $user->email : '',
                    'source' => $token['id']
                ));
                if($customer){
                    // $card = \Stripe\Stripe::cards()->create($advisorDetails->stripe_customer_id, $token['id']); 
                    $total_payment = round(100 * $request->amount);
                    $striperes = Stripe\Charge::create ([
                        "amount" => $total_payment,
                        "currency" => "usd",
                        // 'source' => $token['id'],
                        'customer'=> $customer->id,
                        "description" => "Order placed on Mbox",
                        'metadata' => array(
                            'name' => $user->name,
                            'email' => $user->email,
                            'user_id' => $user->id
                        )
                    ]);
                    if($striperes['status']=='succeeded'){
                        DB::table('invoices')->where('id',$request->invoice_id)->update(['is_paid'=>1,'paid_at'=>date('Y-m-d H:i:s')]);
                        $payment_response = array(
                            'invoice_id'=>$request->invoice_id,
                            'payment_transaction_id'=>$striperes['id'],
                            'payment_status'=>$striperes['status'],
                            'payment_response'=>json_encode($striperes),
                            'status'=>1,
                            'created_at'=>date('Y-m-d H:i:s')
                        ); 
                        Payment::insertGetId($payment_response);
                        $paymentLog = array(
                            'invoice_id'=>$request->invoice_id,
                            'request_param'=>json_encode($request->all()),
                            'response_param'=>json_encode($striperes),
                            'status'=>$payment_response['status'],
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                        PaymentLog::insertGetId($paymentLog);      
                        return response()->json([
                            'status' => true,
                            'message' => "Payment deducted successfully",
                            'data' => $striperes
                        ], Response::HTTP_OK);              
                    }else{
                        $payment_response = array(
                            'invoice_id'=>$request->invoice_id,
                            'payment_status'=>$striperes['status'],
                            'payment_response'=>json_encode($striperes),
                            'status'=>0,
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                        Payment::insertGetId($payment_response);
                        $paymentLog = array(
                            'invoice_id'=>$request->invoice_id,
                            'request_param'=>json_encode($post),
                            'response_param'=>json_encode($striperes),
                            'status'=>$payment_response['status'],
                            'created_at'=>date('Y-m-d H:i:s')
                        );
                        PaymentLog::insertGetId($paymentLog); 
                        return response()->json([
                            'status' => false,
                            'message' => "Something went wrong while deducting payment please try again later!",
                            'data' => []
                        ], Response::HTTP_OK); 
                    }
                    
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => "Customer is not created on stripe please try again!.",
                        'data' => []
                    ], Response::HTTP_OK);
                }
            }else{
                return response()->json([
                    'status' => false,
                    'message' =>"Invalid Token",
                    'data' => []
                ], Response::HTTP_OK);
            }
        }catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], Response::HTTP_OK);
        }
        // $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
        // if($advisorDetails->stripe_customer_id != "" && $advisorDetails->stripe_customer_id != null){
        //     try {
               
        //             // $paymentDetails =  $stripe->paymentMethods->create([
        //             //     'type' => 'card',
        //             //     'card' => [
        //             //       'number' => $request->account_number,
        //             //       'exp_month' => $request->exp_month,
        //             //       'exp_year' => $request->exp_year,
        //             //       'cvc' => $request->cvc,
        //             //     ],
        //             // ]);
                   
        //             // $payment_method = $stripe->paymentMethods->retrieve($paymentDetails['id']);
        //             // $payment_method->attach(['customer' => $advisorDetails->stripe_customer_id]);
                    
        
        //     }catch(Exception $e) {
        //             return response()->json([
        //                 'status' => false,
        //                 'message' => $e->getMessage(),
        //                 'data' => []
        //             ], Response::HTTP_OK);
        //     }
        // }else{
        //     $status = $this->createCustomer($advisorDetails);
        //     if($status) {
        //         $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
        //         try {
        //             $paymentDetails =  $stripe->paymentMethods->create([
        //                 'type' => 'card',
        //                 'card' => [
        //                   'number' => $request->account_number,
        //                   'exp_month' => $request->exp_month,
        //                   'exp_year' => $request->exp_year,
        //                   'cvc' => $request->cvc,
        //                 ],
        //             ]);
        //             $payment_method = $stripe->paymentMethods->retrieve($paymentDetails['id']);
        //             $payment_method->attach(['customer' => $advisorDetails->stripe_customer_id]);
        //             return response()->json([
        //                 'status' => true,
        //                 'message' => "Card saved successfully",
        //                 'data' => []
        //             ], Response::HTTP_OK);
        
        //           }catch(Exception $e) {
        //             return response()->json([
        //                 'status' => false,
        //                 'message' => $e->getMessage(),
        //                 'data' => []
        //             ], Response::HTTP_OK);
        //           }
        //     }else {
        //         return response()->json([
        //             'status' => false,
        //             'message' => "Something went wrong",
        //             'data' => []
        //         ], Response::HTTP_OK);
        //     }
        // }
          
        
        //cus_Jn74KtxONwHBv5
        // print_r($payment_method);
    }

    function createCustomer($request) {
        require_once(public_path().'/stripe/init.php');
        $user = JWTAuth::parseToken()->authenticate();
        //$Skey = config('constants.stripe.stripe_secret_key'); 
        \Stripe\Stripe::setApiKey('sk_test_3eGdHm9suYV79NBBzmCOWcsN');
        // echo $request->currency;die;
       
        try{
            $advisorDetails = User::where('id','=',$request->advisorId)->first();
            // return response()->json([
            //     'status' => true,
            //     'message' => "Card saved successfully",
            //     'data' => $advisorDetails
            // ], Response::HTTP_OK);
            $responseData = \Stripe\Customer::create([
                'name'=>($advisorDetails->name) ? $request->name : '',
                'email'=>($advisorDetails->email) ? $advisorDetails->email : '',
                "address" => [
                    "city" => ($request->city) ? $request->city : '', "country" => '', "line1" => ($request->address_line1) ? $request->address_line1: '', "line2" => "", "postal_code" =>($advisorDetails->post_code)?$advisorDetails->post_code:'', "state" => ''
                ],
            ]);
              $customerDetails = json_decode($responseData,true);
              $customer_id = $responseData['id'];
              if($customer_id != "") {
                AdvisorProfile::where('advisorId','=',$user->id)->update([
                    'stripe_customer_id' => $customer_id
                ]);
                return true;
              }else{
                  return false;
              }
        } catch (Exception $e) {
            return false;
        }
    }

    public function getAllCardByCustomer() {
        require_once(public_path().'/stripe/init.php');
        $user = JWTAuth::parseToken()->authenticate();
        $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
        //$Skey = config('constants.stripe.stripe_secret_key'); 
        try {
            $stripe = new \Stripe\StripeClient(
                'sk_test_3eGdHm9suYV79NBBzmCOWcsN'
              );
              $cardDerails = $stripe->paymentMethods->all([
                'customer' => $advisorDetails->stripe_customer_id,
                'type' => 'card',
              ]);
              return response()->json([
                'status' => true,
                'message' => "success",
                'data' => $cardDerails
            ], Response::HTTP_OK);
        }catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], Response::HTTP_OK);
        }
    }

    public function deleteCard(Request $request) {
         require_once(public_path().'/stripe/init.php');
        $user = JWTAuth::parseToken()->authenticate();
        $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
        //$Skey = config('constants.stripe.stripe_secret_key'); 
        try {
            $stripe = new \Stripe\StripeClient(
                'sk_test_3eGdHm9suYV79NBBzmCOWcsN'
              );
                $stripe->paymentMethods->detach(
                  $request->card_id,
                []
                );
              return response()->json([
                'status' => true,
                'message' => "success",
                'data' => []
            ], Response::HTTP_OK);
        }catch(Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'data' => []
            ], Response::HTTP_OK);
        }
    }

    public function checkoutFromSavedCard(Request $request) {
          require_once(public_path().'/stripe/init.php');
        $user = JWTAuth::parseToken()->authenticate();
        $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
        //$Skey = config('constants.stripe.stripe_secret_key'); 
         $intent = null;
            try {
                \Stripe\Stripe::setApiKey('sk_test_3eGdHm9suYV79NBBzmCOWcsN');
                // $advisorDetails = User::where('id','=',$user->id)->first();
                // $responseData = \Stripe\Customer::create([
                //     'name'=>($advisorDetails->name) ? $advisorDetails->name : '',
                //     'email'=>($advisorDetails->email) ? $advisorDetails->email : '',
                //     'address[city]'=>($advisorDetails->city) ? $advisorDetails->city : '',
                //     'address[country]'=>'',
                //     'address[line1]'=>($advisorDetails->address_line1) ? $advisorDetails->address_line1: '',
                //     'address[line2]'=>($advisorDetails->address_line2) ? $advisorDetails->address_line2: '',
                //     'address[postal_code]'=>($advisorDetails->postal_code)?$advisorDetails->postal_code:'',
                //     'address[state]'=>'',
                //   ]);
                //   $customerDetails = json_decode($responseData,true);
                //   $customer_id = $responseData['id'];
                //   if($customer_id != "") {
                //     AdvisorProfile::where('advisorId','=',$user->id)->update([
                //         'stripe_customer_id' => $customer_id
                //     ]);
                //   }
                  # Create the PaymentIntent
                  $intent = \Stripe\PaymentIntent::create([
                    'payment_method_types' => ['card'],
                    'amount' => $request->amount*100,
                    'currency' => 'USD',
                    'confirm' => true,
                    'customer' => $advisorDetails->stripe_customer_id,
                    'payment_method' => $request->card_id,
                    'description'=>$request->description,
                    'shipping' => [
                        'name' => $user->name,
                        'address' => [
                        'line1' => ($advisorDetails->address_line1) ? $advisorDetails->address_line1: '',
                        'postal_code' =>($advisorDetails->postal_code)?$advisorDetails->postal_code:'',
                        'city' => ($advisorDetails->city) ? $advisorDetails->city : '',
                        'state' => 'us',
                        'country' => 'us',
                     ],
                    ],
               
                  ]);
                 return response()->json([
                            'status' => true,
                            'message' => "Success payment",
                            'data' => $intent
                        ], Response::HTTP_OK);
                } catch (Exception $e) {
                return response()->json([
                            'status' => false,
                            'message' => $e->getMessage(),
                            'data' => []
                        ], Response::HTTP_OK);
                }
    }

    public function getNotification() {
        $user = JWTAuth::parseToken()->authenticate();
        $notificationCount = 0;
        if($user->user_role == 1) {
            $notification = Notifications::where('advisor_id', '=', $user->id)->where('notification_to','=','1')
            ->orderBy('id','DESC')->get();
            $notificationCount = Notifications::where('advisor_id', '=', $user->id)->where('notification_to','=','1')->where('read_unread',0)
            ->count();
        }else{
            $notification = Notifications::where('user_id', '=', $user->id)->where('notification_to','=','0')->orderBy('id','DESC')->get();
            $notificationCount = Notifications::where('user_id', '=', $user->id)->where('notification_to','=','0')->where('read_unread',0)
            ->count();
        }
        foreach($notification as $key =>$item ) {
            if($user->user_role == 1) {
                $userDetails = User::where('id','=',$item->user_id)->first();
                $notification[$key]->userDetails = $userDetails;
            }else{
                $userDetails = User::where('id','=',$item->advisor_id)->first();
                $notification[$key]->userDetails = $userDetails;
            }
        }
        if (count($notification) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $notification,
                'unread_count'=>$notificationCount
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => [],
                'unread_count'=>$notificationCount
            ], Response::HTTP_OK);
        }
    }

    public function updateReadNotification() {
        $user = JWTAuth::parseToken()->authenticate();
        if($user->user_role == 1) {
            Notifications::where('advisor_id','=',$user->id)->where('notification_to','=','1')->update([
                'read_unread' => 1
            ]);
        }else{
            Notifications::where('user_id','=',$user->id)->where('notification_to','=','0')->update([
                'read_unread' => 1
            ]);
        }
        
        return response()->json([
            'status' => true,
            'message' => 'success',
        ], Response::HTTP_OK);
    }

    public function searchPostalCode(Request $request) {
        $search = $request->postal_code;
        $result = PostalCodes::select('id','Postcode')->where('Postcode', 'like', '%' . $search . '%')->limit(20)->get();
        if(!empty($result)) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data'=>$result
            ], Response::HTTP_OK);  
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Postal code is not available',
                'data'=>$result
            ], Response::HTTP_OK);  
        }           
    }

    public function getAllServiceType() {
        // $user = JWTAuth::parseToken()->authenticate();
        $result = ServiceType::where('status',1)->where('parent_id','!=','0')->get();
        if(!empty($result)) {
            foreach($result as $row){
                $row->value = 0;
                $row->service_count = Advice_area::where('service_type_id',$row->id)->count();
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data'=>$result
            ], Response::HTTP_OK);  
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No Service type available',
                'data'=>$result
            ], Response::HTTP_OK);  
        }           
    }

    public function getAllServiceTypeWithAuth() {
        $user = JWTAuth::parseToken()->authenticate();
        $result = ServiceType::where('status',1)->where('parent_id','!=','0')->get();
        if(!empty($result)) {
            foreach($result as $row){
                $row->value = 0;
                $default = DefaultPercent::where('service_id',$row->id)->where('adviser_id',$user->id)->first();
                if($default){
                    $row->value = $default->value_percent;
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data'=>$result
            ], Response::HTTP_OK);  
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No Service type available',
                'data'=>$result
            ], Response::HTTP_OK);  
        }           
    }

    public function getAdvisorResponseTime($advisor_id=0) {
        $fromUserChatData = ChatModel::where('from_user_id', '=', $advisor_id)->orderBy('id','DESC')->groupBy('channel_id')->get();
        $arrayTime = array();
        if(!empty($fromUserChatData)) {
            foreach($fromUserChatData as $item) {
                $toUserChatData = ChatModel::where('to_user_id', '=', $item->to_user_id)->where('channel_id','=',$item->channel_id)->orderBy('id','DESC')->first();   
               
                $d1= new DateTime($toUserChatData->created_at); // first date
                $d2= new DateTime($item->created_at); // second date
                $interval= $d1->diff($d2); // get difference between two dates
                 $time = ($interval->days*24)+$interval->h;
                 array_push($arrayTime,$time);
                
            }
        }
        $responseTime = "N/A";
        if(count($arrayTime) > 0){
            $avg = array_sum($arrayTime)/count($arrayTime);
       
            $avg = $avg/24;
             $avg = number_format((float)($avg), 1, '.', '');
            $avgArr = explode('.',$avg);
            
            if(count($avgArr) > 0) {
                if($avgArr[0] > 0) {
                    $responseTime = $avgArr[0]." Day ".$avgArr[1]." Hours";
                }else{
                    $responseTime = $avgArr[1]." Hours";
                }
             }
        }
         return $responseTime;
    }
    

    public function getFaqLists(Request $request) {
        $audience = (isset($request->audience))?$request->audience:'customer';
        $response = array(
            'list' => Faq::where('audience',$audience)->where('is_featured',1)->where('status',1)->get(),
            'category' => FaqCategory::where('audience',$audience)->where('status',1)->get()
        );

        foreach ($response['category'] as $row) {
            $row->lists = Faq::where('faq_category_id',$row->id)->where('status',1)->get();
        }
        return response()->json([
            'status' => true,
            'message' => 'Record found.',
            'data'=>$response
        ], Response::HTTP_OK);
    }

    public function getCMSData(Request $request) {
        $post = $request->all();
        $postData = array(
            'slug' => $post['page'],
        );
        if(isset($post['type']) && $post['type']!=''){
            $postData['type'] = $post['type'];
        }
        $advice_read = StaticPage::where('slug',$postData['slug'])->where('type',$post['type'])->first();
        return response()->json([
            'status' => true,
            'message' => 'Data fetched successfully.',
            'data'=> $advice_read
        ], Response::HTTP_OK);
    }

    public function doSubmitContactUs(Request $request) {
        $postData = array(
            'name' => (isset($request->name))?$request->name:'',
            'email' => (isset($request->email))?$request->email:'',
            'mobile' => (isset($request->mobile))?$request->mobile:'',
            'message' => (isset($request->message))?$request->message:'',
            'is_replied' => 0,
            'created_at' => date("Y-m-d H:i:s")
        );
        Contactus::insertGetId($postData);
        return response()->json([
            'status' => true,
            'message' => 'Your request has been sent to the admin successfully.',
            'data'=> []
        ], Response::HTTP_OK);
    }
    
    public function makrLeadAsSpam(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $postData = array(
                'user_id' => $user->id,
                'area_id' => (isset($request->area_id))?$request->area_id:0
            );

            $spam_info = AdviceAreaSpam::where('area_id',$postData['area_id'])->where('user_id',$postData['user_id'])->first();
            if(!$spam_info){
                $postData = array(
                    'user_id' => $user->id,
                    'area_id' => (isset($request->area_id))?$request->area_id:0,
                    'reason' => (isset($request->reason))?$request->reason:'',
                    'spam_status' => -1,
                    'created_at' => date("Y-m-d H:i:s")
                );
                $id = AdviceAreaSpam::insertGetId($postData);
                if($id){
                    $area = Advice_area::where('id',$postData['area_id'])->first();
                    if($area){
                        $userdata = User::where('id',$area->user_id)->first();
                        if($userdata){
                            $adviser = AdvisorProfile::where('advisorId',$user->id)->first();
                            if($adviser){
                                $newArr = array(
                                    'name'=>$userdata->name,
                                    'email'=>$userdata->email,
                                    'message_text' => $adviser->display_name.' has marked your need as spam.'
                                );
                                $c = \Helpers::sendEmail('emails.information',$newArr ,$userdata->email,$userdata->name,'Mortgagebox.co.uk – '.$userdata->name,'','');
                            }
                        }
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Your spam request has been submited successfully.',
                    'data'=> []
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'You have already marked it as spam.',
                    'data'=> []
                ], Response::HTTP_OK);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }

    public function markReviewAsSpam(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $postData = array(
                'user_id' => $user->id,
                'review_id' => (isset($request->review_id))?$request->review_id:0
            );

            $spam_info = ReviewSpam::where('review_id',$postData['review_id'])->where('user_id',$postData['user_id'])->first();
            if(!$spam_info){
                $postData = array(
                    'user_id' => $user->id,
                    'review_id' => (isset($request->review_id))?$request->review_id:0,
                    'reason' => (isset($request->reason))?$request->reason:'',
                    'spam_status' => -1,
                    'created_at' => date("Y-m-d H:i:s")
                );
                ReviewSpam::insertGetId($postData);
                return response()->json([
                    'status' => true,
                    'message' => 'Your spam request has been submited successfully.',
                    'data'=> []
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'You have already marked it as spam.',
                    'data'=> []
                ], Response::HTTP_OK);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }

    public function markAreaAsRead(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $post = $request->all();
            $postData = array(
                'adviser_id' => $user->id,
                'area_id' => (isset($post['area_id']))?$post['area_id']:0
            );
            $advice_read = AdviceAreaRead::where('area_id',$postData['area_id'])->where('adviser_id',$postData['adviser_id'])->first();
            if(!$advice_read){
                $postData = array(
                    'adviser_id' => $user->id,
                    'area_id' => (isset($request->area_id))?$request->area_id:0,
                    'created_at' => date("Y-m-d H:i:s")
                );
                AdviceAreaRead::insertGetId($postData);
                return response()->json([
                    'status' => true,
                    'message' => 'Area is marked as read.',
                    'data'=> []
                ], Response::HTTP_OK);
            }
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }

    public function getTeamMember(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $post = $request->all();
            $advice_read = CompanyTeamMembers::where('id',$post['id'])->first();
            return response()->json([
                'status' => true,
                'message' => 'Team member data fetched successfully',
                'data'=> $advice_read
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }


    public function getAllAdviser(Request $request) {
        $post = $request->all();
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $team_member = array();
            $company = companies::where('company_admin',$user->id)->first();
            if($company){
                $company_team = CompanyTeamMembers::where('company_id',$company->id)->where('isCompanyAdmin',0)->get();
                if(count($company_team)){
                    foreach($company_team as $company_team_data){
                        array_push($team_member,$company_team_data->email);
                    }
                }   
            }
            $advice_read = User::whereIn('email',$team_member)->where('status',1)->get();
            if(count($advice_read)){
                foreach($advice_read as $advice_read_data){
                    $advice_read_data_ad = AdvisorProfile::where('advisorId',$advice_read_data->id)->first();
                    if($advice_read_data_ad){
                        $advice_read_data->advisor_data = $advice_read_data_ad;
                    }
                    $advice_read_data->adviser_count = AdvisorBids::where('advisor_id',$advice_read_data->id)->count();
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Team member data fetched successfully',
                'data'=> $advice_read
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }

    public function updateCompanyAdmin(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $post = $request->all();
            $advisor_profiles = AdvisorProfile::where('advisorId',$user->id)->first();
            if($advisor_profiles){
                // echo json_encode($post);exit;
                AdvisorProfile::where('advisorId',$post['company_admin'])->update(['company_logo'=>$advisor_profiles->company_logo]);
                $admin = companies::where('id',$post['company_id'])->update(['company_admin'=>$post['company_admin']]);
                $admin_company = companies::where('id',$post['company_id'])->first();

                // echo json_encode($admin);exit;
                if($admin){
                    $team_member = CompanyTeamMembers::where('company_id',$post['company_id'])->get();
                    // ->where('advisor_id',$user->id)
                    foreach($team_member as $team_member_data){
                        CompanyTeamMembers::where('id',$team_member_data->id)->update(['isCompanyAdmin'=>0,'advisor_id'=>$post['company_admin']]);
                    }
                    $advisor_update_to = AdvisorProfile::where('advisorId',$post['company_admin'])->first();
                    if($advisor_update_to){
                        $team_member_to_update = CompanyTeamMembers::where('company_id',$post['company_id'])->where('email',$advisor_update_to->email)->first();
                        if($team_member_to_update){
                            CompanyTeamMembers::where('id',$team_member_to_update->id)->update(['isCompanyAdmin'=>1]);
                            $advisor = AdvisorProfile::where('advisorId',$request->to_user_id)->first();
                            $this->saveNotification(array(
                                'type'=>'6', // 1:
                                'message'=>'You are a company admin now', // 1:
                                'read_unread'=>'0', // 1:
                                'user_id'=>$user->id,// 1:
                                'advisor_id'=>$post['company_admin'], // 1:
                                'area_id'=>0,// 1:
                                'notification_to'=>1
                            ));
                            $newArr = array(
                                'name'=>$advisor_update_to->display_name,
                                'email'=>$advisor_update_to->email,
                                'message_text' => 'You have now been made the administrator for '.$admin_company->company_name.' by '.$advisor_profiles->display_name.'. This allows you to view the performance of all advisers in your company.'
                            );
                            $c = \Helpers::sendEmail('emails.information',$newArr ,$advisor_update_to->email,$advisor_update_to->display_name,'Mortgagebox.co.uk – Administrator role','','');
                        }
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Team member data fetched successfully',
                    'data'=> []
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong.',
                    'data'=> []
                ], Response::HTTP_OK);
            }            
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }

    public function markProjectCompleted(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $post = $request->all();
            if(isset($post) && !empty($post)){
                AdvisorBids::where('advisor_id',$post['advisor_id'])->where('area_id',$post['area_id'])->update(['status'=>$post['advisor_status']]);
                $status = 0;
                if($post['advisor_status']==2){
                    $status = 3;
                }else if($post['advisor_status']==3){
                    $status = 4;
                }else if($post['advisor_status']==4){
                    $status = 5;
                }
                Advice_area::where('id',$post['area_id'])->update(['area_status'=>$status]);
                return response()->json([
                    'status' => true,
                    'message' => 'Status updated successfully',
                    'data'=> []
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong.',
                    'data'=> []
                ], Response::HTTP_OK);
            }           
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }

    public function getAllServiceTypeWithPreferences() {
        $user = JWTAuth::parseToken()->authenticate();
        $result = ServiceType::where('status',1)->where('parent_id','!=','0')->orderBy('sequence','ASC')->get();
        if(!empty($result)) {
            foreach($result as $row){
                $preferences = AdviserProductPreferences::where('service_id',$row->id)->where('adviser_id',$user->id)->first();
                if($preferences){
                    $row->preference_status = 1;
                    $row->preference_updated_at = $preferences->updated_at;
                }else{
                    $row->preference_status = 0;
                    $row->preference_updated_at = "";
                }
            }
            $mortgage_max_size = 0;
            $mortgage_min_size = 0;
            $profile = AdvisorProfile::where('advisorId',$user->id)->first();
            if($profile){
                $mortgage_max_size = $profile->mortgage_max_size;
                $mortgage_min_size = $profile->mortgage_min_size;
            }
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data'=>$result,
                'mortgage_min_size'=>$mortgage_min_size,
                'mortgage_max_size'=>$mortgage_max_size
            ], Response::HTTP_OK);  
        }else{
            return response()->json([
                'status' => false,
                'message' => 'No Service type available',
                'data'=>$result
            ], Response::HTTP_OK);  
        }           
    }

    /**
     * Show Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function invoiceDisplay(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $post = $request->all();
            
            if(isset($post) && !empty($post)){
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
                    $data['month_data'] = DB::table('invoices')->where('total_due','!=',0)->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->get(); 
                    // where('is_paid',0)->where('is_paid',0)->
                    foreach($data['month_data'] as $month_data){
                        $month_data->show_days = \Helpers::getMonth($month_data->month)." ".$month_data->year;
                    }
                    if(isset($post['date']) && $post['date']!=''){
                        $explode = explode('/',$post['date']);
                        $searchmonth = $explode[0];
                        $searchyear = $explode[1];
                        $data_invoice = DB::table('invoices')->where('advisor_id',$user->id)->where('month',$searchmonth)->where('year',$searchyear)->whereNull('deleted_at')->orderBy('id','DESC')->first();
                        if($data_invoice){
                            $data['invoice'] = $data_invoice;
                        }else{
                            $data['invoice'] = DB::table('invoices')->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->first();
                        }
                        
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
                        $data['invoice']->month_check = $m;
                        $summary = "01 ".$monthArr[$m-1]." ".date("Y")." - ".$day." ".$monthArr[$m-1]." ".date("Y");
                        $data['invoice']->summary = $summary;
                        $data['invoice']->invoice_data = json_decode($data['invoice']->invoice_data);
                        $data['invoice']->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','!=',$data['invoice']->month)->where('advisor_id',$data['invoice']->advisor_id)->sum('total_due');
                        $data['invoice']->paid_prevoius_invoice = DB::table('invoices')->where('is_paid','!=',0)->where('month','!=',$data['invoice']->month)->where('advisor_id',$data['invoice']->advisor_id)->sum('total_due');
                        
                        $data['invoice']->new_fees_arr = AdvisorBids::where('advisor_id',$data['invoice']->advisor_id)->whereMonth('created_at',$m)->with('area')->with('adviser')->get();
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
                        
                        $discount_cre = AdvisorBids::where('advisor_id',$data['invoice']->advisor_id)->where('is_discounted','!=',0)->whereMonth('created_at',$m)->with('area')->with('adviser')->get();
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

                        // $spam_refund = AdviceAreaSpam::where('user_id',$data['invoice']->advisor_id)->where('spam_status',1)->with('area')->whereMonth('created_at',$m)->get();
                        // foreach($spam_refund as $spam_refund_data){
                        //     $spam_refund_need = NeedSpam::where('adviser_id',$spam_refund_data->user_id)->where('area_id',$spam_refund_data->area_id)->first();
                        //     if($spam_refund_need){
                        //         $spam_bid = AdvisorBids::where('id',$spam_refund_need->bid_id)->with('area')->first();
                        //         if($spam_bid){
                        //             $baddress = "";
                        //             if($spam_bid->area){
                        //                 if(!empty($spam_bid->area->user)) {
                        //                     $addressDetails = PostalCodes::where('Postcode',$spam_bid->area->user->post_code)->first();
                        //                     if(!empty($addressDetails)) {
                        //                         if($addressDetails->Country != ""){
                        //                             $baddress = ($addressDetails->Ward != "") ? $addressDetails->Ward.", " : '';
                        //                             $baddress .= ($addressDetails->Constituency != "") ? $addressDetails->Constituency.", " : '';
                        //                             $baddress .= ($addressDetails->Country != "") ? $addressDetails->Country : '';
                        //                         }
                                                
                        //                     }
                        //                 }
                        //             }
                        //             $spam_bid->area->address = $baddress;
                        //             if($spam_bid->status==0){
                        //                 $spam_bid->status_type = "Live Lead";
                        //             }else if($spam_bid->status==1){
                        //                 $spam_bid->status_type = "Hired";
                        //             }else if($spam_bid->status==2){
                        //                 $spam_bid->status_type = "Completed";
                        //             }else if($spam_bid->status==3){
                        //                 $spam_bid->status_type = "Lost";
                        //             }else if($spam_bid->advisor_status==2){
                        //                 $spam_bid->status_type = "Not Proceeding";
                        //             }
                        //             $spam_bid->discount_cycle = "Refund";
                        //             $spam_bid->cost_leads = number_format($spam_bid->cost_leads,2);
                        //             $spam_bid->cost_discounted = number_format($spam_bid->cost_discounted,2);
                        //             // array_push($data['discount_credits'],$spam_bid);
                        //             $spam_bid->date = date("d-M-Y H:i",strtotime($spam_bid->created_at));
                        //             array_push($data['discount_credits'],$spam_bid);
                        //             if($spam_refund_need->cost_of_lead_discounted!=0){
                        //                 $spam_total = $spam_total + $spam_refund_need->cost_of_lead_discounted;
                        //             }else{
                        //                 $spam_total = $spam_total + $spam_refund_need->cost_of_lead;
                        //             }
                        //         }
                        //     }
                        // }
                        // $discount_subtotal_to = $data['invoice']->discount_subtotal + $spam_total;
                        // $data['invoice']->discount_subtotal = number_format($discount_subtotal_to,2);
                        $data['invoice']->discount_credit_arr = $data['discount_credits'];
                    }

                    $data['month_data_unpaid'] = DB::table('invoices')->where('is_paid',0)->where('total_due','!=',0)->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->first(); 
                    // where('is_paid',0)->
                    foreach($data['month_data'] as $month_data){
                        $month_data->show_days = \Helpers::getMonth($month_data->month)." ".$month_data->year;
                    }
                    if($data['month_data_unpaid']){
                        $data['month_data_unpaid']->show_days = \Helpers::getMonth($data['month_data_unpaid']->month)." ".$data['month_data_unpaid']->year;
                        $data['show_month_year'] = \Helpers::getMonth($data['month_data_unpaid']->month)." ".$data['month_data_unpaid']->year;
                        $searchmonth = $data['month_data_unpaid']->month;
                        $searchyear = $data['month_data_unpaid']->year;
                        $data_invoice = DB::table('invoices')->where('advisor_id',$user->id)->where('month',$searchmonth)->where('year',$searchyear)->whereNull('deleted_at')->orderBy('id','DESC')->first();
                        if($data_invoice){
                            $data['invoice_unpaid'] = $data_invoice;
                        }else{
                            $data['invoice_unpaid'] = DB::table('invoices')->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->first();
                        }
                        
                    }else{
                        $data['invoice_unpaid'] = DB::table('invoices')->where('advisor_id',$user->id)->whereNull('deleted_at')->orderBy('id','DESC')->first();
                    }
                    
                    if($data['invoice_unpaid']){
                        $summary = "";
                        $monthArr = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                        $m = $data['invoice_unpaid']->month;
                        if($m==4 || $m==6 || $m==9 || $m==11){
                            $day = 30;
                        }else if($m==2){
                            $day = 28;  
                        }else{
                            $day = 31;
                        }
                        $data['invoice_unpaid']->month_check = $m;
                        $summary = "01 ".$monthArr[$m-1]." ".date("Y")." - ".$day." ".$monthArr[$m-1]." ".date("Y");
                        $data['invoice_unpaid']->summary = $summary;
                        $data['invoice_unpaid']->invoice_data = json_decode($data['invoice_unpaid']->invoice_data);
                        $data['invoice_unpaid']->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','!=',$data['invoice_unpaid']->month)->where('advisor_id',$data['invoice_unpaid']->advisor_id)->sum('total_due');
                        $data['invoice_unpaid']->paid_prevoius_invoice = DB::table('invoices')->where('is_paid','!=',0)->where('month','!=',$data['invoice_unpaid']->month)->where('advisor_id',$data['invoice_unpaid']->advisor_id)->sum('total_due');
                        
                        $data['invoice_unpaid']->new_fees_arr = AdvisorBids::where('advisor_id',$data['invoice_unpaid']->advisor_id)->whereMonth('created_at',$m)->with('area')->with('adviser')->get();
                        // ->where('is_discounted',0)
                        if(count($data['invoice_unpaid']->new_fees_arr)){
                            foreach($data['invoice_unpaid']->new_fees_arr as $new_bid){
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
                        
                        $discount_cre = AdvisorBids::where('advisor_id',$data['invoice_unpaid']->advisor_id)->where('is_discounted','!=',0)->whereMonth('created_at',$m)->with('area')->with('adviser')->get();
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
                        $data['invoice_unpaid']->discount_credit_arr = $data['discount_credits'];
                    }
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Invoice fetched successfully',
                    'data'=> $data
                ], Response::HTTP_OK);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong.',
                    'data'=> []
                ], Response::HTTP_OK);
            }           
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
        $user = JWTAuth::parseToken()->authenticate();
        // $id = $user->id;
        
        // echo json_encode($data);exit;
        return view('advisor.invoice',$data);
    }

    /**
     * Show Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function getCountFilter(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $aStatus = array(-1);
        if($user) {
            $data['read'] = AdviceAreaRead::where('adviser_id',$user->id)->count();
            $data['unread'] = 0;
            $area = AdviceAreaRead::where('adviser_id',$user->id)->get();
            if(count($area)){
                foreach($area as $default_data){
                    array_push($aStatus,$default_data->area_id);
                }
            }
            if(count($aStatus)){
                $data['unread'] = Advice_area::whereNotIn('id',$aStatus)->count();
            }
            $data['not_intrest'] = AdvisorBids::where('advisor_id',$user->id)->where('advisor_status',2)->count();
            $data['none'] = AdvisorBids::where('is_discounted', 0)->count();
            $data['third'] = AdvisorBids::where('is_discounted', 1)->where('discount_cycle', "Third cycle")->count();
            $data['half'] = AdvisorBids::where('is_discounted', 1)->where('discount_cycle', "Second cycle")->count();
            $data['free'] = AdvisorBids::where('is_discounted', 1)->where('discount_cycle', "Fourth cycle")->count();
            $data['today'] = Advice_area::where('created_at', '>=',Carbon::today())->count();
            $data['anytime'] = Advice_area::count();
            $data['yesterday'] = Advice_area::where('created_at','>=', Carbon::yesterday())->where('created_at', '<=',Carbon::today())->count();
            $data['last_hour'] = Advice_area::where('created_at','>=' ,date("Y-m-d H:i:s", strtotime('-1 hour')))->count();
            $data['three_days'] = Advice_area::where('created_at', '>', Carbon::today()->subDays(3))->count();
            $data['one_week'] = Advice_area::where('created_at', '>', Carbon::today()->subDays(7))->count();
            $data['preference'] = Advice_area::where('fees_preference',0)->where('status',1)->count();
            $data['preference_no'] = Advice_area::where('fees_preference',1)->where('status',1)->count();
            return response()->json([
                'status' => true,
                'message' => 'Count fetched successfully',
                'data'=> $data
            ], Response::HTTP_OK);          
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
        $user = JWTAuth::parseToken()->authenticate();
        // $id = $user->id;
        
        // echo json_encode($data);exit;
        return view('advisor.invoice',$data);
    }

    public function getCountAcceptedFilter(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $aStatus = array(-1);
        if($user) {
            $acceptedArr = array();
            $data['unread'] = 0;
            $data['unread'] = ChatModel::where('to_user_id',$user->id)->where('to_user_id_seen',0)->count();
            $data['accepted'] = 0;
            $accepted = AdvisorBids::where('advisor_id',$user->id)->where('status',0)->where('advisor_status',1)->get();
            if(count($accepted)){
                foreach($accepted as $accepted_data){
                    $dataPurchased = Advice_area::where('id',$accepted_data->area_id)->where('advisor_id',0)->first();
                    if($dataPurchased){
                        $data['accepted'] = $data['accepted'] + 1;
                        array_push($acceptedArr,$dataPurchased);
                    }
                }
            }
            $data['hired'] = AdvisorBids::where('advisor_id',$user->id)->where('status',1)->count();
            $data['completed'] = AdvisorBids::where('advisor_id',$user->id)->where('status',2)->where('advisor_status',1)->count();
            $data['no_response'] = 0;
            $noresArrTotal = array();
            $noresNotArr = array();
            $noresyesArr = array();
            $response = Advice_area::where('advisor_id',0)->where('created_at','<',date("Y-m-d H:i:s",strtotime("- 14 days")))->get();
            foreach($response as $response_data){
                array_push($noresArrTotal,$response_data);
                $accepted = AdvisorBids::where('advisor_id',$user->id)->where('area_id',$response_data->id)->where('status',0)->where('advisor_status',1)->count();
                $hired = AdvisorBids::where('area_id',$response_data->id)->where('status',1)->count();
                
                $channelIds = array(-1);
                $channelID = ChatChannel::where('advicearea_id',$response_data->id)->orderBy('id','DESC')->get();
                foreach ($channelID as $chanalesR) {
                    array_push($channelIds, $chanalesR->id);
                }                    
                $chatCount = ChatModel::whereIn('channel_id',$channelIds)->count();;

                if($chatCount==0 && $hired==0 && $accepted>0){
                    $data['no_response'] = $data['no_response'] + 1;
                    array_push($noresNotArr,$response_data);
                }
            }
            $response_another = Advice_area::where('advisor_id',$user->id)->where('area_status',5)->get();
            if(count($response_another)){
                foreach($response_another as $response_another_data){
                    $accepted_another = AdvisorBids::where('advisor_id',$user->id)->where('area_id',$response_another_data->id)->where('status',4)->where('advisor_status',1)->first();
                    if($accepted_another){
                        array_push($noresyesArr,$accepted_another);
                        $data['no_response'] = $data['no_response'] + 1;
                    }
                }
            }
            
            $lostArrTotal = array();
            $lostNotArr = array();
            $lostyesArr = array();

            // $data['no_response'] = Advice_area::where('advisor_id',$user->id)->where('advisor_id',0)->count();
            $data['lost'] = 0;
            $AllMyBids = AdvisorBids::where('advisor_id',$user->id)->where('status','!=',1)->where('status','!=',2)->get();   
            if(count($AllMyBids)){
                foreach($AllMyBids as $bids){
                    array_push($lostArrTotal,$bids);
                    $dataLost = Advice_area::where('id',$bids->area_id)->where('advisor_id','!=',$user->id)->where('advisor_id','!=',0)->first();
                    if($dataLost){
                        $data['lost'] = $data['lost'] + 1;
                        array_push($lostNotArr,$dataLost);
                    }else{
                        $dataLostManual = Advice_area::where('id',$bids->area_id)->where('advisor_id',$user->id)->where('area_status',4)->first();
                        if($dataLostManual){
                            $data['lost'] = $data['lost'] + 1;
                            array_push($lostyesArr,$dataLostManual);
                        }
                    }
                }
            }
            // return response()->json([
            //     'status' => true,
            //     'message' => 'Count fetched successfully',
            //     'data'=> $data,
            //     'lostArr'=>$lostArr,
            //     // 'acceptedArr'=>$acceptedArr
            // ], Response::HTTP_OK);     
            $data['three_month'] = Advice_area::where('advice_areas.created_at','>=',date("Y-m-d",strtotime("- 3 month")))->count();
            $data['six_month'] = Advice_area::where('advice_areas.created_at','>=',date("Y-m-d",strtotime("- 6 month")))->count();
            $data['last_year'] = Advice_area::where('advice_areas.created_at','>=',date("Y-m-d",strtotime("- 12 month")))->count();
            $data['this_year'] = Advice_area::where('advice_areas.created_at','>=',date("Y").'-01-01')->count();
            $data['anytime'] = Advice_area::count();
            return response()->json([
                'status' => true,
                'message' => 'Count fetched successfully',
                'data'=> $data,
                'noresArrTotal'=>$noresArrTotal,
                'noresNotArr'=>$noresNotArr,
                'noresyesArr'=>$noresyesArr,
                'acceptedArr'=>$acceptedArr
            ], Response::HTTP_OK);          
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
        return view('advisor.invoice',$data);
    }

    public function getMortgageSize(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        $mortgage_size = array();
        if($user) {
            $mortgage_size[0]['key'] = '0_74';
            $mortgage_size[0]['value'] = '0_74';
            $mortgage_size[0]['name'] = '£0-£75k';

            $mortgage_size[1]['key'] = '75_249';
            $mortgage_size[1]['value'] = '75_249';
            $mortgage_size[1]['name'] = '£75-£249k';

            $mortgage_size[2]['key'] = '250_499';
            $mortgage_size[2]['value'] = '250_499';
            $mortgage_size[2]['name'] = '£250-£499k';

            $mortgage_size[3]['key'] = '500_999';
            $mortgage_size[3]['value'] = '500_999';
            $mortgage_size[3]['name'] = '£500-£900k';

            $mortgage_size[4]['key'] = '1000';
            $mortgage_size[4]['value'] = '1000';
            $mortgage_size[4]['name'] = '£1m+';
            for($i=0;$i<count($mortgage_size);$i++){
                if($mortgage_size[$i]['key']==1000){
                    $explode = $mortgage_size[$i]['key']."000";                    
                    $mortgage_size[$i]['size_count'] = Advice_area::where('size_want','>',$explode)->count();
                }else{
                    $explode = explode("_",$mortgage_size[$i]['key']);
                    if($explode[0]>0){
                        $explode[0] = (int)$explode[0]."000";
                    }
                    if($explode[1]>0){
                        $explode[1] = (int)$explode[1]."000";
                    }
                    $mortgage_size[$i]['size_count'] = Advice_area::where('size_want','>',$explode[0])->where('size_want','<=',$explode[1])->count();
                }
            }
            return response()->json([
                'status' => true,
                'message' => 'Mortagage size fetched successfully',
                'data'=> $mortgage_size
            ], Response::HTTP_OK);          
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
        return view('advisor.invoice',$data);
    }


    public function downloadInvoice(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        if($user) {
            $post = $request->all();
            if(isset($post) && !empty($post)){
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
                        $explode = explode('/',$post['date']);
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
            
                return $pdf->download('invoice'.$post['date'].'.pdf');
            }else{
                return response()->json([
                    'status' => false,
                    'message' => 'Something went wrong.',
                    'data'=> []
                ], Response::HTTP_OK);
            }           
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Token Expired.',
                'data'=> []
            ], Response::HTTP_OK);
        }
    }
    
}