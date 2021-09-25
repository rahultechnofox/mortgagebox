<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Review Spam List</h4>
                    <h6 style="float: right;"> <?php if ($result->firstItem() != null) {?> Showing {{ $result->firstItem() }}-{{ $result->lastItem() }} of {{ $result->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Customer Name</th>
                                <th>Review</th>
                                <th>Advisor</th>
                                <th>Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($result) > 0)
                            @foreach($result as $users_data)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{\Helpers::checkNull($users_data->review->user->name)}}</td>
                                <td>{{\Helpers::checkNull($users_data->review->reviews)}}</td>
                                <td>{{\Helpers::checkNull($users_data->adviser->display_name)}}</td>
                                <td>{{\Helpers::checkNull($users_data->reason)}}</td>
                                <td>
                                    @if($users_data->spam_status == -1)
                                        <a class="btn btn-success btn-sm btn-add-new waves-effect waves-float waves-light" href="javascript:;" data-bs-toggle="modal" data-bs-target="#modals-slide-in_{{$users_data->id}}">Pending</a>
                                    @else
                                        <a class="btn btn-danger btn-sm btn-add-new waves-effect waves-float waves-light">Refunded</a>
                                    @endif
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

