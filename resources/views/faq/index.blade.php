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
                        <h2 class="content-header-title float-start mb-0">Faq</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Faq List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <a class="btn btn-icon btn-primary" type="button" href="{{url('/admin/faq/create')}}">
                        <i data-feather="plus" class="me-25"></i>
                        <span>Add Faq</span>
                    </a>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-12 col-12 d-md-block mb-1">
                <form role="form" method="get">
                    <div class="form-group row">
                        <div class="col-md-3 col-12">
                            <input type="text" class="form-control" value="<?php if(isset($_GET['name']) && $_GET['name']!=''){ echo $_GET['name']; } ?>" name="name" placeholder="Name">
                        </div>
                        <div class="col-md-2 col-12">
                            <select class="form-select" id="" name="status">    
                                <option value="">Status</option>
                                <option value="1" value="<?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==1){ echo "selected"; } } ?>">Active</option>
                                <option value="0" value="<?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==1){ echo "selected"; } } ?>">Deactive</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <button type="submit" class="dt-button create-new btn btn-primary"><i data-feather="search"></i></button>
                            <a href="javascript:;" onclick="resetFilter()" class="btn btn-outline-secondary"><i data-feather="refresh-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('faq.table')
    </div>
</div>
@endsection