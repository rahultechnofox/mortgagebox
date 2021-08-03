@extends('layouts.app')
@push('style')
<!-- Icons -->
<link rel="stylesheet" href="{{ asset('argon') }}/vendor/nucleo/css/nucleo.css" type="text/css">



<style>

</style>
@endpush
@section('content')

<div class="header bg-gradient-primary pb-8 pt-5 pt-md-8">

<div class="container-fluid">
<div class="row">

<div class="col-xl-8 order-xl-1">
<div class="card bg-secondary shadow">
<div class="card-header bg-white border-0">
<div class="row align-items-center">
    <h3 class="mb-0">{{ __('Add Page') }}</h3>
</div>
</div>
<div class="card-body">

<form method="post" action="{{ route('admin/savePage') }}" autocomplete="off">
    @csrf
    @method('post')

    <h6 class="heading-small text-muted mb-4">{{ __('Add New Page') }}</h6>
    <div class="pl-lg-4">
        <div class="form-group{{ $errors->has('page_name') ? ' has-danger' : '' }}">
            <label class="form-control-label" for="input-page_name">{{ __('Page Name') }}</label>
            <input type="text" name="page_name" id="input-page_name" class="form-control form-control-alternative{{ $errors->has('page_name') ? ' is-invalid' : '' }}" placeholder="{{ __('Page Name') }}" value="">
            
            @if ($errors->has('page_name'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('page_name') }}</strong>
                </span>
            @endif
        </div>
        <div class="form-group{{ $errors->has('password') ? ' has-danger' : '' }}">
            <label class="form-control-label" for="input-password">{{ __('Page Content') }}</label>
            <textarea name="page_content"></textarea>
            
            @if ($errors->has('page_content'))
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $errors->first('page_content') }}</strong>
                </span>
            @endif
        </div>
       
        <div class="text-center">
            <button type="submit" class="btn btn-success mt-4">{{ __('Submit') }}</button>
        </div>
    </div>
</form>
</div>
</div>
</div>
</div>


</div>
</div>
@endsection


@push('js')
<!-- <script src="{{ asset('argon') }}/vendor/jquery/dist/jquery.min.js"></script>
<script src="{{ asset('argon') }}/vendor/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('argon') }}/vendor/js-cookie/js.cookie.js"></script>
<script src="{{ asset('argon') }}/vendor/jquery.scrollbar/jquery.scrollbar.min.js"></script>
<script src="{{ asset('argon') }}/vendor/jquery-scroll-lock/dist/jquery-scrollLock.min.js"></script> -->
<!-- Argon JS -->
<script src="{{ asset('argon') }}/js/argon.js?v=1.2.0"></script>
<script src="https://cdn.ckeditor.com/4.16.1/standard/ckeditor.js"></script>
<script>
                        CKEDITOR.replace( 'page_content' );
</script>
@endpush
