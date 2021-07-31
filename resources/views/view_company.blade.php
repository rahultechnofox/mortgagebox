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
                        <div class="row"><h3>Company Details </h3><hr></div>
                        <div class="row">
                        <div class="text-center">
                        <div class="h5 font-weight-300">
                             <span class="heading">Company Name: {{  $companyDetail->company_name}}</span>
                        </div>
                         
                        <div class="h5 font-weight-300">
                             <span class="heading">Company Created: {{  $companyDetail->created_at}}</span>
                        </div>
                        
                        <div class="h5 font-weight-300">
                             <span class="heading">Total Members: {{  count($team)}}</span>
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
                            <h3 class="mb-0">{{ __('Team Details') }}</h3>
                        </div>
                    </div>
                    <div class="card-body">
                    <div class="table-responsive">
              <table id="users" class="table" style="width:100%;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col" class="sort" data-sort="name">Member Name</th>
                    <th scope="col" class="sort" data-sort="budget">Member Email</th>
                    <th scope="col" class="sort" data-sort="completion">Status</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="list">
                @foreach ($team as $need)
                  <tr>
                    <td>{{$need->name}}</td>
                    <td>{{$need->email}}</td>
                    <td>
                    @if($need->status == 1)
                        Active
                    @else 
                        In-Active    
                    @endif
                      </td>
                    <td ></td>
                  </tr>
                  @endforeach
                   
                </tbody>
              </table>
            </div>
                       
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
