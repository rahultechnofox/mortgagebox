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
            <div class="col-xl-12 order-xl-1">
                <div class="card bg-secondary shadow">
                    <div class="card-header bg-white border-0">
                        <div class="row align-items-center">
                            <h3 class="mb-0">{{ __('Remortgage Details') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                    <div class="pl-lg-12 card">
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Mortgage Type:') }}</label>
                                    {{ old('name', ucfirst($needDetails->service_type)) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Mortgage Size:') }}</label>
                                    {{ old('name', ucfirst($needDetails->size_want)) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Customer Name:') }}</label>
                                    {{ old('name', ucfirst($needDetails->name)) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Cost of Lead:') }}</label>
                                    {{ old('name', ucfirst($needDetails->cost_of_lead)) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Combined Name:') }}</label>
                                    {{ old('name', ucfirst($needDetails->combined_income)) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Date Joined:') }}</label>
                                  {{ ucfirst($needDetails->created_at) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Property Value:') }}</label>
                                    {{ $needDetails->property_currency }} {{ ucfirst($needDetails->property) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Additional Details:') }}</label>
                                    {{ ucfirst($needDetails->description) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Adverse Credit:') }}</label>
                                    {{  ucfirst($needDetails->adverse_credit)}}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('How Soon:') }}</label>
                                    {{ $needDetails->how_soon }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Preference:') }}</label>
                                    {{ $needDetails->advisor_preference}}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Accepted:') }}</label>
                                    {{ $needDetails->totalBids }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Active:') }}</label>
                                    {{ ucfirst($needDetails->bid_status) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Feedback:') }}</label>
                                    {{ ucfirst($needDetails->how_soon) }}
                            </div>
                            <div class="pl-lg-4">
                                    <label class="form-control-label" for="input-name">{{ __('Notes:') }}</label>
                                    {{ ucfirst($needDetails->notes) }}
                            </div>
                         </div>
                         
                    </div>
                    <div class="pl-lg-12 card">

                    </div>
                </div>
            </div>
        </div>
        
        
    </div>
    </div>
@endsection
 

@push('js')
   <script src="{{ asset('argon') }}/js/argon.js?v=1.2.0"></script>
  <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
  <script>
  $(document).ready(function() {
    $('#users').DataTable();
} );
  </script>
@endpush
