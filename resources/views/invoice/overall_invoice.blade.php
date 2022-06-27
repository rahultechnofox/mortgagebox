@extends('layouts.app') @section('content')
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper container-xxl p-0">
		<div class="content-header row">
			<div class="content-header-left col-md-9 col-12 mb-2">
				<div class="row breadcrumbs-top">
					<div class="col-12">
						<h2 class="content-header-title float-start mb-0">Invoice</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a> </li>
								<li class="breadcrumb-item active">Invoice Info </li>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="content-header-right text-md-end col-md-12 col-12 d-md-block mb-1">
                <form role="form" method="get">
                    <div class="form-group row">
						<!-- <div class="col-md-3 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="discount">
                                <option value="">Discount</option>
								<option value="">50%</option>
								<option value="">75%</option>
								<option value="">Free</option>
                            </select>
                        </div>
						<div class="col-md-3 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="service_type">
                                <option value="">Fee type</option>
                            </select>
                        </div> -->
                        <!-- <div class="col-md-3 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="status">    
                                <option value="">Status</option>
                                <option value="0" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==0){ echo "selected"; } } ?>>In-Progress</option>
                                <option value="1" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==1){ echo "selected"; } } ?>>Accepted</option>
								<option value="2" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==2){ echo "selected"; } } ?>>Closed</option>
                                <option value="3" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==3){ echo "selected"; } } ?>>Decline</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-12" style="margin-top: 15px;">
							<input type="text" class="form-select typeahead" name="post_code" id="post_code" onkeypress="getPostCodesAutocomplete();" autocomplete="off"/>
                           
                        </div>
						<div class="col-md-4 col-12" style="margin-top: 15px;">
							<input type="text" id="fp-range" class="form-control flatpickr-range" name="date" placeholder="YYYY-MM-DD to YYYY-MM-DD" value="<?php if(isset($_GET['date']) && $_GET['date']!=''){ echo $_GET['date']; } ?>"/>
                        </div> -->
						<div class="col-md-2 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="advisor_id">
                                <option value="">Adviser</option>
                                @foreach($adviser_data as $adviser_data_data)
                                <option value="{{$adviser_data_data->id}}" <?php if(isset($_GET['advisor_id']) && $_GET['advisor_id']!=''){ if($_GET['advisor_id']==$adviser_data_data->id){ echo "selected"; } } ?>>{{$adviser_data_data->name}}</option>
								@endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="month">    
                                <option value="">Month</option>
                                <option value="1" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==1){ echo "selected"; } } ?>>Jan</option>
                                <option value="2" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==2){ echo "selected"; } } ?>>Feb</option>
                                <option value="3" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==3){ echo "selected"; } } ?>>Mar</option>
                                <option value="4" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==4){ echo "selected"; } } ?>>April</option>
                                <option value="5" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==5){ echo "selected"; } } ?>>May</option>
                                <option value="6" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==6){ echo "selected"; } } ?>>June</option>
                                <option value="7" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==7){ echo "selected"; } } ?>>Jul</option>
                                <option value="8" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==8){ echo "selected"; } } ?>>Aug</option>
                                <option value="9" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==9){ echo "selected"; } } ?>>Sep</option>
                                <option value="10" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==10){ echo "selected"; } } ?>>Oct</option>
                                <option value="11" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==11){ echo "selected"; } } ?>>Nov</option>
                                <option value="12" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==12){ echo "selected"; } } ?>>Dec</option>
                            </select>
                        </div>
                        <?php $firstYear = (int)date('Y'); ?>
                        <div class="col-md-2 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="year">    
                                <option value="">Year</option>
                                <?php for($i=$firstYear;$i>=2002;$i--){ ?>
                                    <option value="<?php echo $i;?>" <?php if(isset($_GET['year']) && $_GET['year']!=''){ if($_GET['year']==$i){ echo "selected"; } } ?>><?php echo $i;?></option>
                                <?php }?>
                            </select>
                        </div>
                        <div class="col-md-2 col-12" style="margin-top: 15px;">
                            <button type="submit" name="submit" value="Search" id="submit" class="dt-button create-new btn btn-primary"><i data-feather="search"></i></button>
                            <a href="javascript:;" onclick="resetFilter()" class="btn btn-outline-secondary"><i data-feather="refresh-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
		</div>

		<div class="content-body">
			<section class="invoice-preview-wrapper">
				<div class="row invoice-preview">
					<!-- Invoice -->
					<div class="col-xl-12 col-md-12 col-12">
						@if(isset($invoice) && count($invoice))
						<div class="card invoice-preview-card">							
							<div class="card-body invoice-padding pt-0">
								<div class="row invoice-spacing">
                                    <h3 class="mb-2">Account Summary:</h3>
									<div class="col-xl-10">
										<p class="card-text mb-25">Previous balance</p>
										<p class="card-text mb-25">Invoice Payment received</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
												<tr>
													<td><span class="fw-bold">@if(isset($invoice['unpaid_prevoius_invoice']) && $invoice['unpaid_prevoius_invoice']!=''){{\Helpers::currency($invoice['unpaid_prevoius_invoice'])}}@else -- @endif</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">@if(isset($invoice['paid_prevoius_invoice']) && $invoice['paid_prevoius_invoice']!=''){{\Helpers::currency($invoice['paid_prevoius_invoice'])}}@else -- @endif</span></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							
                            <div class="card-body invoice-padding pt-0">
								<div class="row invoice-spacing">
                                    <h6 class="mb-2">New fees:</h6>
									<div class="col-xl-10">
										<p class="card-text mb-25">Cost of leads</p>
										<p class="card-text mb-25">Sub-total</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
												<tr>
													<td><span class="fw-bold">@if(isset($invoice['cost_of_lead']) && $invoice['cost_of_lead']!='')<?php echo \Helpers::currency($invoice['cost_of_lead']); ?> @else -- @endif</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">@if(isset($invoice['subtotal']) && $invoice['subtotal']!='') <?php echo \Helpers::currency($invoice['subtotal']); ?> @else -- @endif</span></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
                            <div class="card-body invoice-padding pt-0">
								<div class="row invoice-spacing">
                                    <h6 class="mb-2">Discounts and credits:</h6>
									<div class="col-xl-10">
										<p class="card-text mb-25">Discounts</p>
										<p class="card-text mb-25">Free introductions</p>
										<p class="card-text mb-25">Sub-total</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
											<tr>	
													<td><span class="fw-bold">@if(isset($invoice['discount']) && $invoice['discount']!='') <?php echo \Helpers::currency($invoice['discount']); ?> @else -- @endif</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold"> @if(isset($invoice['free_introduction']) && $invoice['free_introduction']!='') <?php echo \Helpers::currency($invoice['free_introduction']); ?> @else -- @endif</span></td>
												</tr>
                                                <tr>
													@if(isset($invoice['discount']) && $invoice['discount']!='')
														<?php $discount_subtotal = $invoice['discount'] + $invoice['free_introduction']; ?>
													@endif
													<td><span class="fw-bold">@if(isset($discount_subtotal) && $discount_subtotal!='') <?php echo \Helpers::currency($discount_subtotal); ?> @else -- @endif</span></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
                            <div class="card-body invoice-padding pt-0">
								<div class="row invoice-spacing">
									<div class="col-xl-10">
										<p class="card-text mb-25">Total due</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
												<tr>
													<td><span class="fw-bold">@if(isset($invoice['total_due']) && $invoice['total_due']!='') <?php echo \Helpers::currency($invoice['total_due']); ?> @else -- @endif</span></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<div class="card-body invoice-padding pt-0">
								<div class="row invoice-spacing">
                                    <h3 class="mb-2">Tax Summary:</h3>
									<div class="col-xl-10">
										<p class="card-text mb-25">Total taxable amount</p>
										<p class="card-text mb-25">VAT @ of %</p>
										<p class="card-text mb-25">Total current invoice amount</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
												<tr>
													<td><span class="fw-bold">@if(isset($invoice['total_taxable_amount']) && $invoice['total_taxable_amount']!='') <?php echo \Helpers::currency($invoice['total_taxable_amount']); ?> @else -- @endif</span></td>
												</tr>
												<tr>
													<td><span class="fw-bold">@if(isset($invoice['vat']) && $invoice['vat']!='') <?php echo \Helpers::currency($invoice['vat']); ?> @else -- @endif</span></td>
												</tr>
												<tr>
													<td><span class="fw-bold">@if(isset($invoice['total_current_invoice']) && $invoice['total_current_invoice']!='') <?php echo \Helpers::currency($invoice['total_current_invoice']); ?> @else -- @endif</span></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<!-- Address and Contact ends -->
							<!-- Invoice Description starts -->
                            <div class="col-xl-12">
								<h3 class="pd-20">New Fees</h3>
                            </div>
							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th class="py-1">Accepted</th>
											<th class="py-1">Customer</th>
											<th class="py-1">Mortgage</th>
											<th class="py-1">Status</th>
                                            <th class="py-1">Fee type</th>
											<th class="py-1">Amount</th>
										</tr>
									</thead>
									<tbody>
                                        @if(isset($invoice['new_fees_arr']) && count($invoice['new_fees_arr'])>0)
                                            @foreach($invoice['new_fees_arr'] as $new_fees_data)
												<tr>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->date}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->area->user->name}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$new_fees_data->area->property}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$new_fees_data->status_type}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->area->service['name']}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{\Helpers::currency($new_fees_data->cost_leads)}}</span> </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="py-1" colspan="6"> <span class="fw-bold">Record not found</span> </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="py-1" colspan="6" style="text-align:right;"> <span class="fw-bold">New Fees total: @if(isset($invoice['subtotal']) && $invoice['subtotal']!='')  <?php echo \Helpers::currency($invoice['subtotal']); ?> @else -- @endif</span> </td>
										</tr>
									</tbody>
								</table>
							</div>
                            <div class="col-xl-12">
								<h3 class="pd-20">Discounts and credits</h3>
                            </div>
							<div class="table-responsive">
								<table class="table">
									<thead>
										<tr>
											<th class="py-1">Accepted</th>
											<th class="py-1">Customer</th>
											<th class="py-1">Mortgage</th>
                                            <th class="py-1">Fee type</th>
											<th class="py-1">Amount</th>
										</tr>
									</thead>
									<tbody>
                                        @if(isset($invoice['discount_credit_arr']) && count($invoice['discount_credit_arr'])>0)
                                            @foreach($invoice['discount_credit_arr'] as $discount_credits_data)
												<tr>
													<td class="py-1"> <span class="fw-bold">{{$discount_credits_data->date}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$discount_credits_data->area->user->name}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$discount_credits_data->area->property}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($discount_credits_data->area->service) && $discount_credits_data->area->service!=''){{$discount_credits_data->area->service->name}}@else -- @endif</span> </td>
													<td class="py-1"> <span class="fw-bold">{{\Helpers::currency($discount_credits_data->cost_leads)}}</span> </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="py-1" colspan="6"> <span class="fw-bold">Record not found</span> </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="py-1" colspan="6" style="text-align:right;"> <span class="fw-bold">Credit total: @if(isset($invoice['discount']) && $invoice['discount']!='') <?php echo \Helpers::currency($invoice['discount']); ?> @else -- @endif</span> </td>
										</tr>
									</tbody>
								</table>
							</div>
						</div>
						@else
						<div class="card invoice-preview-card" style="padding: 20px;text-align: center;color: red;">
							<p>Invoice not found</p>
						</div>
						@endif
					</div>
				</div>
			</section>
			<div class="modal modal-slide-in fade" id="send-invoice-sidebar" aria-hidden="true">
				<div class="modal-dialog sidebar-lg">
					<div class="modal-content p-0">
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
						<div class="modal-header mb-1">
							<h5 class="modal-title">
                                    <span class="align-middle">Send Invoice</span>
                                </h5> </div>
						<div class="modal-body flex-grow-1">
							<form>
								<div class="mb-1">
									<label for="invoice-from" class="form-label">From</label>
									<input type="text" class="form-control" id="invoice-from" value="shelbyComapny@email.com" placeholder="company@email.com"> </div>
								<div class="mb-1">
									<label for="invoice-to" class="form-label">To</label>
									<input type="text" class="form-control" id="invoice-to" value="qConsolidated@email.com" placeholder="company@email.com"> </div>
								<div class="mb-1">
									<label for="invoice-subject" class="form-label">Subject</label>
									<input type="text" class="form-control" id="invoice-subject" value="Invoice of purchased Admin Templates" placeholder="Invoice regarding goods"> </div>
								<div class="mb-1">
									<label for="invoice-message" class="form-label">Message</label>
									<textarea class="form-control" name="invoice-message" id="invoice-message" cols="3" rows="11" placeholder="Message...">Dear Queen Consolidated, Thank you for your business, always a pleasure to work with you! We have generated a new invoice in the amount of $95.59 We would appreciate payment of this invoice by 05/11/2019</textarea>
								</div>
								<div class="mb-1"> <span class="badge badge-light-primary">
									<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-link me-25"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
									<span class="align-middle">Invoice Attached</span> </span>
								</div>
								<div class="mb-1 d-flex flex-wrap mt-2">
									<button type="button" class="btn btn-primary me-1 waves-effect waves-float waves-light" data-bs-dismiss="modal">Send</button>
									<button type="button" class="btn btn-outline-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- /Send Invoice Sidebar -->
			<!-- Add Payment Sidebar -->
			<div class="modal modal-slide-in fade" id="add-payment-sidebar" aria-hidden="true">
				<div class="modal-dialog sidebar-lg">
					<div class="modal-content p-0">
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
						<div class="modal-header mb-1">
							<h5 class="modal-title">
                                    <span class="align-middle">Add Payment</span>
                                </h5> </div>
						<div class="modal-body flex-grow-1">
							<form>
								<div class="mb-1">
									<input id="balance" class="form-control" type="text" value="Invoice Balance: 5000.00" disabled=""> </div>
								<div class="mb-1">
									<label class="form-label" for="amount">Payment Amount</label>
									<input id="amount" class="form-control" type="number" placeholder="$1000"> </div>
								<div class="mb-1">
									<label class="form-label" for="payment-date">Payment Date</label>
									<input id="payment-date" class="form-control date-picker flatpickr-input" type="text" readonly="readonly"> </div>
								<div class="mb-1">
									<label class="form-label" for="payment-method">Payment Method</label>
									<select class="form-select" id="payment-method">
										<option value="" selected="" disabled="">Select payment method</option>
										<option value="Cash">Cash</option>
										<option value="Bank Transfer">Bank Transfer</option>
										<option value="Debit">Debit</option>
										<option value="Credit">Credit</option>
										<option value="Paypal">Paypal</option>
									</select>
								</div>
								<div class="mb-1">
									<label class="form-label" for="payment-note">Internal Payment Note</label>
									<textarea class="form-control" id="payment-note" rows="5" placeholder="Internal Payment Note"></textarea>
								</div>
								<div class="d-flex flex-wrap mb-0">
									<button type="button" class="btn btn-primary me-1 waves-effect waves-float waves-light" data-bs-dismiss="modal">Send</button>
									<button type="button" class="btn btn-outline-secondary waves-effect" data-bs-dismiss="modal">Cancel</button>
								</div>
							</form>
						</div>
					</div>
				</div>
			</div>
			<!-- /Add Payment Sidebar -->
		</div>
	</div>
</div> @endsection