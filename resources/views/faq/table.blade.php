<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Faq List</h4>
                    <h6 style="float: right;"> <?php if ($page_list->firstItem() != null) {?> Showing {{ $page_list->firstItem() }}-{{ $page_list->lastItem() }} of {{ $page_list->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Question</th>
                                <th>Created Date</th>
                                <th>Faq Category</th>
                                <th>Audience</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($page_list) > 0)
                            @foreach($page_list as $users_data)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{\Helpers::checkNull($users_data->question)}}</td>
                                <td>{{\Helpers::checkEmptydateMdYHIS($users_data->created_at)}}</td>
                                <td>@if(isset($users_data->faq_category) && $users_data->faq_category!=''){{\Helpers::checkNull($users_data->faq_category->name)}}@else -- @endif</td>
                                <td>@if(isset($users_data->audience) && $users_data->audience!=''){{\Helpers::checkNull($users_data->audience->name)}}@else -- @endif</td>
                                <td>
                                    @if($users_data->status == 1)
                                        <a class="btn btn-success btn-sm waves-effect waves-float waves-light" href="javascript:;" onclick="updateStatus('{{$users_data->id}}','0','/admin/update-faq-status');">Active</a>
                                    @else 
                                        <a class="btn btn-danger btn-sm waves-effect waves-float waves-light" href="javascript:;" onclick="updateStatus('{{$users_data->id}}','1','/admin/update-faq-status');">Deactive</a>
                                    @endif
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <!-- <a class="dropdown-item" href="#">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a> -->
                                            <a class="dropdown-item"  href="{{ route('admin/faq/edit',$users_data->id) }}">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a>
                                            <a class="dropdown-item" onclick="return confirm('Are you sure you want to delete?')" href="{{ route('admin/delete-faq',$users_data->id) }}">
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
                        {{$page_list->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Striped rows end -->
</div>