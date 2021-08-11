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
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/users*') ? 'active' : '' }}"><a href="{!! url('admin/users') !!}">Users List</a>
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
                                <h4>Customer Detail</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Customer Name:</h6>
                                        <small>{{isset($userDetails->name) ? $userDetails->name : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Customer ID:</h6>
                                        <small>{{isset($userDetails->id) ? $userDetails->id : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Postcode:</h6>
                                        <small>{{isset($userDetails->post_code) ? $userDetails->post_code : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Location:</h6>
                                        <small>{{isset($userDetails->address) ? $userDetails->address : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Email:</h6>
                                        <small>{{isset($userDetails->email) ? $userDetails->email : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Email Verified:</h6>
                                        <small>
                                            @if($userDetails->email_verified_at == NULL)
                                                No
                                            @else 
                                                Yes
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Last Login:</h6>
                                        <small>--</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Joined Dated:</h6>
                                        <small>{{isset($userDetails->created_at) ? $userDetails->created_at : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Status:</h6>
                                        <small>
                                            @if($userDetails->status ==1)
                                                Active
                                            @else: 
                                                Deactive
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Last Active:</h6>
                                        <small>{{isset($userDetails->last_active) ? $userDetails->last_active : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Password:</h6>
                                        <input type="password" name="password" id="password" class="form-control form-control-merge">
                                        <button type="button" class="dt-button create-new btn btn-primary" onclick="updatePassword('{{$userDetails->id}}');">Update</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <button type="button" class="dt-button create-new btn btn-primary" onclick="deleteCustomer('{{$userDetails->id}}');">Suspend customer</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Joined Dated:</h6>
                                        <table class="table table-striped">
                                            <tr>
                                                <th>Pending</th>
                                                <th>New Lead</th>
                                                <th>Closed</th>
                                            <tr>
                                            <tr>
                                                <td>5</td>
                                                <td>2</td>
                                                <td>2</td>
                                            <tr>
                                        <table>
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
@endsection