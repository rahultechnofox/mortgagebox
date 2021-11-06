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
                        <h2 class="content-header-title float-start mb-0">Users</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/users*') ? 'active' : '' }}"><a href="{!! route('users.index') !!}">Users List</a>
                                </li>
                                <li class="breadcrumb-item active">User Info
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
                                <h4>{{$data->name}}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Email:</h6>
                                        <small>{{isset($data->email) ? $data->email : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">User Name:</h6>
                                        <small>{{isset($data->name) ? $data->name : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">First Name:</h6>
                                        <small>{{isset($data->fname) ? $data->fname : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Last Name:</h6>
                                        <small>{{isset($data->lname) ? $data->lname : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">University Name:</h6>
                                        <small>{{isset($data->university_name) ? $data->university_name : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">School Name:</h6>
                                        <small>{{isset($data->school_name) ? $data->school_name : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Gender:</h6>
                                        <small>{{isset($data->gender) ? $data->gender : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Designation:</h6>
                                        <small>{{isset($data->designation) ? $data->designation : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            {{-- <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">Save Changes</button>
                                <a href="{!! route('users.index') !!}" class="btn btn-outline-secondary">Back</a>
                            </div> --}}
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="account" aria-labelledby="account-tab" role="tabpanel">
                                <div class="col-xl-12 col-lg-12">
                                    <ul class="nav nav-pills nav-justified">
                                        <li class="nav-item" style="background: #ededed;">
                                            <a class="nav-link active" id="read-tab-justified" data-bs-toggle="pill" href="#read-justified" aria-expanded="false">Read/ Download</a>
                                        </li>&nbsp;&nbsp;
                                        <li class="nav-item" style="background: #ededed;">
                                            <a class="nav-link" id="upload-tab-justified" data-bs-toggle="pill" href="#upload-justified" aria-expanded="false">Upload as Author</a>
                                        </li>&nbsp;&nbsp;
                                        <li class="nav-item" style="background: #ededed;">
                                            <a class="nav-link" id="verification-tab-justified" data-bs-toggle="pill" href="#verification-justified" aria-expanded="false">Verification</a>
                                        </li>&nbsp;&nbsp;
                                        <li class="nav-item" style="background: #ededed;">
                                            <a class="nav-link" id="reviewer-tab-justified" data-bs-toggle="pill" href="#reviewer-justified" aria-expanded="false">Reviewer</a>
                                        </li>&nbsp;&nbsp;
                                        <li class="nav-item" style="background: #ededed;">
                                            <a class="nav-link" id="access-tab-justified" data-bs-toggle="pill" href="#access-justified" aria-expanded="false">Access</a>
                                        </li>&nbsp;&nbsp;
                                        <li class="nav-item" style="background: #ededed;">
                                            <a class="nav-link" id="advertisement-tab-justified" data-bs-toggle="pill" href="#advertisement-justified" aria-expanded="false">Advertisement</a>
                                        </li>
                                    </ul>
                                </div>
                                <div class="tab-content">
                                    <!-- Read/ Download Tab-->
                                    @include('users.show_read_tab')
                                    <!-- Upload as Author Tab-->
                                    @include('users.show_upload_tab')
                                    <!-- Verification Tab-->
                                    @include('users.show_verification_tab')
                                    <!-- Reviewer Tab-->
                                    @include('users.show_reviewer_tab')
                                    <!-- Access Tab-->
                                    @include('users.show_access_tab')
                                    <!-- Advertisement Tab-->
                                    @include('users.show_advertisement_tab')
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            
            </section>
        </div>
    </div>
</div>
<!-- Modal to Pay -->
<div class="modal fade text-start" id="inlineForm" tabindex="-1" aria-labelledby="myModalLabel33" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="myModalLabel33">Payment Popup</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="#">
                <div class="modal-body">
                    <label>Enter transaction ID: </label>
                    <div class="mb-1">
                        <input type="text" placeholder="" class="form-control" />
                    </div>
                    <label>Note: </label>
                    <div class="mb-1">
                        <input type="text" placeholder="" class="form-control" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Pay</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection