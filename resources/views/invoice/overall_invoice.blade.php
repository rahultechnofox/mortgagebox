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
                        <!-- <?php $firstYear = (int)date('Y'); ?> -->
                        <!-- <div class="col-md-2 col-12" style="margin-top: 15px;">
                            
                        </div> -->
						<?php $diff = (int)date('Y') - $smallest_invoice_year;
						$firstYear = (int)date('Y') - $diff; ?>
                        <div class="col-md-2 col-12" style="margin-top: 15px;">
                            <select class="form-select" id="" name="year">    
                                <option value="">Year</option>
                                <?php for($i=$firstYear;$i<=date('Y');$i++){ ?>
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
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($new_fees_data->area->user) && $new_fees_data->area->user!='') {{$new_fees_data->area->user->name}} @else -- @endif </span> </td>
													<td class="py-1"> <span class="fw-bold">@if(isset($new_fees_data->area) && $new_fees_data->area!='') {{$new_fees_data->area->property}} @else -- @endif</span> </td>
													<td class="py-1"> <span class="fw-bold">{{$new_fees_data->status_type}}</span> </td>
                                                    <td class="py-1"> <span class="fw-bold">@if(isset($new_fees_data->area->service) && $new_fees_data->area->service!=''){{$new_fees_data->area->service['name']}} @else -- @endif</span> </td>
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
			
		</div>
	</div>
</div> @endsection