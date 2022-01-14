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
use App\Models\PostalCodes;

use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PagesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request){
        $post = $request->all();
        // if(isset($_GET['type']) && $_GET['type']!=''){
        //     $post['type']
        // }
        $pages = StaticPage::getPages($post);
        return view('pages.index',['page_list'=>$pages]);
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){

        $data['row'] = StaticPage::find($id);
        return view('pages.edit',$data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
        $post = $request->all();
        $postUpdate = array(
            'page_name' => $post['page_name'],
            'page_content' => $post['page_content']
        );
        StaticPage::where('id',$post['id'])->update($postUpdate);
        return redirect()->to('admin/pages?type='.$post['type'])->with('success','Page content updated successfully');
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
                $user = StaticPage::where('id',$post['id'])->update($post);
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
    function destroy($page_id){
        StaticPage::where('id', '=', $page_id)->delete();
        return redirect()->to('admin/pages')->with('success','Page content deleted successfully');
    }

    public function importExcelPostcode(Request $request){
        try {
            $post = $request->all();
            $filename=$_FILES["excel"]["tmp_name"];
            // print_r($_FILES["excel"]["size"]);exit;
            if($_FILES["excel"]["size"] > 0){
                $file = fopen($filename, "r");
                $c = 0;
                while(($filesop = fgetcsv($file, 1000, ",")) !== false){
                    $fname = $filesop[0];
                    $lname = $filesop[1];
                    $postArr = array(
                        'Postcode'=>$filesop[0],
                        'InUse'=>$filesop[1],
                        'Latitude'=>$filesop[2],
                        'Longitude'=>$filesop[3],
                        'Easting'=>$filesop[4],
                        'Northing'=>$filesop[5],
                        'GridRef'=>$filesop[6],
                        'County'=>$filesop[7],
                        'District'=>$filesop[8],
                        'Ward'=>$filesop[9],
                        'DistrictCode'=>$filesop[10],
                        'WardCode'=>$filesop[11],
                        'Country'=>$filesop[12],
                        'CountyCode'=>$filesop[13],
                        'Constituency'=>$filesop[14],
                        'Introduced'=>$filesop[15],
                        'Terminated'=>date("Y-m-d",strtotime($filesop[16])),
                        'Parish'=>$filesop[17],
                        'NationalPark'=>$filesop[18],
                        'Population'=>$filesop[19],
                        'Households'=>$filesop[20],
                        'BuiltUpArea'=>$filesop[21],
                        'BuiltUpSubDivision'=>$filesop[22],
                        'LowerLayerSuperOutputArea'=>$filesop[23],
                        'RuralUrban'=>$filesop[24],
                        'Region'=>$filesop[25],
                        'Altitude'=>$filesop[26],
                        'LondonZone'=>$filesop[27],
                        'LSOACode'=>$filesop[28],
                        'LocalAuthority'=>$filesop[29],
                        'MSOACode'=>$filesop[30],
                        'MiddleLayerSuperOutputArea'=>$filesop[31],
                        'ParishCode'=>$filesop[32],
                        'CensusOutputArea'=>$filesop[33],
                        'ConstituencyCode'=>$filesop[34],
                        'IndexOfMultipleDeprivation'=>$filesop[35],
                        'Quality'=>$filesop[36],
                        'UserType'=>$filesop[37],
                        'LastUpdated'=>$filesop[38],
                        'NearestStation'=>$filesop[39],
                        'DistanceToStation'=>$filesop[40],
                        'PostcodeArea'=>$filesop[41],
                        'PostcodeDistrict'=>$filesop[42],
                        'PoliceForce'=>$filesop[43],
                        'WaterCompany'=>$filesop[44],
                        'PlusCode'=>$filesop[45],
                        'AverageIncome'=>$filesop[46],
                        'SewageCompany'=>$filesop[47],
                        'TravelToWorkArea'=>$filesop[48],
                        'created_at'=>date("Y-m-d"),
                    );
                    PostalCodes::insertGetId($postArr);
                    $c = $c + 1;
                }
                // while (($emapData = fgetcsv($file, 10000, ",")) !== FALSE){
                //     print_r($emapData[0]);
                // }
            }
            fclose($file);
            echo "successfully imported";exit;
            // $validate = [
            //     'id' => 'required',
            //     'status' => 'required'
            // ];
            // $validator = Validator::make($post, $validate);
            // if ($validator->fails()) {
            //      $data['error'] = $validator->errors();
            //     return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.required_field_missing')));
            // }else{
            //     unset($post['_token']);
            //     $user = StaticPage::where('id',$post['id'])->update($post);
            //     if($user){
            //         return response(\Helpers::sendSuccessAjaxResponse('Status updated successfully.',$user));
            //     }else{
            //         return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.smothing_went_wrong')));
            //     }
            // }
        } catch (\Exception $ex) {
            return response(\Helpers::sendFailureAjaxResponse(config('constant.common.messages.there_is_an_error').$ex));
        }
    }
}
