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
                                <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Customers List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <!-- <button class="btn btn-icon btn-primary" type="button" >
                        <i data-feather="plus" class="me-25"></i>
                        <span>Add User</span>
                    </button> -->
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-12 col-12 d-md-block mb-1">
                <form role="form" method="get">
                    <div class="form-group row">
                        <div class="col-md-3 col-12">
                            <input type="text" class="form-control" value="<?php if(isset($_GET['search']) && $_GET['search']!=''){ echo $_GET['search']; } ?>" name="search" placeholder="Search">
                        </div>
                        <div class="col-md-3 col-12">
                            <input type="text" id="fp-default" value="<?php if(isset($_GET['created_at']) && $_GET['created_at']!=''){ echo date("Y-m-d",strtotime($_GET['created_at'])); } ?>" name="created_at" class="form-control flatpickr-basic" placeholder="Date" />
                        </div>
                        <div class="col-md-2 col-12">
                            <select class="form-select" id="" name="email_status">
                                <option value="">Email Verified</option>
                                <option value="1" <?php if(isset($_GET['email_status']) && $_GET['email_status']!=''){ if($_GET['email_status']==1){ echo "selected"; } } ?>>Yes</option>
                                <option value="0" <?php if(isset($_GET['email_status']) && $_GET['email_status']!=''){ if($_GET['email_status']==0){ echo "selected"; } } ?>>No</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <select class="form-select" id="" name="status">    
                                <option value="">Status</option>
                                <option value="1" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==1){ echo "selected"; } } ?>>Active</option>
                                <option value="0" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==0){ echo "selected"; } } ?>>Deactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <button type="submit" name="submit" value="Search" id="submit" class="dt-button create-new btn btn-primary"><i data-feather="search"></i></button>
                            <a href="javascript:;" onclick="resetFilter()" class="btn btn-outline-secondary"><i data-feather="refresh-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('users.table')
    </div>
</div>
@endsection