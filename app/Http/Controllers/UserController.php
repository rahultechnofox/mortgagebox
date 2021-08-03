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
    public function addReview(Request $request)
    {
      
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
    public function index(User $model)
    {
        // return view('users.index');
        
        return view('dashboard.index');
    }
    public function dashboard(User $model)
    {
        // return view('users.index');
        return view('dashboard.index');
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
}
