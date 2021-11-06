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
                        <h2 class="content-header-title float-start mb-0">Users</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/users*') ? 'active' : '' }}"><a href="{!! route('users.index') !!}">Users List</a>
                                </li>
                                <li class="breadcrumb-item active">Edit User
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
                                <div class="d-flex mb-2">
                                    @if($user->image)
                                    <img src="{{asset('upload/users/580x400/'.$user->image)}}" alt="users avatar" class="user-avatar users-avatar-shadow rounded me-2 my-25 cursor-pointer show_image"  height="90" width="90" onerror="this.src=`{{asset('upload/users/no-image.png')}}`;"/>
                                    @else
                                    <img src="{{asset('upload/users/no-image.png')}}" alt="users avatar" class="user-avatar users-avatar-shadow rounded me-2 my-25 cursor-pointer show_image" height="90" width="90" onerror="this.src=`{{asset('upload/users/no-image.png')}}`;" />
                                    @endif
                                    <div class="mt-50">
                                    <h4>{{$user->name}}</h4>
                                        <div class="col-12 d-flex mt-1 px-0">
                                            <label class="btn btn-primary me-75 mb-0" for="change-picture">
                                                <input class="uploadBannerBtn hide" type="file" name="image" onchange="uploadImage(this,'show_image',0,'admin/uploadImage','users');" accept="image/jpg, image/jpeg"/>
                                                <span class="d-none d-sm-block uplodBTNText" onclick="triggerFileInput('uploadBannerBtn')">Change</span>
                                                <!-- <input class="form-control" type="file" id="change-picture" hidden accept="image/png, image/jpeg, image/jpg" name="image"/> -->
                                                <span class="d-block d-sm-none">
                                                    <i class="me-0" data-feather="edit"></i>
                                                </span>
                                            </label>
                                            <button class="btn btn-outline-secondary d-none d-sm-block" onclick="removeImage('show_image');">Remove</button>
                                            <button class="btn btn-outline-secondary d-block d-sm-none">
                                                <i class="me-0" data-feather="trash-2"></i>
                                            </button> 
                                        </div>
                                    </div>
                                </div>
                                <form method="post" id="userForm">
                                @csrf
                                    <input type="hidden" name="id" id="id" value="{{$user->id}}">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="email">Email</label>
                                                <input type="text" class="form-control" placeholder="Email" value="{{$user->email}}" name="email" id="email" readonly/>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="username">Username</label>
                                                <input type="text" class="form-control" placeholder="User Name" value="{{$user->username}}" name="username" id="username" readonly />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="phone">First Name</label>
                                                <input type="text" class="form-control" placeholder="First Name" value="{{$user->first_name}}" name="first_name" id="first_name" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="username">Last Name</label>
                                                <input type="text" class="form-control" placeholder="Last Name" value="{{$user->last_name}}" name="last_name" id="last_name" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="username">Gender</label>
                                                <select class="form-select" id="gender" name="gender">
                                                    <option value="other" @if($user->gender == 'other') selected  @endif>Not defined</option>
                                                    <option value="male" @if($user->gender == 'male') selected  @endif>Male</option>
                                                    <option value="female" @if($user->gender == 'female') selected  @endif>Female</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="username">University Name</label>
                                                <input type="text" class="form-control" placeholder="University Name" value="" name="" id="" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="username">School Name</label>
                                                <input type="text" class="form-control" placeholder="School Name" value="" name="" id="" />
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="username">Designation</label>
                                                <select class="form-select" id="" name="">
                                                    <option value="">Designation</option>
                                                    <option value="">Free User</option>
                                                    <option value="">Student School</option>
                                                    <option value="">Student University</option>
                                                    <option value="">Professor</option>
                                                    <option value="">Teacher</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="mb-1">
                                                <label class="form-label" for="status">Status</label>
                                                <select class="form-select" id="status" name="status">
                                                    <option value="1" @if($user->status == 1) selected  @endif>Active</option>
                                                    <option value="0" @if($user->status == 0) selected  @endif>In Active</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                            <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="addUser('userForm','edit');">Save Changes</button>
                                            <a href="{!! route('users.index') !!}" class="btn btn-outline-secondary">Back</a>
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
@endsection