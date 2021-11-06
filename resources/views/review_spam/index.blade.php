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
                        <h2 class="content-header-title float-start mb-0">Review Spam</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Review Spam List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <!-- <a class="btn btn-icon btn-primary" type="button" href="{{url('/admin/faq/create')}}">
                        <i data-feather="plus" class="me-25"></i>
                        <span>Add Faq</span>
                    </a> -->
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-12 col-12 d-md-block mb-1">
                <form role="form" method="get">
                    <div class="form-group row">
                        <div class="col-md-3 col-12">
                            <input type="text" class="form-control" value="<?php if(isset($_GET['name']) && $_GET['name']!=''){ echo $_GET['name']; } ?>" name="name" placeholder="Search">
                        </div>
                        <div class="col-md-2 col-12">
                            <button type="submit" class="dt-button create-new btn btn-primary"><i data-feather="search"></i></button>
                            <a href="javascript:;" onclick="resetFilter()" class="btn btn-outline-secondary"><i data-feather="refresh-ccw"></i></a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        @include('review_spam.table')
    </div>
</div>
<div class="modal modal-slide-in fade" id="modals-slide-in">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0" id="servicesForm" method="post">
            @csrf
            <button type="button" class="btn-close" onclick="resetContactUsForm();" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Reply to Query</h5>
            </div>
            <input name="id" id="id" type="hidden">
            <div class="modal-body flex-grow-1">
                <div class="mb-1 parent_id">
                    <input type="hidden" name="id" id="id" value="">
                    <label class="form-label" for="Department">Email</label>
                    <input type="text" class="form-control" placeholder="Email" name="email" id="email" readonly/>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="Department">Subject</label>
                    <input type="text" class="form-control" placeholder="subject" name="subject" id="subject" value="Contact us query reply" readonly/>
                </div>
                <div class="mb-1">
                    <label class="form-label" for="Department">Message</label>
                    <textarea class="form-control" placeholder="Message" name="message" required=""></textarea>
                </div>
                <button type="button" class="btn btn-primary data-submit me-1" id="createBtn" onclick="replyContactus('servicesForm');">Submit</button>
                <button type="reset" onclick="resetContactUsForm();" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
@if(count($result) > 0)
    @foreach($result as $users_data)
        <div class="modal modal-slide-in fade" id="modals-slide-in_{{$users_data->id}}">
            <div class="modal-dialog sidebar-sm">
                <form class="add-new-record modal-content pt-0" id="servicesForm" method="post">
                    @csrf
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">×</button>
                    <div class="modal-header mb-1">
                        <h5 class="modal-title" id="exampleModalLabel">Take a decision</h5>
                    </div>
                    <input name="id" id="id" type="hidden">
                    <div class="modal-body flex-grow-1">
                        <p>Agree with review?</p>
                        <button type="button" class="btn btn-primary data-submit me-1" id="createBtn" onclick="takeDecision('{{$users_data->id}}','1');">Yes</button>
                        <button type="button" class="btn btn-outline-secondary" onclick="takeDecision('{{$users_data->id}}','0');">No</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach
@endif
@endsection