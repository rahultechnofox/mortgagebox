@extends('layouts.app') @section('content')
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper container-xxl p-0">
		<div class="content-header row">
			<div class="content-header-left col-md-9 col-12 mb-2">
				<div class="row breadcrumbs-top">
					<div class="col-12">
						<h2 class="content-header-title float-start mb-0">Professional</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a> </li>
								<li class="breadcrumb-item {{ Request::is('admin/advisors*') ? 'active' : '' }}"><a href="{!! url('admin/advisors') !!}">Professionals List</a> </li>
								<li class="breadcrumb-item active">Professional Info </li>
							</ol>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="content-body">
			<section class="invoice-preview-wrapper">
				<div class="row invoice-preview">
					<!-- Invoice -->
					<div class="col-xl-12 col-md-12 col-12">
						<div class="card invoice-preview-card">
							<div class="card-body invoice-padding pb-0">
								<!-- Header starts -->
								<div class="d-flex justify-content-between flex-md-row flex-column invoice-spacing mt-0">
									<div>
                                    <div class="logo-wrapper" style="width:80%;float:left;">
                                        <img src="{{url('/argon/img/brand/logo.png')}}" style="width:20%;">
                                        <h6 class="mb-2">Mortgage Box</h6>
										<p class="card-text mb-25">123 High Street,</p>
										<p class="card-text mb-25">Imagnary town surrey, TW122AA,</p>
										<p class="card-text mb-0">United Kingdom</p>
                                        <p class="card-text mb-25"></p>
                                        <p class="card-text mb-0" style="margin-top: 15px;">This is not a payment address</p>
                                        <p class="card-text mb-0" style="margin-top: 15px;">VAT Number: GB1234567890</p>
                                        <p class="card-text mb-0">Company Number: 123456789</p>

                                        <h6 class="mb-2" style="margin-top: 15px;">Bill To: </h6>
										<p class="card-text mb-25">Office 149, 450 South Brand Brooklyn</p>
										<p class="card-text mb-25">San Diego County, CA 91905, USA</p>
										<p class="card-text mb-0">+1 (123) 456 7891, +44 (876) 543 2198</p>
									</div>
									<div class="mt-md-0 mt-2" style="width:20%;float:right;">
										<h4 class="invoice-title">
                                                Invoice
                                                <span class="invoice-number">@if(isset($invoice) && $invoice!='') #{{$invoice->invoice_number}} @endif</span>
                                            </h4>
										<div class="invoice-date-wrapper">
											<p class="invoice-date-title">Date Issued:</p>
											<p class="invoice-date">@if(isset($invoice) && $invoice!='') {{$invoice->created_at}} @endif</p>
										</div>
										<div class="invoice-date-wrapper">
											<p class="invoice-date-title">Due Date:</p>
											<p class="invoice-date">29/08/2020</p>
										</div>
									</div>
								</div>
								<!-- Header ends -->
							</div>
							<hr class="invoice-spacing">
							<!-- Address and Contact starts -->
							<div class="card-body invoice-padding pt-0">
								<div class="row invoice-spacing">
                                    <h3 class="mb-2">Account Summary:</h3>
									<div class="col-xl-10">
										<p class="card-text mb-25">Previous balance</p>
										<p class="card-text mb-25">Invoice Payment received 4-Aug-2021 - Thank you</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
												<tr>
													<td><span class="fw-bold">$12,110.55</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">$12,110.55</span></td>
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
													<td><span class="fw-bold">$12,110.55</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">$12,110.55</span></td>
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
													<td><span class="fw-bold">$12,110.55</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">$12,110.55</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">$12,110.55</span></td>
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
													<td><span class="fw-bold">$12,110.55</span></td>
												</tr>
											</tbody>
										</table>
									</div>
								</div>
							</div>
							<!-- Address and Contact ends -->
							<!-- Invoice Description starts -->
                            <div class="col-xl-12">
                                <p class="card-text mb-25">New Fees</p>
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
                                        @if(isset($new_fees) && count($new_fees)>0)
                                            @foreach($new_fees as $new_fees_data)
                                                <tr>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->created_at}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($new_fees_data->area->user) && $new_fees_data->area->user!=''){{$new_fees_data->area->user->name}} @else -- @endif</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($new_fees_data['area']) && $new_fees_data['area']!=''){{$new_fees_data['area']->property}} @else -- @endif</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">
                                                        <?php
                                                            if($new_fees_data->status==0){
                                                                echo "In-Progress";
                                                            }else if($new_fees_data->status==1){
                                                                echo "Accepted";
                                                            }else if($new_fees_data->status==2){
                                                                echo "Closed";
                                                            }else if($new_fees_data->status==3){
                                                                echo "Rejected";
                                                            }else{
                                                                echo "--";
                                                            }
                                                        ?>
                                                    </span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($new_fees_data->area) && $new_fees_data->area!=''){{ucfirst($new_fees_data->area->service_type)}} @else -- @endif</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->cost_leads}}</span> </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="py-1"> <span class="fw-bold">Record not found</span> </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="py-1" colspan="6" style="text-align:right;"> <span class="fw-bold">New Fees total: $60.00</span> </td>
										</tr>
									</tbody>
								</table>
							</div>
                            <div class="col-xl-12">
                                <p class="card-text mb-25">Discounts and credits</p>
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
                                        @if(isset($discount_credits) && count($discount_credits)>0)
                                            @foreach($discount_credits as $discount_credits_data)
                                                <tr>
                                                    <td class="py-1"> <span class="fw-bold">{{$discount_credits_data->created_at}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($discount_credits_data->area->user) && $discount_credits_data->area->user!=''){{$discount_credits_data->area->user->name}} @else -- @endif</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($discount_credits_data['area']) && $discount_credits_data['area']!=''){{$discount_credits_data['area']->property}} @else -- @endif</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">
                                                        <?php
                                                            if($discount_credits_data->status==0){
                                                                echo "In-Progress";
                                                            }else if($discount_credits_data->status==1){
                                                                echo "Accepted";
                                                            }else if($discount_credits_data->status==2){
                                                                echo "Closed";
                                                            }else if($discount_credits_data->status==3){
                                                                echo "Rejected";
                                                            }else{
                                                                echo "--";
                                                            }
                                                        ?>
                                                    </span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($discount_credits_data->area) && $discount_credits_data->area!=''){{ucfirst($discount_credits_data->area->service_type)}} @else -- @endif</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$discount_credits_data->cost_leads}}</span> </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="py-1"> <span class="fw-bold">Record not found</span> </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="py-1" colspan="6" style="text-align:right;"> <span class="fw-bold">Credit total: $60.00</span> </td>
										</tr>
									</tbody>
								</table>
							</div>
							<!-- <div class="card-body invoice-padding pb-0">
								<div class="row invoice-sales-total-wrapper">
									<div class="col-md-6 order-md-1 order-2 mt-md-0 mt-3">
										<p class="card-text mb-0"> <span class="fw-bold">Salesperson:</span> <span class="ms-75">Alfie Solomons</span> </p>
									</div>
									<div class="col-md-6 d-flex justify-content-end order-md-2 order-1">
										<div class="invoice-total-wrapper">
											<div class="invoice-total-item">
												<p class="invoice-total-title">Subtotal:</p>
												<p class="invoice-total-amount">$1800</p>
											</div>
											<div class="invoice-total-item">
												<p class="invoice-total-title">Discount:</p>
												<p class="invoice-total-amount">$28</p>
											</div>
											<div class="invoice-total-item">
												<p class="invoice-total-title">Tax:</p>
												<p class="invoice-total-amount">21%</p>
											</div>
											<hr class="my-50">
											<div class="invoice-total-item">
												<p class="invoice-total-title">Total:</p>
												<p class="invoice-total-amount">$1690</p>
											</div>
										</div>
									</div>
								</div>
							</div> -->
							<!-- Invoice Description ends -->
							<!-- <hr class="invoice-spacing"> -->
							<!-- Invoice Note starts -->
							<!-- <div class="card-body invoice-padding pt-0">
								<div class="row">
									<div class="col-12"> <span class="fw-bold">Note:</span> <span>It was a pleasure working with you and your team. We hope you will keep us in mind for future freelance
                                                projects. Thank You!</span> </div>
								</div>
							</div> -->
							<!-- Invoice Note ends -->
						</div>
					</div>
					<!-- /Invoice -->
				</div>
			</section>
			<!-- Send Invoice Sidebar -->
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