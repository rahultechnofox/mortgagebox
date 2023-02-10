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
                                <th>Period</th>
                                <th>Gross Invoice</th>
                                <th>Credits</th>
                                <th>Net Invoice</th>
                                <th>Received</th>
                                <th>Outstanding</th>
                                <th>Invoice Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($result) > 0)
                            @foreach($result as $users_data)
                            <tr>
                                <td><a href="{{url('/admin/invoice-list')}}/{{$users_data->month}}">{{\Helpers::getMonth($users_data->month)}} {{date("Y",strtotime($users_data->created_at))}}</a></td>
                                <td><?php echo \Helpers::currency($users_data->subtotal_month); ?></td>
                                <td><?php echo \Helpers::currency($users_data->discount_month); ?></td>
                                <td><?php echo \Helpers::currency($users_data->total_due_month); ?></td>
                                <td>
                                    <?php echo \Helpers::currency($users_data->received_month); ?>
                                </td>
                                <td>
                                    <?php echo \Helpers::currency($users_data->outstanding_month); ?>
                                </td>
                                <td>{{date("d-M-Y",strtotime($users_data->created_at))}}</td>
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