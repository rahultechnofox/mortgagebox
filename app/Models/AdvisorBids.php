<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use DB;
use App\Models\AdvisorProfile;

class AdvisorBids extends Model
{
    use HasFactory;
    protected $fillable = [
        'advisor_id','area_id','status','advisor_status','cost_leads','cost_discounted','free_introduction','accepted_date'
    ];

    public function area(){
        return $this->hasOne('App\Models\Advice_area',"id","area_id")->with('user');
    }
    public function adviser(){
        return $this->hasOne('App\Models\AdvisorProfile',"advisorId","advisor_id");
    }

    public static function getInvoice($search){
        try {
            $query = new Self;
            // if(isset($search['search']) && $search['search']!=''){
            //     $query = $query->where('advisor_profiles.name', 'like', '%' .strtolower($search['search']). '%');
            // }
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
                // echo json_encode($from);
                // echo json_encode($to);exit;
                if(isset($explode[0]) && $explode[0]!='' && isset($explode[1]) && $explode[1]!=''){
                    // $from = trim($explode[0]);
                    // $to = trim($explode[1]);
                    $start = date("Y-m-d",strtotime($from));
                    $end = date('Y-m-d',strtotime($to));
                    $query = $query->whereBetween('created_at', [$start, $end]);
                }
            }
            
            // if(isset($search['created_at']) && $search['created_at']!=''){
            //     $query = $query->whereDate('advisor_profiles.created_at', '=',date("Y-m-d",strtotime($search['created_at'])));
            // }

            $data['site_address'] = DB::table('app_settings')->where('key','site_address')->first();
            $data['site_name'] = DB::table('app_settings')->where('key','mail_from_name')->first();
            $data['new_fees'] = array();
            $data['discount_credits'] = array();
            $data['invoice'] = DB::table('invoices')->where('month',date('m'))->first();
            
            $previous_month = date('m') - 1;
            $newTotal = 0;
            $preTotal = 0;
            $prePaidTotal = 0;
            $hourdiff = 0;
            $discountCreditTotal = 0;
            $discountTotal = 0;
            $discountBidTotal = 0;
            $freeIntroductionTotal = 0;
            $discountArr = array();
            $bid_data = $query->with('area')->with('adviser')->get();
            // echo json_encode($bid_data);exit;
            foreach($bid_data as $pre){
                if(date("m",strtotime($pre->created_at))<date("m",strtotime($previous_month))){
                    $preTotal = $preTotal + $pre->cost_leads;
                }
                if(date("m",strtotime($pre->created_at))<date("m",strtotime($previous_month)) && $pre->is_paid_invoice){
                    $prePaidTotal = $prePaidTotal + $pre->cost_leads;
                }
                if(date("m",strtotime($pre->created_at))==date("m")){
                    $newTotal = $newTotal + $pre->cost_leads;
                }
                $date = date('Y-m-d', strtotime($pre->created_at . " +1 days"));
                if($pre->accepted_date!=null){
                    if(date("Y-m-d",strtotime($pre->accepted_date))>$date){
                        // echo json_encode($pre);exit;
                        if($pre->free_introduction==1){
                            $freeIntroductionTotal = $freeIntroductionTotal + $pre->cost_discounted;
                        }else{
                            $discountBidTotal = $discountBidTotal + $pre->cost_leads;
                        }
                        $discountTotal = $discountTotal + $pre->cost_discounted;
                        $discountCreditTotal = $discountCreditTotal + $pre->cost_leads;
                        array_push($discountArr,$pre);
                    }
                    if(date("Y-m-d",strtotime($pre->accepted_date))>$date && date("Y-m-d",strtotime($pre->accepted_date))<date('Y-m-d', strtotime($pre->created_at . " +1 days"))){
                        $pre->fee_type_for_discounted = "50% discount"; 
                    }else if(date("Y-m-d",strtotime($pre->accepted_date))>date('Y-m-d', strtotime($pre->created_at . " +2 days")) && date("Y-m-d",strtotime($pre->accepted_date))<date('Y-m-d', strtotime($pre->created_at . " +3 days"))){
                        $pre->fee_type_for_discounted = "75% discount";
                    }else if(date("Y-m-d",strtotime($pre->accepted_date))>date('Y-m-d', strtotime($pre->created_at . " +3 days"))){
                        $pre->fee_type_for_discounted = "Free bid";
                    }
                }else{
                    $pre->fee_type_for_discounted = ""; 
                }
            }
            $data['previous_total'] = $preTotal;
            $data['previous_invoice_paid_till'] = \Helpers::getMonth($previous_month);
            $data['previous_paid_total'] = $prePaidTotal;
            $data['new_invoice_total'] = $newTotal;
            $data['discount_credit_total'] = $discountCreditTotal;
            $data['discount_total'] = $discountTotal;
            $data['free_introductions_total'] = $freeIntroductionTotal;
            $data['discount_bid_total'] = $discountBidTotal;
            $data['sub_total_discount'] = $data['discount_bid_total'] - $data['free_introductions_total'];
            $data['new_fees'] = $query->with('area')->with('adviser')->get();
            $data['discount_credits'] = $discountArr;
            $data['tax_amount'] = 0;
            $data['taxable_amount'] = 0;
            $data['tax'] = 0;
            $data['total_due'] = $data['new_invoice_total'] - $data['sub_total_discount'];
            $tax = DB::table('app_settings')->select('value')->where('key','tax_amount')->first();
            if($tax){
                $tax_cal = $data['total_due']/(1+($tax->value/100));
                $data['taxable_amount'] = $tax_cal;
                $data['tax_amount'] = $data['total_due'] - $tax_cal;
                $data['tax'] = $tax->value;
            }
            // echo json_encode($data);exit;
            return $data;
        }catch (\Exception $e) {
            return ['status' => false, 'message' => $e->getMessage() . ' '. $e->getLine() . ' '. $e->getFile()];
        }
    }
}
