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
                                <th>Cost</th>
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
                                <td>{{\Helpers::checknull($users_data->company_admin_name)}}</td>
                                <td>{{$users_data->total_advisor}}</td>
                                <td>{{$users_data->accepted_leads}}</td>
                                <td>{{$users_data->live_leads}}</td>
                                <td>{{$users_data->hired_leads}}</td>
                                <td>{{$users_data->completed_leads}}</td>
                                <td>@if($users_data->success_percent!=0){{number_format($users_data->success_percent,2)}}@else{{$users_data->success_percent}}@endif%</td>
                                <td>{{\Helpers::currency($users_data->value)}}</td>
                                <td>{{\Helpers::currency($users_data->cost)}}</td>
                                <td>
                                    @if($users_data->status == 1)
                                        <a class="btn btn-success btn-sm waves-effect waves-float waves-light">Active</a>
                                    @else 
                                        <a class="btn btn-danger btn-sm waves-effect waves-float waves-light">Deactive</a>
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