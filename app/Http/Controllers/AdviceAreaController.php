<?php

namespace App\Http\Controllers;

use App\Models\Advice_area;
use JWTAuth;
use App\Models\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Exception;

class AdviceAreaController extends Controller
{
    public function addAdviceArea(Request $request) {
        if($request->user_id == 0 || $request->user_id == "") {
            
            $data = $request->only('name', 'email', 'password');
            $validator = Validator::make($data, [
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:6|max:50'
            ]);
            //Send failed response if request is not valid
            if ($validator->fails()) {
                return response()->json(['status'=>false,'error' => $validator->messages()], 200);
            }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'post_code' => $request->post_code,
                'password' => bcrypt($request->password)
            ])->id;
             
            $request->user_id = $user;

            $newArr = array(
                'name'=>$request->name,
                'email'=>$request->email,
                'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($request->user_id)
            );
            $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$request->email,$request->name,'Email Verification','','');
           
            // $credentials = $request->only('email', 'password');
            // try {
            //     $token = JWTAuth::attempt($credentials, ['exp' => Carbon::now()->addDays(7)->timestamp]);

            //     JWTAuth::authenticate($token);
            // }catch(Exception $e){
            //     return response()->json([
            //         'success' => false,
            //         'token' => $token,
                   
            //     ], Response::HTTP_OK);
            // }
        } 
       // JWTAuth::parseToken()->authenticate();
        $user = Advice_area::create([
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
            'success' => true,
            'message' => 'Advice area added successfully',
            'data' => $user
        ], Response::HTTP_OK);
    }
}
