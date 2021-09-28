<?php

namespace App\Http\Controllers;
use App\Models\Contactus;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class ContactUsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['result'] = Contactus::getContactUs($post);
        // echo json_encode($data);exit;
        return view('contact.index',$data);
    }
    /**
     * Send reply the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        try {
            $post = $request->all();
            $validate = [
                'email' => 'required',
                'subject' => 'required',
                'message' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                unset($post['_token']);
                $newArr = array(
                    'name'=>$post['name'],
                    'email'=>$post['email'],
                    'message'=>$post['message'],
                );
                $c = \Helpers::sendEmail('emails.contact_us_query_reply',$newArr ,$post['email'],$post['name'],'Contact Us Query Reply','','');
                return response(\Helpers::sendSuccessAjaxResponse('Successfully replied to contact us query.',[]));                
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
    /**
     * Show the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
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
                $contact = Contactus::where('id',$post['id'])->first();
                if($contact){
                    return response(\Helpers::sendSuccessAjaxResponse('Contact us fetched successfully.',$contact));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                }
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
}
