@extends('layouts.app')
@push('style')
 <!-- Icons -->
 <link rel="stylesheet" href="{{ asset('argon') }}/vendor/nucleo/css/nucleo.css" type="text/css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" type="text/css">
<style>
 
</style>
  @endpush
@section('content')
    <div class="header bg-gradient-primary   pt-md-6">
     <div class="container-fluid">
      <div class="row">
        <div class="col">
          <div class="card" style="padding:10px !important;">
            
               <h3> Advisor List-Admin Dashboard</h3>
              @if(session()->has('message'))
              <div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                  <strong>Success!</strong>  {{ session()->get('message') }}
                </div>
              @endif
             
            <div class="table-responsive">
              <table id="users" class="table" style="width:100%;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col" class="sort" data-sort="name">Id</th>
                    <th scope="col" class="sort" data-sort="budget">Name</th>
                    <th scope="col" class="sort" data-sort="status">PostCode</th>
                    <th scope="col" class="sort" data-sort="status">Email</th>
                    <th scope="col" class="sort" data-sort="completion">FCA Number</th>
                    <th scope="col" class="sort" data-sort="completion">Accepted Leads</th>
                    <th scope="col" class="sort" data-sort="completion">Live Leads</th>
                    <th scope="col" class="sort" data-sort="completion">Hired</th>
                    <th scope="col" class="sort" data-sort="completion">Completed</th>
                    <th scope="col" class="sort" data-sort="completion">Success</th>
                    <th scope="col" class="sort" data-sort="completion">Value</th>
                    <th scope="col" class="sort" data-sort="completion">Cost</th>
                    <th scope="col" class="sort" data-sort="completion">Status</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="list">
                @foreach ($userDetails as $user)
                  <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->display_name}}</td>
                    <td>{{ $user->postcode != "" ? $user->postcode : 'N/A' }}</td>
                    <td>{{ $user->email != "" ? $user->email : 'N/A' }}</td>
                    <td>{{ $user->FCANumber != "" ? $user->FCANumber : 'N/A' }}</td>
                    <td>{{$user->acceptedLeads}}</td>
                    <td>{{$user->live_leads}}</td>
                    <td>{{$user->hired_leads}}</td>
                    <td>{{$user->completed_leads}}</td>
                    <td>N/A</td>
                    <td>N/A</td>
                    <td>
                    @if($user->email_verified_at == NULL)
                      Not Verifed
                     @else: 
                     Verfied
                     @endif
                    </td>
                    <td>
                      <a href="{{ route('admin/view-advisor',$user->id) }}">
                       <i class="fas fa-eye"></i>
                      </a>&nbsp;
                      <a onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-advisor',$user->id) }}">
                       <i class="fas fa-trash"></i>
                      </a>
                      </td>
                    <td ></td>
                  </tr>
                  @endforeach
                   
                </tbody>
              </table>
            </div>
            <!-- Card footer -->
            
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
