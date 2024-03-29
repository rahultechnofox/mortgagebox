<?php

namespace App\Http\Controllers;

use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorOffers;
use App\Models\AdvisorProfile;
use App\Models\ChatChannel;
use App\Models\ChatModel;
use App\Models\ReviewRatings;
use App\Models\Notifications;
use App\Models\PostalCodes;

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

class UserController extends Controller
{
    protected $user;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['users'] = User::getLists($post);
        // echo json_encode($data);exit;
        return view('users.index',$data);
    }
    /**
     * Display the specified resource..
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $userDetails = User::where('id','=',$id)->first();
        if($userDetails){
            $postCode = PostalCodes::select('District','Country')->where('Postcode',$userDetails->post_code)->first();
            if($postCode){
                $userDetails->district = $postCode->District;
                $userDetails->country = $postCode->Country;
            }else{
                $userDetails->district = "";
                $userDetails->country = "";
            }
        }
        $advice_area =  Advice_area::select('advice_areas.*')
            ->where('advice_areas.user_id', '=', $id)
            ->get();
        $userDetails->total_needs = count($advice_area);
        $adviceBidClosed = 0;
        $adviceBidCompleted = 0;
        $adviceBidActive = 0;
        $pendingBidCount = 0;
        $finalCompletedClosed = 0;
        
        foreach($advice_area as $items) {
            $adviceBidCl= AdvisorBids::where('area_id',$items->id)->where('status','=','2')->get();
            $adviceBidCompleted = $adviceBidCompleted+count($adviceBidCl);
            $adviceBidCom= Advice_area::where('id',$items->id)->where('status','=','2')->get();
            $adviceBidClosed = $adviceBidClosed+count($adviceBidCom);
            $adviceBidAc= Advice_area::where('id',$items->id)->where('status','=','1')->get();
            $adviceBidActive = $adviceBidActive+count($adviceBidAc);
            $pendingCount= AdvisorBids::where('area_id',$items->id)->where('status','=','0')->get();
            $pendingBidCount = $pendingBidCount+count($pendingCount);
            $finalCompletedClosed = $adviceBidCompleted + $adviceBidClosed;
        }
        $userDetails->closed = $adviceBidClosed;
        $userDetails->final_closed = $finalCompletedClosed;
        $userDetails->active_bid = $adviceBidActive;
        $userDetails->pending_bid = $pendingBidCount;
        return view('users.show',['userDetails'=>$userDetails]);
    }

    function verifyEmail($id){
        $user = User::where('id',$id)->first();
        if($user){
            $newArr = array(
                'name'=>$user->name,
                'email'=>$user->email,
                'url' => config('constants.urls.email_verification_url')."".$this->getEncryptedId($user->id)
            );
            $c = \Helpers::sendEmail('emails.email_verification',$newArr ,$user->email,$user->name,'Email Verification','','');
        }
        return redirect()->back()->with('message',"Verification link is set to registered email id");
        
    }

    function sendResetPasswordEmail($id){
        $user = User::where('id',$id)->first();
        if($user){
            $password = $this->generateRandomString(10);
            $user->update([
                'password' => bcrypt($password)
            ]);
            $newArr = array(
                'name'=>$user->name,
                'email'=>$user->email,
                'password' => $password
            );
            $c = \Helpers::sendEmail('emails.reset_password',$newArr ,$user->email,$user->name,'Forgot Password','','');
        }
        return redirect()->back()->with('message',"Reset link is sent to registered email id");
        
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

    function generateRandomString($length = 10){
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function destroy($customer_id) {
        User::where('id', '=', $customer_id)->delete();
        Advice_area::where('user_id', '=', $customer_id)->delete();
        $data['message'] = 'Customer deleted!';
        return redirect()->to('admin/users')->with('message', $data['message']);
    }
    public function addReview(Request $request)
    {
        if(isset($request->reviewer_name) && $request->reviewer_name !="" ) {
            $userDetails = new \stdClass();
            $userDetails->id = 0;
            $userDetails->name = $request->reviewer_name; 

        }else{
            $request->reviewer_name = "";
            $userDetails = JWTAuth::parseToken()->authenticate();
        }
        $review_arr = array(
            'user_id' => $userDetails->id,
            'advisor_id' => $request->advisor_id,
            'rating' => $request->rating,
            'review_title' => $request->review_title,
            'reviews' =>$request->reviews,
            'status' => $request->status,
            'parent_review_id' => $request->parent_review_id,
            'reply_reason' =>$request->reply_reason,
            'spam_reason' => $request->spam_reason,
            'reviewer_name'=>$request->reviewer_name
        );
        if($request->reply_reason!=''){
            $review_arr['replied_on'] = date("Y-m-d H:i:s");
        }
        if(isset($request->area_id) && $request->area_id!=0){
            $review_arr['area_id'] = $request->area_id;
        }
        $rating = ReviewRatings::create($review_arr)->id;
        $this->saveNotification(array(
            'type'=>'4', // 1:
            'message'=>'New review recieved from customer '.$userDetails->name, // 1:
            'read_unread'=>'0', // 1:
            'user_id'=>$userDetails->id,// 1:
            'advisor_id'=>$request->advisor_id, // 1:
            'area_id'=>0,// 1:
            'notification_to'=>1
        ));
        $advisor = AdvisorProfile::where('advisorId',$request->advisor_id)->first();
        $userReviewd = User::where('id',$userDetails->id)->first();
        if(isset($advisor) && $advisor!=''){
            if(isset($userReviewd) && $userReviewd!=''){
                $newArr = array(
                    'name'=>$advisor->display_name,
                    'email'=>$advisor->email,
                    'message_text' => 'You have received a new review from '.$userReviewd->name,
                    'url' =>config('constants.urls.host_url')."/adviser?type=Review",
                    'btn_text' => 'Reply'
                );
                $c = \Helpers::sendEmail('emails.information',$newArr ,$advisor->email,$advisor->display_name,'Mortgagebox.co.uk – New Review from '.$userReviewd->name,'','');
            }
        }
        return response()->json([
            'status' => true,
            'message' => 'Rating added successfully',
        ], Response::HTTP_OK);
    }
    // public function addReview(Request $request)
    // {
    //     if(isset($request->reviewer_name) && $request->reviewer_name !="" ) {
    //         $userDetails = new \stdClass();
    //         $userDetails->id = 0;
    //         $userDetails->name = $request->reviewer_name; 

    //     }else{
    //         $request->reviewer_name = "";
    //         $userDetails = JWTAuth::parseToken()->authenticate();
    //     }
         
    //     // foreach($advice_area as $items) {
    //     //     $adviceBidCl= AdvisorBids::where('area_id',$items->id)->where('status','=','2')->get();
    //     //     $adviceBidClosed = $adviceBidClosed+count($adviceBidCl);
    //     //     $adviceBidAc= Advice_area::where('id',$items->id)->where('status','=','1')->get();
    //     //     $adviceBidActive = $adviceBidActive+count($adviceBidAc);
    //     //     $pendingCount= AdvisorBids::where('area_id',$items->id)->where('status','=','0')->get();
    //     //     $pendingBidCount = $pendingBidCount+count($pendingCount);
    //     // }
    //     // $userDetails->closed = $adviceBidClosed;
    //     // $userDetails->active_bid = $adviceBidActive;
    //     // $userDetails->pending_bid = $pendingBidCount;
    //     // echo json_encode($userDetails);exit;
    //     return view('users.show',['userDetails'=>$userDetails]);
    // }
    public function openAddReview(Request $request)
    {
        if(isset($request->reviewer_name) && $request->reviewer_name !="" ) {
            $userDetails = new \stdClass();
            $userDetails->id = 0;
            $userDetails->name = $request->reviewer_name; 

        }else{
            $request->reviewer_name = "";
            $userDetails = JWTAuth::parseToken()->authenticate();
        }
        
        $rating = ReviewRatings::create([
            'user_id' => $userDetails->id,
            'advisor_id' => $request->advisor_id,
            'rating' => $request->rating,
            'review_title' => $request->review_title,
            'reviews' =>$request->reviews,
            'status' => $request->status,
            'parent_review_id' => $request->parent_review_id,
            'reply_reason' =>$request->reply_reason,
            'spam_reason' => $request->spam_reason,
            'reviewer_name'=>$request->reviewer_name,
            'is_invited'=>1,
        ])->id;
        $this->saveNotification(array(
            'type'=>'4', // 1:
            'message'=>'New review recieved from customer '.$userDetails->name, // 1:
            'read_unread'=>'0', // 1:
            'user_id'=>$userDetails->id,// 1:
            'advisor_id'=>$request->advisor_id, // 1:
            'area_id'=>0,// 1:
            'notification_to'=>1
        ));
         $advisor = AdvisorProfile::where('advisorId',$request->advisor_id)->first();
        $userReviewd = User::where('id',$userDetails->id)->first();

            $newArr = array(
                'name'=>$advisor->display_name,
                'email'=>$advisor->email,
                'message_text' => 'You have received a new review from '.$userDetails->name,
                'url' =>config('constants.urls.host_url')."/adviser?type=Review",
                'btn_text' => 'Reply'
            );
            $c = \Helpers::sendEmail('emails.information',$newArr ,$advisor->email,$advisor->display_name,'Mortgagebox.co.uk – New Review from '.$userDetails->name,'','');
        return response()->json([
            'status' => true,
            'message' => 'Rating added successfully',
        ], Response::HTTP_OK);
    }
    public function dashboard(User $model)
    {
        // return view('users.index');
        return view('dashboard');
    }
    public function users(User $model)
    {
        // return view('users.index');
        return view('users');
    }
    public function saveNotification($data) {
        $notification = Notifications::create($data);
        if($notification) {
            return true;
        }else {
            return false;
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request){
        try {
            $post = $request->all();
            $validate = [
                'password' => 'required'
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                if(isset($post['password']) && $post['password']!=''){
                    $post['password'] = Hash::make($post['password']);
                }
                unset($post['_token']);
                $user = User::where('id',$post['id'])->update($post);
                if($user){
                    return response(\Helpers::sendSuccessAjaxResponse('Customer updated successfully.',$user));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }

    /**
     * Delete the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deleteCustomer(Request $request){
        try {
            $post = $request->all();
            $user = User::where('id',$post['id'])->delete();
            return response(\Helpers::sendSuccessAjaxResponse('Customer deleted successfully.',[]));
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
    public function updateStatus(Request $request){
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

    /**
     * Update email the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateEmail(Request $request){
        try {
            $post = $request->all();
            $validate = [
                'id' => 'required',
                'email' => 'required'
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                unset($post['_token']);
                $checkUser = User::where('email',$post['email'])->where('id','!=',$post['id'])->first();
                if($checkUser){
                    return response(\Helpers::sendFailureAjaxResponse("Email is already exist."));
                }else{
                    $user = User::where('id',$post['id'])->update($post);
                    $checkAdvisor = AdvisorProfile::where('advisorId',$post['id'])->first();
                    if($checkAdvisor){
                        AdvisorProfile::where('id',$checkAdvisor->id)->update($post);
                    }
                    if($user){
                        return response(\Helpers::sendSuccessAjaxResponse('Email updated successfully.',$user));
                    }else{
                        return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                    }
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
}