<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Companies List</h4>
                    <h6 style="float: right;"> <?php if ($companyDetails->firstItem() != null) {?> Showing {{ $companyDetails->firstItem() }}-{{ $companyDetails->lastItem() }} of {{ $companyDetails->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Company ID</th>
                                <th>Company Name</th>
                                <th>Contact Admin</th>
                                <th>No. of Advisers</th>
                                <th>Accepted Leads</th>
                                <th>Live Leads</th>
                                <th>Hired</th>
                                <th>Completed</th>
                                <th>Success %</th>
                                <th>Value</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($companyDetails) > 0)
                            @foreach($companyDetails as $users_data)
                            <tr>
                                <td>{{$users_data->id}}</td>
                                <td><a href="{{url('/admin/company/show')}}/{{$users_data->id}}">{{$users_data->company_name}}</a></td>
                                <td>Jhon Blogs</td>
                                <td>{{$users_data->total_advisor}}</td>
                                <td>{{$users_data->accepted_leads}}</td>
                                <td>{{$users_data->live_leads}}</td>
                                <td>{{$users_data->hired}}</td>
                                <td>{{$users_data->completed}}</td>
                                <td>{{$users_data->value}}</td>
                                <td>{{$users_data->cost}}</td>
                                <td>
                                    @if ($users_data->status == 1) 
                                        <a href="javascript:;" onclick="updateStatus('{{$users_data->id}}','0','/admin/update-company-status');">Active</a>
                                    @else
                                        <a href="javascript:;" onclick="updateStatus('{{$users_data->id}}','1','/admin/update-company-status');">In-Active</a>
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
                        {{$companyDetails->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Striped rows end -->
</div>