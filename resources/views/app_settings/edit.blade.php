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
                        <h2 class="content-header-title float-start mb-0">Promotion</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Promotion Info
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
                                <form action="{{url('admin/update-setting')}}" method="post">
                                    @csrf
                                    <div class="row">
                                        @foreach($result as $row)
                                        @if($row->key =='new_adviser_status' || $row->key == 'friend_active')
                                            @if($row->key =='new_adviser_status')
                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="new_adviser_status">{{$row->name}}</label>
                                                        <div class="form-check form-check-primary form-switch">
                                                            <input type="checkbox" @if(isset($row) && $row!='') @if($row->value==1) checked @endif @endif class="form-check-input" id="customSwitch3" name="new_adviser_status" value="{{$row->value}}" />
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                            @if($row->key =='friend_active')
                                                <div class="col-md-12">
                                                    <div class="mb-1">
                                                        <label class="form-label" for="friend_active">{{$row->name}}</label>
                                                        <div class="form-check form-check-primary form-switch">
                                                            <input type="checkbox" @if(isset($row) && $row!='') @if($row->value==1) checked @endif @endif class="form-check-input" id="customSwitch3" name="friend_active"  />
                                                            <!-- value="{{$row->value}}" -->
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <label class="form-label" for="{{$row->key}}">{{$row->name}}</label>
                                                    <input @if($row->key =='no_of_free_leads_adviser' || $row->key == 'no_of_free_leads_refer_friend') type="number" min="0" @else type="text" @endif class="form-control " placeholder="{{$row->name}}" value="{{$row->value}}" name="{{$row->key}}" id="{{$row->key}}"/>
                                                </div>
                                            </div>
                                        @endif                                        
                                        @endforeach
                                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">Save</button>
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

