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
                        <h2 class="content-header-title float-start mb-0">Advisor</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/advisors*') ? 'active' : '' }}"><a href="{!! url('admin/advisors') !!}">Advisors List</a>
                                </li>
                                <li class="breadcrumb-item active">Advisor Info
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
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Lead Summery</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Live Lead:</h6>
                                        <small>{{isset($userDetails->live_leads) ? $userDetails->live_leads : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Hired:</h6>
                                        <small>{{isset($userDetails->hired_leads) ? $userDetails->hired_leads : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Completed:</h6>
                                        <small>{{isset($userDetails->completed_leads) ? $userDetails->completed_leads : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">Lost:</h6>
                                        <small>{{isset($userDetails->lost_leads) ? $userDetails->lost_leads : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>{{ __('Overview') }}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Email:') }}</h6>
                                        <small>{{isset($userDetails->email) ? $userDetails->email : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Contact Number:') }}</h6>
                                        <small>{{isset($profile->phone_number) ? $profile->phone_number : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('FCA Number:') }}:</h6>
                                        <small>{{isset($profile->FCANumber) ? $profile->FCANumber : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Sex:') }}</h6>
                                        <small>{{isset($profile->gender) ? $profile->gender : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Langauge:') }}</h6>
                                        <small>{{isset($profile->language) ? $profile->language : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('City/Town:') }}</h6>
                                        <small>{{isset($profile->city) ? $profile->city : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Location:') }}</h6>
                                        <small>{{isset($profile->address_line1) ? $profile->address_line1 : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Last Updated:') }}</h6>
                                        <small>{{isset($userDetails->last_active) ? $userDetails->last_active : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Postal Code:') }}</h6>
                                        <small>{{isset($userDetails->postcode) ? $userDetails->postcode : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Joined:') }}</h6>
                                        <small>{{isset($userDetails->created_at) ? $userDetails->created_at : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection