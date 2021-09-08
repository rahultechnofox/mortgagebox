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
								<li class="breadcrumb-item {{ Request::is('admin/invoice*') ? 'active' : '' }}"><a href="{!! url('admin/invoice') !!}">Invoice </a> </li>
								@if(isset($row->month) && $row->month!='')<li class="breadcrumb-item {{ Request::is('admin/invoice-list*') ? 'active' : '' }}"><a href="{!! url('admin/invoice-list') !!}/{{$row->month}}">Invoice List</a> </li>@endif
								<li class="breadcrumb-item active">Invoice Info </li>
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
                                        <!-- <h6 class="mb-2"></h6> -->
										<?php
											if($row->invoice_data->seller_address!=''){
												$explode = explode("\\n",$row->invoice_data->seller_address);
											}
										?>
										@if(isset($explode) && count($explode))
											@foreach($explode as $explode_data)
												<p class="card-text mb-25">{{$explode_data}}</p>
											@endforeach
										@else
											<p class="card-text mb-25"><?php echo json_encode($row->invoice_data->seller_address); ?></p>
										@endif

                                        <h6 class="mb-2" style="margin-top: 15px;">Bill To: </h6>
										<p class="card-text mb-25">{{$row->adviser->company_name}}</p>
										<p class="card-text mb-25">@if(isset($row->adviser->address_line1) && $row->adviser->address_line1!=''){{$row->adviser->address_line1}}, @endif @if(isset($row->adviser->address_line2) && $row->adviser->address_line2!='') {{$row->adviser->address_line2}} @endif</p>
										<p class="card-text mb-0">@if(isset($row->adviser->city) && $row->adviser->city!='') {{$row->adviser->city}} @endif @if(isset($row->adviser->postcode) && $row->adviser->address_line1!=''){{$row->adviser->postcode}}@endif</p>
									</div>
									<div class="mt-md-0 mt-2" style="width:20%;float:right;">
										<h4 class="invoice-title">
                                                Invoice
                                                <span class="invoice-number">@if(isset($row) && $row!='') #{{$row->invoice_number}} @endif</span>
                                            </h4>
										<div class="invoice-date-wrapper">
											<p class="invoice-date-title">Date Issued:</p>
											<p class="invoice-date">@if(isset($row) && $row!='') {{$row->created_at}} @endif</p>
										</div>
										<!-- <div class="invoice-date-wrapper">
											<p class="invoice-date-title">Due Date:</p>
											<p class="invoice-date">29/08/2020</p>
										</div> -->
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
										<p class="card-text mb-25">Invoice Payment received</p>
									</div>
									<div class="col-xl-2 p-0 mt-xl-0 mt-2">
										<table>
											<tbody>
												<tr>
													<td><span class="fw-bold">{{\Helpers::currency($row->unpaid_prevoius_invoice)}}</span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold">{{\Helpers::currency($row->paid_prevoius_invoice)}}</span></td>
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
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->cost_of_lead); ?></span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->subtotal); ?></span></td>
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
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->discount); ?></span></td>
												</tr>
                                                <tr>
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->free_introduction); ?></span></td>
												</tr>
                                                <tr>
													<?php $discount_subtotal = $row->discount + $row->free_introduction; ?>
													<td><span class="fw-bold"><?php echo \Helpers::currency($discount_subtotal); ?></span></td>
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
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->total_due); ?></span></td>
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
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->total_taxable_amount); ?></span></td>
												</tr>
												<tr>
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->vat); ?></span></td>
												</tr>
												<tr>
													<td><span class="fw-bold"><?php echo \Helpers::currency($row->total_current_invoice); ?></span></td>
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
                                        @if(isset($row->invoice_data->new_fees_data) && count($row->invoice_data->new_fees_data)>0)
                                            @foreach($row->invoice_data->new_fees_data as $new_fees_data)
                                                <tr>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->date}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->customer}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$new_fees_data->mortgage}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$new_fees_data->status}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$new_fees_data->free_type}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{\Helpers::currency($new_fees_data->amount)}}</span> </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="py-1"> <span class="fw-bold">Record not found</span> </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="py-1" colspan="6" style="text-align:right;"> <span class="fw-bold">New Fees total: <?php echo \Helpers::currency($row->subtotal); ?></span> </td>
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
                                        @if(isset($row->invoice_data->discount_credit_data) && count($row->invoice_data->discount_credit_data)>0)
                                            @foreach($row->invoice_data->discount_credit_data as $discount_credits_data)
												<tr>
                                                    <td class="py-1"> <span class="fw-bold">{{$discount_credits_data->date}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$discount_credits_data->customer}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$discount_credits_data->mortgage}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">{{$discount_credits_data->free_type}}</span> </td>
													<td class="py-1"> <span class="fw-bold">{{\Helpers::currency($discount_credits_data->amount)}}</span> </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td class="py-1"> <span class="fw-bold">Record not found</span> </td>
                                            </tr>
                                        @endif
                                        <tr>
                                            <td class="py-1" colspan="6" style="text-align:right;"> <span class="fw-bold">Credit total: <?php echo \Helpers::currency($row->discount); ?></span> </td>
										</tr>
									</tbody>
								</table>
							</div>
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
</div>
@endsection