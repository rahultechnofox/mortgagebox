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
        return view('users.index',$data);
    }
    /**
     * Display the specified resource..
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $userDetails = User::where('id','=',$id)->first();
        $advice_area =  Advice_area::select('advice_areas.*')
            ->where('advice_areas.user_id', '=', $id)
            ->get();
        $userDetails->total_needs = count($advice_area);
        $adviceBidClosed = 0;
        $adviceBidActive = 0;
        $pendingBidCount = 0;
        foreach($advice_area as $items) {
            $adviceBidCl= AdvisorBids::where('area_id',$items->id)->where('status','=','2')->get();
            $adviceBidClosed = $adviceBidClosed+count($adviceBidCl);
            $adviceBidAc= AdvisorBids::where('area_id',$items->id)->where('status','=','1')->get();
            $adviceBidActive = $adviceBidActive+count($adviceBidAc);
            $pendingCount= AdvisorBids::where('area_id',$items->id)->where('status','=','0')->get();
            $pendingBidCount = $pendingBidCount+count($pendingCount);
        }
        $userDetails->closed = $adviceBidClosed;
        $userDetails->active_bid = $adviceBidActive;
        $userDetails->pending_bid = $pendingBidCount;
        return view('users.show',['userDetails'=>$userDetails]);
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

    public function addReview(Request $request){
        $userDetails = JWTAuth::parseToken()->authenticate();
        $rating = ReviewRatings::create([
            'user_id' => $userDetails->id,
            'advisor_id' => $request->advisor_id,
            'rating' => $request->rating,
            'review_title' => $request->review_title,
            'reviews' =>$request->reviews,
            'status' => $request->status,
            'parent_review_id' => $request->parent_review_id,
            'reply_reason' =>$request->reply_reason,
            'spam_reason' => $request->spam_reason
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
        return response()->json([
            'status' => true,
            'message' => 'Rating added successfully',
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
            if($user){
                return response(\Helpers::sendSuccessAjaxResponse('Customer deleted successfully.',$user));
            }else{
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
}