<div class="content-body">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Professionals List</h4>
                    <h6 style="float: right;"> <?php if ($adviors->firstItem() != null) {?> Showing {{ $adviors->firstItem() }}-{{ $adviors->lastItem() }} of {{ $adviors->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Name</th>
                                <th>Role</th>
                                <th>Email Confirmed?</th>
                                <th>FCA Checked?</th>
                                <th>Accepted Leads</th>
                                <th>Live Leads</th>
                                <th>Hired</th>
                                <th>Completed</th>
                                <th>Success%</th>
                                <th>Value</th>
                                <th>Cost</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($adviors) > 0)
                            @foreach($adviors as $users_data)
                            <tr>
                                <td>{{$users_data->id}}</td>
                                <td><a href="{{ route('admin/advisors/show',$users_data->advisorId) }}">{{$users_data->display_name}}</a></td>
                                <td>{{$users_data->role != "" ? $users_data->role : '--' }}</td>
                                <td>
                                    @if($users_data->email_verified_at!='')
                                        <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                    @else
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                    @endif
                                </td>
                                <td>
                                @if($users_data->FCA_verified != "")  
                                    <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Yes</span>
                                @else
                                    <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">No</span>
                                @endif
                                </td>
                                <td>{{$users_data->accepted_leads}}</td>
                                <td>{{$users_data->live_leads}}</td>
                                <td>{{$users_data->hired_leads}}</td>
                                <td>{{$users_data->completed_leads}}</td>
                                <td>{{$users_data->success_percent}}%</td>
                                <td>{{\Helpers::currency($users_data->eastimated_lead)}}</td>
                                <td>{{$users_data->cost_of_lead}}</td>
                                <td>
                                    @if($users_data->user_status==1)
                                        @if($users_data->email_verified_at!='' && $users_data->FCA_verified!='')
                                            <span class="badge rounded-pill badge-light-success me-1" style="margin-bottom: 10px;">Active</span>
                                        @else
                                            <span class="badge rounded-pill badge-light-warning me-1" style="margin-bottom: 10px;">Pending</span>
                                        @endif
                                    @elseif($users_data->user_status==0)
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Suspended</span>
                                    @elseif($users_data->user_status==2)
                                        <span class="badge rounded-pill badge-light-danger me-1" style="margin-bottom: 10px;">Inactive</span>
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
                        {{$adviors->withQueryString()->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>