<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use DB;

class Invoice extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $fillable = ['invoice_number', 'month', 'year','invoice_data','is_paid','advisor_id'];
    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
    */
    protected $dates = ['deleted_at'];
    public function adviser(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","advisor_id");
    }
    public static function getInvoiceList($search){
        try {
            $query = new Self;
            $data = $query->groupBy('month')->orderBy('id','DESC')->paginate(config('constants.paginate.num_per_page'));
            foreach($data as $row){
                $row->subtotal_month = DB::table('invoices')->where('month',$row->month)->where('year',$row->year)->sum('subtotal');
                $row->discount_month = DB::table('invoices')->where('month',$row->month)->where('year',$row->year)->sum('discount');
                $row->total_due_month = DB::table('invoices')->where('month',$row->month)->where('year',$row->year)->sum('total_due');
                $row->received_month = DB::table('invoices')->where('month',$row->month)->where('year',$row->year)->where('is_paid',1)->sum('total_due');
                $row->outstanding_month = DB::table('invoices')->where('month',$row->month)->where('year',$row->year)->where('is_paid',0)->sum('total_due');
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getInvoiceDetailBasisOfMonth($search){
        try {
            $query = new Self;
            if(isset($search['is_paid']) && $search['is_paid']!=''){
                $query = $query->where('is_paid',$search['is_paid']);
            }
            if(isset($search['advisor_id']) && $search['advisor_id']!=''){
                $query = $query->where('advisor_id',$search['advisor_id']);
            }
            if(isset($search['month']) && $search['month']!=''){
                $query = $query->where('month',$search['month']);
            }
            if(isset($search['year']) && $search['year']!=''){
                $query = $query->where('year',$search['year']);
            }
            $data = $query->orderBy('id','DESC')->with('adviser')->paginate(config('constants.paginate.num_per_page'));
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getInvoiceDetail($id=0,$search){
        try {
            $query = new Self;
            if(isset($id) && $id!=0){
                $query = $query->where('id',$id);
            }
            $data = $query->with('adviser')->first();
            if($data){
                $data->unpaid_prevoius_invoice = DB::table('invoices')->where('is_paid',0)->where('month','<',$data->month)->where('advisor_id',$data->advisor_id)->sum('total_due');
                $data->paid_prevoius_invoice = DB::table('invoices')->where('is_paid',1)->where('month','<',$data->month)->where('advisor_id',$data->advisor_id)->sum('total_due');
                $data->new_fees_arr = AdvisorBids::where('advisor_id',$data->advisor_id)->where('is_discounted',0)->with('area')->with('adviser')->get();
                if(count($data->new_fees_arr)){
                    foreach($data->new_fees_arr as $new_bid){
                        $new_bid->date = date("d-M-Y H:i",strtotime($new_bid->created_at));
                        if($new_bid->status==0){
                            $new_bid->status_type = "Live Lead";
                        }else if($new_bid->status==1){
                            $new_bid->status_type = "Hired";
                        }else if($new_bid->status==2){
                            $new_bid->status_type = "Completed";
                        }else if($new_bid->status==3){
                            $new_bid->status_type = "Lost";
                        }else if($new_bid->advisor_status==2){
                            $new_bid->status_type = "Not Proceeding";
                        }
                    }
                }
                $data->discount_credit_arr = AdvisorBids::where('advisor_id',$data->advisor_id)->where('is_discounted','!=',0)->with('area')->with('adviser')->get();
                if(count($data->discount_credit_arr)){
                    foreach($data->discount_credit_arr as $discount_bid){
                        $discount_bid->date = date("d-M-Y H:i",strtotime($discount_bid->created_at));
                        if($discount_bid->status==0){
                            $discount_bid->status_type = "Live Lead";
                        }else if($discount_bid->status==1){
                            $discount_bid->status_type = "Hired";
                        }else if($discount_bid->status==2){
                            $discount_bid->status_type = "Completed";
                        }else if($discount_bid->status==3){
                            $discount_bid->status_type = "Lost";
                        }else if($discount_bid->advisor_status==2){
                            $discount_bid->status_type = "Not Proceeding";
                        }
                    }
                }
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }

    public static function getOverAllInvoice($search){
        try {
            $query = new Self;
            if(isset($search['status']) && $search['status']!=''){
                $query = $query->where('advisor_bids.status',$search['status']);
            }
            $adviserArr = array();
            if(isset($search['post_code']) && $search['post_code']!=''){
                $adviser = AdvisorProfile::where('postcode',$search['post_code'])->get();
                foreach($adviser as $adviser_data){
                    if(!in_array($adviser_data->advisorId,$adviserArr)){
                        array_push($adviserArr,$adviser_data->advisorId);
                    }
                }
                if(count($adviserArr)){
                    $query = $query->whereIn('advisor_id',$adviserArr);
                }
            }
            if(isset($search['advisor_id']) && $search['advisor_id']!=''){
                $query = $query->where('advisor_id',$search['advisor_id']);
            }
            if(isset($search['date']) && $search['date']!=''){
                $explode = explode("to",$search['date']);
                $from = trim($explode[0]);
                $to = trim($explode[1]);
                if(isset($explode[0]) && $explode[0]!='' && isset($explode[1]) && $explode[1]!=''){
                    $start = date("Y-m-d",strtotime($from));
                    $end = date('Y-m-d',strtotime($to));
                    $query = $query->whereBetween('created_at', [$start, $end]);
                }
            }
            
            if(isset($search['created_at']) && $search['created_at']!=''){
                $query = $query->whereDate('created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            }
            if(isset($search['month']) && $search['month']!=''){
                $query = $query->where('month',$search['month']);
            }
            if(isset($search['year']) && $search['year']!=''){
                $query = $query->where('year',$search['year']);
            }
            $data = $query->with('adviser')->get();
            if(count($data)){
                $cost_of_lead = 0;
                $subtotal = 0;
                $discount = 0;
                $free_introduction = 0;
                $total_taxable_amount = 0;
                $vat = 0;
                $total_current_invoice = 0;
                $total_due = 0;
                $paid_prevoius_invoice = 0;
                $unpaid_prevoius_invoice = 0;
                $new_lead = array();
                $discount_lead = array();
                $adviser_arr = array();
                foreach($data as $row){
                    $row->invoice_data = json_decode($row->invoice_data);
                    $cost_of_lead = $cost_of_lead + $row->cost_of_lead;
                    $subtotal = $subtotal + $row->subtotal;
                    $discount = $discount + $row->discount;
                    $free_introduction = $free_introduction + $row->free_introduction;
                    $total_taxable_amount = $total_taxable_amount + $row->total_taxable_amount;
                    $vat = $vat + $row->vat;
                    $total_current_invoice = $total_current_invoice + $row->total_current_invoice;
                    $total_due = $total_due + $row->total_due;
                    $cost_of_lead = $cost_of_lead + $row->cost_of_lead;
                    $cost_of_lead = $cost_of_lead + $row->cost_of_lead;
                    $unpaid_prevoius_invoice_sum = DB::table('invoices')->where('is_paid',0)->where('month','<',$row->month)->sum('total_due');
                    $unpaid_prevoius_invoice = $unpaid_prevoius_invoice + $unpaid_prevoius_invoice_sum;
                    $paid_prevoius_invoice_sum = DB::table('invoices')->where('is_paid',1)->where('month','<',$row->month)->sum('total_due');
                    $paid_prevoius_invoice = $paid_prevoius_invoice + $paid_prevoius_invoice_sum;
                    if(isset($row->invoice_data->new_fees_data) && count($row->invoice_data->new_fees_data)){
                        foreach($row->invoice_data->new_fees_data as $new_fees_data){
                            array_push($new_lead,$new_fees_data);
                        }
                    }
                    if(isset($row->invoice_data->discount_credit_data) && count($row->invoice_data->discount_credit_data)){
                        foreach($row->invoice_data->discount_credit_data as $discount_credits_data){
                            array_push($discount_lead,$discount_credits_data);
                        }
                    }
                    array_push($adviser_arr,$row->advisor_id);
                }
                $data['new_fees_arr'] = AdvisorBids::whereIn('advisor_id',$adviser_arr)->where('is_discounted',0)->with('area')->with('adviser')->get();
                if(count($data['new_fees_arr'])){
                    foreach($data['new_fees_arr'] as $new_bid){
                        $new_bid->date = date("d-M-Y H:i",strtotime($new_bid->created_at));
                        if($new_bid->status==0){
                            $new_bid->status_type = "Live Lead";
                        }else if($new_bid->status==1){
                            $new_bid->status_type = "Hired";
                        }else if($new_bid->status==2){
                            $new_bid->status_type = "Completed";
                        }else if($new_bid->status==3){
                            $new_bid->status_type = "Lost";
                        }else if($new_bid->advisor_status==2){
                            $new_bid->status_type = "Not Proceeding";
                        }
                    }
                }
                $data['discount_credit_arr'] = AdvisorBids::whereIn('advisor_id',$adviser_arr)->where('is_discounted','!=',0)->with('area')->with('adviser')->get();
                if(count($data['discount_credit_arr'])){
                    foreach($data['discount_credit_arr'] as $discount_bid){
                        $discount_bid->date = date("d-M-Y H:i",strtotime($discount_bid->created_at));
                        if($discount_bid->status==0){
                            $discount_bid->status_type = "Live Lead";
                        }else if($discount_bid->status==1){
                            $discount_bid->status_type = "Hired";
                        }else if($discount_bid->status==2){
                            $discount_bid->status_type = "Completed";
                        }else if($discount_bid->status==3){
                            $discount_bid->status_type = "Lost";
                        }else if($discount_bid->advisor_status==2){
                            $discount_bid->status_type = "Not Proceeding";
                        }
                    }
                }
                $data['cost_of_lead'] = $cost_of_lead;
                $data['subtotal'] = $subtotal;
                $data['discount'] = $discount;
                $data['free_introduction'] = $free_introduction;
                $data['total_taxable_amount'] = $total_taxable_amount;
                $data['vat'] = $vat;
                $data['total_current_invoice'] = $total_current_invoice;
                $data['total_due'] = $total_due;
                $data['paid_prevoius_invoice'] = $paid_prevoius_invoice;
                $data['unpaid_prevoius_invoice'] = $unpaid_prevoius_invoice;
                $data['new_fees_data'] = $new_lead;
                $data['discount_credit_data'] = $discount_lead;
            }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
