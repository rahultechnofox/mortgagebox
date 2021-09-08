<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;

class Invoice extends Model
{
    use HasFactory;

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
            }
            // if($data){
            //     $previous_month = $data->month - 1;
            //     $newTotal = 0;
            //     $preTotal = 0;
            //     $prePaidTotal = 0;
            //     $hourdiff = 0;
            //     $discountCreditTotal = 0;
            //     $discountTotal = 0;
            //     $discountBidTotal = 0;
            //     $freeIntroductionTotal = 0;
            //     $discountArr = array();
            //     $bid_data = AdvisorBids::where('advisor_id',$data->advisor_id)->with('area')->get();
            //     foreach($bid_data as $pre){
            //         if(date("m",strtotime($pre->created_at))<date("m",strtotime($previous_month))){
            //             $preTotal = $preTotal + $pre->cost_leads;
            //         }
            //         if(date("m",strtotime($pre->created_at))<date("m",strtotime($previous_month)) && $pre->is_paid_invoice){
            //             $prePaidTotal = $prePaidTotal + $pre->cost_leads;
            //         }
            //         if(date("m",strtotime($pre->created_at))==date("m")){
            //             $newTotal = $newTotal + $pre->cost_leads;
            //         }
            //         $date = date('Y-m-d', strtotime($pre->created_at . " +1 days"));
            //         if(date("Y-m-d",strtotime($pre->accepted_date))>$date){
            //             if($pre->free_introduction==1){
            //                 $freeIntroductionTotal = $freeIntroductionTotal + $pre->cost_discounted;
            //             }else{
            //                 $discountBidTotal = $discountBidTotal + $pre->cost_leads;
            //             }
            //             $discountTotal = $discountTotal + $pre->cost_discounted;
            //             $discountCreditTotal = $discountCreditTotal + $pre->cost_leads;
            //             array_push($discountArr,$pre);
            //         }
            //     }
            //     $data['previous_total'] = $preTotal;
            //     $data['previous_invoice_paid_till'] = \Helpers::getMonth($previous_month);
            //     $data['previous_paid_total'] = $prePaidTotal;
            //     $data['new_invoice_total'] = $newTotal;
            //     $data['discount_credit_total'] = $discountCreditTotal;
            //     $data['discount_total'] = $discountTotal;
            //     $data['free_introductions_total'] = $freeIntroductionTotal;
            //     $data['discount_bid_total'] = $discountBidTotal;
            //     $data['sub_total_discount'] = $data['discount_bid_total'] - $data['free_introductions_total'];
            //     $data['new_fees'] = AdvisorBids::where('advisor_id',$data->advisor_id)->with('area')->get();
            //     $data['discount_credits'] = $discountArr;
            //     $data['tax_amount'] = 0;
            //     $data['taxable_amount'] = 0;
            //     $data['tax'] = 0;
            //     $data['total_due'] = $data['new_invoice_total'] - $data['discount_total'];
            //     $tax = DB::table('app_settings')->select('value')->where('key','tax_amount')->first();
            //     if($tax){
            //         $tax_cal = $data['total_due']/(1+($tax->value/100));
            //         $data['taxable_amount'] = $tax_cal;
            //         $data['tax_amount'] = $data['total_due'] - $tax_cal;
            //         $data['tax'] = $tax->value;
            //     }
            // }
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
