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
use Illuminate\Support\Facades\Validator;


use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['page_list'] = ServiceType::getServices($post);
        $data['services'] = ServiceType::where('parent_id',0)->where('status',1)->get();
        return view('services.index',$data);
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
                if(isset($post['parent_id']) && $post['parent_id']!=''){
                    $postData['parent_id'] = $post['parent_id'];
                }
                unset($post['_token']);
                if(isset($post['id'])){
                    $id = ServiceType::where('id',$post['id'])->update($postData);
                    if($id){
                        return response(\Helpers::sendSuccessAjaxResponse('Services updated successfully.',[]));
                    }else{
                        return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
                    }
                }else{
                    $postData['status'] = 1;
                    $postData['created_at'] = date("Y-m-d H:i:s");
                    $id = ServiceType::insertGetId($postData);
                    if($id){
                        return response(\Helpers::sendSuccessAjaxResponse('Services added successfully.',[]));
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
                $service = ServiceType::where('id',$post['id'])->first();
                if($service){
                    return response(\Helpers::sendSuccessAjaxResponse('Service fetched successfully.',$service));
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
    public function updateServiceStatus(Request $request){
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
                $user = ServiceType::where('id',$post['id'])->update($post);
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
        ServiceType::where('id', '=', $id)->delete();
        return redirect()->to('admin/services')->with('success','Service deleted successfully');
    }
}
