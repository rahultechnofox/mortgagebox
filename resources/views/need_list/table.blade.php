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
                                <th>Need ID</th>
                                <th>Customer Name</th>
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
                                <td><a href="{{ route('admin/need/show',$users_data->id) }}">{{$users_data->id}}</a></td>
                                <td>{{$users_data->name}}</td>
                                <td>{{ $users_data->created_at != "" ? \Helpers::formatDateTime($users_data->created_at) : '--' }}</td>
                                <td>{{ $users_data->service_type != "" ? ucfirst($users_data->service_type) : '--' }}</td>
                                <td>{{ $users_data->size_want != "" ? \Helpers::currency($users_data->size_want) : '--' }}</td>
                                <td>{{$users_data->offer_count}}</td>
                                <td>{{$users_data->active_count}}</td>
                                <td>
                                    @if(isset($users_data->offer_count) && $users_data->offer_count!=0)
                                        <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Matched</span>
                                    @else
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Unmatched</span>
                                    @endif
                                </td>
                                <td>@if(isset($users_data->selected_pro) && $users_data->selected_pro!=''){{\Helpers::checkNull($users_data->selected_pro->advisor_name)}}@else -- @endif</td>
                                <td>@if(isset($users_data->close_type) && $users_data->close_type!=''){{\Helpers::checkNull($users_data->close_type)}}@else -- @endif</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin/need/show',$users_data->id) }}">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a>
                                            <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-need',$users_data->id) }}">
                                                <i data-feather="trash" class="me-50"></i>
                                                <span>Delete</span>
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