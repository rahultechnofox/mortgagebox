@extends('layouts.app')
@section('content')
<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">Need List</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{url('admin')}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Need List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <!-- <button class="btn btn-icon btn-primary" type="button" >
                        <i data-feather="plus" class="me-25"></i>
                        <span>Add Need</span>
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
                            <select class="form-select" id="" name="service_id">
                                <option value="">Service</option>
                                @foreach($services as $services_data)
                                <option value="{{$services_data->id}}" <?php if(isset($_GET['service_id']) && $_GET['service_id']!=''){ if($_GET['service_id']==$services_data->id){ echo "selected"; } } ?>>{{$services_data->name}}</option>
                                @endforeach
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
        @include('need_list.table')
    </div>
</div>
@if(count($userDetails) > 0)
    @foreach($userDetails as $users_data)
        <div class="modal modal-slide-in fade" id="modals-slide-in_{{$users_data->id}}"">
            <div class="modal-dialog sidebar-sm">
                <form class="add-new-record modal-content pt-0" id="servicesForm" method="post">
                    @csrf
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    <div class="modal-header mb-1">
                        <h5 class="modal-title" >Reviews</h5>
                    </div>
                    <input name="id" id="id" type="hidden">
                    <div class="modal-body flex-grow-1">
                        <div class="mb-1">
                            <p>Title :</p>
                            <p>@if(isset($users_data->rating) && $users_data->rating!=''){{$users_data->rating->review_title}}@endif</p>
                            <p>Review :</p>
                            <p>@if(isset($users_data->rating) && $users_data->rating!=''){{$users_data->rating->reviews}}@endif</p>
                        </div>
                        <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif
@endsection