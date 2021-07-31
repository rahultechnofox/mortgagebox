<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use App\Models\Advice_area;
use App\Models\AdvisorBids;
use App\Models\AdvisorOffers;
use App\Models\AdvisorProfile;
use App\Models\ChatChannel;
use App\Models\ChatModel;
use App\Models\ReviewRatings;
use JWTAuth;
 
use App\Models\UserNotes;
 
use Tymon\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Validator;
 
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback($provider,Request $request)
    {
            if($request->has("error_code")){
                return abort("503",$request->error_message);
            }
            $getUser = Socialite::driver($provider)->user();
            $user=User::where("email",$getUser->getEmail())->first();
            if($user){
                Auth::login($user,true);
                // $user->markEmailAsVerified();
                return redirect('/');
            }else{
                $user= new User();
                $user->name = $getUser->getName();
                $user->email = $getUser->getEmail();
                $user->role_id =2; 
                $user->status =1;
                $user->password = Hash::make(Str::random(40));
                $user->provider = $provider;
                $user->provider_token = $getUser->getId();
                $user->save();
                $user->markEmailAsVerified();
                Auth::login($user,true);
                return redirect('/');
            
            }
      
    }
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

        $this->middleware('guest')->except('logout');
    }

    public function showLoginForm()
    {
        return view('auth.login');
    }

    protected function redirectTo()
    {   
        return '/admin';
    }
}
