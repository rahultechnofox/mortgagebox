<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Need List</h4>
                    <h6 style="float: right;"> <?php if ($userDetails->firstItem() != null) {?> Showing {{ $userDetails->firstItem() }}-{{ $userDetails->lastItem() }} of {{ $userDetails->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Customer Name</th>
                                <th>Need ID</th>
                                <th>Request Date</th>
                                <th>Service</th>
                                <th>Size</th>
                                <th>Bids</th>
                                <th>Active</th>
                                <th>Status</th>
                                <th>Selected Pro</th>
                                <th>Feedback</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($userDetails) > 0)
                            @foreach($userDetails as $users_data)
                            <tr>
                                <td>{{$users_data->name}}</td>
                                <td>{{$users_data->id}}</td>
                                <td>{{ $users_data->created_at != "" ? $users_data->created_at : 'N/A' }}</td>
                                <td>{{ $users_data->service_type != "" ? $users_data->service_type : 'N/A' }}</td>
                                <td>{{ $users_data->size_want != "" ? $users_data->size_want : 'N/A' }}</td>
                                <td>{{$users_data->offer_count}}</td>
                                <td>{{$users_data->live_leads}}</td>
                                
                                <td>
                                @if($users_data->bid_status == 0)
                                In-Progress
                                @elseif($users_data->bid_status == 1)
                                Accepted
                                @elseif($users_data->bid_status == 2)
                                Closed 
                                @elseif($users_data->bid_status == 3)
                                Declined 
                                @else:
                                    {{$users_data->bid_status}}
                                @endif
                                </td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin/view-need',$users_data->id) }}">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a>
                                            <!-- <a class="dropdown-item" href="#">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a> -->
                                            <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-need',$users_data->id) }}">
                                                <i data-feather="trash" class="me-50"></i>
                                                <span>Deletes</span>
                                            </a>
                                        </div>
                                    </div>
                                </td>
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
                <div class="card-footer">
                    <div class="pagination" style="float: right;">
                        {{$userDetails->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Striped rows end -->
</div>