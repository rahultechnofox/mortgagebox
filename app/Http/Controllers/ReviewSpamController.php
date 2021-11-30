<?php

namespace App\Http\Controllers;
use App\Models\ReviewSpam;
use App\Models\ReviewRatings;

use App\Models\Invoice;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class ReviewSpamController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['result'] = ReviewSpam::getReviewSpam($post);
        // echo json_encode($data);exit;
        return view('review_spam.index',$data);
    }

    /**
     * Take decision status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function takeDecision(Request $request){
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
                // $post['spam_status'] = 0;
                $spam = ReviewSpam::where('id',$post['id'])->first();
                if($spam){
                    ReviewRatings::where('id',$spam->review_id)->update(['status'=>2]);
                }
                $user = ReviewSpam::where('id',$post['id'])->update($post);
                if($post['spam_status']==1){
                    $review = ReviewSpam::where('id',$post['id'])->first();
                    if($review){
                        Invoice::where('month',date('m'))->where('advisor_id',$review->user_id)->delete();
                    }
                }
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
}
