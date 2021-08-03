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
               <h3> Customer List-Admin Dashboard</h3>
               
              @if(session()->has('message'))
              <div class="alert alert-success alert-dismissible">
                  <button type="button" class="close" data-dismiss="alert">&times;</button>
                  <strong>Success!</strong>  {{ session()->get('message') }}
                </div>
               
              
              @endif
                
               
                
             <!-- Light table -->
            <div class="table-responsive">
              <table id="users" class="table align-items-center table-flush table-responsive" style="width:100%;">
                <thead class="thead-light">
                  <tr>
                    <th scope="col" class="sort" data-sort="name">Id</th>
                    <th scope="col" class="sort" data-sort="budget">Name</th>
                    <th scope="col" class="sort" data-sort="status">PostCode</th>
                    <th scope="col" class="sort" data-sort="status">Email</th>
                    <th scope="col" class="sort" data-sort="completion">Date Joined</th>
                    <th scope="col" class="sort" data-sort="completion">Last Active</th>
                    <th scope="col" class="sort" data-sort="completion">Status</th>
                    <th scope="col" class="sort" data-sort="completion">Action</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="list">
                @foreach ($userDetails as $user)
                  <tr>
                  <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->post_code}}</td>
                    <td>{{$user->email}}</td>
                    <td>{{$user->created_at}}</td>
                    <td>{{$user->last_active}}</td>
                    <td>
                    @if($user->email_verified_at == NULL)
                      Not Verifed
                     @else: 
                     Verfied
                     @endif
                    </td>
                    <td>
                      <a href="{{ route('admin/view-customer',$user->id) }}">
                       <i class="fas fa-eye"></i>
                      </a>&nbsp;
                      <a onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-customer',$user->id) }}">
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
              <!-- <nav aria-label="...">
                <ul class="pagination justify-content-end mb-0">
                  <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">
                      <i class="fas fa-angle-left"></i>
                      <span class="sr-only">Previous</span>
                    </a>
                  </li>
                  <li class="page-item active">
                    <a class="page-link" href="#">1</a>
                  </li>
                  <li class="page-item">
                    <a class="page-link" href="#">2 <span class="sr-only">(current)</span></a>
                  </li>
                  <li class="page-item"><a class="page-link" href="#">3</a></li>
                  <li class="page-item">
                    <a class="page-link" href="#">
                      <i class="fas fa-angle-right"></i>
                      <span class="sr-only">Next</span>
                    </a>
                  </li>
                </ul>
              </nav> -->
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
