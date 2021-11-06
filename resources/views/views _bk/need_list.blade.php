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
            
               <h3> Need List-Admin Dashboard</h3>
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
                    <th scope="col" class="sort" data-sort="name">Customer Name</th>
                    <th scope="col" class="sort" data-sort="budget">Need ID</th>
                    <th scope="col" class="sort" data-sort="status">Request Date</th>
                    <th scope="col" class="sort" data-sort="status">Service</th>
                    <th scope="col" class="sort" data-sort="completion">Size</th>
                    <th scope="col" class="sort" data-sort="completion">Bids</th>
                    <th scope="col" class="sort" data-sort="completion">Active</th>
                    <th scope="col" class="sort" data-sort="completion">Status</th>
                    <th scope="col" class="sort" data-sort="completion">Selected Pro</th>
                    <th scope="col" class="sort" data-sort="completion">Feedback</th>
                    <th scope="col" class="sort" data-sort="completion">Action</th>
                    <th scope="col"></th>
                  </tr>
                </thead>
                <tbody class="list">
                @foreach ($userDetails as $need)
                  <tr>
                    <td>{{$need->name}}</td>
                    <td>{{$need->id}}</td>
                    <td>{{ $need->created_at != "" ? $need->created_at : 'N/A' }}</td>
                    <td>{{ $need->service_type != "" ? $need->service_type : 'N/A' }}</td>
                    <td>{{ $need->size_want != "" ? $need->size_want : 'N/A' }}</td>
                    <td>{{$need->offer_count}}</td>
                    <td>{{$need->live_leads}}</td>
                    
                    <td>
                    @if($need->bid_status == 0)
                      In-Progress
                     @elseif($need->bid_status == 1)
                      Accepted
                    @elseif($need->bid_status == 2)
                      Closed 
                    @elseif($need->bid_status == 3)
                      Declined 
                    @else:
                        {{$need->bid_status}}
                     @endif
                    </td>
                    <td>N/A</td>
                    <td>N/A</td>
                    <td>
                      <a href="{{ route('admin/view-need',$need->id) }}">
                       <i class="fas fa-eye"></i>
                      </a>&nbsp;
                      <a onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-need',$need->id) }}">
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
