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
                        <h2 class="content-header-title float-start mb-0">Need</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item {{ Request::is('admin/need*') ? 'active' : '' }}"><a href="{!! url('admin/need') !!}">Need List</a>
                                </li>
                                <li class="breadcrumb-item active">Need Info
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
                                <h4>{{ __('Remortgage Details') }}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Mortgage Type:') }}:</h6>
                                        <small>{{isset($needDetails->service_type) ? old('name', ucfirst($needDetails->service_type)) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Mortgage Size:') }}:</h6>
                                        <small>{{isset($needDetails->size_want) ? old('name', ucfirst($needDetails->size_want)) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Customer Name:') }}:</h6>
                                        <small>{{isset($needDetails->name) ? old('name', ucfirst($needDetails->name)): '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Date Joined:') }}</h6>
                                        <small>{{isset($needDetails->created_at) ? ucfirst($needDetails->created_at) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Property Value:') }}</h6>
                                        <small>{{isset($needDetails->property) ? $needDetails->property_currency.''.ucfirst($needDetails->property) : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Combined Name:') }}</h6>
                                        <small>{{isset($needDetails->combined_income) ? old('name', ucfirst($needDetails->combined_income)) : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Cost of Lead:') }}</h6>
                                        <small>{{isset($needDetails->cost_of_lead) ? old('name', ucfirst($needDetails->cost_of_lead)) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Average Value:') }}</h6>
                                        <small>{{isset($needDetails->adverse_credit) ? $needDetails->adverse_credit : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Additional Details:') }}:</h6>
                                        <small>{{isset($needDetails->advisor_preference) ? $needDetails->advisor_preference : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Adverse Credit:') }}</h6>
                                        <small>{{isset($needDetails->adverse_credit) ? $needDetails->adverse_credit : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('How Soon:') }}</h6>
                                        <small>{{isset($needDetails->how_soon) ? $needDetails->how_soon : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Preference:') }}</h6>
                                        <small>{{isset($needDetails->advisor_preference) ? $needDetails->advisor_preference : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Accepted:') }}:</h6>
                                        <small>{{isset($needDetails->totalBids) ? $needDetails->totalBids : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Active:') }}</h6>
                                        <small>{{isset($needDetails->bid_status) ? ucfirst($needDetails->bid_status): '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Feedback:') }}</h6>
                                        <small>{{isset($needDetails->how_soon) ? ucfirst($needDetails->how_soon): '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Notes:') }}:</h6>
                                        <small>{{isset($needDetails->notes) ? ucfirst($needDetails->notes): '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                <button type="button" class="btn btn-danger mb-1 mb-sm-0 me-0 me-sm-1" onclick="updateStatus('{{$needDetails->id}}','0','/admin/update-need-status');">Suspend</button>
                                <button type="button" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1" onclick="updateStatus('{{$needDetails->id}}','1','/admin/update-need-status');">Activate</button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex mb-2">
                            <div class="mt-50">
                                <h4>Revenue Generated</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Professional</th>
                                            <th>Bid Status</th>
                                            <th>Bid Time</th>
                                            <th>Bid Cycle</th>
                                            <th>Full Fee</th>
                                            <th>Discount</th>
                                            <th>Final Fee</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $i = 1; ?>
                                        @if(count($needDetails->bids) > 0)
                                            @foreach($needDetails->bids as $bids_data)
                                                <tr>
                                                    <td>{{$i}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->advisor_name)}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->bid_status)}}</td>
                                                    <td>{{\Helpers::checkEmptydateMdYHIS($bids_data->created_at)}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->cost_of_lead_drop)}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->cost_leads)}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->cost_discounted)}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->cost_discounted)}}</td>
                                                </tr>
                                                <?php $i++; ?>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="15" class="recordnotfound"><span>No results found.</span></td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
@endsection