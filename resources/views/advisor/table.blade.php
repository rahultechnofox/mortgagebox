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
                                <td>{{$users_data->role != "" ? $users_data->role : 'N/A' }}</td>
                                <td>
                                    @if($users_data->email_status!='')
                                        @if($users_data->email_status==1)
                                            Yes
                                        @else
                                            No
                                        @endif
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>{{$users_data->FCA_verified != "" ? $users_data->FCA_verified : 'N/A' }}</td>
                                <td>{{$users_data->accepted_leads}}</td>
                                <td>{{$users_data->live_leads}}</td>
                                <td>{{$users_data->hired_leads}}</td>
                                <td>{{$users_data->completed_leads}}</td>
                                <td>10%</td>
                                <td>{{$users_data->value}}</td>
                                <td>{{$users_data->cost}}</td>
                                <td>
                                    @if($users_data->status == 1)
                                        Active
                                    @else 
                                        Deactive
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
                        {{$adviors->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>