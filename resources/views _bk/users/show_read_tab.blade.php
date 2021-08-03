<div role="tabpanel" class="tab-pane active" id="read-justified" aria-labelledby="read-tab-justified" aria-expanded="true">
    <div class="row text-md-end mb-1">
        <span>Revenue given by user: $10000</span>
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
                        <option value="">Completed</option>
                        <option value="">In Progress</option>
                    </select>
                </div>
                <div class="col-md-4 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Category</option>
                        <option value="">Cat 1</option>
                        <option value="">Cat 2</option>
                    </select>
                </div> 
            </div>
            <div class="form-group row mt-1">
                <div class="col-md-4 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Sub Category</option>  
                        <option value="">Sub Category 1</option>
                        <option value="">Sub Category 2</option>
                        <option value="">Sub Category 3</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Download</option>
                        <option value="">Read Online</option>
                    </select>
                </div>
                <div class="col-md-3 col-12">
                    <select class="form-select" id="" name="">
                        <option value="">Purchased</option>
                        <option value="">Borrowed</option>
                    </select>
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
                                <th>S.no</th>
                                <th>Audio/ Video/ Ebook</th>
                                <th>Category</th>
                                <th>Sub - Category</th>
                                <th>Borrowed/ Purchased/ Free Downloaded</th>
                                <th>Start date/ time</th>
                                <th>End date/ time</th>
                                <th>Status</th>
                                <th>Sale Price</th>
                                <th>Borrowed Price</th>
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