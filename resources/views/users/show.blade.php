@extends('layouts.app') 
@section('content')
<div class="app-content content ">
	<div class="content-overlay"></div>
	<div class="header-navbar-shadow"></div>
	<div class="content-wrapper container-xxl p-0">
		<div class="content-header row">
			<div class="content-header-left col-md-9 col-12 mb-2">
				<div class="row breadcrumbs-top">
					<div class="col-12">
						<h2 class="content-header-title float-start mb-0">Customers</h2>
						<div class="breadcrumb-wrapper">
							<ol class="breadcrumb">
								<li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a> </li>
								<li class="breadcrumb-item {{ Request::is('admin/users*') ? 'active' : '' }}"><a href="{!! url('admin/users') !!}">Customers List</a> </li>
								<li class="breadcrumb-item active">Customer Info </li>
							</ol>
						</div>
					</div>
				</div>
			</div>
			<div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <a class="btn btn-icon btn-primary" href="{{url('/admin/users')}}">
                        <i data-feather="arrow-left" class="me-25"></i>
                        <span>Back to customer list</span>
                    </a>
                </div>
            </div>
		</div>
		<div class="content-body">
			<section class="app-user-edit">
				<div class="card">
					<div class="card-body">
						<div class="d-flex mb-2">
							<div class="mt-50">
								<h4>Customer Detail</h4> </div>
						</div>
						<div class="row">
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Customer Name:</h6> <small>{{isset($userDetails->name) ? $userDetails->name : '--'}}</small> </div>
								</div>
							</div>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Customer ID:</h6> <small>{{isset($userDetails->id) ? $userDetails->id : '--'}}</small> </div>
								</div>
							</div>
							<hr>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Postcode:</h6> <small>{{isset($userDetails->post_code) ? $userDetails->post_code : '--'}}</small> </div>
								</div>
							</div>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Location:</h6> <small><?php if(isset($userDetails->district) && $userDetails->district!=''){ echo $userDetails->district.",";  }else{ echo ''; } ?> {{isset($userDetails->country) && $userDetails->country!='' ? $userDetails->country: '--'}}</small> </div>
								</div>
							</div>
							<hr>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Email:</h6> <small>{{isset($userDetails->email) ? $userDetails->email : '--'}}</small> </div>
								</div>
							</div>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Email Verified:</h6> 
                                        <small>
                                            @if($userDetails->email_verified_at == NULL)
												<span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                            @else 
												<span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                            @endif
                                        </small> 
										<a href="{{url('/admin/verifyEmail/')}}/{{$userDetails->id}}" class="btn btn-secondary btn-sm btn-add-new waves-effect waves-float waves-light" onclick="resetPassword('{{$userDetails->id}}');">Send Email Verification Link</a>
                                    </div>
								</div>
							</div>
							<hr>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Last Login:</h6> <small>{{isset($userDetails->updated_at) ? \Helpers::formatDateTime($userDetails->updated_at) : '--' }}</small> </div>
								</div>
							</div>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Joined Dated:</h6> <small>{{isset($userDetails->created_at) ? \Helpers::formatDateTime($userDetails->created_at) : '--' }}</small> </div>
								</div>
							</div>
							<hr>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Status:</h6> <small>
                                            @if($userDetails->status ==1)
												@if($userDetails->email_verified_at!=null)
													<span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Active</span>
												@else
													<span class="badge rounded-pill badge-light-warning me-1" style="margin-bottom: 10px;">Pending</span>
												@endif
                                            @else
                                                <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Suspended</span>
                                            @endif
                                        </small> 
                                    </div>
								</div>
							</div>
							<div class="col-md-6 mb-1">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Last Active:</h6> <small>{{isset($userDetails->last_active) ? \Helpers::formatDateTime($userDetails->last_active) : '--' }}</small> </div>
								</div>
							</div>
							<hr>
							<div class="col-md-5 mb-1">
								<div>
									<div class="transaction-percentage">
										<h6 class="transaction-title">Password:</h6>
										<input type="password" name="password" id="password" class="form-control form-control-merge" style="float: left;width: 65%;">
										<button type="button" class="dt-button create-new btn btn-primary" onclick="updatePassword('{{$userDetails->id}}');" style="float: left;width: 35%;">Update</button>
									</div>
								</div>
							</div>
							<div class="col-md-12 mb-1" style="margin-top: 15px;">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title" style="display: inline-block;">Reset Password:</h6>
										<button type="button" class="btn btn-secondary btn-sm btn-add-new waves-effect waves-float waves-light" onclick="resetPassword('{{$userDetails->id}}');">Send Reset Password Link</button>
									</div>
								</div>
							</div>
							<div class="col-md-12 mb-1" style="margin-top: 15px;">
								<div class="d-flex">
									<div class="transaction-percentage">
										@if($userDetails->status==1)		
											<button type="button" class="btn btn-danger btn-sm btn-add-new waves-effect waves-float waves-light" data-bs-toggle="modal" data-bs-target="#inlineForm">Suspend customer</button>
										@else
											<button type="button" class="btn btn-success btn-sm btn-add-new waves-effect waves-float waves-light" onclick="updateStatus('{{$userDetails->id}}','1','/admin/update-user-status');">Activate customer</button>
										@endif
									</div>
								</div>
							</div>
							<div class="col-md-6 mb-1" style="margin-top: 15px;">
								<div class="d-flex">
									<div class="transaction-percentage">
										<h6 class="transaction-title">Need Summary:</h6>
										<table class="table table-striped">	
											<tr>
												<th>Pending</th>
												<th>New Lead</th>
												<th>Closed</th>
                                            </tr>
											@if(isset($userDetails->pending_bid) && isset($userDetails->active_bid) && isset($userDetails->closed))
												<tr>
													<td>{{$userDetails->pending_bid}}</td>
													<td>{{$userDetails->active_bid}}</td>
													<td>{{$userDetails->closed}}</td>
												</tr>
											@endif
										</table>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
</div> 
<div class="modal fade text-start" id="inlineForm" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Suspend Customer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#">
                <div class="modal-body">
                    <label>Suspend Reason</label>
                    <div class="mb-1">
                        <textarea placeholder="Suspended reason" class="form-control" name="suspend_reason" id="suspend_reason"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="updateStatus('{{$userDetails->id}}','0','/admin/update-user-status',true,'suspend_reason');">Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection