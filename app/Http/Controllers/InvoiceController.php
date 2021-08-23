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
use App\Models\Invoice;
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
use App\Models\AppSettings;

class InvoiceController extends Controller
{
    /**
     * Display Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request) {
        // $data['site_address'] = DB::table('app_settings')->where('key','site_address')->first();
        // $data['site_name'] = DB::table('app_settings')->where('key','mail_from_name')->first();
        // $data['new_fees'] = array();
        // $data['discount_credits'] = array();
        // $data['invoice'] = DB::table('invoices')->where('month',date('m'))->first();
        // $previous_month = date('m') - 1;
        // $newTotal = 0;
        // $preTotal = 0;
        // $prePaidTotal = 0;
        // $hourdiff = 0;
        // $discountCreditTotal = 0;
        // $discountTotal = 0;
        // $discountBidTotal = 0;
        // $freeIntroductionTotal = 0;
        // $discountArr = array();
        // $bid_data = AdvisorBids::with('area')->with('adviser')->get();
        // foreach($bid_data as $pre){
        //     if(date("m",strtotime($pre->created_at))<date("m",strtotime($previous_month))){
        //         $preTotal = $preTotal + $pre->cost_leads;
        //     }
        //     if(date("m",strtotime($pre->created_at))<date("m",strtotime($previous_month)) && $pre->is_paid_invoice){
        //         $prePaidTotal = $prePaidTotal + $pre->cost_leads;
        //     }
        //     if(date("m",strtotime($pre->created_at))==date("m")){
        //         $newTotal = $newTotal + $pre->cost_leads;
        //     }
        //     $date = date('Y-m-d', strtotime($pre->created_at . " +1 days"));
        //     if(date("Y-m-d",strtotime($pre->accepted_date))>date("Y-m-d",strtotime($date))){
        //         if($pre->free_introduction==1){
        //             $freeIntroductionTotal = $freeIntroductionTotal + $pre->cost_discounted;
        //         }else{
        //             $discountBidTotal = $discountBidTotal + $pre->cost_leads;
        //         }
        //         $discountTotal = $discountTotal + $pre->cost_discounted;
        //         $discountCreditTotal = $discountCreditTotal + $pre->cost_leads;
        //         array_push($discountArr,$pre);
        //     }
        //     if(date("Y-m-d",strtotime($pre->accepted_date))>date("Y-m-d",strtotime($date)) && date("Y-m-d",strtotime($pre->accepted_date))<date('Y-m-d', strtotime($pre->created_at . " +1 days"))){
        //         $pre->fee_type_for_discounted = "50% discount"; 
        //     }else if(date("Y-m-d",strtotime($pre->accepted_date))>date('Y-m-d', strtotime($pre->created_at . " +2 days")) && date("Y-m-d",strtotime($pre->accepted_date))<date('Y-m-d', strtotime($pre->created_at . " +3 days"))){
        //         $pre->fee_type_for_discounted = "75% discount";
        //     }else if(date("Y-m-d",strtotime($pre->accepted_date))>date('Y-m-d', strtotime($pre->created_at . " +3 days"))){
        //         $pre->fee_type_for_discounted = "Free bid";
        //     }
        // }
        // $data['previous_total'] = $preTotal;
        // $data['previous_invoice_paid_till'] = \Helpers::getMonth($previous_month);
        // $data['previous_paid_total'] = $prePaidTotal;
        // $data['new_invoice_total'] = $newTotal;
        // $data['discount_credit_total'] = $discountCreditTotal;
        // $data['discount_total'] = $discountTotal;
        // $data['free_introductions_total'] = $freeIntroductionTotal;
        // $data['discount_bid_total'] = $discountBidTotal;
        // $data['sub_total_discount'] = $data['discount_bid_total'] - $data['free_introductions_total'];
        // $data['new_fees'] = AdvisorBids::with('area')->with('adviser')->get();
        // $data['discount_credits'] = $discountArr;
        // $data['tax_amount'] = 0;
        // $data['taxable_amount'] = 0;
        // $data['tax'] = 0;
        // $data['total_due'] = $data['new_invoice_total'] - $data['sub_total_discount'];
        // $tax = DB::table('app_settings')->select('value')->where('key','tax_amount')->first();
        // if($tax){
        //     $tax_cal = $data['total_due']/(1+($tax->value/100));
        //     $data['taxable_amount'] = $tax_cal;
        //     $data['tax_amount'] = $data['total_due'] - $tax_cal;
        //     $data['tax'] = $tax->value;
        // }
        $post = $request->all();
        $data = AdvisorBids::getInvoice($post);
        $data['post_code'] = PostalCodes::get();
        $data['adviser_data'] = User::where('user_role',1)->get();
        // echo json_encode($data);exit;
        return view('invoice.index',$data);
    }
}
