@extends('backEnd.layouts.master')
@section('title','POS Create')
@section('css')
<style>
    .pos_product_item {
        cursor: pointer;
        transition: all 0.3s ease;
        border: 1px solid #eee;
        border-radius: 8px;
        overflow: hidden;
        background: #fff;
        height: 100%;
    }
    .pos_product_item:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transform: translateY(-2px);
    }
    .pos_product_img {
        height: 120px;
        width: 100%;
        object-fit: cover;
        background: #f8f9fa;
    }
    .pos_product_content {
        padding: 10px;
    }
    .pos_product_title {
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 5px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: #333;
    }
    .pos_product_price {
        font-weight: 700;
        color: #28a745;
    }
    .category-btn {
        margin-right: 5px;
        margin-bottom: 5px;
        border-radius: 20px;
        padding: 5px 15px;
        font-size: 13px;
    }
    .cart-summary {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        margin-top: 20px;
    }
    .cart-table-container {
        max-height: 400px;
        overflow-y: auto;
    }
    .qty-input {
        width: 40px;
        text-align: center;
        border: 1px solid #ddd;
        height: 30px;
    }
    .qty-btn {
        width: 30px;
        height: 30px;
        padding: 0;
        line-height: 28px;
    }
</style>
<link href="{{asset('public/backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">POS - Point of Sale</h4>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Products -->
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <!-- Search & Filter -->
                    <div class="row mb-3">
                        <div class="col-md-12 mb-3">
                            <input type="text" id="search_product" class="form-control" placeholder="Search product by name or barcode..." autofocus>
                        </div>
                        <div class="col-md-12">
                            <div class="d-flex flex-wrap">
                                <button class="btn btn-outline-primary category-btn active" data-id="all">All</button>
                                @foreach($categories as $category)
                                <button class="btn btn-outline-secondary category-btn" data-id="{{$category->id}}">{{$category->name}}</button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div class="row" id="product_grid">
                        @foreach($products as $product)
                        <div class="col-md-3 col-sm-4 col-6 mb-3 product-item-wrapper" data-category="{{ $product->category_id ?? 'all' }}" data-name="{{ strtolower($product->name) }}" data-code="{{ $product->type == 1 ? ($product->pro_barcode ?? '') : implode(',', array_filter(array_merge([$product->pro_barcode], $product->variables->pluck('pro_barcode')->toArray()))) }}" data-id="{{ $product->id }}" data-type="{{ $product->type }}">
                            <div class="pos_product_item cart_add" data-id="{{$product->id}}" data-type="{{$product->type}}">
                                <img src="{{asset($product->image ? $product->image->image : 'public/images/no-image.png')}}" class="pos_product_img" alt="{{$product->name}}">
                                <div class="pos_product_content">
                                    <h6 class="pos_product_title" title="{{$product->name}}">{{$product->name}}</h6>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <span class="pos_product_price">{{$product->new_price}}</span>
                                        <small class="text-muted">Stk: {{ $product->type == 0 ? ($product->variables_sum_stock ?? 0) : $product->stock }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Cart -->
        <div class="col-lg-5">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0"><i class="fas fa-shopping-cart me-1"></i> Current Order</h5>
                        <button type="button" class="btn btn-danger btn-sm cartclear"><i class="fas fa-trash-alt"></i> Clear</button>
                    </div>

                    <!-- Customer Selection -->
                    <form action="{{route('admin.order.store')}}" method="POST" class="pos_form" data-parsley-validate="" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                             <div class="form-check mb-2">
                              <label class="form-check-label" for="guest_customer" >
                                Guest Customer
                              </label>
                              <input class="form-check-input" type="checkbox" name="guest_customer" value="1" id="guest_customer">
                            </div>

                            <div class="new_customer">
                                <div class="row">
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" name="name" placeholder="Customer Name">
                                    </div>
                                    <div class="col-md-6 mb-2">
                                        <input type="text" class="form-control" name="phone" placeholder="Phone Number">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <input type="text" class="form-control" name="address" placeholder="Address">
                                    </div>
                                    <div class="col-md-12 mb-2">
                                        <select class="form-control select2" name="area" id="area">
                                            <option value="">Select Shipping Area...</option>
                                            @foreach($shippingcharge as $shipping)
                                            <option value="{{$shipping->id}}">{{$shipping->name}} - {{$shipping->amount}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Items -->
                        <div class="cart-table-container">
                            <table class="table table-sm table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 40%">Item</th>
                                        <th style="width: 30%">Qty</th>
                                        <th style="width: 20%">Price</th>
                                        <th style="width: 10%">x</th>
                                    </tr>
                                </thead>
                                <tbody id="cartTable">
                                    <!-- Loaded via AJAX -->
                                    @include('backEnd.order.cart_content')
                                </tbody>
                            </table>
                        </div>

                        <!-- Cart Summary -->
                        <div class="cart-summary bg-light p-3 mt-3">
                            <div id="cart_details">
                                <!-- Loaded via AJAX -->
                                @include('backEnd.order.cart_details')
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="fas fa-check-circle me-1"></i> Place Order</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script src="{{asset('public/backEnd/')}}/assets/libs/parsleyjs/parsley.min.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/js/pages/form-validation.init.js"></script>
<script src="{{asset('public/backEnd/')}}/assets/libs/select2/js/select2.min.js"></script>
<!-- Variable Product Modal -->
<div class="modal fade" id="variableModal" tabindex="-1" role="dialog" aria-labelledby="variableModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="variableModalLabel">Select Variation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="variable_options" class="list-group">
                    <!-- Options will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $(".select2").select2();

        // Guest Customer Toggle
        $('#guest_customer').change(function() {
            if ($(this).is(':checked')) {
                $('.new_customer').hide();
                $('.new_customer input').removeAttr('required');
            } else {
                $('.new_customer').show();
                $('.new_customer input').attr('required', 'required');
            }
        });

        // Live Search — filters grid by name or barcode, and auto-adds on exact barcode match (either automatically on typing, or on Enter key)
        var isSearchingBarcode = false;

        // Auto-add product to cart if exact barcode matches
        $('#search_product').on('input', function() {
            if (isSearchingBarcode) return;
            var value = $(this).val().trim();
            if (value === '') return;

            var exactMatch = false;
            $("#product_grid .product-item-wrapper").each(function() {
                var codeAttr = $(this).data('code');
                if (codeAttr !== undefined && codeAttr !== null) {
                    var codes = codeAttr.toString().split(',');
                    if (codes.includes(value)) {
                        exactMatch = true;
                        return false; // break loop
                    }
                }
            });

            if (exactMatch) {
                isSearchingBarcode = true;
                $.ajax({
                    type: "GET",
                    data: { keyword: value },
                    url: "{{ route('admin.livesearch') }}",
                    success: function(response) {
                        isSearchingBarcode = false;
                        if (response.status === 'success') {
                            cart_content();
                            cart_details();
                            $('#search_product').val(''); // Clear input
                            $("#product_grid .product-item-wrapper").show(); // Show all products
                            toastr.success(response.message || "Product added to cart");
                        } else if (response.status === 'limitover') {
                            toastr.error(response.message || "Stock limit over");
                        }
                    },
                    error: function() {
                        isSearchingBarcode = false;
                    }
                });
            }
        });

        $('#search_product').on('keypress', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                e.preventDefault();
                if (isSearchingBarcode) return;
                var value = $(this).val().trim();
                if (value === '') return;

                isSearchingBarcode = true;
                // Send to backend to add this barcode item to the cart
                $.ajax({
                    type: "GET",
                    data: { keyword: value },
                    url: "{{ route('admin.livesearch') }}",
                    success: function(response) {
                        isSearchingBarcode = false;
                        if (response.status === 'success') {
                            cart_content();
                            cart_details();
                            $('#search_product').val(''); // Clear input
                            $("#product_grid .product-item-wrapper").show(); // Show all products
                            toastr.success(response.message || "Product added to cart");
                        } else if (response.status === 'limitover') {
                            toastr.error(response.message || "Stock limit over");
                        } else {
                            toastr.error("Barcode not found");
                        }
                    },
                    error: function() {
                        isSearchingBarcode = false;
                        toastr.error("Error adding product");
                    }
                });
            }
        });

        $('#search_product').on('keyup', function(e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                return;
            }
            var value = $(this).val().trim();
            var valueLower = value.toLowerCase();

            // Regular keyup: filter the grid
            $("#product_grid .product-item-wrapper").filter(function() {
                var nameMatch = $(this).data('name').indexOf(valueLower) > -1;
                
                var codeAttr = $(this).data('code');
                var codeMatch = false;
                if (codeAttr !== undefined && codeAttr !== null) {
                    var codes = codeAttr.toString().split(',');
                    codeMatch = codes.some(function(code) {
                        return code.indexOf(value) > -1;
                    });
                }
                
                $(this).toggle(nameMatch || codeMatch);
            });
        });

        // Category Filter
        $('.category-btn').click(function() {
            $('.category-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');
            
            var categoryId = $(this).data('id');
            if (categoryId == 'all') {
                $('.product-item-wrapper').show();
            } else {
                $('.product-item-wrapper').hide();
                 $("#product_grid .product-item-wrapper").filter(function() {
                    $(this).toggle($(this).data('category') == categoryId);
                });
            }
        });

        // Add to Cart (Variable Product Support)
        $(document).on("click", ".cart_add", function (e) {
            e.preventDefault();
            var id = $(this).data('id');
            var type = $(this).data('type'); 
            
            if (id) {
                if(type == 0 || type == '0') {
                     $.ajax({
                        type: "GET",
                        url: "{{route('admin.product_variable_search')}}",
                        data: { id: id },
                        dataType: "json",
                        success: function (response) {
                            if (response.status == 'success') {
                                // It is a variable product
                                 var html = '';
                                    $.each(response.data, function(index, item) {
                                         var imageSrc = item.image ? location.origin + '/' + item.image : response.product_image;
                                        
                                        html += '<a href="#" class="list-group-item list-group-item-action variable-select" ' +
                                                'data-id="' + id + '" ' +
                                                'data-size="' + (item.size || '') + '" ' +
                                                'data-color="' + (item.color || '') + '">' +
                                                '<div class="d-flex w-100 justify-content-between align-items-center">' +
                                                '<div class="d-flex align-items-center">' +
                                                '<img src="' + imageSrc + '" style="width: 40px; height: 40px; object-fit: cover; margin-right: 10px;" class="rounded">' +
                                                '<div>' +
                                                '<h6 class="mb-0">' + (item.size ? 'Size: ' + item.size : '') + (item.size && item.color ? ' | ' : '') + (item.color ? 'Color: ' + item.color : '') + '</h6>' +
                                                '<small>Stock: ' + item.stock + '</small>' +
                                                '</div>' +
                                                '</div>' +
                                                '<span class="badge badge-primary badge-pill">' + item.new_price + '</span>' +
                                                '</div>' +
                                                '</a>';
                                    });
                                    $('#variable_options').html(html);
                                    $('#variableModal').modal('show');
                            } else {
                                // Failed - Add directly
                                 addToCart(id, '', '');
                            }
                        }
                    });
                } else {
                    // Simple Product - Add directly
                     addToCart(id, '', '');
                }
            }
        });

        // Handle Variable Selection from Modal
        $(document).on("click", ".variable-select", function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            var size = $(this).data('size');
            var color = $(this).data('color');
            
            addToCart(id, size, color);
            $('#variableModal').modal('hide');
        });

        function addToCart(id, size, color) {
            $.ajax({
                cache: false,
                type: "GET",
                data: { id: id, size: size, color: color },
                url: "{{route('admin.order.cart_add')}}",
                dataType: "json",
                success: function (cartinfo) {
                    cart_content();
                    cart_details();
                },
                error: function() {
                    toastr.error("Failed to add to cart");
                }
            });
        }
            
        // Cart Actions (delegated events)
        $(document).on('click', '.cart_increment', function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            var qty = $(this).val();
            $.ajax({
                url: "{{route('admin.order.cart_increment')}}",
                data: {id: id, qty: qty},
                success: function(r){ cart_content(); cart_details(); }
            });
        });

        $(document).on('click', '.cart_decrement', function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            var qty = $(this).val();
            $.ajax({
                url: "{{route('admin.order.cart_decrement')}}",
                data: {id: id, qty: qty},
                success: function(r){ cart_content(); cart_details(); }
            });
        });

        $(document).on('click', '.cart_remove', function(e) {
            e.preventDefault();
            var id = $(this).data("id");
            $.ajax({
                url: "{{route('admin.order.cart_remove')}}",
                data: {id: id},
                success: function(r){ cart_content(); cart_details(); }
            });
        });

        $(document).on('click', '.cartclear', function(e) {
            $.ajax({
                url: "{{route('admin.order.cart_clear')}}",
                success: function(r){ cart_content(); cart_details(); }
            });
        });
        
        $(document).on('change', '.product_discount', function() {
                var id = $(this).data("id");
                var discount = $(this).val();
                $.ajax({
                    url: "{{route('admin.order.product_discount')}}",
                    data: {id: id, discount: discount},
                    success: function(r){ cart_content(); cart_details(); }
                });
        });

        $("#area").on("change", function () {
            var id = $(this).val();
            $.ajax({
                type: "GET",
                data: { id: id },
                url: "{{route('admin.order.cart_shipping')}}",
                success: function(cartinfo){
                    cart_content(); 
                    cart_details();
                }
            });
        });
    });

    function cart_content() {
        $.ajax({
            type: "GET",
            url: "{{route('admin.order.cart_content')}}",
            dataType: "html",
            success: function(cartinfo) {
                $("#cartTable").html(cartinfo);
            },
        });
    }

    function cart_details() {
        $.ajax({
            type: "GET",
            url: "{{route('admin.order.cart_details')}}",
            dataType: "html",
            success: function(cartinfo) {
                $("#cart_details").html(cartinfo);
            },
        });
    }
</script>
@endsection
