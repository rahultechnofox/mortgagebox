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
            
               <h3> Company List-Admin Dashboard</h3>
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
                    <th scope="col" class="sort" data-sort="name">Company ID</th>
                    <th scope="col" class="sort" data-sort="budget">Company Name</th>
                    <th scope="col" class="sort" data-sort="completion">Action</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="list">
                @foreach ($companyDetails as $need)
                  <tr>
                    <td>{{$need->id}}</td>
                    <td>{{$need->company_name}}</td>
                    <td>
                      <a href="{{ route('admin/view-company',$need->id) }}">
                       <i class="fas fa-eye"></i>
                      </a>&nbsp;
                      <a onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-company',$need->id) }}">
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
