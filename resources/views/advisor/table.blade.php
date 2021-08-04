<div class="content-body">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Advisor List</h4>
                    <h6 style="float: right;"> <?php if ($userDetails->firstItem() != null) {?> Showing {{ $userDetails->firstItem() }}-{{ $userDetails->lastItem() }} of {{ $userDetails->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>PostCode</th>
                                <th>Email</th>
                                <th>FCA Number</th>
                                <th>Accepted Leads</th>
                                <th>Live Leads</th>
                                <th>Hired</th>
                                <th>Completed</th>
                                <th>Success</th>
                                <th>Value</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($userDetails) > 0)
                            @foreach($userDetails as $users_data)
                            <tr>
                                <td>{{$users_data->id}}</td>
                                <td>{{$users_data->display_name}}</td>
                                <td>{{$users_data->postcode != "" ? $users_data->postcode : 'N/A' }}</td>
                                <td>{{$users_data->email != "" ? $users_data->email : 'N/A' }}</td>
                                <td>{{$users_data->FCANumber != "" ? $users_data->FCANumber : 'N/A' }}</td>
                                <td>{{$users_data->acceptedLeads}}</td>
                                <td>{{$users_data->live_leads}}</td>
                                <td>{{$users_data->hired_leads}}</td>
                                <td>{{$users_data->completed_leads}}</td>
                                <td>N/A</td>
                                <td>N/A</td>
                                <td>
                                    @if($users_data->email_verified_at == NULL)
                                        Not Verifed
                                    @else: 
                                        Verfied
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin/advisors/show',$users_data->id) }}">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a>
                                            <!-- <a class="dropdown-item" href="#">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a> -->
                                            <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-advisor',$users_data->id) }}">
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
</div>