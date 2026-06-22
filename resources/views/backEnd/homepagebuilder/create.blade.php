@extends('backEnd.layouts.master')
@section('title', 'Create Homepage Section')
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
                <h4 class="page-title">Create Homepage Section</h4>
            </div>
        </div>
    </div>       
    <!-- end page title --> 
   <div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <form action="{{route('homepagebuilder.store')}}" method="POST" class="row" enctype="multipart/form-data">
                    @csrf
                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="title" class="form-label">Title *</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}" id="title" required="">
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
                            <input type="text" class="form-control @error('heading') is-invalid @enderror" name="heading" value="{{ old('heading') }}" id="heading">
                            @error('heading')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="type" class="form-label">Section Type *</label>
                            <select class="form-control select2 @error('type') is-invalid @enderror" name="type" id="section_type" required>
                                <option value="">Select Type</option>
                                <option value="slider">Slider (Images)</option>
                                <option value="product_grid">Product Grid (Specific Products)</option>
                                <option value="product_slider">Product Slider (Specific Products)</option>
                                <option value="collection">Collection (Image + Link)</option>
                                <option value="banner">Banner (Image)</option>
                                <option value="category">Category (Specific Category)</option>
                                <option value="category_bar">Category Bar (List of Categories)</option>
                                <option value="brand_slider">Brand Slider</option>
                                <option value="product_with_banner">Product with Banner (Image + Grid)</option>
                            </select>
                            @error('type')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <!-- Dynamic Fields -->
                    
                    <!-- Slider Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_slider">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Slider Images *</label>
                            <input type="file" class="form-control" name="image[]" multiple accept="image/*">
                            <small class="text-muted">Upload multiple images.</small>
                        </div>
                        <div class="form-group mb-3">
                            <label for="links" class="form-label">Links (One per line, matching image order)</label>
                            <textarea class="form-control" name="links" rows="3" placeholder="http://example.com/link1&#10;http://example.com/link2"></textarea>
                        </div>
                    </div>

                    <!-- Product Grid Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_product_grid">
                        <div class="form-group mb-3">
                            <label for="product_ids" class="form-label">Select Products *</label>
                            <select class="form-control select2-image" name="product_ids[]" multiple>
                                @foreach($products as $product)
                                <option value="{{$product->id}}" data-image="{{asset($product->image->image ?? 'public/frontEnd/images/no-image.png')}}" data-price="{{$product->new_price}}">{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="form-group mb-3">
                            <label for="limit" class="form-label">Product Limit</label>
                            <input type="number" class="form-control" name="limit" value="10">
                        </div>
                    </div>

                     <!-- Collection Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_collection">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Collection Image *</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                        <div class="form-group mb-3">
                            <label for="link" class="form-label">Link (URL or Category)</label>
                             <input type="text" class="form-control" name="link" placeholder="Enter URL">
                        </div>
                    </div>
                    
                     <!-- Banner Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_banner">
                        <div class="form-group mb-3">
                            <label for="image" class="form-label">Banner Image *</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                         <div class="form-group mb-3">
                            <label for="link" class="form-label">Link</label>
                             <input type="text" class="form-control" name="link" placeholder="Enter URL">
                        </div>
                    </div>

                     <!-- Category Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_category">
                        <div class="form-group mb-3">
                            <label for="category_id" class="form-label">Select Category *</label>
                            <select class="form-control select2" name="category_id">
                                 <option value="">Select Category</option>
                                @foreach($categories as $category)
                                <option value="{{$category->id}}">{{$category->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                     <!-- Product with Banner Fields -->
                    <div class="col-sm-12 section-field d-none" id="type_product_with_banner">
                        <div class="form-group mb-3">
                            <label class="form-label">Banner Image *</label>
                            <input type="file" class="form-control" name="image" accept="image/*">
                        </div>
                         <div class="form-group mb-3">
                            <label class="form-label">Banner Link</label>
                             <input type="text" class="form-control" name="link" placeholder="Enter URL">
                        </div>
                        <div class="form-group mb-3">
                            <label class="form-label">Select Products *</label>
                            <select class="form-control select2-image" name="product_ids[]" multiple>
                                @foreach($products as $product)
                                <option value="{{$product->id}}" data-image="{{asset($product->image->image ?? 'public/frontEnd/images/no-image.png')}}" data-price="{{$product->new_price}}">{{$product->name}}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="form-group mb-3">
                            <label class="form-label">Product Limit</label>
                            <input type="number" class="form-control" name="limit" value="8">
                        </div>
                    </div>

                    <div class="col-sm-12">
                        <div class="form-group mb-3">
                            <label for="sort_order" class="form-label">Sort Order</label>
                            <input type="number" class="form-control @error('sort_order') is-invalid @enderror" name="sort_order" value="{{ old('sort_order') }}" id="sort_order">
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
                                <input type="checkbox" value="1" name="status" checked>
                                <span class="slider round"></span>
                            </label>
                            @error('status')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div>
                        <input type="submit" class="btn btn-success" value="Submit">
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
        $('#section_type').change(function() {
            var type = $(this).val();
            $('.section-field').addClass('d-none');
            
            // Disable inputs in hidden sections to avoid validation errors/unwanted data
            $('.section-field input, .section-field select, .section-field textarea').prop('disabled', true);
            
            if(type) {
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
        });
        
        // Trigger change on load if value exists
        if($('#section_type').val()) {
            $('#section_type').trigger('change');
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
