<?php

namespace App\Http\Controllers;

use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorEnquiries;
use App\Models\AdvisorOffers;
use App\Models\AdvisorPreferencesCustomer;
use App\Models\AdvisorPreferencesProducts;
use App\Models\AdvisorPreferencesDefault;
use App\Models\AdvisorProfile;
use App\Models\BillingAddress;
use App\Models\ChatChannel;
use App\Models\ChatModel;
use App\Models\companies;
use App\Models\CompanyTeamMembers;
use App\Models\LocationPreferences;
use App\Models\PostalCodes;
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

class AdvisorController extends Controller
{
    protected $user;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        $data['adviors'] = User::getAdvisors($post);
        // echo json_encode($data);exit;
        return view('advisor.index',$data);
    }
    /**
     * Display the specified resource..
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $data = User::getAdvisorDetail($id);
        // echo json_encode($data);exit;
        return view('advisor.show',$data);
    }
    /**
     * Update FCA the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateFCAStatus(Request $request){
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
                if($post['status']==1){
                    $postData['FCA_verified'] = date("Y-m-d H:i:s");
                }else{
                    $postData['FCA_verified'] = null;
                }
                $user = AdvisorProfile::where('id',$post['id'])->update($postData);
                if($user){
                    return response(\Helpers::sendSuccessAjaxResponse('FCA updated successfully.',$user));
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
    public function updateAdvisorStatus(Request $request){
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
                    return response(\Helpers::sendSuccessAjaxResponse('Account suspended successfully.',$user));
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
    function destroy($advisor_id) {
        User::where('id', '=', $advisor_id)->delete();
        AdvisorProfile::where('advisorId', '=', $advisor_id)->delete();
        AdvisorBids::where('advisor_id', '=', $advisor_id)->delete();
        AdvisorPreferencesCustomer::where('advisor_id', '=', $advisor_id)->delete();
        AdvisorPreferencesProducts::where('advisor_id', '=', $advisor_id)->delete();
        $data['message'] = 'Advisor deleted!';
        return redirect()->to('admin/advisors')->with('message', $data['message']);
    }
    function getReviewRating()
    {

        $user = JWTAuth::parseToken()->authenticate();
        $rating =  ReviewRatings::select('review_ratings.*', 'users.name', 'users.email', 'users.address')
            ->join('users', 'review_ratings.user_id', '=', 'users.id')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->where('review_ratings.status', '=', 0)
            ->get();

        $averageRating = ReviewRatings::avg('rating');

        $ratingExcellent =  ReviewRatings::where('review_ratings.rating', '<=', '5')
            ->where('review_ratings.rating', '>', '4')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingGreat =  ReviewRatings::where('review_ratings.rating', '<=', '4')
            ->where('review_ratings.rating', '>', '3')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingAverage =  ReviewRatings::where('review_ratings.rating', '<=', '3')
            ->where('review_ratings.rating', '>', '2')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingPoor =  ReviewRatings::where('review_ratings.rating', '<=', '2')
            ->where('review_ratings.rating', '>', '1')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
            ->where('review_ratings.rating', '>', '0')
            ->where('review_ratings.advisor_id', '=', $user->id)
            ->count();

        return response()->json([
            'status' => true,
            'data' => $rating,
            'avarageRating' => number_format((float)$averageRating, 2, '.', ''),
            'total' => count($rating),
            'ratingType' => array(
                'excellent' => $ratingExcellent,
                'great' => $ratingGreat,
                'average' => $ratingAverage,
                'poor' => $ratingPoor,
                'bad' => $ratingBad,
            )

        ], Response::HTTP_OK);
    }
    public function getAdvisorLinks()
    {
        $id = JWTAuth::parseToken()->authenticate();
        $advisor_data = AdvisorProfile::select('web_address', 'facebook', 'twitter', 'linkedin_link', 'updated_at')->where('advisorId', '=', $id->id)->first();
        if ($advisor_data) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advisor_data
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function setAdvisorLinks(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $advisorDetails = AdvisorProfile::select('web_address', 'facebook', 'twitter', 'linkedin_link', 'updated_at')->where('advisorId', '=', $id->id)->update(
            [
                'web_address' => $request->web_address,
                'facebook' => $request->facebook,
                'twitter' => $request->twitter,
                'linkedin_link' => $request->linkedin_link,
            ]
        );
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'Links updated successfully',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }

    public function getAdvisorProfileByAdvisorId($id)
    {
        JWTAuth::parseToken()->authenticate();
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id)->first();
        $last_activity = User::select('users.last_active')->where('id', '=', $id)->first();
        $offer_data = AdvisorOffers::where('advisor_id', '=', $id)->get();

        $rating =  ReviewRatings::select('review_ratings.*', 'users.name', 'users.email', 'users.address')
            ->join('users', 'review_ratings.user_id', '=', 'users.id')
            ->where('review_ratings.advisor_id', '=', $id)
            ->where('review_ratings.status', '=', 0)
            ->get();

        $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
        ->Where('advisor_status','=',1)
        ->Where('advisor_id','=',$id)
        ->count();
        
        $averageRating = ReviewRatings::where('advisor_id', '=', $id)->avg('rating');

        if ($advisor_data) {
            $advisor_data->used_by  = $usedByMortage;
            $advisor_data->last_activity  = $last_activity->last_active;
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $advisor_data,
                'offers' => $offer_data,
                'review_rating' => $rating,
                'avarageRating' => number_format((float)$averageRating, 1, '.', ''),
                'total' => count($rating),

            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    public function searchAdvisor(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $post_code = "";
        $advice_area = "";
        $mortgage_value = "";
        $how_soon = "";
        $mortgage_value = "";
        $local_advisor = "";
        $gender = "";
        $language = "";
        if (isset($request->post_code)) {
            $post_code = $request->post_code;
        }
        if (isset($request->advice_area)) {
            $advice_area = $request->advice_area;
        }
        if (isset($request->how_soon)) {
            $how_soon = $request->how_soon;
        }
        if (isset($request->mortgage_value)) {
            $mortgage_value = $request->mortgage_value;
        }
        if (isset($request->local_advisor)) {
            $local_advisor = $request->local_advisor;
        }
        if (isset($request->gender)) {
            $gender = $request->genders;
        }
        if (isset($request->language)) {
            $language = $request->language;
        }
        
        $advisor_data = array();

        $sql = "SELECT  ap.* from advisor_profiles as ap left join advisor_preferences_customers as apc  on ap.advisorId = apc.advisor_id";
        $sql .= " left join advisor_preferences_products as app on ap.advisorId = app.advisor_id";
        // $sql .= " left join review_ratings as rr on ap.advisorId = rr.user_id";
        $sql .= " where ";
       
        if (!empty($request->how_soon)) {
            foreach ($request->how_soon as $key => $column_name) {
                if ($key === array_key_first($request->how_soon)) {
                    $sql .= " apc." . $column_name . " = 1 ";
                } else {
                    $sql .= " OR apc." . $column_name . " = 1 ";
                }
            }
        }
        if (!empty($request->advice_area)) {
            foreach ($request->advice_area as $key => $column_name) {
                if ($key === array_key_first($request->advice_area)) {
                    if (!empty($request->how_soon)) {
                        $sql .= "OR app." . $column_name . " = 1 ";
                    } else {
                        $sql .= " app." . $column_name . " = 1 ";
                    }
                } else {
                    $sql .= " OR app." . $column_name . " = 1 ";
                }
            }
        }

        // if (!empty($request->mortgage_value)) {
        //     foreach ($request->mortgage_value as $key => $column_name) {
        //         if ($key === array_key_first($request->mortgage_value)) {
        //             if (!empty($request->how_soon) || !empty($request->advice_area) ) {

        //                 $sql .= "OR app." . $column_name . " = 1 ";
        //             } else {
        //                 $sql .= " app." . $column_name . " = 1 ";
        //             }
        //         } else {
        //             $sql .= " OR app." . $column_name . " = 1 ";
        //         }
        //     }
        // }
        if (isset($request->gender)) {
            if (!empty($request->how_soon) || !empty($request->advice_area)) {
                $sql .= " AND ap.gender  = '" . $request->gender . "'";
            } else {
                $sql .= " ap.gender  =  '" . $request->gender . "'";
            }
        }
        if (isset($request->language)) {
            if (!empty($request->how_soon) || !empty($request->advice_area)) {
                $sql .= " AND ap.language  =  '" . $request->language . "'";
            } else {
                $sql .= " ap.language  =  '" . $request->language . "'";
            }
        }
        if (isset($request->local_advisor) && $request->local_advisor !=0 ) {
            if (!empty($request->how_soon) || !empty($request->advice_area)) {
                $sql .= " AND apc.non_uk_citizen  =  '" . $request->local_advisor . "'";
            } else {
                $sql .= " apc.non_uk_citizen  = '" . $request->local_advisor . "'";
            }
        }
        if ($post_code != "") {
            if (!empty($request->how_soon) || !empty($request->advice_area)|| isset($request->language) || isset($request->gender) ||  $request->local_advisor !=0) {
             $sql .= "  AND ap.postcode  =  '" . $post_code . "'";
            }else{
                $sql .= "   ap.postcode  =  '" . $post_code . "'";  
            }
        }
        
        $advisor_data = DB::select($sql);

        $getCustomerPostalDetails = PostalCodes::where('Postcode', '=', $post_code)->first();
        $dataArray = array();
        if ($advisor_data) {
            foreach ($advisor_data as $key => $item) {
                $rating =  ReviewRatings::select('review_ratings.*')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->where('review_ratings.status', '=', 0)
                    ->get();

                $averageRating = ReviewRatings::where('review_ratings.advisor_id', '=', $item->advisorId)->where('review_ratings.status', '=', 0)->avg('rating');

                $ratingExcellent =  ReviewRatings::where('review_ratings.rating', '<=', '5')
                    ->where('review_ratings.rating', '>', '4')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->count();

                $ratingGreat =  ReviewRatings::where('review_ratings.rating', '<=', '4')
                    ->where('review_ratings.rating', '>', '3')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->count();

                $ratingAverage =  ReviewRatings::where('review_ratings.rating', '<=', '3')
                    ->where('review_ratings.rating', '>', '2')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->count();

                $ratingPoor =  ReviewRatings::where('review_ratings.rating', '<=', '2')
                    ->where('review_ratings.rating', '>', '1')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->count();

                $ratingBad =  ReviewRatings::where('review_ratings.rating', '<=', '1')
                    ->where('review_ratings.rating', '>', '0')
                    ->where('review_ratings.advisor_id', '=', $item->advisorId)
                    ->count();

                $item->avarageRating = number_format((float)$averageRating, 2, '.', '');
                $item->rating = [
                    'total' => count($rating),
                ];
                $usedByMortage = AdvisorBids::orWhere('status','=',1)->orWhere('status','=',2)
                ->Where('advisor_status','=',1)
                ->Where('advisor_id','=',$item->advisorId)
                ->count();
                $item->used_by  = $usedByMortage;
                $dataArray[] = $item;
            }

            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $dataArray
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_OK);
        }
    }
    public function updateAdvisorAboutUs(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $advisorDetails = AdvisorProfile::where('advisorId', '=', $id->id)->update(
            [
                'short_description' => $request->short_description,
                'description' => $request->description,
                'description_updated' => Date('Y-m-d H:i:s'),
            ]
        );
        
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();
        // Update Compnay info for all advisers
        if($advisor_data->company_id > 0) {
            $advisorDetails = AdvisorProfile::where('company_id', '=', $advisor_data->company_id)->update(
            [
                'description' => $request->description,
                'description_updated' => Date('Y-m-d H:i:s'),
            ]
        );
        
        }
        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }
    public function updateAdvisorGeneralInfo(Request $request)
    {
        $id = JWTAuth::parseToken()->authenticate();
        $data = $request->only('display_name');
        if ($request->company_logo == "") {
            $request->company_logo = "";
        }
        if ($request->image == "") {
            $request->image = "";
        }

        if ($request->hasFile('company_logo')) {
            $uploadFolder = 'advisor';
            $image = $request->file('company_logo');
            $name = $request->file('company_logo')->getClientOriginalName();
            $extension = $request->file('company_logo')->extension();
            $originalString = str_replace("." . $extension, "", $name);
            $upfileName = $name;

            $num = 1;


            while (Storage::exists("public/" . $uploadFolder . "/" . $upfileName)) {
                $file_name = (string) $originalString . "-" . $num;
                $upfileName = $file_name . "." . $extension;
                $num++;
            }
            $image_uploaded_path = $image->storeAs($uploadFolder, $upfileName, 'public');
            $request->company_logo = basename($image_uploaded_path);


            $uploadedImageResponse = array(
                "image_name" => basename($image_uploaded_path),
                "image_url" => Storage::disk('public')->url($image_uploaded_path),
                "mime" => $image->getClientMimeType()
            );
        } else if ($request->company_logo != "") {
            $request->company_logo =  str_replace("data:image/jpeg;base64,","",$request->company_logo);
            $request->company_logo =  str_replace("data:image/png;base64,","",$request->company_logo);
            $file = base64_decode($request->company_logo);
            $folderName = 'advisor';

            $safeName = $this->quickRandom(10) . '.' . 'png';
            $destinationPath = public_path() . "/storage/" . $folderName;
            file_put_contents($destinationPath . "/" . $safeName, $file);

            //save new file path into db
            $request->company_logo = $safeName;
        }

        if ($request->hasFile('image')) {
            $uploadFolder = 'advisor';
            $image = $request->file('image');
            $name = $request->file('image')->getClientOriginalName();
            $extension = $request->file('image')->extension();
            $originalString = str_replace("." . $extension, "", $name);
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
        } else if ($request->image != "") {
            $request->image =  str_replace("data:image/jpeg;base64,","",$request->image);
            $request->image =  str_replace("data:image/png;base64,","",$request->image);
            $file = base64_decode($request->image);
            $folderName = 'advisor';

            $safeName = $this->quickRandom(10) . '.' . 'png';
            $destinationPath = public_path() . "/storage/" . $folderName;
            file_put_contents($destinationPath . "/" . $safeName, $file);

            //save new file path into db
            $request->image = $safeName;
        }
        $arr = array();
        if ($request->company_logo != "") {
            $arr['company_logo'] = $request->company_logo;
        }
        if ($request->image != "") {
            $arr['image'] = $request->image;
        }
        if ($request->display_name != "") {
            $arr['display_name'] = $request->display_name;
        }
        if ($request->FCANumber != "") {
            $arr['FCANumber'] = $request->FCANumber;
        }
        if ($request->phone_number != "") {
            $arr['phone_number'] = $request->phone_number;
        }
        if ($request->city != "") {
            $arr['city'] = $request->city;
        }
        if ($request->postcode != "") {
            $arr['postcode'] = $request->postcode;
        }
        if ($request->role != "") {
            $arr['role'] = $request->role;
        }

        if ($request->network != "") {
            $arr['network'] = $request->network;
        }

        if ($request->email != "") {
            $arr['email'] = $request->email;
        }

        if ($request->gender != "") {
            $arr['gender'] = $request->gender;
        }

        if ($request->language != "") {
            $arr['language'] = $request->language;
        }

        if ($request->company_name != "") {
            $arr['company_name'] = $request->company_name;
        }

        $advisorDetails = AdvisorProfile::where('advisorId', '=', $id->id)->update($arr);
        $advisor_data = AdvisorProfile::where('advisorId', '=', $id->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'Profile updated successfully',
            'data' => $advisor_data
        ], Response::HTTP_OK);
    }
    public  function quickRandom($length = 16)
    {
        $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

        return substr(str_shuffle(str_repeat($pool, 5)), 0, $length);
    }
    public function getAdvisorProductPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->first();
        if (empty($notification)) {
            AdvisorPreferencesProducts::create([
                'advisor_id' => $user->id,
            ]);
        }
        $notification = AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    function updateAdvisorProductPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->update([
            "remortgage" => $request->remortgage,
            "first_buyer" => $request->first_buyer,
            "next_buyer" => $request->next_buyer,
            "but_let" => $request->but_let,
            "equity_release" => $request->equity_release,
            "overseas" => $request->overseas,
            "self_build" => $request->self_build,
            "mortgage_protection" => $request->mortgage_protection,
            "secured_loan" => $request->secured_loan,
            "bridging_loan" => $request->bridging_loan,
            "commercial" => $request->commercial,
            "something_else" => $request->something_else,
            "mortgage_min_size" => $request->mortgage_min_size,
            "mortgage_max_size" => $request->mortgage_max_size,

        ]);
        $notification = AdvisorPreferencesProducts::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    public function getAdvisorCustomerPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->first();
        if (empty($notification)) {

            AdvisorPreferencesCustomer::create([
                'advisor_id' => $user->id,
            ]);
        }
        $notification = AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    function updateAdvisorCustomerPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->update([
            "self_employed" => $request->self_employed,
            "non_uk_citizen" => $request->non_uk_citizen,
            "adverse_credit" => $request->adverse_credit,
            "ltv_max" => $request->ltv_max,
            "lti_max" => $request->lti_max,
            "asap" => $request->asap,
            "next_3_month" => $request->next_3_month,
            "more_3_month" => $request->more_3_month,
            "fees_preference" => $request->fees_preference,
        ]);
        $notification = AdvisorPreferencesCustomer::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    public function getAdvisorLocationPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $locations = AdvisorProfile::select(['postcode AS post_code', 'serve_range AS miles'])->where('advisorId', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }
    function updateAdvisorLocationPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorProfile::where('advisorId', '=', $user->id)->update([
            "postcode" => $request->post_code,
            "serve_range" => $request->miles,
        ]);
        $locations = AdvisorProfile::select(['postcode AS post_code', 'serve_range AS miles'])->where('advisorId', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }
    public function getAdvisorBillingAddress(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
        if (empty($locations)) {

            BillingAddress::create([
                'advisor_id' => $user->id,
            ]);
        }
        $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }
    function updateAdvisorBillingAddress(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        BillingAddress::where('advisor_id', '=', $user->id)->update([
            "contact_name" => $request->contact_name,
            "invoice_name" => $request->invoice_name,
            "address_one" => $request->address_one,
            "address_two" => $request->address_two,
            "city" => $request->city,
            "post_code" => $request->post_code,
            "contact_number" => $request->contact_number,
            "is_vat_registered" => $request->is_vat_registered,

        ]);
        $locations = BillingAddress::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $locations
        ], Response::HTTP_OK);
    }

    public function getAdvisorFirstMessage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $firstMessage = AdvisorProfile::select('advisor_profiles.first_message')->where('advisorId', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $firstMessage
        ], Response::HTTP_OK);
    }
    function updateFirstMessage(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorProfile::where('advisorId', '=', $user->id)->update([
            "first_message" => $request->first_message,

        ]);
        $firstMessage = AdvisorProfile::select('advisor_profiles.first_message')->where('advisorId', '=', $user->id)->first();

        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $firstMessage
        ], Response::HTTP_OK);
    }
    function advisorTeam(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $company_data = CompanyTeamMembers::where('email', '=', $request->email)->first();
        if (!empty($company_data)) {
            return response()->json([
                'status' => false,
                'message' => 'Email already exists',
                'data' => []
            ], Response::HTTP_OK);
        }
        $profile = CompanyTeamMembers::create([
            'company_id' => $request->company_id,
            'name' => $request->name,
            'email' => $request->email,
            'advisor_id' => $user->id
        ]);
        
        //Send Email
        $msg = "";
        $msg .= "Hello ".ucfirst($request->name)."\n\n";
        $msg .= "<p>".ucfirst($user->name)." invites you to join the company.</p>\n\n";
        $msg .= "<p>Please click the link below to create your account and join the team.</p>\n\n";
        $msg .= "<a href='".config('constants.urls.host_url')."'>Create Account</a>\n\n";
        $msg .= "Best wishes\n\n";
        $msg .= "The Mortgagebox team\n\n";

        mail($request->email, "Invitation | Mortgagebox.co.uk", $msg);
        
        return response()->json([
            'status' => true,
            'message' => 'Team member added successfully',
            'data' => []
        ], Response::HTTP_OK);
    }
    function updateTeam(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $company_data = CompanyTeamMembers::where('id', '=', $request->id)->first();
        if (empty($company_data)) {
            return response()->json([
                'status' => false,
                'message' => 'No record found',
                'data' => []
            ], Response::HTTP_OK);
        }
        $updatedData = array();
        $updatedData['company_id'] = $request->company_id;
        if (isset($request->name)) {
            $updatedData['name'] = $request->name;
        }
        if (isset($request->status)) {
            $updatedData['status'] = $request->status;
        }
        $profile = CompanyTeamMembers::where('id', '=', $request->team_id)->update($updatedData);
        return response()->json([
            'status' => true,
            'message' => 'Team member updated successfully',
            'data' => []
        ], Response::HTTP_OK);
    }
    function getAdvisorTeam($company_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $teams = CompanyTeamMembers::select('company_team_members.*')
            ->where('company_team_members.company_id', '=', $company_id)
            ->where('company_team_members.advisor_id', '=', $user->id)
            ->join('companies', 'company_team_members.company_id', '=', 'companies.id')
           ->get();

        foreach ($teams as $key => $item) {
            $teamAdvisorDetails = User::where('users.email', '=', $item->email)->where('user_role', '=', 1)
                ->join('advisor_profiles', 'users.id', '=', 'advisor_profiles.advisorId')->first();
            $teams[$key]['advisorDetails'] = $teamAdvisorDetails;
        }
        
        if (count($teams) > 0) {
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $teams
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => []
            ], Response::HTTP_UNAUTHORIZED);
        }
    }
    function checkIfExistInAdvisorTeam($company_id)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $isExist = CompanyTeamMembers::where('company_id', $company_id)
            ->where('email', $user->email)->first();
        $roleInCompany = "Contact_Administrator";
        if ($isExist) {
            $roleInCompany = $isExist->isCompanyAdmin ? "Mortgage_Adviser" : "Contact_Administrator";
            return response()->json([
                'status' => true,
                'message' => 'success',
                'data' => $roleInCompany
            ], Response::HTTP_OK);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not found',
                'data' => $roleInCompany
            ], Response::HTTP_OK);
        }
    }
    public function deleteTeam(Request $request, $id)
    {

        $userDetails = JWTAuth::parseToken()->authenticate();
        $offers = CompanyTeamMembers::where('id', '=', $id)->delete();
        //User created, return success response
        $chatData = CompanyTeamMembers::get();
        return response()->json([
            'status' => true,
            'message' => 'Team member deleted successfully',
            'data' => $chatData
        ], Response::HTTP_OK);
    }
    function getDistanceRange($customerEasting, $advisorEasting, $customerNorthing, $advisorNorthing)
    {
        $C5 = pow(abs($advisorEasting - $customerEasting), 2);
        $D5 = pow(abs($advisorNorthing - $customerNorthing), 2);
        $distanceInMeter = pow(($C5 + $D5), 0.5);
        return $distanceInKm = floor($distanceInMeter / 1000);
    }
    function makeEnquiry(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        if (empty($notification)) {
            AdvisorEnquiries::create([
                'name' => $request->name,
                'email' => $request->email,
                'mortgage_required' => $request->mortgage_required,
                'prop_value' => $request->prop_value,
                'combined_income' => $request->combined_income,
                'how_soon' => $request->how_soon,
                'post_code' => $request->post_code,
                'anything_else' => $request->anything_else,
                'advisor_id' => $request->advisor_id,
                'user_id' => $user->id,
                'need_advice' => $request->need_advice,
                'match_me' => $request->match_me,
            ]);
        }
        $msg = "";
        $msg .= "<table>"; 


        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Name";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->name;
        $msg .= "</td>"; 
        $msg .= "</tr>"; 

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Email";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->email;
        $msg .= "</td>"; 
        $msg .= "</tr>";
        
        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Mortgage Required";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->mortgage_required;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Property Value";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->prop_value;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Combined Income";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->combined_income;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "How soon do you need the mortgage?";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->how_soon;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Postcode";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->post_code;
        $msg .= "</td>"; 
        $msg .= "</tr>";
        
        $msg .= "<tr>"; 
        $msg .= "<th>"; 
        $msg .= "Is there anything else you feel is important?";
        $msg .= "</th>"; 
        $msg .= "<td>"; 
        $msg .= $request->anything_else;
        $msg .= "</td>"; 
        $msg .= "</tr>";

        $msg .= "</table>"; 

        $userDetails = User::where('id', '=', $request->advisor_id)->first();

        $headers = "From: mbox@technofox.co.in\r\n";
        $headers .= "Reply-To: mbox@technofox.co.in\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        mail($userDetails->email, "New Enquiry", $msg,$headers);

        return response()->json([
            'status' => true,
            'message' => 'Enquiry sent successfully!',
            'data' => []
        ], Response::HTTP_OK);
    }
    function advisorDashboard() {
        $userDetails = JWTAuth::parseToken()->authenticate();
        $matched_last_hour = DB::table('advice_areas')
            ->where('created_at', '>=',DB::raw('DATE_SUB(NOW(), INTERVAL 1 HOUR)'))
            ->count();
        $matched_last_today = Advice_area::whereDate('created_at', Carbon::today())->count();
        $matched_last_yesterday = Advice_area::whereDate('created_at', Carbon::yesterday())->count();
        $less_than_3_days = Advice_area::where('created_at', '>', Carbon::yesterday()->subDays(3))->where('created_at', '<', Carbon::today())->count();
        $remortgage = Advice_area::where('service_type', '=', 'remortgage')->count();
        $next_time_buyer = Advice_area::where('service_type', '=', 'first time buyer')->count();
        $first_time_buyer = Advice_area::where('service_type', '=', 'next time buyer')->count();
        $buy_to_let = Advice_area::where('service_type', '=', 'buy to let')->count();
        $unread_count_total = DB::select("SELECT count(*) as count_message FROM `chat_models` AS m LEFT JOIN `chat_channels` AS c ON m.channel_id = c.id WHERE  m.to_user_id = $userDetails->id AND m.to_user_id_seen = 0");
        
        $live_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 0)
            ->where('advisor_status', '=', 1)
            ->where('created_at', '>', Carbon::today()->subDays(30))
            ->count();
        $live_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 0)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(90))
        ->count();

        $live_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 0)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(365))
        ->count();

        $hired_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 1)
            ->where('advisor_status', '=', 1)
            ->where('created_at', '>', Carbon::today()->subDays(30))
            ->count();
        $hired_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 1)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(90))
        ->count();

        $hired_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 1)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(365))
        ->count();

        $completed_leads_months = AdvisorBids::where('advisor_id','=',$userDetails->id)
            ->where('status', '=', 2)
            ->where('advisor_status', '=', 1)
            ->where('created_at', '>', Carbon::today()->subDays(30))
            ->count();
        $completed_leads_quarter = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 2)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(90))
        ->count();

        $completed_leads_year = AdvisorBids::where('advisor_id','=',$userDetails->id)
        ->where('status', '=', 2)
        ->where('advisor_status', '=', 1)
        ->where('created_at', '>', Carbon::today()->subDays(365))
        ->count();
        
        return response()->json([
            'status' => true,
            'message' => '',
            'data' => [
                'matched_card_one'=>array(
                    'last_hour'=>$matched_last_hour,
                    'today'=>$matched_last_today,
                    'yesterday'=>$matched_last_yesterday,
                    'less_than_3_days'=>$less_than_3_days,
                ),
                'matched_card_two'=>array(
                    'early_bid'=>'0',
                    '50_off'=>'0',
                    '70_off'=>'0',
                    'free'=>'0',
                ),
                'matched_card_three'=>array(
                    'remortgage'=>$remortgage,
                    'next_time_buyer'=>$next_time_buyer,
                    'first_time_buyer'=>$first_time_buyer,
                    'buy_to_let'=>$buy_to_let,
                ),
                'accepted_card_one'=>array(
                    'live_leads'=>$live_leads_months,
                    'hired'=>$hired_leads_months,
                    'completed'=>$completed_leads_months,
                ),
                'accepted_card_two'=>array(
                    'live_leads'=>$live_leads_quarter,
                    'hired'=>$hired_leads_quarter,
                    'completed'=>$completed_leads_quarter,
                ),
                'accepted_card_three'=>array(
                    'live_leads'=>$live_leads_year,
                    'hired'=>$hired_leads_year,
                    'completed'=>$completed_leads_year,
                ),
                'performance'=>array(
                    'conversion_rate'=>'',
                    'cost_of_leads'=>'',
                    'estimated_revenue'=>'',
                ),
                'message_unread_count'=>$unread_count_total[0]->count_message,
                'notification_unread_count'=>0

            ]
        ], Response::HTTP_OK);
    }
    public function getAdvisorDefaultPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->first();
        if (empty($notification)) {
            AdvisorPreferencesDefault::create([
                'advisor_id' => $user->id,
            ]);
        }
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    function updateAdvisorDefaultPreference(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->update([
            "remortgage" => $request->remortgage,
            "first_buyer" => $request->first_buyer,
            "next_buyer" => $request->next_buyer,
            "but_let" => $request->but_let,
            "equity_release" => $request->equity_release,
            "overseas" => $request->overseas,
            "self_build" => $request->self_build,
            "mortgage_protection" => $request->mortgage_protection,
            "secured_loan" => $request->secured_loan,
            "bridging_loan" => $request->bridging_loan,
            "commercial" => $request->commercial,
            "something_else" => $request->something_else,
        ]);
        $notification = AdvisorPreferencesDefault::where('advisor_id', '=', $user->id)->first();
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => $notification
        ], Response::HTTP_OK);
    }
    // Function for invoice generate
    public function invoice(Request $request) {
        $user = JWTAuth::parseToken()->authenticate();
        // $advisor_id = $request->advisor_id;
        $month = date('m');
        $year = date('Y');
        if(isset($request->selected_date) && $request->selected_date !="") {
            $month = date('m',strtotime($request->selected_date));
            $year = date('Y',strtotime($request->selected_date));
        }
        
        $total_this_month_cost_of_leads_subtotal = AdvisorBids::where('advisor_id','=',$user->id)
        ->where('status','>=',1)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->sum('cost_leads');
        // ->get();

        $cost_leads_this_month = AdvisorBids::select('cost_leads','accepted_date','cost_discounted','free_introduction')->where('advisor_id','=',$user->id)
        ->where('status','>=',1)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->get();
        $cost_of_leads_of_the_monthArr = array();
        $discount_of_the_monthArr = array();
        foreach($cost_leads_this_month as $key=> $item) {
            $cost_of_leads_of_the_monthArr[$key]['message']='Invoice payment received on '.Date('d-M-Y',strtotime($item->accepted_date)).' - Thank you';
            $cost_of_leads_of_the_monthArr[$key]['cost']=($item->cost_leads!="")?$item->cost_leads:"0";
        }


        // for discount

        $total_this_month_discount_subtotal = AdvisorBids::where('advisor_id','=',$user->id)
        ->where('status','>=',1)
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->sum('cost_discounted');
        
        // TODO: free introduction

        $subtotal_of_discount_and_credit = $total_this_month_discount_subtotal+0;
        $total_dues = $total_this_month_cost_of_leads_subtotal-$subtotal_of_discount_and_credit;
        $total_amount = 0;
        // Tax Summary... added tax 5% of total dues and 20% extra vat 
                $tax_on_this_invoice = (5/100)*$total_dues;
                $vat_on_this_invoice = (20/100)*$total_dues;
                $total_amount_final = $total_dues+$tax_on_this_invoice+$vat_on_this_invoice;
                $total_amount_final = number_format((float)($total_amount_final),2,'.','');
        // end of tax summary...
        return response()->json([
            'status' => true,
            'message' => 'success',
            'data' => array(
                'new_fess'=>array('cost_of_leads_of_the_month'=>$cost_of_leads_of_the_monthArr,
                'cost_of_leads_sub_total'=>$total_this_month_cost_of_leads_subtotal),
                // add array for discount section
                'discounts_and_credits'=>array('discount_subtotal'=>$total_this_month_discount_subtotal,
                'free_introduction_subtotal'=>0,'subtotal'=>$subtotal_of_discount_and_credit),
                // total dues : subtotal of cost of lead minus discount_and_credit_subtotal
                'total_dues'=>$total_dues,
                'total_taxable_amount'=>number_format((float)($total_dues+$tax_on_this_invoice),2,'.',''),
                'vat_on_invoice'=>number_format((float)($vat_on_this_invoice),2,'.',''),
                'total_current_invoice_amount'=>$total_amount_final

                
            )
        ], Response::HTTP_OK);
        
    }
}
