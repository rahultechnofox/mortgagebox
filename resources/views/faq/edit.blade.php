@extends('layouts.app')
@section('content')
<script src="https://cdn.ckeditor.com/ckeditor5/28.0.0/classic/ckeditor.js"></script>

<!-- BEGIN: Content-->
<div class="app-content content ">
    <div class="content-overlay"></div>
    <div class="header-navbar-shadow"></div>
    <div class="content-wrapper container-xxl p-0">
        <div class="content-header row">
            <div class="content-header-left col-md-9 col-12 mb-2">
                <div class="row breadcrumbs-top">
                    <div class="col-12">
                        <h2 class="content-header-title float-start mb-0">Faq</h2>
                        <div class="breadcrumb-wrapper">
                            <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{'/'}}">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item"><a href="{!! url('admin/faq') !!}">Faq List</a>
                                </li>
                                <li class="breadcrumb-item active">Faq Edit
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <section class="app-user-edit">
                <div class="card">
                    <div class="card-body">
                        <div class="tab-content">
                            <div class="tab-pane active" id="account" aria-labelledby="account-tab" role="tabpanel">
                                <form action="{{url('admin/add-update-faq')}}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{$row->id}}">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Faq Category</label>
                                                <select class="form-control" name="faq_category_id" id="faq_category_id" required onchange="getAudience(this.value);">
                                                    <option value="">Select Faq Category</option>
                                                    @foreach($faq_category as $faq_category_data)
                                                        <option value="{{$faq_category_data->id}}" <?php if($faq_category_data->id==$row->faq_category_id){ echo "selected"; } ?> >{{$faq_category_data->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Audience</label>
                                                <input type="text" class="form-control" name="audience" placeholder="Audience" id="audience" value="{{ucfirst($row->audience)}}" required readonly/>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Question</label>
                                                <input type="text" class="form-control" placeholder="Question" name="question" id="question" value="{{$row->question}}" required/>
                                            </div>
                                        </div>

                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <label class="form-label" for="name">Answer</label>
                                                <textarea class="form-control" name="answer" id="editor" value="{{$row->answer}}" placeholder="Answer">{{$row->answer}}</textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="mb-1">
                                                <div class="form-check form-check-inline mb-10">
                                                    <input class="form-check-input disabled-checkbox" type="checkbox" id="is_featured" name="is_featured" value="1" <?php if($row->is_featured==1){ echo "checked"; } ?> >
                                                    <label class="form-check-label" for="is_featured">Featured</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12 d-flex flex-sm-row flex-column mt-2">
                                            <button type="submit" class="btn btn-primary mb-1 mb-sm-0 me-0 me-sm-1">Save Changes</button>
                                            <a href="{!! url('admin/faq') !!}" class="btn btn-outline-secondary">Back</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>
<script type="text/javascript">
    ClassicEditor
    .create( document.querySelector( '#editor' ), {} )
    .catch( error => {
        console.log( error );
    } );
</script>
@endsection

