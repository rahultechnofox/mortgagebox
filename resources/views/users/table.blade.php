<div class="content-body">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Customers List</h4>
                    <h6 style="float: right;"> <?php if ($users->firstItem() != null) {?> Showing {{ $users->firstItem() }}-{{ $users->lastItem() }} of {{ $users->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>PostCode</th>
                                <th>Email</th>
                                <th>Date Joined</th>
                                <th>Last Active</th>
                                <th>Email Verified</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($users) > 0)
                            @foreach($users as $users_data)
                            <tr>
                                <td>{{$users_data->id}}</td>
                                <td><a href="{{ route('admin/users/show',$users_data->id) }}">{{$users_data->name}}</a></td>
                                <td>{{$users_data->post_code}}</td>
                                <td>{{$users_data->email}}</td>
                                <td>{{\Helpers::formatDateTime($users_data->created_at)}}</td>
                                <td>{{\Helpers::formatDateTime($users_data->last_active)}}</td>
                                <td>
                                    @if($users_data->email_status == 0)
                                        <span class="badge rounded-pill badge-light-danger me-1">Not Verifed</span>
                                    @else
                                        <span class="badge rounded-pill badge-light-success me-1">Verfied</span>
                                    @endif
                                </td>
                                <td>
                                    @if($users_data->status ==1)
                                        @if($users_data->email_verified_at!=null)
                                            <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Active</span>
                                        @else
                                            <span class="badge rounded-pill badge-light-warning me-1" style="margin-bottom: 10px;">Pending</span>
                                        @endif
                                    @else 
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin/users/show',$users_data->id) }}">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a>
                                            <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-customer',$users_data->id) }}">
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
                        {{$users->withQueryString()->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>