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
        $post = $request->all();
        $data['result'] = Invoice::getInvoiceList($post);
        $data['post_code'] = array();
        // $data['post_code'] = PostalCodes::get();
        $data['adviser_data'] = User::where('user_role',1)->get();
        if(count($data['result'])){
            foreach($data['result'] as $row){
                // $total = 0; 
                // $row->invoice_data = json_decode($row->invoice_data); 
                // $sub = 0;
                // if(json_encode($row->invoice_data->sub_total_without_tax)!=null){
                //     $sub = json_encode($row->invoice_data->sub_total_without_tax);
                // }
                // // echo $sub;exit;
                // $total += (int) $sub;
                // $row->total = $total;
                $total = 0;
                $row->invoice_data = json_decode($row->invoice_data);
                $sub = 0;
                if (property_exists($row->invoice_data, 'new_taxable_amount') && is_numeric($row->invoice_data->new_taxable_amount)) {
                $sub = $row->invoice_data->new_taxable_amount;
                }
                $total += (int) $sub;
                $row->total = $total;
            }
            
        }
        // echo json_encode($data['result']);exit;
        return view('invoice.index',$data);
    }
    /**
     * Display Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function list($month,Request $request) {
        $post = $request->all();
        if(isset($_GET['month']) && $_GET['month']!=''){
            $post['month'] = $_GET['month'];
        }else{
            $post['month'] = $month;
        }
        
        $data['result'] = Invoice::getInvoiceDetailBasisOfMonth($post);
        foreach($data['result'] as $row){
            $row->invoice_data = json_decode($row->invoice_data);
        }
        // echo json_encode($data['result']);exit;
        $data['adviser'] = User::where('user_role',1)->where('status',1)->with('advisor_profile')->get();
        return view('invoice.invoice_list',$data);
    }
    /**
     * Display Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request) {
        $post = $request->all();
        $data['row'] = Invoice::getInvoiceDetail($id,$post);
        if($data['row']){
            $data['row']->invoice_data = json_decode($data['row']->invoice_data);
        }
        return view('invoice.show',$data);
    }
    /**
     * Display Invoice
     *
     * @return \Illuminate\Http\Response
     */
    public function invoice(Request $request) {
        $post = $request->all();
        $data['smallest_invoice_year'] = 0;
        $smallest_invoice_year = Invoice::select('year')->orderBy('year','ASC')->first();
        if($smallest_invoice_year){
            $data['smallest_invoice_year'] = $smallest_invoice_year->year;
        }
        
        // echo json_encode($data['smallest_invoice_year']);exit;
        $data['invoice'] = Invoice::getOverAllInvoice($post);
        $data['adviser_data'] = User::where('user_role',1)->orderBy('id','DESC')->get();
        return view('invoice.overall_invoice',$data);
    }

    /**
     * Get postcodes for autocomplete.
     *
     * @return \Illuminate\Http\Response
     */
    public function postCodeAutocomplete(Request $request)
    {
        $post = $request->all();
        $data = PostalCodes::select("Postcode")
                ->where("Postcode","LIKE","%{$request['query']}%")
                ->get();
   
        return response($data);
    }
}
