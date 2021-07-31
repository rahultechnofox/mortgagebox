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
use App\Models\ReviewRatings;
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

        $msg = "You have successfully created account.\n Please verfiy your account by click below link ";
        $msg .= config('constants.urls.email_verification_url');

        $msg .= $this->getEncryptedId($user->id);
        $msg = wordwrap($msg, 70);
        mail($request->email, "Email Verification", $msg);

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
        $user->slug = $this->getEncryptedId($user->id);
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
            $msg = "You have successfully changed your email.\n Please verfiy your account by click below link ";
            $msg .= config('constants.urls.email_verification_url');

            $msg .= $this->getEncryptedId($userDetails->id);
            $msg = wordwrap($msg, 70);
            mail($request->email, "Email Verification", $msg);
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

            $msg = "You have successfully created account.\n Please verfiy your account by click below link ";
            $msg .= config('constants.urls.email_verification_url');

            $msg .= $this->getEncryptedId($request->user_id);
            $msg = wordwrap($msg, 70);
            mail($request->email, "Email Verification", $msg);

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
        if (!empty($company_data)) {
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
            'company_id' => $company_id

        ]);
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
            $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE c.advicearea_id = $item->id AND m.to_user_id_seen = 0");

            $advice_area[$key]->unread_message_count = $unread_count_total[0]->count_message;
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
        UserNotes::create([
            'user_id' => $user->id,
            'notes' => $request->notes,
            'advice_id' => $request->advice_id,
        ]);
        //User created, return success response
        return response()->json([
            'status' => true,
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
            $result =  $userDetails->update([
                'email_status' => 1,
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

            $msg = "Your temporary password is \n" . $password;
            $msg = wordwrap($msg, 70);
            mail($userDetails->email, "Forgot Password", $msg);

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

        $msg = "To verify your email \n Please click below link ";
        $msg .= config('constants.urls.email_verification_url');

        $msg .= $this->getEncryptedId($user->id);
        $msg = wordwrap($msg, 70);
        mail($user->email, "Email Verification", $msg);

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
    function searchMortgageNeeds()
    {
        $user = JWTAuth::parseToken()->authenticate();
        $advice_area =  Advice_area::select('advice_areas.*', 'users.name', 'users.email', 'users.address')
            ->join('users', 'advice_areas.user_id', '=', 'users.id')
            ->paginate();

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
        if ($request->advisor_status == 1) {
            $advice_area = AdvisorBids::create([
                'advisor_id' => $request->advisor_id,
                'area_id' => $request->area_id,
                'advisor_status' => $request->advisor_status,
            ]);
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
            return response()->json([
                'status' => true,
                'message' => 'Mark as not intrested',
            ], Response::HTTP_OK);
        }
    }

    public function inviteUsers(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (isset($request->emails) && !empty($request->emails)) {
            $invitedUrl = config('constants.urls.host_url');
            $invitedUrl .= "/invite/" . $this->getEncryptedId($user->id);
            $emailSubject = "MortgageBox Invitation";
            $emailList = implode(", ", $request->emails);

            $to = $request->emails[0];
            $subject = $emailSubject;
            $headers = "Bcc: " . $emailList . "\r\n";
            $headers .= "From: no-reply@mortgagebox.com\r\n" .
                "X-Mailer: php";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";
            $message = '<html><body>';
            $message .= "Hi,\r\n";
            $message .= $user->name . " invites you to join MortgageBox. Please click on below link to join\r\n<br>";
            $message .= $invitedUrl;
            $message .= '</body></html>';
            $message = wordwrap($message, 70);
            //echo $invitedUrl;

            mail($to, $subject, $message, $headers);

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
    public function startChat(Request $request)
    {

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
        // $chatData =  ChatModel::select('chat_models.*','advisor_profiles.display_name','advisor_profiles.company_name')
        //                 ->join('advisor_profiles', 'chat_models.from_user_id', '=', 'advisor_profiles.advisorId' )
        //                  ->where('to_user_id','=',$user->id)->where('to_user_id_seen','=',0)->get();
        $chatData = \DB::select("
                SELECT chat_models.*, advisor_profiles.display_name, advisor_profiles.company_name FROM `chat_models` LEFT JOIN `advisor_profiles` ON chat_models.from_user_id = advisor_profiles.advisorId WHERE chat_models.id IN (SELECT MAX(id) FROM chat_models WHERE chat_models.to_user_id = $user->id AND chat_models.to_user_id_seen = 0 GROUP BY chat_models.channel_id)
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
}
