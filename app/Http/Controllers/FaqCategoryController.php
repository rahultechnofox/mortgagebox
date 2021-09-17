<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorProfile;
use App\Models\AdvisorPreferencesCustomer;
use App\Models\AdvisorPreferencesProducts;
use App\Models\companies;
use App\Models\CompanyTeamMembers;
use App\Models\StaticPage;
use App\Models\ServiceType;
use App\Models\FaqCategory;
use App\Models\Audience;
use UploadImage;

use Illuminate\Support\Facades\Validator;


use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FaqCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['page_list'] = FaqCategory::getFaqCategories($post);
        $data['audience'] = Audience::where('status',1)->get();
        return view('faq_category.index',$data);
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
                'name' => 'required',
            ];
            $validator = Validator::make($post, $validate);
            if ($validator->fails()) {
                 $data['error'] = $validator->errors();
                return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            }else{
                $postData['name'] = $post['name'];
                if(isset($post['audience']) && $post['audience']!=''){
                    $postData['audience'] = $post['audience'];
                }
                if(isset($post['image']) && $post['image']!=''){
                    $postData['image'] = $post['image'];
                }
                unset($post['_token']);
                if(isset($post['id'])){
                    $id = FaqCategory::where('id',$post['id'])->update($postData);
                    if($id){
                        return response(\Helpers::sendSuccessAjaxResponse('Faq category updated successfully.',[]));
                    }else{
                        return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                    }
                }else{
                    $postData['status'] = 1;
                    $postData['created_at'] = date("Y-m-d H:i:s");
                    $id = FaqCategory::insertGetId($postData);
                    if($id){
                        return response(\Helpers::sendSuccessAjaxResponse('Faq category added successfully.',[]));
                    }else{
                        return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                    }
                }
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
                $service = FaqCategory::where('id',$post['id'])->first();
                if($service){
                    return response(\Helpers::sendSuccessAjaxResponse('Faq category fetched successfully.',$service));
                }else{
                    return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                }
            }
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
    public function updateFaqCategoryStatus(Request $request){
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
                $user = FaqCategory::where('id',$post['id'])->update($post);
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
        FaqCategory::where('id', '=', $id)->delete();
        return redirect()->to('admin/faq-category')->with('success','Faq category deleted successfully');
    }

    /**
     * Function to Upload Faq Category image.
     * @return Response
    **/
    public function uploadFaqCategoryImage(Request $request){
        try {
            if($request->ajax()){
                $post = $request->all();
                if($post['image']!=''){
                    $images = $post['image'];
                    $post['image'] = time() . rand() .'.'.$images->getClientOriginalExtension();
                    $destinationPath = public_path('/upload/faq_category/580x400');
                    $img = \UploadImage::make($images->getRealPath());
                    $img->resize(580, 400, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($destinationPath.'/'.$post['image']);
                    $destinationPath = public_path('/upload/faq_category/original');
                    $images->move($destinationPath, $post['image']);
                }
                return response(\Helpers::sendSuccessAjaxResponse('Record updated.',$post['image']));
            }else{
              return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.invalid_request')));
            }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
}
