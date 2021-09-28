<?php

namespace App\Http\Controllers;

use App\Models\Advice_area;
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
use App\Models\ReviewSpam;
use JWTAuth;
use App\Models\User;
use App\Models\UserNotes;
use App\Models\CompanyTeamMembers;
use App\Models\Faq;
use App\Models\FaqCategory;
use App\Models\Contactus;
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
        $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');
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
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'status' => false,
                    'message' => 'Login credentials are invalid.',
                ], 400);
            }
        } catch (JWTException $e) {
            return $credentials;
            return response()->json([
                'status' => false,
                'message' => 'Could not create token.',
            ], 500);
        }

        $user =  USER::where('email', '=', $request->email)->first();
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
        $user->slug = $this->getEncryptedId($user->id);
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
            $newArr = array(
                'name'=>$request->name,
                'email'=>$request->email,
                'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
            );
            $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');

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
        $company_data = companies::where('company_name', '=', $request->company_name)->first();
        $company_id = 0;
        $description = "";
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
        //Request is valid, create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'user_role' => '1',
            'nationality' => $request->nationality,
            'address' => $request->address,
            'fca_number' => $request->fca_number,
            'company_name' => $request->company_name,
            'post_code' => $request->post_code,
            'password' => bcrypt($request->password)
        ]);
        $advisor_id = $user->id;
        
        //Request is valid, create new user
        $profile = AdvisorProfile::create([
            'advisorId' => $advisor_id,
            'FCANumber' => $request->fca_number,
            'address_line1' => $request->address,
            'display_name' => $request->name,
            'company_name' => $request->company_name,
            'serve_range' => $request->serve_range,
            'company_id' => $company_id,
            'email' => $request->email,
            'postcode' => $request->post_code,
            'description' => $description

        ]);
        companies::where('id',$company_id)->update(array('company_admin'=>$advisor_id));
        if(isset($company_id) && $company_id!=0){
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
        
        // $msg = "";
        // $msg .= "Welcome\n\n";
        // $msg .= "Hello ".ucfirst($request->name).",\n\n";
        // $msg .= "<p>Mortgogebox is the smart and cost-effective way for finding new customers actively looking for mortgages based on your specialties and preferences. We let you focus on what you do best ... arranging great mortgages for your customers rather than scrambling around for leads</p>\n\n";
        // $msg .= "<p>Mortgogebox will help grow and sustain your business and allow you to efficiently manage your customer relationships. We also give you the tools to manage them all in one place.</p>\n\n";
        // $msg .= "<p>To activate your account and start finding mortgage advisors please click on the following link</p>\n\n";
        // $msg .= "<a href='".config('constants.urls.email_verification_url').$this->getEncryptedId($advisor_id)."'>Activate Account</a>\n\n";
        // $msg .= "Best wishes\n\n";
        // $msg .= "The Mortgagebox team\n\n";
        // $msg = "You have successfully created account.\n Please verfiy your account by click below link ";
        // $msg .= config('constants.urls.email_verification_url');

        // $msg .= $this->getEncryptedId($request->user_id);
        //  $msg = wordwrap($msg, 70);

        // $mailStatus = mail($request->email, "Welcome to Mortgagebox.co.uk", $msg);
        $newArr = array(
            'name'=>$request->name,
            'email'=>$request->email,
            'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
        );
        $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Welcome to Mortgagebox.co.uk','','');
        //User created, return success response
        
        return response()->json([
            'status' => true,
            'message' => 'Advisor created successfully',
            'data' => $user
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
    public function addNewAdviceArea(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if ($user != "") {
            $ltv_max  = ($request->property)/$request->size_want;
            $lti_max  = ($request->property)/$request->combined_income;

            Advice_area::create([
                'user_id' => $user->id,
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
                'ltv_max'=>$ltv_max,
                'lti_max'=>$lti_max

            ]);
            //User created, return success response
            return response()->json([
                'status' => true,
                'message' => 'Advice area added successfully',

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
        $advice_area = Advice_area::where('user_id', '=', $id->id)->orderBy('id', 'DESC')->get();
        // ->leftJoin('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.id')

        // $advice_area =  DB::select('SELECT advice_areas.*,advisor_bids.status as bid_status FROM advice_areas  LEFT JOIN advisor_bids ON advice_areas.id = advisor_bids.area_id where user_id ='.$id->id.'');
        foreach ($advice_area as $key => $item) {
            // $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->id AND m.to_user_id != $id->id AND m.to_user_id_seen = 0");
            $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->id AND m.to_user_id = $id->id AND m.to_user_id_seen = 0");

            $advice_area[$key]->unread_message_count = $unread_count_total[0]->count_message;
            $offer_count = AdvisorBids::where('area_id','=',$item->id)->count();
            $advice_area[$key]->offer_count = $offer_count;
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
        $advice_area = Advice_area::where('id', '=', $id)->first();
        $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $advice_area->id AND m.to_user_id_seen = 0");
        $advice_area->unread_message_count = $unread_count_total[0]->count_message;

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
            ], Response::HTTP_UNAUTHORIZED);
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
        $advice_area = Advice_area::where('id', '=', $request->id)->update([
            'status' => 2,
            'close_type' => $request->close_type,
            'advisor_id' => $request->advisor_id,
            'need_reminder' => $request->need_reminder,
            'initial_term' => $request->initial_term,
            'start_date' => $request->start_date,
        ]);
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
    public function searchAdvisor()
    {
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
        }
        return redirect()->away(config('constants.urls.host_url'));
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
    public function resendActivationMail()
    {
        $user = JWTAuth::parseToken()->authenticate();

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

    function matchLeads()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id','=',$user->id)->first();
        $requestTime = [];
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
        
        // TODO: Ltv max and Lti Max need to check for filter
        $userPreferenceProduct = AdvisorPreferencesProducts::where('advisor_id','=',$user->id)->first();
        $service_type = array();
        if(!empty($userPreferenceProduct)) {
            if($userPreferenceProduct->remortgage == 1) {
                $service_type[] = "remortgage";
            }
            if($userPreferenceProduct->first_buyer == 1) {
                $service_type[]= "first time buyer";
            }
            if($userPreferenceProduct->next_buyer == 1) {
                $service_type[]= "next time buyer";
            }
            if($userPreferenceProduct->but_let == 1) {
                $service_type[]= "buy to let";
            }
            if($userPreferenceProduct->equity_release == 1) {
                $service_type[]= "equity release";
            }
            if($userPreferenceProduct->overseas == 1) {
                $service_type[]= "overseas";
            }
            if($userPreferenceProduct->self_build == 1) {
                $service_type[]= "self build";
            }
            if($userPreferenceProduct->mortgage_protection == 1) {
                $service_type[]= "mortgage protection";
            }
            if($userPreferenceProduct->secured_loan == 1) {
                $service_type[]= "secured loan";
            }
            if($userPreferenceProduct->bridging_loan == 1) {
                $service_type[]= "bridging loan";
            }
            if($userPreferenceProduct->commercial == 1) {
                $service_type[]= "commercial";
            }
            if($userPreferenceProduct->something_else == 1) {
                $service_type[]= "something else";
            }
        }
        // DB::enableQueryLog();
        
       $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address')
            ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
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
                            $q->orWhere('advice_areas.service_type',$sitem);
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
        })->whereNotIn('advice_areas.id',function($query) use ($user){
            $query->select('area_id')->from('advisor_bids')->where('advisor_id','=',$user->id);
        })->orderBy('advice_areas.id','DESC')->groupBy('advice_areas.'.'id')
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
        ->groupBy('advice_areas.'.'advisor_preference_language')->paginate();
        $bidCountArr = array();
        //$lastquery = DB::getQueryLog();
        //dd(end($lastquery));
        //echo '<pre>=';print_r($advice_area);die;
        foreach($advice_area as $key=> $item) {
            $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3)? 0:1;
            }
            $advice_area[$key]->totalBids = $bidCountArr;
            
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
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
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
            $advice_area[$key]->lead_value = $item->size_want_currency.$lead_value;
            $advice_area[$key]->is_read = 0;
            $read = AdviceAreaRead::where('area_id',$item->id)->where('adviser_id','=',$user->id)->first();
            if($read){
                $advice_area[$key]->is_read = 1;
            }
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

    function searchMortgageNeeds(Request $request)
    {
        // TODO: promotion: ["early-bird", "75-off", "none", "none", "free", "50-off"]:
        // $area_arr = array();
        $user = JWTAuth::parseToken()->authenticate();
        $userPreferenceCustomer = AdvisorPreferencesCustomer::where('advisor_id','=',$user->id)->first();
        $ltv_max = $userPreferenceCustomer->ltv_max;
        $lti_max = $userPreferenceCustomer->lti_max;
        // TODO: Ltv max and Lti Max need to check for filter
        $service_type = $request->advice_area;
        // echo print_r($service_type);die;
        $fees_preference = $request->fees_preference;
        $promotion = $request->promotion;
        $mortgage_value = $request->mortgage_value;
        $lead_submitted = $request->lead_submitted;
        $status = $request->status;
        $search = $request->search;
        $area_arr = array(-1);
        if(isset($status) && count($status)){
            foreach($status as $items){
                if($items=='read' || $items=='unread'){
                    $area = AdviceAreaRead::where('adviser_id',$user->id)->get();
                    foreach($area as $area_data){
                        array_push($area_arr,$area_data->area_id);
                    }
                }
            }
        }
        // $promotion_data_arr = array(-1);
        // if(isset($promotion) && count($promotion)){
        //     foreach($promotion as $promotion_data){
        //         if($promotion_data=="none") {
        //             $q->where('advice_areas.fees_preference',0);
        //         }
        //     }
        // }
        //, 'users.name', 'users.email', 'users.address'
        
        $advice_area =  Advice_area::select('advice_areas.*','users.name', 'users.email', 'users.address')
            ->leftJoin('users', 'advice_areas.user_id', '=', 'users.id')
            ->leftJoin('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
            ->where(function($query) use ($service_type){
                if(!empty($service_type)  && count($service_type) > 0) {
                    $query->where(function($q) use ($service_type) {
                        foreach($service_type as $item ){
                            $q->orWhere('advice_areas.service_type',$item);
                        }
                    });
                }
            
        })->where(function($query) use ($search){
            if($search != "") {
                $query->orWhere('advice_areas.service_type', 'like', '%' . $search . '%');
                $query->orWhere('advice_areas.description', 'like', '%' . $search . '%');
                $query->orWhere('advice_areas.request_time', 'like', '%' . $search . '%');
                $query->orWhere('advice_areas.advisor_preference_language', 'like', '%' . $search . '%');
            }
            
        })->where(function($query) use ($fees_preference){
            if(!empty($fees_preference) && count($fees_preference) > 0) {
                $query->where(function($q) use ($fees_preference) {
                    foreach($fees_preference as $item ){
                        if($item=="no_fee") {
                            $q->where('advice_areas.fees_preference',0);
                        }
                    }
                });
            }
        })->where(function($query) use ($mortgage_value){
            if(!empty($mortgage_value) && count($mortgage_value) > 0) {
                $query->where(function($q) use ($mortgage_value) {
                    $minMaxArr = array();
                    foreach($mortgage_value as $item ){
                        if($item != "") {
                            if($item=="1m"){
                                $minMaxArr[] = 1000000;
                                $minMaxArr[] = 1000000*100000;
                             }else{
                                [$min,$max] = explode("_",$item);
                                $minMaxArr[] = $min*1000;
                                $minMaxArr[] = $max*1000;
                             }
                        }
                         
                    }
                    if(count($minMaxArr) > 0) {
                        $q->where('advice_areas.size_want','>=',min($minMaxArr));
                        $q->where('advice_areas.size_want','>=',max($minMaxArr));
                    }
                });
            }
        })->where(function($query) use ($lead_submitted){

            //
            if(!empty($lead_submitted)) {
                $query->where(function($q) use ($lead_submitted) {
                    foreach($lead_submitted as $item ){
                        //lead_submitted: ["anytime", "yesterday", "last_hour", "less_3_days", "today", "less_3_week"]: ///
                        if($item!="anytime"){
                            if($item=="today"){
                                $q->orWhereDate('advice_areas.created_at', Carbon::today());
                            }else if($item=="yesterday") {
                                $q->orWhereDate('advice_areas.created_at', Carbon::yesterday());
                            }else if($item=="last_hour") {
                                //TODO last hour query
                               // $q->orWhereDate('created_at', Carbon::yesterday());
                            }else if($item=="less_3_days") {
                             $q->orWhere('advice_areas.created_at', '>', Carbon::yesterday()->subDays(3))->where('advice_areas.created_at', '<', Carbon::today())->count();
                            }else if($item=="less_3_week") {
                                $q->orWhere('advice_areas.created_at', '>', Carbon::yesterday()->subDays(21))->where('advice_areas.created_at', '<', Carbon::today())->count();
                            }
                        }
                        
                    }
                });
            }
        })->where(function($query) use ($status,$user){
            $area_arr_data = array(-1);
            if(!empty($status)) {
                $query->where(function($q) use ($status,$user) {
                    foreach($status as $item){
                        // status: ["unread", "read", "not-interested"]: calculate from bid table: advisor_status column: 
                        if($item == "read") {
                            $q->whereIn('advice_areas.id',$area_arr);
                        }else if($item == "unread") {
                            $q->whereNotIn('advice_areas.id',$area_arr);
                        }else if($item == "not-interested") {
                            $q->orWhere('advisor_bids.advisor_status','=',2)->where('advisor_bids.advisor_id',$user->id);
                        }
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
        })->groupBy('advice_areas.'.'id')
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
        ->groupBy('advice_areas.'.'advisor_preference_language')
        ->groupBy('users.'.'name')
        ->groupBy('users.'.'email')
        ->groupBy('users.'.'address')
        ->groupBy('advice_areas.'.'ltv_max')
        ->groupBy('advice_areas.'.'lti_max')
        ->paginate();
        $bidCountArr = array();
        foreach($advice_area as $key=> $item) {
            $adviceBid = AdvisorBids::where('area_id',$item->id)->orderBy('status','ASC')->get();
            foreach($adviceBid as $bid) {
                $bidCountArr[] = ($bid->status == 3)? 0:1;
            }
            $advice_area[$key]->totalBids = $bidCountArr;

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
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
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
            $advice_area[$key]->lead_value = $item->size_want_currency.$lead_value;
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
                    $amount = number_format((float)$costOfLead, 2, '.', '');
                    if($hourdiff < 24) {
                        $discounted_price = 0;
                    }
                    if($hourdiff > 24 && $hourdiff < 48) {
                        $discounted_price = number_format((float)($amount/2), 2, '.', '');;
                    }
                    if($hourdiff > 48 && $hourdiff < 72) {
                        $newAmount = (75 / 100) * $amount;
                        $discounted_price = number_format((float)($newAmount), 2, '.', '');;
                    }
                    if($hourdiff > 72) {
                        $discounted_price = number_format((float)($costOfLead), 2, '.', '');;
                    }
                    
                    $advice_area = AdvisorBids::create([
                        'advisor_id' => $request->advisor_id,
                        'area_id' => $request->area_id,
                        'advisor_status' => $request->advisor_status,
                        'cost_leads'=>$costOfLead,
                        'cost_discounted'=>$discounted_price
                    ]);
                   
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
                        'message' => 'Bid placed successfully',
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
        if (isset($request->emails) && !empty($request->emails)) {
            //To be discussed
            // $invitedUrl = config('constants.urls.host_url');
            // $invitedUrl .= "/invite/" . $this->getEncryptedId($user->id);
            // $emailSubject = "MortgageBox Invitation";
            // $emailList = implode(", ", $request->emails);

            // $to = $request->emails[0];
            // $subject = $emailSubject;
            // $headers = "Bcc: " . $emailList . "\r\n";
            // $headers .= "From: no-reply@mortgagebox.com\r\n" .
            //     "X-Mailer: php";
            // $headers .= "MIME-Version: 1.0\r\n";
            // $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            // $message = '<html><body>';
            // $message .= "Hi,\r\n";
            // $message .= $user->name . " invites you to join MortgageBox. Please click on below link to join\r\n<br>";
            // $message .= $invitedUrl;
            // $message .= '</body></html>';
            // $message = wordwrap($message, 70);
            // //echo $invitedUrl;

            // mail($to, $subject, $message, $headers);

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

        $advice_area =  AdvisorBids::select('advisor_bids.*', 'users.name', 'users.email', 'users.address', 'advisor_profiles.display_name', 'advisor_profiles.tagline', 'advisor_profiles.FCANumber', 'advisor_profiles.company_name', 'advisor_profiles.phone_number', 'advisor_profiles.address_line1', 'advisor_profiles.address_line2', 'advisor_profiles.city', 'advisor_profiles.postcode', 'advisor_profiles.web_address', 'advisor_profiles.facebook', 'advisor_profiles.image', 'advisor_profiles.short_description')
            ->join('users', 'advisor_bids.advisor_id', '=', 'users.id')
            ->join('advisor_profiles', 'advisor_bids.advisor_id', '=', 'advisor_profiles.advisorId')
            ->where('advisor_bids.area_id', '=', $id)
            ->where('advisor_bids.advisor_status', '=', $status)
            ->get();

        if ($advice_area) {
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
            }
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

    public function startChat(Request $request){
        $user = JWTAuth::parseToken()->authenticate();
        $chatData = array();
        $channel_id  = 0;
        $channel_name = "channel" . "-" . $request->advicearea_id . "-" . $request->from_user_id . "-" . $request->to_user_id;
        $channelDetails =  ChatChannel::where('from_user_id', '=', $request->from_user_id)->where('to_user_id', '=', $request->to_user_id)->first();
        $channelExist =  ChatChannel::where('from_user_id', '=', $request->to_user_id)->where('to_user_id', '=', $request->from_user_id)->first();
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
        return response()->json([
            'status' => true,
            'channel' => ['channel_id' => $channel_id, 'channel_name' => $channel_name],
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    public function sendMessage(Request $request)
    {
        JWTAuth::parseToken()->authenticate();
        $chatData = array();
        $message = ChatModel::create([
            'from_user_id' => $request->from_user_id,
            'to_user_id' => $request->to_user_id,
            'channel_id' => $request->channel_id,
            'text' => $request->text,

        ]);
        $message_id  = $message->id;

        $chatData = ChatModel::where('id', '=', $message_id)->orderBy('id', 'desc')->first();
        return response()->json([
            'status' => true,
            'channel' => $request->channel_id,
            'data' => $chatData
        ], Response::HTTP_OK);
    }

    function advisorAcceptedLeads()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address', 'advisor_bids.advisor_id as advisor_id')
        ->join('users', 'advice_areas.user_id', '=', 'users.id')
        ->join('advisor_bids', 'advice_areas.id', '=', 'advisor_bids.area_id')
        ->where('advisor_bids.advisor_status', '=', 1)
        ->where('advisor_bids.advisor_id', '=', $user->id)
        ->paginate();

        $bidCountArr = array();
        foreach($advice_area as $key=> $item) {
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
                        // $address .= ($addressDetails->District != "") ? $addressDetails->District."," : '';
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
            $advice_area[$key]->lead_value = $item->size_want_currency.$lead_value;
            // $show_status = "Live Leads"; 
            $bidDetailsStatus = AdvisorBids::where('area_id',$item->id)->where('advisor_id','=',$user->id)->first();
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
            $advice_area[$key]->show_status = $show_status;

            $channelIds = array(-1);
            $channelID = ChatChannel::where('advicearea_id',$item->id)->orderBy('id','DESC')->get();
            foreach ($channelID as $chanalesR) {
                array_push($channelIds, $chanalesR->id);
            }
            $advice_area[$key]->last_notes = UserNotes::where('advice_id', '=', $item->id)->where('user_id',$user->id)->get();
            $last_chat_data = ChatModel::whereIn('channel_id',$channelIds)->take(5)->orderBy('id','DESC')->with('from_user')->with('to_user')->get();
            if(isset($last_chat_data) && count($last_chat_data)){
                 foreach($last_chat_data as $chat){
                     if(date('Y-m-d')==date("Y-m-d",strtotime($chat->created_at))){
                        $chat->date_time = date("H:i",strtotime($chat->created_at));
                    }else{
                        $chat->date_time = date("d M Y H:i",strtotime($chat->created_at));
                    }
                 }
            }

            $advice_area[$key]->spam_info = AdviceAreaSpam::where('area_id',$item->id)->where('user_id','=',$user->id)->first();
            
            $advice_area[$key]->last_chat = $last_chat_data;
            // echo "<br>";
            // echo "Preference Value==".$AdvisorPreferencesDefault->$advisorDetaultValue;
            // echo "<br>";
            // echo "Size want==".$item->size_want;
            // echo "<br>";
            // echo "After Calclulate Value==".$main_value;
            // echo "<br>";
            // echo "Divided Value==".($item->size_want/100);
            // echo "<br>";
            // echo "Final Value==".$lead_value;
            // echo "<br>";
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

    function getRecentMessages()
    {
        $user = JWTAuth::parseToken()->authenticate();
  
        $chatData = \DB::select("
                SELECT chat_models.*,users.name, advisor_profiles.display_name, advisor_profiles.company_name FROM `chat_models` LEFT JOIN `advisor_profiles` ON chat_models.from_user_id = advisor_profiles.advisorId LEFT JOIN `users` ON chat_models.from_user_id = users.id WHERE chat_models.id IN (SELECT MAX(id) FROM chat_models WHERE chat_models.to_user_id = $user->id AND chat_models.to_user_id_seen = 0 GROUP BY chat_models.channel_id)
            ");

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
            $uploadFolder = 'chat';
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


            if ($uploadedImageResponse['mime'] == "image/png" || $uploadedImageResponse['mime'] == "image/jpeg" || $uploadedImageResponse['mime'] == "image/jpg" || $uploadedImageResponse['mime'] == "image/gif") {
                $message_type = 1;
            } else if ($uploadedImageResponse['mime'] == "application/vnd.openxmlformats-officedocument.wordprocessingml.document") {
                $message_type = 2;
            } else if ($uploadedImageResponse['mime'] == "application/pdf") {
                $message_type = 3;
            } else if ($uploadedImageResponse['mime'] == "application/csv") {
                $message_type = 4;
            } else if ($uploadedImageResponse['mime'] == "application/zip") {
                $message_type = 6;
            } else {
                $message_type = 7;
            }
        }
        $uploadedImageResponse['image_url'];
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
            'data' => $chatData
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

    public function selectOrDeclineOffer($id,$status) {
        //1:accepted, 3: rejected
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorBids::where('id', '=', $id)->update([
            'status'=>$status
        ]);
        $bidDetails = AdvisorBids::where('id', '=', $id)->first();
        $message = "";
        if($status==1){
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
        }else{
            $message = "Offer declined";
            $this->saveNotification(array(
                'type'=>'2', // 1:
                'message'=>'Your bid declined by customer '.$user->name, // 1:
                'read_unread'=>'0', // 1:
                'user_id'=>$user->id,// 1:
                'advisor_id'=>$bidDetails->advisor_id, // 1:
                'area_id'=>$bidDetails->area_id,// 1:
                'notification_to'=>1
            ));
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
        $stripe = new \Stripe\StripeClient(
            'sk_test_51J904DSD18gBSiDIJxuDFBCwwNmhwgTXiU2SPAWDt20XaL7htbtnHKBkqfzu9tTJxPaDBcKrK6KCfxQO0MJcdcGX00zusc4Lug'
          );
          $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
          if($advisorDetails->stripe_customer_id != "" && $advisorDetails->stripe_customer_id != null){
              try {
                    $paymentDetails =  $stripe->paymentMethods->create([
                        'type' => 'card',
                        'card' => [
                          'number' => $request->account_number,
                          'exp_month' => $request->exp_month,
                          'exp_year' => $request->exp_year,
                          'cvc' => $request->cvc,
                        ],
                    ]);
                    $payment_method = $stripe->paymentMethods->retrieve($paymentDetails['id']);
                    $payment_method->attach(['customer' => $advisorDetails->stripe_customer_id]);
                    return response()->json([
                        'status' => true,
                        'message' => "Card saved successfully",
                        'data' => []
                    ], Response::HTTP_OK);
        
                  }catch(Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                        'data' => []
                    ], Response::HTTP_OK);
                  }
          }else{
            $status = $this->createCustomer($advisorDetails);
            if($status) {
                $advisorDetails = AdvisorProfile::where('advisorId','=',$user->id)->first();
                try {
                    $paymentDetails =  $stripe->paymentMethods->create([
                        'type' => 'card',
                        'card' => [
                          'number' => $request->account_number,
                          'exp_month' => $request->exp_month,
                          'exp_year' => $request->exp_year,
                          'cvc' => $request->cvc,
                        ],
                    ]);
                    $payment_method = $stripe->paymentMethods->retrieve($paymentDetails['id']);
                    $payment_method->attach(['customer' => $advisorDetails->stripe_customer_id]);
                    return response()->json([
                        'status' => true,
                        'message' => "Card saved successfully",
                        'data' => []
                    ], Response::HTTP_OK);
        
                  }catch(Exception $e) {
                    return response()->json([
                        'status' => false,
                        'message' => $e->getMessage(),
                        'data' => []
                    ], Response::HTTP_OK);
                  }
            }else {
                return response()->json([
                    'status' => false,
                    'message' => "Something went wrong",
                    'data' => []
                ], Response::HTTP_OK);
            }
          }
          
        
        //cus_Jn74KtxONwHBv5
        // print_r($payment_method);
    }

    function createCustomer($request) {
        require_once(public_path().'/stripe/init.php');
        $user = JWTAuth::parseToken()->authenticate();
        //$Skey = config('constants.stripe.stripe_secret_key'); 
        \Stripe\Stripe::setApiKey('sk_test_51J904DSD18gBSiDIJxuDFBCwwNmhwgTXiU2SPAWDt20XaL7htbtnHKBkqfzu9tTJxPaDBcKrK6KCfxQO0MJcdcGX00zusc4Lug');
        // echo $request->currency;die;
        try{
            $advisorDetails = User::where('id','=',$request->advisorId)->first();
            $responseData = \Stripe\Customer::create([
                'name'=>($advisorDetails->name) ? $request->name : '',
                'email'=>($advisorDetails->email) ? $advisorDetails->email : '',
                'address[city]'=>($request->city) ? $request->city : '',
                'address[country]'=>'',
                'address[line1]'=>($request->address_line1) ? $request->address_line1: '',
                'address[line2]'=>($request->address_line2) ? $request->address_line2: '',
                'address[postal_code]'=>($request->postal_code)?$request->postal_code:'',
                'address[state]'=>'',
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
                'sk_test_51J904DSD18gBSiDIJxuDFBCwwNmhwgTXiU2SPAWDt20XaL7htbtnHKBkqfzu9tTJxPaDBcKrK6KCfxQO0MJcdcGX00zusc4Lug'
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
                'sk_test_51J904DSD18gBSiDIJxuDFBCwwNmhwgTXiU2SPAWDt20XaL7htbtnHKBkqfzu9tTJxPaDBcKrK6KCfxQO0MJcdcGX00zusc4Lug'
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
                \Stripe\Stripe::setApiKey('sk_test_51J904DSD18gBSiDIJxuDFBCwwNmhwgTXiU2SPAWDt20XaL7htbtnHKBkqfzu9tTJxPaDBcKrK6KCfxQO0MJcdcGX00zusc4Lug');
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
            ->get();
            $notificationCount = Notifications::where('advisor_id', '=', $user->id)->where('notification_to','=','1')->where('read_unread',0)
            ->count();
        }else{
            $notification = Notifications::where('user_id', '=', $user->id)->where('notification_to','=','0')->get();
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
        $result = ServiceType::get();
        if(!empty($result)) {
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
                AdviceAreaSpam::insertGetId($postData);
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
}