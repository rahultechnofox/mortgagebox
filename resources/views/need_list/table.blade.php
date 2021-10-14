<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Need List</h4>
                    <h6 style="float: right;"> 
                        <?php if ($userDetails->firstItem() != null) {?> Showing {{ $userDetails->firstItem() }}-{{ $userDetails->lastItem() }} of {{ $userDetails->total() }} <?php }?>
                        <form>
                            <select id="pagination" class="form-select">
                                <option value="5" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 5){ echo "selected"; }else{ if($entry_count==5){ echo "selected"; } } ?> >5</option>
                                <option value="10" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 10){ echo "selected"; }else{ if($entry_count==10){ echo "selected"; } } ?>>10</option>
                                <option value="25" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 25){ echo "selected"; }else{ if($entry_count==25){ echo "selected"; } } ?>>25</option>
                                <option value="50" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 50){ echo "selected"; }else{ if($entry_count==50){ echo "selected"; } } ?>>50</option>
                                <option value="100" <?php if(isset($_GET['per_page']) && $_GET['per_page'] == 100){ echo "selected"; }else{ if($entry_count==100){ echo "selected"; } } ?>>100</option>
                            </select> 
                        </form>
                    </h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Need ID </th>
                                <th>Customer Name</th>
                                <th>Request Date</th>
                                <th>Service</th>
                                <th>Size</th>
                                <th>Bids</th>
                                <th>Active</th>
                                <th>Status</th>
                                <th>Selected Pro</th>
                                <th>Feedback</th>
                                <th>Final Pro</th>
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
                                <td>@if(isset($users_data->service) && $users_data->service!='') {{ $users_data->service->name }} @else -- @endif</td>
                                <td>{{ $users_data->size_want != "" ? \Helpers::currencyWithoutDecimal($users_data->size_want) : '--' }}</td>
                                <td>{{$users_data->offer_count}}</td>
                                <td>{{$users_data->active_count}}</td>
                                <td>
                                    @if(isset($users_data->area_status) && $users_data->area_status==0)
                                        <span class="badge rounded-pill badge-light-info me-1" style="margin-bottom: 10px;">Matching</span>
                                    @elseif(isset($users_data->area_status) && $users_data->area_status==1)
                                        <span class="badge rounded-pill badge-light-warning me-1" style="margin-bottom: 10px;">Matched</span>
                                    @elseif(isset($users_data->area_status) && $users_data->area_status==2)
                                        <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Advisor Selected</span>
                                    @elseif(isset($users_data->area_status) && $users_data->area_status==3)
                                        <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Completed</span>
                                    @elseif(isset($users_data->area_status) && $users_data->area_status==4)
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Closed</span>
                                    @endif
                                    <!-- @if(isset($users_data->offer_count) && $users_data->offer_count!=0)
                                        <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Matched</span>
                                    @else
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Unmatched</span>
                                    @endif -->
                                </td>
                                <td>@if(isset($users_data->selected_pro) && $users_data->selected_pro!=''){{\Helpers::checkNull($users_data->selected_pro->advisor_name)}}@else -- @endif</td>
                                <td>N/A</td>
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
                        {{$userDetails->withQueryString()->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Striped rows end -->
</div>
<script>
     document.getElementById('pagination').onchange = function() { 
         window.location = "{!! $userDetails->url(1) !!}&per_page=" + this.value; 
    };  </script>