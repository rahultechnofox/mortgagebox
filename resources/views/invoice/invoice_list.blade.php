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
                        <h2 class="content-header-title float-start mb-0">Invoice</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                    </li>
                                <li class="breadcrumb-item active">Invoice List</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-3 col-12 d-md-block">
                <div class="mb-1 breadcrumb-right">
                    <!-- <a class="btn btn-icon btn-primary" href="{{url('/admin/users')}}">
                        <i data-feather="plus" class="me-25"></i>
                        <span>Back to customer list</span>
                    </a> -->
                </div>
            </div>
            <div class="content-header-right text-md-end col-md-12 col-12 d-md-block mb-1">
                <form role="form" method="get">
                    <div class="form-group row">
                        <div class="col-md-2 col-12">
                            <select class="form-select" name="is_paid">
                                <option value="">Payment Status</option>
                                <option value="1" <?php if(isset($_GET['is_paid']) && $_GET['is_paid']!=''){ if($_GET['is_paid']==1){ echo "selected"; } } ?>>Paid</option>
                                <option value="0" <?php if(isset($_GET['is_paid']) && $_GET['is_paid']!=''){ if($_GET['is_paid']==0){ echo "selected"; } } ?>>Not Paid</option>
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <select class="form-select" id="" name="advisor_id">    
                                <option value="">Adviser</option>
                                @foreach($adviser as $adviser_data)
                                    <option value="{{$adviser_data->id}}" <?php if(isset($_GET['advisor_id']) && $_GET['advisor_id']!=''){ if($_GET['advisor_id']==$adviser_data->id){ echo "selected"; } } ?>>@if(isset($adviser_data->advisor_profile) && $adviser_data->advisor_profile!=''){{$adviser_data->advisor_profile->display_name}}@endif</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 col-12">
                            <select class="form-select" id="" name="month">    
                                <option value="">Month</option>
                                <option value="1" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==1){ echo "selected"; } } ?>>Jan</option>
                                <option value="2" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==2){ echo "selected"; } } ?>>Feb</option>
                                <option value="3" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==3){ echo "selected"; } } ?>>Mar</option>
                                <option value="4" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==4){ echo "selected"; } } ?>>April</option>
                                <option value="5" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==5){ echo "selected"; } } ?>>May</option>
                                <option value="6" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==6){ echo "selected"; } } ?>>June</option>
                                <option value="7" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==7){ echo "selected"; } } ?>>Jul</option>
                                <option value="8" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==8){ echo "selected"; } } ?>>Aug</option>
                                <option value="9" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==9){ echo "selected"; } } ?>>Sep</option>
                                <option value="10" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==10){ echo "selected"; } } ?>>Oct</option>
                                <option value="11" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==11){ echo "selected"; } } ?>>Nov</option>
                                <option value="12" <?php if(isset($_GET['month']) && $_GET['month']!=''){ if($_GET['month']==12){ echo "selected"; } } ?>>Dec</option>
                            </select>
                        </div>
                        <?php $firstYear = (int)date('Y') - 20; ?>
                        <div class="col-md-2 col-12">
                            <select class="form-select" id="" name="year">    
                                <option value="">Year</option>
                                <?php for($i=$firstYear;$i<=date('Y');$i++){ ?>
                                    <option value="<?php echo $i;?>" <?php if(isset($_GET['year']) && $_GET['year']!=''){ if($_GET['year']==$i){ echo "selected"; } } ?>><?php echo $i;?></option>
                                <?php }?>
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
        @include('invoice.invoice_table')
    </div>
</div>
@endsection