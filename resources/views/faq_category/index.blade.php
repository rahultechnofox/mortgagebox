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
                        <h2 class="content-header-title float-start mb-0">Faq Categories</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{url('home')}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Faq Categories List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <button class="btn btn-icon btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modals-slide-in">
                        <i data-feather="plus" class="me-25"></i>
                        <span>Add Faq Category</span>
                    </button>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-12 col-12 d-md-block mb-1">
                <form role="form" method="get">
                    <div class="form-group row">
                        <div class="col-md-3 col-12">
                            <input type="text" class="form-control" value="<?php if(isset($_GET['name']) && $_GET['name']!=''){ echo $_GET['name']; } ?>" name="name" placeholder="Name">
                        </div>
                        <div class="col-md-2 col-12">
                            <select class="form-select" name="status">    
                                <option value="">Status</option>
                                <option value="1" value="<?php if(isset($_GET['status'])){ if($_GET['status']==1){ echo "selected"; } } ?>">Active</option>
                                <option value="0" value="<?php if(isset($_GET['status'])){ if($_GET['status']==0){ echo "selected"; } } ?>">Deactive</option>
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
        @include('faq_category.table')
    </div>
</div>
<div class="modal modal-slide-in fade" id="modals-slide-in">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0" id="faqCategoryForm" method="post">
            @csrf
            <button type="button" class="btn-close" onclick="resetFaqCategoryForm();" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Add Faq Category</h5>
            </div>
            <input name="id" id="id" type="hidden">
            <div class="modal-body flex-grow-1">
                <div class="mb-1 audience_id">
                    <label class="form-label" for="Audience">Audience</label>
                    <select class="form-control" name="audience_id" id="audience_id">
                        <option value="">Select Audience</option>
                        @foreach($audience as $audience_data)
                            <option value="{{$audience_data->id}}">{{$audience_data->name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="name">Name</label>
                    <input type="text" class="form-control" placeholder="Name" name="name" id="name" />
                </div>
                <button type="button" class="btn btn-primary data-submit me-1" onclick="addUpdateFaqCategory('faqCategoryForm');">Submit</button>
                <button type="reset" onclick="resetFaqCategoryForm();" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
@endsection