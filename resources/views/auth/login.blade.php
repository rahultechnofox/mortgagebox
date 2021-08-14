@extends('layouts.auth.default')

@section('content')
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper">
        <div class="content-header row">
        </div>
        <div class="content-body">
            <div class="auth-wrapper auth-v2">
                <div class="auth-inner row m-0">
                    <a class="brand-logo" href="#">
                        <h2 class="brand-text text-primary ms-1">
                            <img src="{{asset('argon/img/brand/logo.png')}}" style="width: 180px;">
                        </h2>
                    </a>
                    <div class="d-none d-lg-flex col-lg-8 align-items-center p-5">
                        <div class="w-100 d-lg-flex align-items-center justify-content-center px-5"><img class="img-fluid" src="{{asset('app-assets/images/pages/login-v2.svg')}}" alt="Login V2" /></div>
                    </div>
                    <div class="d-flex col-lg-4 align-items-center auth-bg px-2 p-lg-5">
                        <div class="col-12 col-sm-8 col-md-6 col-lg-12 px-xl-2 mx-auto">
                            <h2 class="card-title fw-bold mb-1">Welcome to Admin! 👋</h2>
                            <p class="card-text mb-2">Please sign-in to your account and start the adventure</p>
                            @if(Session::has('error'))
                            <div class="alert alert-danger" role="alert" style="padding: 15px;">
                              {{ Session::get('error') }}
                            </div>
                            @endif
                            <form class="auth-login-form mt-2" action="{{ route('login') }}" method="POST">
                                {!! csrf_field() !!}
                                <div class="mb-1">
                                    <label class="form-label" for="login-email">Email</label>
                                    <input class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}" placeholder="{{ __('Email') }}" type="email" name="email" value="{{ old('email') }}" required autofocus />
                                </div>
                                <div class="mb-1">
                                    <div class="d-flex justify-content-between">
                                        <label class="form-label" for="login-password">Password</label>
                                    </div>
                                    <div class="input-group input-group-merge form-password-toggle">
                                        <input class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }} form-control-merge" name="password" placeholder="{{ __('Password') }}" type="password" required /><span class="input-group-text cursor-pointer"><i data-feather="eye"></i></span>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100" tabindex="4">{{ __('Sign in') }}</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
