<div class="content-body">
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Invoice List</h4>
                    <h6 style="float: right;"> <?php if ($result->firstItem() != null) {?> Showing {{ $result->firstItem() }}-{{ $result->lastItem() }} of {{ $result->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Professional</th>
                                <th>Invoice Amount</th>
                                <th>Payment Status</th>
                                <th>Payment Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($result) > 0)
                                @foreach($result as $users_data)
                                    <tr>
                                        <td>@if(isset($users_data->adviser) && $users_data->adviser!='')<a href="{{url('/admin/invoice-detail')}}/{{$users_data->id}}">{{$users_data->adviser->display_name}}@else -- @endif</a></td>
                                        <td><?php echo \Helpers::currency(json_encode($users_data->invoice_data->total_dues)); ?></td>
                                        <td>
                                            @if($users_data->is_paid==1)
                                                Paid
                                            @else
                                                Not Paid
                                            @endif
                                        </td>
                                        <td>{{date("d-M-Y",strtotime($users_data->updated_at))}}</td>
                                        <td>
                                            <a href="{{ url('admin/invoice-detail/') }}/{{$users_data->id}}">
                                                <span>View Invoice</span>
                                            </a>
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
                        {{$result->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>