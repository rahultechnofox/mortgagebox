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
                        <h2 class="content-header-title float-start mb-0">Admin</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item active">Admin Profile
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
                                {!! Form::model($user, ['method' => 'PATCH','enctype'=>'multipart/form-data', 'route' => ['users.update', $user->id]]) !!}
                                @csrf
                                <div class="d-flex mb-2">
                                    @if($user->image)
                                    <img src="{{asset('app-assets/images/users/'.$user->image)}}" alt="users avatar" class="user-avatar users-avatar-shadow rounded me-2 my-25 cursor-pointer" height="90" width="90" onerror="this.src='{{asset('app-assets/images/users/no-image.png')}}';"/>
                                    @else
                                    <img src="{{asset('app-assets/images/users/no-image.png')}}" alt="users avatar" class="user-avatar users-avatar-shadow rounded me-2 my-25 cursor-pointer" height="90" width="90" />
                                    @endif
                                    <div class="mt-50">
                                    <h4>{{$user->name}}</h4>
                                        <div class="col-12 d-flex mt-1 px-0">
                                            <label class="btn btn-primary me-75 mb-0" for="change-picture">
                                                <span class="d-none d-sm-block">Change</span>
                                                <input class="form-control" type="file" id="change-picture" hidden accept="image/png, image/jpeg, image/jpg" name="image"/>
                                                <span class="d-block d-sm-none">
                                                    <i class="me-0" data-feather="edit"></i>
                                                </span>
                                            </label>
                                            <button class="btn btn-outline-secondary d-none d-sm-block">Remove</button>
                                            <button class="btn btn-outline-secondary d-block d-sm-none">
                                                <i class="me-0" data-feather="trash-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label" for="username">Name</label>
                                            <input type="text" class="form-control" placeholder="User Name" value="{{$user->name}}" name="name" id="username" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label" for="email">Email</label>
                                            <input type="text" class="form-control" placeholder="Email" value="{{$user->email}}" name="email" id="email" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label" for="phone">Phone</label>
                                            <input type="text" class="form-control" placeholder="Phone" value="{{$user->phone}}" name="phone" id="phone" />
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-1">
                                            <label class="form-label" for="password">Password</label>
                                            <input type="password" class="form-control" placeholder="Password" name="password" id="password" />
                                        </div>
                                    </div>
                                    <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                        <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">Save Changes</button>
                                        <a href="{!! route('dashboard') !!}" class="btn btn-outline-secondary">Back</a>
                                    </div>
                                </div>
                                {!! Form::close() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection