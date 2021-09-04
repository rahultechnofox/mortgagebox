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
                        <h2 class="content-header-title float-start mb-0">Services</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{url('home')}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Services List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <button class="btn btn-icon btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modals-slide-in" onclick="resetServiceForm();">
                        <i data-feather="plus" class="me-25"></i>
                        <span>Add Service</span>
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
                            <select class="form-select" id="" name="status">    
                                <option value="">Status</option>
                                <option value="1" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==1){ echo "selected"; } } ?>>Active</option>
                                <option value="0" <?php if(isset($_GET['status']) && $_GET['status']!=''){ if($_GET['status']==1){ echo "selected"; } } ?>>Deactive</option>
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
        @include('services.table')
    </div>
</div>
<div class="modal modal-slide-in fade" id="modals-slide-in">
    <div class="modal-dialog sidebar-sm">
        <form class="add-new-record modal-content pt-0" id="servicesForm" method="post">
            @csrf
            <button type="button" class="btn-close" onclick="resetServiceForm();" data-bs-dismiss="modal" aria-label="Close">×</button>
            <div class="modal-header mb-1">
                <h5 class="modal-title" id="exampleModalLabel">Add Service</h5>
            </div>
            <input name="id" id="id" type="hidden">
            <div class="modal-body flex-grow-1">
                <div class="mb-1 parent_id">
                    <input type="hidden" name="parent_id" id="parent_id" value="{{$services[0]->id}}">
                    <label class="form-label" for="Department">Services</label>
                    <select class="form-control" name="parent_id">
                        @foreach($services as $service_data)
                            <option value="{{$service_data->id}}" selected>{{$service_data->name}}</option>
                        @endforeach
                    </select>                    
                </div>
                <div class="mb-1">
                    <label class="form-label" for="Department">Name</label>
                    <input type="text" class="form-control" placeholder="Name" name="name" id="name" required="" />
                </div>
                <button type="button" class="btn btn-primary data-submit me-1" id="createBtn" onclick="addUpdateService('servicesForm');">Submit</button>
                <button type="reset" onclick="resetServiceForm();" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </form>
    </div>
</div>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="{{asset('app-assets/vendors/js/tables/datatable/jquery.dataTables.min.js')}}"></script>
<script type="text/javascript">
    $("#tablecontents" ).sortable({
        items: "tr",
        cursor: 'move',
        opacity: 0.6,
        update: function() {
            sendOrderToServer();
        }
    });

    function sendOrderToServer() {
        var order = [];
        var token = $('meta[name="csrf-token"]').attr('content');
        $('tr.row1').each(function(index,element) {
            order.push({
            id: $(this).attr('data-id'),
            position: index+1
            });
        });
        $.ajax({
            type: "POST", 
            dataType: "json", 
            url: "{{ url('admin/updateSequence') }}",
                data: {
                order: order,
                _token: token
            },
            success: function(response) {
                if (response.status == "success") {
                    console.log(response);
                } else {
                    console.log(response);
                }
            }
        });
    }
</script>
@endsection