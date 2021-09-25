<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Contact List</h4>
                    <h6 style="float: right;"> <?php if ($result->firstItem() != null) {?> Showing {{ $result->firstItem() }}-{{ $result->lastItem() }} of {{ $result->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Mobile</th>
                                <th>Message</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($result) > 0)
                            @foreach($result as $users_data)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{\Helpers::checkNull($users_data->name)}}</td>
                                <td>{{\Helpers::checkNull($users_data->email)}}</td>
                                <td>{{\Helpers::checkNull($users_data->mobile)}}</td>
                                <td>{{\Helpers::checkNull($users_data->message)}}</td>
                                <td>{{\Helpers::formatDateTime($users_data->created_at)}}</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="javascript:;" onclick="getContactUsData('{{$users_data->id}}')" data-bs-toggle="modal" data-bs-target="#modals-slide-in">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a>

                                            <!-- <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-faq',$users_data->id) }}">
                                                <i data-feather="trash" class="me-50"></i>
                                                <span>Delete</span>
                                            </a> -->
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
                        {{$result->withQueryString()->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Striped rows end -->
</div>

