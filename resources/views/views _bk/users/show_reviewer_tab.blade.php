<div role="tabpanel" class="tab-pane" id="reviewer-justified" aria-labelledby="reviewer-tab-justified" aria-expanded="true">
    <div class="row text-md-end mb-1">
        <span>Total Reviews Done: 200</span>
    </div>
    <div class="row text-md-end mb-1">
        <form role="form" method="get">
            <div class="form-group row">
                <div class="col-md-4 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">All</option>
                        <option value="">Video</option>
                        <option value="">Ebook</option>
                        <option value="">Audio</option>
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
                <div class="col-md-4 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Language</option>
                        <option value="">English</option>
                        <option value="">German</option>
                        <option value="">Spanish</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Disapproved</option>
                        <option value="">Approved</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <input type="text" placeholder="Search" class="form-control" />
                </div>
                <div class="col-md-2 col-12">
                    <button type="submit" name="submit" value="Search" id="submit" class="dt-button create-new btn btn-primary"><i data-feather="search"></i></button>
                    <a href="javascript:;" onclick="resetFilter()" class="btn btn-outline-secondary"><i data-feather="refresh-ccw"></i></a>
                </div>
            </div>
        </form>
    </div>
    <div class="row" id="table-striped1">
        <div class="col-12">
            <div class="card">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Unique ID</th>
                                <th>Book Name</th>
                                <th>Type</th>
                                <th>Language</th>
                                <th>Date book assigned</th>
                                <th>Date review submitted</th>
                                <th>Day taken</th>
                                <th>Category</th>
                                <th>Sub category</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="10" class="recordnotfound"><span>No results found.</span></td>
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