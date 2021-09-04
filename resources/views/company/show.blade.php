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
                        <h2 class="content-header-title float-start mb-0">Company</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ url('/') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/companies*') ? 'active' : '' }}"><a href="{!! url('admin/companies') !!}">Company List</a>
                                </li>
                                <li class="breadcrumb-item active">Company Info
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
                                <h4>Company Detail</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Company Name:</h6>
                                        <small>{{isset($company_detail->company_name) ? $company_detail->company_name : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <small>
                                            @if(isset($company_detail->id))
                                                <img src="{{url('no-image.png')}}" style="width: 80px;">
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">FCA Number:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->FCANumber : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Network:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->network : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Address Line 1:</h6>
                                        <small>{{isset($company_detail->adviser) && $company_detail->adviser->address_line1!='' ? $company_detail->adviser->address_line1 : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Address Line 2:</h6>
                                        <small>
                                        {{isset($company_detail->adviser) && $company_detail->adviser->address_line2!='' ? $company_detail->adviser->address_line2 : '--' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Town/City:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->city : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Postcode:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->postcode : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Status:</h6>
                                        <small>
                                            @if($company_detail->status==1)
                                                <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Active</span>
                                            @else
                                                <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Suspended</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Primary Contact</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Name:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->display_name : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Contact No.:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->phone_number : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Email:</h6>
                                        <small>{{isset($company_detail->adviser) ? $company_detail->adviser->email : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Team Members</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Name</th>
                                            <th>Role</th>
                                            <th>Email Confirmed?</th>
                                            <th>FCA Checked?</th>
                                            <th>Accepted Leads</th>
                                            <th>Live Leads</th>
                                            <th>Hired</th>
                                            <th>Completed</th>
                                            <th>Success %</th>
                                            <th>Value</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @if(count($company_detail->team_members) > 0)
                                            @foreach($company_detail->team_members as $company_detail_data)
                                                <tr>
                                                    <td>{{$company_detail_data->id}}</td>
                                                    <td>{{$company_detail_data->name}}</td>
                                                    <td>@if(isset($company_detail_data->team_data) && $company_detail_data->team_data!=''){{$company_detail_data->team_data->role}}@else -- @endif</td>
                                                    <td>
                                                        @if(isset($company_detail_data->team_data) && $company_detail_data->team_data!='')
                                                            @if($company_detail_data->team_data->email_status==1)
                                                                <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                                            @else
                                                                <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                                            @endif
                                                        @else 
                                                            -- 
                                                        @endif
                                                    </td>
                                                    <td>
                                                        @if(isset($company_detail_data->team_data_advisor_profile) && $company_detail_data->team_data_advisor_profile!='')
                                                            @if($company_detail_data->team_data_advisor_profile->FCA_verified != "")  
                                                                <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                                            @else
                                                                <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                                            @endif
                                                        @else 
                                                            -- 
                                                        @endif
                                                    </td>
                                                    <td>{{$company_detail_data->accepted_leads}}</td>
                                                    <td>{{$company_detail_data->live_leads}}</td>
                                                    <td>{{$company_detail_data->hired}}</td>
                                                    <td>{{$company_detail_data->completed}}</td>
                                                    <td>{{$company_detail_data->success_percent}}%</td>
                                                    <td>{{\Helpers::currency($company_detail_data->value)}}</td>
                                                    <td>
                                                        @if($company_detail_data->status==1)
                                                            <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Active</span>
                                                        @else
                                                            <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Deactive</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                                <?php $i++; ?>
                                            @endforeach
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
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Notes</h4>
                            </div>
                        </div>
                        <div class="row">
                            @if(count($company_detail->notes)>0)
                                @foreach($company_detail->notes as $notes_data)
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
                                <input name="company_id" id="company_id" type="hidden" value="{{$company_detail->id}}">
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
                                @if($company_detail->status==1)
                                <button type="button" class="btn btn-danger mb-1 mb-sm-0 me-0 me-sm-1" data-bs-toggle="modal" data-bs-target="#inlineForm">Suspend</button>
                                <!-- onclick="updateStatus('{{$company_detail->id}}','0','/admin/update-company-status');" -->
                                @else
                                <button type="button" class="btn btn-success mb-1 mb-sm-0 me-0 me-sm-1" onclick="updateStatus('{{$company_detail->id}}','1','/admin/update-company-status');">Activate</button>
                                @endif
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
                <h4 class="modal-title" id="myModalLabel33">Suspend Company</h4>
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
                    <button type="button" class="btn btn-danger waves-effect waves-float waves-light" onclick="updateStatus('{{$company_detail->id}}','0','/admin/update-company-status',true,'suspend_reason');">Suspend</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection