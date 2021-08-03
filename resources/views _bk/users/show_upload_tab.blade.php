<div role="tabpanel" class="tab-pane" id="upload-justified" aria-labelledby="upload-tab-justified" aria-expanded="true">
    <div class="row">
        <div class="col-xl-6 col-lg-6">
            <ul class="nav nav-pills nav-justified">
                <li class="nav-item" style="background: #ededed;">
                    <a class="nav-link active" id="uploaded-tab-justified" data-bs-toggle="pill" href="#uploaded-justified" aria-expanded="false">Uploaded</a>
                </li>&nbsp;&nbsp;
                <li class="nav-item" style="background: #ededed;">
                    <a class="nav-link" id="payout-tab-justified" data-bs-toggle="pill" href="#payout-justified" aria-expanded="false">Payout</a>
                </li>
            </ul>
        </div>
        <div class="col-xl-6 col-lg-6 text-md-end">Balance: $10000</div>
    </div>
    <div class="row text-md-end mb-1">
        <form role="form" method="get">
            <div class="form-group row">
                <div class="col-md-2 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">All</option>
                        <option value="">Video</option>
                        <option value="">Ebook</option>
                        <option value="">Audio</option>
                    </select>
                </div>
                <div class="col-md-2 col-12">
                    <select class="form-select" id="" name="">    
                        <option value="">Borrow</option>
                        <option value="">Sale</option>
                    </select>
                </div>
                <div class="col-md-4 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Category</option>
                        <option value="">Cat 1</option>
                        <option value="">Cat 2</option>
                    </select>
                </div> 
                <div class="col-md-4 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Sub Category</option>  
                        <option value="">Sub Category 1</option>
                        <option value="">Sub Category 2</option>
                        <option value="">Sub Category 3</option>
                    </select>
                </div>
            </div>
            <div class="form-group row mt-1">
                <div class="col-md-6 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Language</option>
                        <option value="">English</option>
                        <option value="">German</option>
                        <option value="">Spanish</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Approved</option>
                        <option value="">Disapproved</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <button type="submit" name="submit" value="Search" id="submit" class="dt-button create-new btn btn-primary"><i data-feather="search"></i></button>
                    <a href="javascript:;" onclick="resetFilter()" class="btn btn-outline-secondary"><i data-feather="refresh-ccw"></i></a>
                </div>
            </div>
        </form>
    </div>
    <div role="tabpanel" class="tab-pane active" id="uploaded-justified" aria-labelledby="uploaded-tab-justified" aria-expanded="true">
        <div class="row" id="table-striped2">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Unique ID</th>
                                    <th>Date of upload</th>
                                    <th>Name of the book</th>
                                    <th>Type</th>
                                    <th>Language</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Reviewer Name</th>
                                    <th>Borrowed Count</th>
                                    <th>Sale Count</th>
                                    <th>Free</th>
                                    <th>Total Earning</th>
                                    <th>Admin Commission</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="13" class="recordnotfound"><span>No results found.</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="pagination" style="float: right;">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div role="tabpanel" class="tab-pane" id="payout-justified" aria-labelledby="payout-tab-justified" aria-expanded="true">
        <div class="row" id="table-striped3">
            <div class="col-12">
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Unique ID</th>
                                    <th>Pay Date</th>
                                    <th>Name of the book</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Sub-Category</th>
                                    <th>Outstanding Borrowed Count</th>
                                    <th>Outstanding Borrowed Earning</th>
                                    <th>Outstanding Sale Count</th>
                                    <th>Outstanding Sale Earning</th>
                                    <th>Admin Commission</th>
                                    <th>Transaction ID</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>12</td>
                                    <td>24/07/2021</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>--</td>
                                    <td>
                                        <button class="btn btn-outline-secondary btn-sm me-75 mb-0" tabindex="0" aria-controls="DataTables_Table_0" type="button" data-bs-toggle="modal" data-bs-target="#inlineForm"><span>Pay</span></button>
                                    </td>
                                </tr>
                                {{-- <tr>
                                    <td colspan="12" class="recordnotfound"><span>No results found.</span></td>
                                </tr> --}}
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="pagination" style="float: right;">
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>