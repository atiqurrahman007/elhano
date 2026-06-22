@extends('backEnd.layouts.master')
@section('title', 'Edit Homepage Section')
@section('css')
<link href="{{ asset('public/backEnd') }}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
<link href="{{ asset('public/backEnd') }}/assets/libs/summernote/summernote-lite.min.css" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <a href="{{route('homepagebuilder.index')}}" class="btn btn-primary rounded-pill">Manage</a>
                </div>
                <h4 class="page-title">Edit Homepage Section</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{route('homepagebuilder.update', $section->id)}}" method="POST" class="row" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ $section->title }}" id="title" required="">
                            @error('title')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="heading" class="form-label">Heading (Frontend Display)</label>
                            <input type="text" class="form-control @error('heading') is-invalid @enderror" name="heading" value="{{ $section->heading }}" id="heading">
                            @error('heading')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="type" class="form-label">Section Type</label>
                            @php $sectionKey = is_array($section->section_key) ? implode('_', $section->section_key) : (string)($section->section_key ?? ''); @endphp
                            <input type="text" class="form-control" value="{{ucfirst(str_replace('_', ' ', $sectionKey))}}" readonly>
                            <input type="hidden" name="type" id="section_type" value="{{$sectionKey}}">
                            <small class="text-muted">Type cannot be changed once created.</small>
                        </div>
                    </div>

                    <!-- Dynamic Fields -->
                    @php $params = $section->params ?? []; @endphp

                    <!-- Slider Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_slider">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Slider Images</label>
                            <input type="file" class="form-control" name="image[]" multiple accept="image/*">
                            <div class="mt-2">
                                @if(isset($params['images']) && is_array($params['images']))
                                    @foreach($params['images'] as $imgIndex => $img)
                                        @php $sliderImg = $img; while(is_array($sliderImg)) { $sliderImg = reset($sliderImg); } $sliderImg = (string)($sliderImg ?? ''); @endphp
                                        @if($sliderImg)
                                        <div class="d-inline-block me-2 mb-2 position-relative">
                                            <img src="{{asset($sliderImg)}}" height="60" style="border:1px solid #ddd;border-radius:4px;">
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" name="remove_images[]" value="{{$imgIndex}}" id="remove_slider_{{$imgIndex}}">
                                                <label class="form-check-label text-danger small" for="remove_slider_{{$imgIndex}}">Remove</label>
                                            </div>
                                        </div>
                                        @endif
                                    @endforeach
                                @endif
                            </div>
                        </div>
                        <div class="form-group mb-3">
                            <label for="links" class="form-label">Links (One per line)</label>
                            <textarea class="form-control" name="links" rows="3">{{ isset($params['links']) ? (is_array($params['links']) ? implode("\n", $params['links']) : $params['links']) : '' }}</textarea>
                        </div>
                    </div>

                    <!-- Product Grid Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_product_grid">
                        <div class="form-group mb-3">
                            <label for="product_ids" class="form-label">Select Products</label>
                            <select class="form-control select2-image" name="product_ids[]" multiple>
                                @foreach($products as $product)
                                <option value="{{$product->id}}" data-image="{{asset($product->image->image ?? 'public/frontEnd/images/no-image.png')}}" data-price="{{$product->new_price}}" {{ isset($params['product_ids']) && in_array($product->id, $params['product_ids']) ? 'selected' : '' }}>{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="form-group mb-3">
                            <label for="limit" class="form-label">Product Limit</label>
                            <input type="number" class="form-control" name="limit" value="{{ $params['limit'] ?? 10 }}">
                        </div>
                    </div>

                     <!-- Collection Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_collection">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Collection Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                             @if(isset($params['image']))
                                @php $previewImage = $params['image']; while(is_array($previewImage)) { $previewImage = reset($previewImage); } $previewImage = (string)($previewImage ?? ''); @endphp
                                @if($previewImage)
                                    <div class="d-inline-block mt-2">
                                        <img src="{{asset($previewImage)}}" height="80" style="border:1px solid #ddd;border-radius:4px;">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_collection_image">
                                            <label class="form-check-label text-danger small" for="remove_collection_image">Remove Image</label>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                        <div class="form-group mb-3">
                            <label for="link" class="form-label">Link (URL or Category)</label>
                             <input type="text" class="form-control" name="link" value="{{ $params['link'] ?? '' }}" placeholder="Enter URL">
                        </div>
                    </div>
                    
                     <!-- Banner Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_banner">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Banner Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                            @if(isset($params['image']))
                                @php $previewImage = $params['image']; while(is_array($previewImage)) { $previewImage = reset($previewImage); } $previewImage = (string)($previewImage ?? ''); @endphp
                                @if($previewImage)
                                    <div class="d-inline-block mt-2">
                                        <img src="{{asset($previewImage)}}" height="80" style="border:1px solid #ddd;border-radius:4px;">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_banner_image">
                                            <label class="form-check-label text-danger small" for="remove_banner_image">Remove Image</label>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                         <div class="form-group mb-3">
                            <label for="link" class="form-label">Link</label>
                             <input type="text" class="form-control" name="link" value="{{ $params['link'] ?? '' }}" placeholder="Enter URL">
                        </div>
                    </div>

                     <!-- Category Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_category">
                        <div class="form-group mb-3">
                            <label for="category_id" class="form-label">Select Category</label>
                            <select class="form-control select2" name="category_id">
                                 <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{$category->id}}" {{ isset($params['category_id']) && $params['category_id'] == $category->id ? 'selected' : '' }}>{{$category->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                     <!-- Product with Banner Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_product_with_banner">
                        <div class="form-group mb-3">
                            <label class="form-label">Banner Image</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                             @if(isset($params['image']))
                                @php $previewImage = $params['image']; while(is_array($previewImage)) { $previewImage = reset($previewImage); } $previewImage = (string)($previewImage ?? ''); @endphp
                                @if($previewImage)
                                    <div class="d-inline-block mt-2">
                                        <img src="{{asset($previewImage)}}" height="80" style="border:1px solid #ddd;border-radius:4px;">
                                        <div class="form-check mt-1">
                                            <input class="form-check-input" type="checkbox" name="remove_image" value="1" id="remove_pwb_image">
                                            <label class="form-check-label text-danger small" for="remove_pwb_image">Remove Image</label>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        </div>
                         <div class="form-group mb-3">
                            <label class="form-label">Banner Link</label>
                             <input type="text" class="form-control" name="link" value="{{ $params['link'] ?? '' }}" placeholder="Enter URL">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Select Products</label>
                            <select class="form-control select2-image" name="product_ids[]" multiple>
                                @foreach($products as $product)
                                <option value="{{$product->id}}" data-image="{{asset($product->image->image ?? 'public/frontEnd/images/no-image.png')}}" data-price="{{$product->new_price}}" {{ isset($params['product_ids']) && is_array($params['product_ids']) && in_array($product->id, $params['product_ids']) ? 'selected' : '' }}>{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="form-group mb-3">
                            <label class="form-label">Product Limit</label>
                            <input type="number" class="form-control" name="limit" value="{{ $params['limit'] ?? 8 }}">
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" name="sort_order" value="{{ $section->sort_order }}" id="sort_order">
                            @error('sort_order')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-6 mb-3">
                        <div class="form-group">
                            <label for="status" class="d-block">Status</label>
                            <label class="switch">
                                <input type="hidden" name="status" value="0">
                                <input type="checkbox" value="1" name="status" {{ $section->status == 1 ? 'checked' : '' }}>
                                <span class="slider round"></span>
                            </label>
                        </div>
                    </div>
                    <div>
                        <input type="submit" class="btn btn-success" value="Update">
                    </div>

                </form>

            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div> <!-- end col-->
   </div>
</div>
@endsection


@section('script')
<script src="{{ asset('public/backEnd/') }}/assets/libs/parsleyjs/parsley.min.js"></script>
<script src="{{ asset('public/backEnd/') }}/assets/js/pages/form-validation.init.js"></script>
<script src="{{ asset('public/backEnd/') }}/assets/libs/select2/js/select2.min.js"></script>
<script src="{{ asset('public/backEnd/') }}/assets/js/pages/form-advanced.init.js"></script>
<script>
    $(document).ready(function() {
        var type = $('#section_type').val();
        if(type) {
            $('.section-field').addClass('d-none');
            // Enable/disable inputs based on type
             $('.section-field input, .section-field select, .section-field textarea').prop('disabled', true);
             
            var target = '#type_' + type;
            // Reuse product_grid fields for product_slider
            if(type == 'product_slider') {
                target = '#type_product_grid';
            }

            if($(target).length) {
                $(target).removeClass('d-none');
                 $(target).find('input, select, textarea').prop('disabled', false);
            }
        }

        // Custom Select2 with Image
        function formatState (state) {
            if (!state.id) {
                return state.text;
            }
            var baseUrl = state.element.getAttribute('data-image');
            var price = state.element.getAttribute('data-price');
            if(!baseUrl) return state.text;

            var $state = $(
                '<span><img src="' + baseUrl + '" class="img-flag" style="height: 30px; width: 30px; object-fit: cover; margin-right: 10px;" /> ' + state.text + ' (' + price + ')</span>'
            );
            return $state;
        };

        $('.select2-image').select2({
            templateResult: formatState,
            templateSelection: formatState,
            width: '100%'
        });
    });
</script>
@endsection
