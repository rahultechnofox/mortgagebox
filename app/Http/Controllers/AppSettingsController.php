<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AppSettings;
use anlutro\LaravelSettings\Facade as Setting;

class AppSettingsController extends Controller{
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($type){
        $data['result'] = AppSettings::where('type',$type)->get();
        return view('app_settings.edit',$data);
    }

    /**Update setting
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updateSetting(Request $request){
        $post = $request->all();
        if(!empty($post)){
            unset($post['_token']);
            // echo json_encode($post);exit;
            if(isset($post['new_adviser_status'])){
                $post['new_adviser_status'] = 1;
            }else{
                $post['new_adviser_status'] = 0;
            }
            if(isset($post['friend_active'])){
                $post['friend_active'] = 1;
            }else{
                $post['friend_active'] = 0;
            }
        	foreach ($post as $key => $value) {
                $exist = AppSettings::where('key',$key)->first();
    			if ($exist) {
    				$id = AppSettings::where('id',$exist->id)->update(array('value'=>$value));
    			}else{
    				AppSettings::insert(array('key'=>$key,'value'=>$value));
    			}
            }
            $settingsc = AppSettings::all();
            foreach ($settingsc as $row) {
                Setting::set($row->key, $row->value);
            }
            Setting::save();
        	return back()->with('success','Setting updated successfully.');  
        }else{
        	return back()->with('error','unable to update try again.');  
        }
    }
}
