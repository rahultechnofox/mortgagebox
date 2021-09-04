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
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Mortgage Type:') }}</h6>
                                        <small>{{isset($needDetails->service_type) ? old('name', ucfirst($needDetails->service_type)) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Mortgage Size:') }}</h6>
                                        <small>{{isset($needDetails->size_want) ? old('name', \Helpers::currency($needDetails->size_want)) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Customer Name:') }}:</h6>
                                        <small>{{isset($needDetails->name) ? old('name', ucfirst($needDetails->name)): '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Date Joined:') }}</h6>
                                        <small>
                                            @if(isset($needDetails->created_at)) 
                                                {{\Helpers::formatDateTime($needDetails->created_at)}} 
                                            @else
                                                --
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Property Value:') }}</h6>
                                        <small>{{isset($needDetails->property) ? $needDetails->property_currency.''.$needDetails->property : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Combined Name:') }}</h6>
                                        <small>{{isset($needDetails->combined_income) ? \Helpers::currency($needDetails->combined_income) : '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Cost of Lead:') }}</h6>
                                        <small>{{isset($needDetails->cost_of_lead) ? old('name', $needDetails->cost_of_lead) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Average Value:') }}</h6>
                                        <small>{{isset($needDetails->adverse_credit) ? \Helpers::currency($needDetails->adverse_credit) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12 mb-10 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Additional Details:') }}</h6>
                                        <small>{{isset($needDetails->description) ? $needDetails->description : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        @if(isset($needDetails) && ($needDetails->self_employed!=0 || $needDetails->non_uk_citizen!=0 || $needDetails->adverse_credit!=0))
                                            @if($needDetails->self_employed!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox1" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox1">Self Employed  </label>
                                            </div>
                                            @endif
                                            @if($needDetails->non_uk_citizen!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox2" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox2">Non UK Citizen</label>
                                            </div>
                                            @endif
                                            @if($needDetails->adverse_credit!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox3" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox3">Adverse Credit</label>
                                            </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('How Soon:') }}</h6>
                                        <small>{{isset($needDetails->request_time) ? $needDetails->request_time : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Preference:') }}</h6>
                                        <small class="mb-10">
                                            @if($needDetails->contact_preference_face_to_face!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox1" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox1">Face to Face  </label>
                                            </div>
                                            @endif
                                            @if($needDetails->contact_preference_online!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox2" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox2">Online</label>
                                            </div>
                                            @endif
                                            @if($needDetails->contact_preference_telephone!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox3" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox3">Telephone</label>
                                            </div>
                                            @endif
                                            @if($needDetails->contact_preference_evening_weekend!=0)
                                            <div class="form-check form-check-inline mb-10">
                                                <input class="form-check-input disabled-checkbox" type="checkbox" id="inlineCheckbox4" value="checked" disabled checked>
                                                <label class="form-check-label" for="inlineCheckbox4">Evening Weekend</label>
                                            </div>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-3 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Accepted:') }}</h6>
                                        <small>{{isset($needDetails->totalBids) ? count($needDetails->totalBids) : '--'}}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Status:') }}</h6>
                                        <small> 
                                            @if(isset($needDetails->bids) && count($needDetails->bids)>0)
                                                <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Matched</span>
                                            @else
                                                <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Unmatched</span>
                                            @endif
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-1">
                                <div class="d-flex">
                                    <div class="transaction-percentage">
                                        <h6 class="transaction-title">{{ __('Feedback:') }}</h6>
                                        <small>{{isset($needDetails->how_soon) ? ucfirst($needDetails->how_soon): '--' }}</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="col-md-12 mb-1">
                                <div class="transaction-percentage">
                                    <h6 class="transaction-title">{{ __('Notes:') }}</h6>
                                    @if(count($needDetails->notes)>0)
                                        @foreach($needDetails->notes as $notes_data)
                                            <div class="col-md-12">
                                                <div class="mb-1">
                                                    <div class="transaction-percentage">
                                                        <input class="form-control" value="{{date('M d, Y',strtotime($notes_data->created_at))}} - {{$notes_data->notes}}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @else
                                        --
                                    @endif
                                </div>
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
                                                    <td>{{\Helpers::checkNull($bids_data->adviser_name)}}</td>
                                                    <td>
                                                        <?php
                                                            $live_leads = \Helpers::getLiveLeads($needDetails->how_soon);
                                                        ?>
                                                        @if($live_leads==1)
                                                            <span class="badge rounded-pill badge-light-primary me-1" style="margin-bottom: 10px;">Live Leads</span>
                                                        @elseif($bids_data->status==1 && $bids_data->advisor_status==1)
                                                            <span class="badge rounded-pill badge-light-info me-1" style="margin-bottom: 10px;">Hired</span>
                                                        @elseif($bids_data->status==2 && $bids_data->advisor_status==1)
                                                            <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Completed</span>
                                                        @elseif($bids_data->status==3 || $bids_data->advisor_status==1)
                                                            <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Not Proceeding</span>
                                                        @elseif($bids_data->status==1 || $bids_data->advisor_status==2)
                                                            <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Lost</span>
                                                        @endif
                                                    </td>
                                                    <td>{{\Helpers::formatDateTime($bids_data->created_at)}}</td>
                                                    <td>{{\Helpers::checkNull($bids_data->cost_of_lead_drop)}}</td>
                                                    <td>{{\Helpers::currency($bids_data->cost_leads)}}</td>
                                                    <td>{{\Helpers::currency($bids_data->price_drop)}}</td>
                                                    <td>@if($bids_data->final_amount_after_discount!='') @if($bids_data->final_amount_after_discount=='0.00') Free @else {{\Helpers::currency($bids_data->final_amount_after_discount)}} @endif @else {{\Helpers::currency($bids_data->cost_leads)}} @endif</td>
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