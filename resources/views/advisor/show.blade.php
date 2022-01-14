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
                        <h2 class="content-header-title float-start mb-0">Professional</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/advisors*') ? 'active' : '' }}"><a href="{!! url('admin/advisors') !!}">Professionals List</a>
                                </li>
                                <li class="breadcrumb-item active">Professional Info
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <section class="app-user-edit">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>{{ __('Overview') }}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Name:') }}</h6>
                                        <small>{{isset($userDetails->name) ? $userDetails->name : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Role:') }}</h6>
                                        <small>{{isset($userDetails->role) && $userDetails->role!='' ? $userDetails->role : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Status:') }}</h6>
                                        <small>
                                            @if(isset($userDetails->status) && $userDetails->status!='')
                                                @if($userDetails->status==1)
                                                    @if($userDetails->email_verified_at!='' && $profile->FCA_verified!='')
                                                        <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Active</span>
                                                    @else
                                                        <span class="badge rounded-pill badge-light-warning me-1" style="margin-bottom: 10px;">Pending</span>
                                                    @endif
                                                @elseif($userDetails->status==0)
                                                    <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Suspended</span>
                                                @else
                                                    <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Inactive</span>
                                                @endif
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <small>
                                            @if(isset($profile) && $profile->image!='')
                                            <?php $url = url('storage/advisor/'.$profile->image); ?>
                                                @if($url!='')
                                                    <img src="{{$url}}" style="width: 80px;" onerror="this.onerror=null;this.src=`{{url('no-image.png')}}`">
                                                @else
                                                    <img src="{{url('no-image.png')}}" style="width: 80px;">
                                                @endif
                                            @else
                                                <img src="{{url('no-image.png')}}" style="width: 80px;">
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Active Team Members:') }}</h6>
                                        <small>
                                            @if(isset($profile) && $profile!='')
                                                @if($profile->company_id!='' && $profile->company_id!=0)
                                                     <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                                @else
                                                    <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                                @endif
                                            @else
                                                --
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            @if(isset($userDetails->status) && $userDetails->status==0)
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Additional Info:') }}</h6>
                                        <small>{{isset($userDetails->suspend_reason) ? $userDetails->suspend_reason : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            @endif
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('FCA Verified:') }}</h6>
                                        <small>
                                            @if($profile->invalidate_fca==1)
                                                <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Invalid</span>
                                            @else
                                                @if(isset($profile->FCA_verified) && $profile->FCA_verified != "")  
                                                    <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                                @else
                                                    <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                                @endif
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Email Verified:') }}</h6>
                                        <small>
                                        @if($userDetails->email_verified_at!='')
                                            {{isset($userDetails->email_verified_at) && $userDetails->email_verified_at!=''? \Helpers::formatDateTime($userDetails->email_verified_at) : '--'}}
                                        @else
                                            --
                                        @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>{{ __('Details') }}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Email:') }}</h6>
                                        <small>{{isset($userDetails->email) ? $userDetails->email : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Contact Number:') }}</h6>
                                        <small>{{isset($profile->phone_number) ? $profile->phone_number : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('FCA Number:') }}</h6>
                                        <small>{{isset($profile->FCANumber) ? $profile->FCANumber : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Last Updated:') }}</h6>
                                        <small>{{isset($userDetails->last_active) ? \Helpers::formatDateTime($userDetails->updated_at) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Sex:') }}</h6>
                                        <small>
                                            @if(isset($profile->gender) && $profile->gender!='') 
                                                @if($profile->gender!='null')
                                                    {{$profile->gender}} 
                                                @else   
                                                    --
                                                @endif
                                            @else
                                                --                                            
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Langauge:') }}</h6>
                                        <small>
                                            @if(isset($profile->language) && $profile->language!='') 
                                                @if($profile->language!='null')
                                                    {{$profile->language}} 
                                                @else   
                                                    --
                                                @endif
                                            @else
                                                --                                            
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Joined:') }}</h6>
                                        <small>{{isset($userDetails->created_at) ? \Helpers::formatDateTime($userDetails->created_at) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Current Balance:') }}</h6>
                                        <small>
                                            @if(isset($userDetails->current_balance))
                                                {{\Helpers::currency($userDetails->current_balance)}}
                                            @else
                                                --                                            
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('City/Town:') }}</h6>
                                        <small>
                                            @if(isset($profile->city) && $profile->city!='') 
                                                {{$profile->city}} 
                                            @else
                                                --                                            
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Postal Code:') }}</h6>
                                        <small>
                                            @if(isset($profile->postcode) && $profile->postcode!='') 
                                                {{$profile->postcode}} 
                                            @else
                                                --                                            
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Need Summary</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="table-responsive" style="min-height: auto;">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Active</th>
                                            <th>Closed</th>
                                            <th>Live Lead</th>
                                            <th>Hired</th>
                                            <th>Completed</th>
                                            <th>Not Proceeding</th>
                                            <th>Lost</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if(isset($userDetails) && $userDetails!='')
                                            <tr>
                                                <td>{{isset($userDetails->accepted_leads) ? $userDetails->accepted_leads : '--'}}</td>
                                                <td>{{isset($userDetails->closed) ? $userDetails->closed : '--'}}</td>
                                                <td>{{isset($userDetails->live_leads) ? $userDetails->live_leads : '--'}}</td>
                                                <td>{{isset($userDetails->hired_leads) ? $userDetails->hired_leads : '--'}}</td>
                                                <td>{{isset($userDetails->completed_leads) ? $userDetails->completed_leads : '--'}}</td>
                                                <td>{{isset($userDetails->not_proceed) ? $userDetails->not_proceed : '--'}}</td>
                                                <td>{{isset($userDetails->lost_leads) ? $userDetails->lost_leads : '--'}}</td>
                                            </tr>
                                        @else
                                            <tr>
                                                <td colspan="15" class="recordnotfound"><span>No results found.</span></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Notes</h4>
                            </div>
                        </div>
                        <div class="row">
                            @if(isset($profile->notes) && count($profile->notes)>0)
                                @foreach($profile->notes as $notes_data)
                                    <div class="col-md-12">
                                        <div class="mb-1">
                                            <div class="transaction-percentage">
                                                <input class="form-control" value="{{date('M d, Y',strtotime($notes_data->created_at))}} - {{$notes_data->notes}}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            <form id="noteForm">
                                <input name="company_id" id="company_id" type="hidden" value="{{isset($profile) ? $profile->company_id : ''}}">
                                <div class="col-md-10" style="display: inline-block;">
                                    <div class="mb-1">
                                        <div class="transaction-percentage">
                                            <input class="form-control" name="notes" placeholder="Notes">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-1" style="display: inline-block;">
                                    <div class="mb-1">
                                        <div class="transaction-percentage">
                                            <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="addNotes('noteForm');">Add</button>
                                        </div>
                                    </div>
                                </div>
                            </form>
                            <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1"
                                 onclick="validFCANumber(1,'{{isset($profile) ? $profile->id : ''}}');">Valid FCA number</button>
                                <button type="button" class="btn btn-secondary mb-1 mb-sm-0 me-0 me-sm-1" onclick="validFCANumber(2,'{{isset($profile) ? $profile->id : ''}}');">Invalid FCA number</button>
                                @if(isset($userDetails->status) && $userDetails->status==0)
                                    <button type="button" class="btn btn-success mb-1 mb-sm-0 me-0 me-sm-1" onclick="updateStatus('{{$userDetails->id}}','1','/admin/update-advisor-status');">Activate</button>
                                @endif
                                @if(isset($userDetails->status) && $userDetails->status==1)
                                    <button type="button" class="btn btn-danger mb-1 mb-sm-0 me-0 me-sm-1"  data-bs-toggle="modal" data-bs-target="#inlineForm" onclick="resetSuspended('advisor_suspended');">Suspend</button>
                                    <button type="button" class="btn btn-dark mb-1 mb-sm-0 me-0 me-sm-1" onclick="updateStatus('{{$userDetails->id}}','2','/admin/update-advisor-status');">Inactive</button>
                                @endif
                                @if(isset($userDetails->status) && $userDetails->status==2)
                                    <button type="button" class="btn btn-danger mb-1 mb-sm-0 me-0 me-sm-1"  data-bs-toggle="modal" data-bs-target="#inlineForm" onclick="resetSuspended('advisor_suspended');">Suspend</button>
                                    <button type="button" class="btn btn-success mb-1 mb-sm-0 me-0 me-sm-1" onclick="updateStatus('{{$userDetails->id}}','1','/admin/update-advisor-status');">Activate</button>
                                @endif
                                @if(isset($invoice) && $invoice!='')
                                    <a href="{{url('/admin/advisors/invoice/')}}/{{$userDetails->id}}" class="btn btn-info mb-1 mb-sm-0 me-0 me-sm-1">Invoice</a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@if(isset($userDetails))
<div class="modal fade text-start" id="inlineForm" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Suspend Customer</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="resetSuspended('advisor_suspended');"></button>
            </div>
            <form id="advisor_suspended">
                <div class="modal-body">
                    <label>Suspend Reason</label>
                    <div class="mb-1">
                        <select class="form-control" name="reason" id="reason" onchange="selectValue(this.value);">
                            <option value="">Select Reason</option>
                            <option value="Unpaid Invoice">Unpaid Invoice</option>
                            <option value="T & C Broken">T & C Broken</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <label class="reason hide">Suspend Reason</label>
                    <div class="mb-1 reason hide">
                        <textarea placeholder="Suspended reason" class="form-control" name="suspend_reason" id="suspend_reason"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="updateStatus('{{$userDetails->id}}','0','/admin/update-advisor-status',true,'suspend_reason');">Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection