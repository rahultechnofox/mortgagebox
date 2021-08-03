<div class="content-body">
    <!-- Striped rows start -->
    <div class="row" id="table-striped">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Users List</h4>
                    <h6 style="float: right;"> <?php if ($data->firstItem() != null) {?> Showing {{ $data->firstItem() }}-{{ $data->lastItem() }} of {{ $data->total() }} <?php }?></h6>
                </div>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>S.no</th>
                                <th>Unique ID</th>
                                <th>Username</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Email</th>
                                <th>Registered/ Created on</th>
                                <th>Author</th>
                                <th>Reviewer</th>
                                <th>Verified</th>
                                <th>Designation</th>
                                <th>University Name</th>
                                <th>School Name</th>
                                <th>Last Login</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $i = 1; ?>
                            @if(count($data) > 0)
                            @foreach($data as $res)
                            <tr>
                                <td>{{$i}}</td>
                                <td>{{$res->id}}</td>
                                <td>{{ isset($res->username)?$res->username:'--' }}</td>
                                <td>{{ isset($res->first_name)?$res->first_name:'--' }}</td>
                                <td>{{ isset($res->last_name)?$res->last_name:'--' }}</td>
                                <td>{{ isset($res->email)?$res->email:'--' }}</td>
                                <td>{{ isset($res->created_at)?\Helpers::commonDateTimeFormate($res->created_at):'--' }}</td>
                                <td>Yes</td>
                                <td>No</td>
                                <td>Yes</td>
                                <td>Student</td>
                                <td>Oxford</td>
                                <td>Excellence</td>
                                <td>Date</td>
                                <td>
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                            <i data-feather="more-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('users.show', $res->id) }}">
                                                <i data-feather="eye" class="me-50"></i>
                                                <span>Detail</span>
                                            </a>
                                            <a class="dropdown-item" href="{{ route('users.edit', $res->id) }}">
                                                <i data-feather="edit-2" class="me-50"></i>
                                                <span>Edit</span>
                                            </a>
                                            {!! Form::open(['route' => ['users.destroy', $res->id], 'method' => 'delete']) !!}
                                            {!! Form::button('<i data-feather="trash" class="me-50"></i><span>Delete</span>', [
                                                'data-toggle' => 'tooltip',
                                                'data-placement' => 'bottom',
                                                'title' => 'Delete',
                                                'type' => 'submit',
                                                'class' => 'dropdown-item',
                                                'onclick' => "return confirm('Are you sure?')",
                                                'style' => "width:100%;"
                                            ]) !!}
                                            {!! Form::close() !!}
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
                        {{$data->links('pagination::bootstrap-4')}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Striped rows end -->
</div>