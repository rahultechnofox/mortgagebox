<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Invoice</title>
	<style>
		body{ background: #f7f7f7; margin: 0px; padding: 0px;}
	</style>
</head>

<body>
<table border="0" align="center" cellpadding="0" cellspacing="0" style=" width: 98%; max-width: 768px; margin: 0 auto; background: #fff; font-size: 14px; font-family: 'Lucida Grande', 'Lucida Sans Unicode', 'Lucida Sans', 'DejaVu Sans', Verdana, 'sans-serif'">
  <tbody>
    <tr>
      <td align="left" valign="middle" style="height: 10px;"></td>
    </tr>
    <tr>
      <td align="left" valign="middle" style="border-bottom: 1px solid #ddd;"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td width="50%" align="left" valign="middle"><img src="{{url('app-assets/images/logo/logo.png')}}" style="width: 200px; height: auto;" alt=""/></td>
            <td width="50%" align="right" valign="middle" style="line-height: 22px;">Invoice Number : #{{$invoice->invoice_number}}<br>
		Invoice Month: {{$invoice->show_date_month}}</td>
          </tr>
          <tr>
            <td colspan="2" align="left" valign="middle" style="height: 20px;"></td>
            </tr>
          <tr>
            <td colspan="2" align="left" valign="middle" style="height: 15px;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td align="left" valign="middle" style="line-height: 22px; font-size: 13px;width: 50%;">{{$invoice->invoice_data->seller_address}}</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="right" valign="middle" style="line-height: 22px; font-size: 13px;">Date Issued: {{$invoice->issued_date}}<br>Due Date: {{$invoice->due_date}}</td>
                </tr>
              </tbody>
            </table></td>
          </tr>
          <tr>
            <td colspan="2" align="left" valign="middle" style="height: 15px;"></td>
          </tr>
        </tbody>
      </table ></td>
    </tr>
    <tr>
      <td align="left" valign="middle" style="height: 10px;"></td>
    </tr>
    <tr>
      <td align="left" valign="middle"><table width="96%" border="0" cellpadding="0" cellspacing="0" style="width: 31%;padding-left: 18px;">
        <tbody>
          <tr>
            <td align="left" valign="top" style="font-size: 13px; color: #666;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr>
                  <td height="22" align="left" valign="middle" style="height: 35px; font-size: 16px;">Bill To</td>
                  </tr>
                <tr>
                  <td align="left" valign="middle" style="line-height: 20px;width: 40%;">{{$invoice->invoice_data->bill_to_address}}</td>
                  </tr>
                </tbody>
            </table></td>
            </tr>
        </tbody>
      </table></td>
    </tr>
    <tr>
      <td align="left" valign="middle" style="height: 20px;">&nbsp;</td>
    </tr>
    @if($invoice->unpaid_prevoius_invoice!=0)
    <tr>
      <td align="left" valign="top"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0" style="border: 1px solid #ddd;">
        <tbody>

          <tr>
            <td width="50%" height="35" align="left" valign="middle" style="font-size: 14px; color: #fff; background: #006fee; padding-left: 15px;">Account Summary</td>
            <td width="50%" height="35" align="right" valign="middle" style="font-size: 14px; color: #fff; background: #006fee; padding-right: 15px">{{$invoice->summary}}</td>
          </tr>
          <tr>
            <td height="30" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Preview Balance</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->unpaid_prevoius_invoice}}</td>
          </tr>
        </tbody>
      </table></td>
    </tr>
    @endif
	 <tr>
      <td align="left" valign="middle" style="height: 20px;">&nbsp;</td>
    </tr>
    <tr>
      <td align="left" valign="top"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0" style="border: 1px solid #ddd;">
        <tbody>
          <tr>
            <td width="50%" height="35" align="left" valign="middle" style="font-size: 14px; color: #fff; background: #006fee; padding-left: 15px;">New Fees</td>
            <td width="50%" height="35" align="right" valign="middle" style="font-size: 14px; color: #fff; background: #006fee; padding-right: 15px">&nbsp;</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Cost of Leads</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->cost_of_lead}}</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Total Taxable amount</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->sub_total_without_tax}}</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">VAT at a rate</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->vat_amount}}</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Subtotal</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->subtotal}}</td>
          </tr>
        </tbody>
      </table></td>
    </tr> 	
	 
	  <tr>
      <td align="left" valign="middle" style="height: 20px;">&nbsp;</td>
    </tr>
    <tr>
      <td align="left" valign="top"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0" style="border: 1px solid #ddd;">
        <tbody>
          <tr>
            <td width="50%" height="35" align="left" valign="middle" style="font-size: 14px; color: #fff; background: #006fee; padding-left: 15px;">Discount and Credit</td>
            <td width="50%" height="35" align="right" valign="middle" style="font-size: 14px; color: #fff; background: #006fee; padding-right: 15px">&nbsp;</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Discount</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->discount}}</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Fee introductions</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->free_introduction}}</td>
          </tr>
          <tr>
            <td height="24" align="left" valign="middle" style="font-size: 13px; color: #666; padding-left: 15px;">Sub Total</td>
            <td align="right" valign="middle" style="font-size: 13px; color: #666; padding-right: 15px;">£{{$invoice->discount_subtotal}}</td>
          </tr>
          <tr>
            <td height="32" align="left" valign="middle" style="font-size:16px; color: #666; padding-left: 15px;">Total Due</td>
            <td align="right" valign="middle" style="font-size: 16px; color: #666; padding-right: 15px;">£{{$invoice->total_due}}</td>
          </tr>
        </tbody>
      </table></td>
    </tr> 	
	 
	
    <tr>
      <td align="left" valign="middle" style="height: 15px;">&nbsp;</td>
    </tr>
    
    <tr>
      <td align="center" valign="top"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td height="35" align="left" valign="top" style="font-size: 16px; font-weight: 600; color: #111;">New Fees</td>
            </tr>
          <tr>
            <td align="left" valign="top" style="font-size: 13px; color: #666;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr style="background: #f4f8fe">
                  <td height="30" align="left" valign="middle" style="padding-left: 15px;">Accepted Date</td>
                  <td align="left" valign="middle">Customer </td>
                  <td align="left" valign="middle">Mortage</td>
                  <td align="left" valign="middle">Status</td>
                  <td align="left" valign="middle">Fee Type</td>
                  <td align="left" valign="middle">Amount</td>
                </tr>
                @if(isset($invoice->new_fees_arr) && count($invoice->new_fees_arr))
                @foreach($invoice->new_fees_arr as $new_fees_arr_data)
                <tr>
                  <td height="30" align="left" valign="middle" style="padding-left: 15px;">{{$new_fees_arr_data->date}}</td>
                  <td align="left" valign="middle">{{$new_fees_arr_data->area->user->name}}</td>
                  <td align="left" valign="middle">£{{number_format($new_fees_arr_data->area->size_want)}}</td>
                  <td align="left" valign="middle">{{$new_fees_arr_data->status_type}}</td>
                  <td align="left" valign="middle">{{$new_fees_arr_data->area->service->name}}</td>
                  <td align="left" valign="middle">£{{$new_fees_arr_data->cost_leads}}</td>
                </tr>
                @endforeach
                @endif
                <tr>
                  <td height="30" align="left" valign="middle" style="padding-left: 15px;">&nbsp;</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="right" valign="middle">New Fees Total: &nbsp;</td>
                  <td align="left" valign="middle"> £{{$invoice->subtotal}}</td>
                </tr>
              </tbody>
            </table></td>
          </tr>
        </tbody>
      </table></td>
    </tr>
    <tr>
      <td align="left" valign="middle">&nbsp;</td>
    </tr>
    <tr>
      <td align="left" valign="middle"><table width="96%" border="0" align="center" cellpadding="0" cellspacing="0">
        <tbody>
          <tr>
            <td height="35" align="left" valign="top" style="font-size: 16px; font-weight: 600; color: #111;">Discounts and Credits</td>
          </tr>
          <tr>
            <td align="left" valign="top" style="font-size: 13px; color: #666;"><table width="100%" border="0" cellspacing="0" cellpadding="0">
              <tbody>
                <tr style="background: #f4f8fe">
                  <td height="30" align="left" valign="middle" style="padding-left: 15px;">Accepted Date</td>
                  <td align="left" valign="middle">Customer </td>
                  <td align="left" valign="middle">Mortage</td>
                  <td align="left" valign="middle">Status</td>
                  <td align="left" valign="middle">Fee Type</td>
                  <td align="left" valign="middle">Amount</td>
                </tr>
                @if(isset($invoice->discount_credit_arr) && count($invoice->discount_credit_arr))
                @foreach($invoice->discount_credit_arr as $discount_credit_arr_data)
                <tr>
                  <td height="30" align="left" valign="middle" style="padding-left: 15px;">{{$discount_credit_arr_data->date}}</td>
                  <td align="left" valign="middle">{{$discount_credit_arr_data->area->user->name}}</td>
                  <td align="left" valign="middle">£{{number_format($discount_credit_arr_data->area->size_want)}}</td>
                  <td align="left" valign="middle">{{$discount_credit_arr_data->status_type}}</td>
                  <td align="left" valign="middle">{{$discount_credit_arr_data->discount_cycle}}</td>
                  <td align="left" valign="middle">£{{$discount_credit_arr_data->cost_leads}}</td>
                </tr>
                @endforeach
                @endif
                <tr>
                  <td height="30" align="left" valign="middle" style="padding-left: 15px;">&nbsp;</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="left" valign="middle">&nbsp;</td>
                  <td align="right" valign="middle">Credit Total:&nbsp;</td>
                  <td align="left" valign="middle">£{{$invoice->discount_subtotal}}</td>
                </tr>
              </tbody>
            </table></td>
          </tr>
        </tbody>
      </table></td>
    </tr>
    <tr>
      <td align="left" valign="middle">&nbsp;</td>
    </tr>
  </tbody>
</table>
<p>&nbsp;</p>
<p>&nbsp;</p>
	
	
	
</body>
</html>
