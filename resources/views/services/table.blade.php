<div class="content-body">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Services List</h4>
                    <h6 style="float: right;"> <?php if ($page_list->firstItem() != null) {?> Showing {{ $page_list->firstItem() }}-{{ $page_list->lastItem() }} of {{ $page_list->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($page_list) > 0)
                            @foreach($page_list as $users_data)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$users_data->name}}</td>
                                <td>
                                    @if ($users_data->status == 1) 
                                        <a href="{{ 'status-service/0/'.$users_data->id }}">Active</a>
                                    @else
                                        <a href="{{ 'status-service/1/'.$users_data->id }}">In-Active</a>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('admin/edit-service',$service->id) }}">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a>
                                            <!-- <a class="dropdown-item" href="#">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a> -->
                                            <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-service',$service->id) }}">
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
                        {{$page_list->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>