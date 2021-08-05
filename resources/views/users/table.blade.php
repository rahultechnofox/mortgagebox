<div class="content-body">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Users List</h4>
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
                                <th>Email Verified</th>
                                <th>Last Active</th>
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
                                <td>{{$users_data->name}}</td>
                                <td>{{$users_data->post_code}}</td>
                                <td>{{$users_data->email}}</td>
                                <td>{{$users_data->created_at}}</td>
                                <td>
                                    @if($users_data->email_status == 0)
                                        Not Verifed
                                    @else
                                        Verfied
                                    @endif
                                </td>
                                <td>{{$users_data->last_active}}</td>
                                <td>
                                    @if($users_data->status == 1)
                                        Active
                                    @else 
                                        Deactive
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
                                            <!-- <a class="dropdown-item" href="#">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a> -->
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
                        {{$users->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>