@extends('layouts.app')
@section('content')
<script src="https://cdn.ckeditor.com/ckeditor5/28.0.0/classic/ckeditor.js"></script>

<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0"><?php if(isset($_GET['type']) && $_GET['type']!=''){ echo ucfirst($_GET['type']); }?> Page</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item"><a href="{!! url('admin/pages') !!}"><?php if(isset($_GET['type']) && $_GET['type']!=''){ echo ucfirst($_GET['type']); }?> Pages List</a>
                                </li>
                                <li class="breadcrumb-item active"><?php if(isset($_GET['type']) && $_GET['type']!=''){ echo ucfirst($_GET['type']); }?> Page Info
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
                        <div class="tab-content">
                            <div class="tab-pane active" id="account" aria-labelledby="account-tab" role="tabpanel">
                                <form action="{{url('admin/update-page')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$row->id}}">
                                    <div class="row">
                                        <!-- <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Page Name</label>
                                                <select class="form-control" name="type" id="type">
                                                    <option value="">Select Type</option>
                                                    <option value="adviser" <?php if(isset($row) && $row!=''){ if($row->type=='adviser'){ echo "selected"; } } ?>>Adviser</option>
                                                    <option value="customer" <?php if(isset($row) && $row!=''){ if($row->type=='customer'){ echo "selected"; } } ?>>Customer</option>
                                                </select>
                                                <!-- <input type="text" class="form-control " placeholder="Page Title" value="{{$row->page_name}}" name="page_name" id="page_name" /> -->
                                            <!-- </div> -->
                                        <!-- </div> --> 
                                        <?php if(isset($_GET['type']) && $_GET['type']!=''){ ?>
                                            <input type="hidden" value="{{$_GET['type']}}" name="type" id="type" />
                                        <?php }else{ ?>
                                            <input type="hidden" value="@if(isset($row) && $row!=''){{$row->type}}@else{{$_GET['type']}}@endif" name="type" id="type" />
                                        <?php }?>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Page Name</label>
                                                <input type="text" class="form-control " placeholder="Page Title" value="{{$row->page_name}}" name="page_name" id="page_name" />
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Page Description</label>
                                                <textarea class="form-control" name="page_content" id="editor" name="Page Description" value="{{$row->page_content}}">{{$row->page_content}}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">Save Changes</button>
                                            <a href="{!! url('admin/pages') !!}<?php if(isset($_GET['type']) && $_GET['type']!=''){ echo "?type=".$_GET['type']; }?>" class="btn btn-outline-secondary">Back</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script type="text/javascript">
    ClassicEditor
    .create( document.querySelector( '#editor' ), {} )
    .catch( error => {
        console.log( error );
    } );
</script>
@endsection

