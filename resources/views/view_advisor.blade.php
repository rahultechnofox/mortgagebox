@extends('layouts.app')
@push('style')
 <!-- Icons -->
 <link rel="stylesheet" href="{{ asset('argon') }}/vendor/nucleo/css/nucleo.css" type="text/css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" type="text/css">
<style>
 
</style>
  @endpush
@section('content')

<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">
    
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-4 order-xl-2 mb-5 mb-xl-0">
                <div class="card card-profile shadow">
                    <div class="row justify-content-center">
                        <div class="col-lg-3 order-lg-2">
                            <div class="card-profile-image">
                                <!-- <a href="#">
                                    <img src="{{ asset('argon') }}/img/theme/team-4-800x800.jpg" class="rounded-circle">
                                </a> -->
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-body pt-0 pt-md-4">
                        <div class="row"><h3>Lead Summery</h3><hr></div>
                        <div class="row">
                        <div class="">
                       
                        <div class="h5 font-weight-300">
                             <span class="heading">Live Lead: {{  $userDetails->live_leads}}</span>
                        </div>
                        
                        <div class="h5 font-weight-300">
                             <span class="heading">Hired: {{  $userDetails->hired_leads}}</span>
                        </div>
                        <div class="h5 font-weight-300">
                             <span class="heading">Completed: {{  $userDetails->completed_leads}}</span>
                        </div>
                        
                        
                        <div class="h5 font-weight-300">
                             <span class="heading">Lost: {{  $userDetails->lost_leads}}</span>
                        </div>
                        </div>
                        
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-8 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Advisor Details') }}</h3>
                        </div>
                    </div>
                    <div class="card-body card">
                        <form method="post" action="{{ route('profile.update') }}" autocomplete="off">
                            @csrf
                            @method('put')
                                 
                            <h6 class="heading-small text-muted mb-4">{{ __('Overview') }}</h6>
                             
                            <div class="pl-lg-4">
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Advisor ID:') }}</label>
                                    #{{ $userDetails->id}}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Name:') }}</label>
                                    {{ $profile->display_name }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Company Name:') }}</label>
                                    {{ $profile->company_name }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('FCA Verfied:') }}</label>
                                    {{ $profile->FCA_verified }}
                                </div>
                            </div>
                            <h6 class="heading-small text-muted mb-4">{{ __('Details') }}</h6>
                             
                            <div class="pl-lg-4">
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Email:') }}</label>
                                    <a href="mailto:{{ $userDetails->email}}">{{ $userDetails->email}}</a>
                                    
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Contact Number:') }}</label>
                                    {{ $profile->phone_number }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('FCA Number:') }}</label>
                                    {{ $profile->FCANumber }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Sex:') }}</label>
                                    {{ $profile->gender }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Langauge:') }}</label>
                                    {{ $profile->language }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('City/Town:') }}</label>
                                    {{ $profile->city }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Location:') }}</label>
                                    {{ $profile->address_line1 }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Last Updated:') }}</label>
                                    {{ $userDetails->last_active }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Postal Code:') }}</label>
                                    {{ $userDetails->postcode }}
                                </div>
                                <div class="form-group{{ $errors->has('name') ? ' has-danger' : '' }}">
                                    <label class="form-control-label" for="input-name">{{ __('Joined:') }}</label>
                                    {{ $userDetails->created_at }}
                                </div>
                            </div>
                        </form>
                         
                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
    </div>
@endsection
 

@push('js')
    <!-- <script src="{{ asset('argon') }}/vendor/jquery/dist/jquery.min.js"></script>
  <script src="{{ asset('argon') }}/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="{{ asset('argon') }}/vendor/js-cookie/js.cookie.js"></script>
  <script src="{{ asset('argon') }}/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
  <script src="{{ asset('argon') }}/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script> -->
  <!-- Argon JS -->
  <script src="{{ asset('argon') }}/js/argon.js?v=1.2.0"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#users').DataTable();
} );
  </script>
@endpush
