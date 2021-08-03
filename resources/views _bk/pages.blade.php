@extends('layouts.app')
@push('style')
 <!-- Icons -->
 <link rel="stylesheet" href="{{ asset('argon') }}/vendor/nucleo/css/nucleo.css" type="text/css">

<link rel="stylesheet" href="https://cdn.datatables.net/1.10.25/css/jquery.dataTables.min.css" type="text/css">
<style>
 
</style>
  @endpush
@section('content')
    
    <div class="header bg-gradient-primary pb-8 pt-5 pt-md-6">
    
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <div class="card" style="padding:10px !important;">
            <!-- Card header -->
               <h3> Page List-Admin Dashboard</h3>
               
              @if(session()->has('message'))
              <div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                  <strong>Success!</strong>  {{ session()->get('message') }}
                </div>
               
              
              @endif
                
               
                
             <!-- Light table -->
            <div class="table-responsive">
            <a href="{{ route('admin/addPage') }}" style="float:right;">
            <button class="btn btn-icon btn-primary" type="button">
	            <span class="btn-inner--icon"><i class="ni ni-plus-17"></i></span>
                <span class="btn-inner--text">Add New</span>
            </button>
            </a>
              <table id="users" class="table align-items-center table-flush table-responsive" style="width:100%;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col" class="sort" data-sort="name">Id</th>
                    <th scope="col" class="sort" data-sort="budget">Page Name</th>
                    <th scope="col" class="sort" data-sort="status">Slug</th>
                    <th scope="col" class="sort" data-sort="completion">Status</th>
                    <th scope="col" class="sort" data-sort="completion">Action</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="list">
                @foreach ($page_list as $page)
                  <tr>
                  <td>{{$page->id}}</td>
                    <td>{{$page->page_name}}</td>
                    <td>{{$page->slug}}</td>
                    <td>
                    @if ($page->status == 1) 
                          <a href="{{ 'status-page/0/'.$page->id }}">Active</a>
                    @else
                          <a href="{{ 'status-page/1/'.$page->id }}">In-Active</a>
                    @endif
                      
                      </td>
                   <td>
                      <a href="{{ route('admin/edit-page',$page->id) }}">
                       <i class="fas fa-edit"></i>
                      </a>&nbsp;
                      <a onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-page',$page->id) }}">
                       <i class="fas fa-trash"></i>
                      </a>
                      </td>
                    <td ></th>
                  </tr>
                  @endforeach
                   
                </tbody>
              </table>
            </div>
            <!-- Card footer -->
            <div class="card-footer py-4">
              
            </div>
          </div>
        </div>
      </div>
      <!-- Dark table -->
    
      <!-- Footer -->
      




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
