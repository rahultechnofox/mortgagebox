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
class AdviceController extends Controller
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
            $invited_by = 0;
            if($request->invited_by != "") {
                $invited_by = $this->getDecryptedId($request->invited_by);
                
                $user = User::findOrFail($invited_by);
                $user->invite_count = $user->invite_count + 1;
                $user->save();
            }
            
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'post_code' => $request->post_code,
                'password' => bcrypt($request->password),
                'invited_by' => $invited_by,
            ])->id;
            $request->user_id = $user;
            // $msg = "";
            // $msg .= "Welcome\n\n";
            // $msg .= "Hello ".ucfirst($request->name).",\n\n";
            // $msg .= "<p>Finding the right mortgage should be easy, but too often it's a hassle. Some mortgage web-sites / advisors aren't as helpful or that easy to use. And how can you be sure you've been given the best deal when you only use one?</p>\n\n";
            // $msg .= "<p>That's why we launched mortgagebox. To give you choice by matching you to five expert mortgage advisers, based on your mortgage needs, who then contoct you initially through mortgagebox</p>\n\n";
            // $msg .= "<p>Meet/talk/message the advisers and then choose the one best suited to your needs. This could be based on product, speed of execution, service offered, lack of fees or how well you gel with the adviser</p>\n\n";
            // $msg .= "<p>We've created a free account for you to manage your mortgage need. Please click the link below to activate your account and start finding your mortgage advisers</p>\n\n";
            // $msg .= "<a href='".config('constants.urls.email_verification_url').$this->getEncryptedId($request->user_id)."'>Activate Account</a>\n\n";
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
        } 
        
        $user = Advice_area::create([
            'user_id' => $request->user_id,
            // 'service_type' => $request->service_type,
            'service_type_id' => (int)$request->service_type_id,
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
    function getEncryptedId($id) {
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

	function getDecryptedId($id) {
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
		return openssl_decrypt ($id, $ciphering, $encryption_key, $options, $encryption_iv);
	}

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}
