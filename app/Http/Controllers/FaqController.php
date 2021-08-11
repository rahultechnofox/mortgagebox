<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FaqCategory;
use App\Models\Audience;
use App\Models\Faq;
use Illuminate\Support\Facades\Validator;

class FaqController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $pages = Faq::getFaq($post);
        return view('faq.index',['page_list'=>$pages]);
    }
    /**
     * Show the form for creating the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function create(){
        $data['faq_category'] = FaqCategory::where('status',1)->get();
        $data['audience'] = Audience::where('status',1)->get();
        return view('faq.create',$data);
    }
    /**
     * Add , Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        try {
            $post = $request->all();
            $validate = [
                'faq_category_id' => 'required',
                'question' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                $data['error'] = $validator->errors();
                return redirect()->to('admin/faq')->with('error',config('constant.common.messages.required_field_missing'));
                // return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                $postData['question'] = $post['question'];
                $postData['answer'] = $post['answer'];
                unset($post['_token']);
                if(isset($post['id'])){
                    $id = Faq::where('id',$post['id'])->update($postData);
                    if($id){
                        return redirect()->to('admin/faq')->with('success','Faq updated successfully');
                        // return response(\Helpers::sendSuccessAjaxResponse('Faq updated successfully.',[]));
                    }else{
                        return redirect()->to('admin/faq')->with('error',config('constant.common.messages.smothing_went_wrong'));
                        // return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                    }
                }else{
                    $postData['status'] = 1;
                    $postData['created_at'] = date("Y-m-d H:i:s");
                    $id = Faq::insertGetId($postData);
                    if($id){
                        return redirect()->to('admin/faq')->with('success','Faq added successfully');
                        
                        // return response(\Helpers::sendSuccessAjaxResponse('Faq added successfully.',[]));
                    }else{
                        return redirect()->to('admin/faq')->with('error',config('constant.common.messages.smothing_went_wrong'));

                        // return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                    }
                }
            }
        } catch (\Exception $ex) {
                return redirect()->to('admin/faq')->with('error',config('constant.common.messages.there_is_an_error').$ex);

            // return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        $data['row'] = Faq::find($id);
        return view('faq.edit',$data);
    }
    /**
     * Update status the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateFaqStatus(Request $request){
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
                $user = Faq::where('id',$post['id'])->update($post);
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    function destroy($id) {
        Faq::where('id', '=', $id)->delete();
        return redirect()->to('admin/faq')->with('success','Faq deleted successfully');
    }
}
