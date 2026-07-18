@extends('backEnd.layouts.master')
@section('title', 'Edit Order #' . $order->invoice_id)
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
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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

        /* Order Edit Info Banner */
        .order-edit-info {
            background: linear-gradient(135deg, #f0f4ff 0%, #e8f0fe 100%);
            border: 1px solid #c8d6e5;
            border-radius: 8px;
            padding: 12px 16px;
        }

        .status-badge {
            font-size: 0.8rem;
            padding: 4px 12px;
            border-radius: 20px;
        }
    </style>
    <link href="{{asset('public/backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-fluid">
        <!-- Page Header -->
        <div class="row align-items-center mb-3"
            style="background: #fff; padding: 12px 15px; border-radius: 8px; border: 1px solid #eee; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
            <div class="col-6">
                <h4 class="mb-0 text-dark fw-bold"><i class="fas fa-edit text-primary me-2"></i> Edit Order #{{ $order->invoice_id }}</h4>
            </div>
            <div class="col-6 text-end">
                <a href="{{ route('admin.order.invoice', ['invoice_id' => $order->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3" target="_blank">
                    <i class="fas fa-file-invoice me-1"></i> Invoice
                </a>
                <a href="{{ route('admin.orders', ['slug' => 'all']) }}" class="btn btn-success btn-sm rounded-pill px-3 ms-2">
                    <i class="fas fa-list me-1"></i> Orders
                </a>
                <a href="{{ url('admin/dashboard') }}" class="btn btn-danger btn-sm rounded-pill px-3 ms-2">
                    <i class="fas fa-sign-out-alt me-1"></i> Exit
                </a>
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
                                <input type="text" id="search_product" class="form-control"
                                    placeholder="Search product by name or barcode..." autofocus>
                            </div>
                            <div class="col-md-12">
                                <div class="d-flex flex-wrap">
                                    <button class="btn btn-outline-primary category-btn active" data-id="all">All</button>
                                    @foreach($categories as $category)
                                        <button class="btn btn-outline-secondary category-btn"
                                            data-id="{{$category->id}}">{{$category->name}}</button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Product Grid -->
                        <div class="row" id="product_grid">
                            @foreach($products as $product)
                                <div class="col-md-3 col-sm-4 col-6 mb-3 product-item-wrapper"
                                    data-category="{{ $product->category_id ?? 'all' }}"
                                    data-name="{{ strtolower($product->name) }}"
                                    data-code="{{ $product->type == 1 ? ($product->pro_barcode ?? '') : implode(',', array_filter(array_merge([$product->pro_barcode], $product->variables->pluck('pro_barcode')->toArray()))) }}"
                                    data-id="{{ $product->id }}" data-type="{{ $product->type }}">
                                    <div class="pos_product_item cart_add" data-id="{{$product->id}}"
                                        data-type="{{$product->type}}">
                                        <img src="{{asset($product->image ? $product->image->image : 'public/images/no-image.png')}}"
                                            class="pos_product_img" alt="{{$product->name}}">
                                        <div class="pos_product_content">
                                            <h6 class="pos_product_title" title="{{$product->name}}">{{$product->name}}</h6>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="pos_product_price">{{$product->new_price}}</span>
                                                <small class="text-muted">Stk:
                                                    {{ $product->type == 0 ? ($product->variables_sum_stock ?? 0) : $product->stock }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Cart & Order Management -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0"><i class="fas fa-shopping-cart me-1"></i> Order Items</h5>
                            <button type="button" class="btn btn-danger btn-sm cartclear"><i
                                    class="fas fa-trash-alt me-1"></i> Clear Cart</button>
                        </div>

                        <!-- Customer Selection -->
                        <form action="{{route('admin.order.pos_update')}}" method="POST" class="pos_form"
                            data-parsley-validate="" enctype="multipart/form-data">
                            @csrf
                            <input type="hidden" name="order_id" value="{{$order->id}}">
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <label class="form-check-label" for="guest_customer">
                                        Guest Customer
                                    </label>
                                    <input class="form-check-input" type="checkbox" name="guest_customer" value="1"
                                        id="guest_customer" {{ ($order->customer_id == 1 || ($order->shipping && $order->shipping->address == 'POS' && empty($order->shipping->name))) ? 'checked' : '' }}>
                                </div>

                                <div class="new_customer">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" name="name" placeholder="Customer Name" value="{{ $order->shipping ? $order->shipping->name : '' }}">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" name="phone" placeholder="Phone Number" value="{{ $order->shipping ? $order->shipping->phone : '' }}">
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

                            <!-- Discount customization input -->
                            <div class="row g-2 mt-3 align-items-center">
                                <div class="col-6">
                                    <span class="fw-semibold text-muted" style="font-size: 0.85rem;"><i
                                            class="fas fa-tag me-1"></i> Order Discount</span>
                                </div>
                                <div class="col-3">
                                    <input type="number" id="pos_discount_input"
                                        class="form-control form-control-sm text-end fw-bold"
                                        value="{{ Session::get('pos_discount') ?? 0 }}" min="0" placeholder="0"
                                        style="font-size: 0.9rem;">
                                </div>
                                <div class="col-3">
                                    <select id="pos_discount_type"
                                        class="form-select form-select-sm bg-light fw-semibold text-muted"
                                        style="font-size: 0.85rem;">
                                        <option value="flat" {{ (Session::get('pos_discount_type') ?? 'flat') == 'flat' ? 'selected' : '' }}>Flat (৳)</option>
                                        <option value="percent" {{ (Session::get('pos_discount_type') ?? 'flat') == 'percent' ? 'selected' : '' }}>Percent (%)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Cart Summary -->
                            <div class="cart-summary bg-light p-3 mt-2">
                                <div id="cart_details">
                                    <!-- Loaded via AJAX -->
                                    @include('backEnd.order.cart_details')
                                </div>
                            </div>


                            <!-- Exchange Adjustment Section -->
                            <div class="card border-info mt-3" id="exchange_settlement_card">
                                <div class="card-header bg-soft-info text-dark py-2 d-flex justify-content-between align-items-center" style="background: rgba(13, 202, 240, 0.1);">
                                    <h6 class="mb-0 fw-bold text-info"><i class="fas fa-random me-1"></i> Exchange & Settlement</h6>
                                    <span class="badge bg-info text-white" id="exchange_mode_badge">Exchange / Return</span>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row g-2 mb-2" style="font-size: 0.85rem;">
                                        <div class="col-6 text-muted">Original Paid Amount:</div>
                                        <div class="col-6 text-end fw-bold text-dark" id="exchange_original_paid">৳ 0.00</div>
                                    </div>
                                    <div class="row g-2 mb-2" style="font-size: 0.85rem;">
                                        <div class="col-6 text-muted">New Order Total:</div>
                                        <div class="col-6 text-end fw-bold text-dark" id="exchange_new_total">৳ 0.00</div>
                                    </div>
                                    <hr class="my-2">
                                    
                                    <!-- Status Message/Difference -->
                                    <div class="mb-3" id="exchange_diff_status"></div>

                                    <!-- Hidden Inputs to submit diff details -->
                                    <input type="hidden" name="payment_difference" id="exchange_difference_input" value="0">

                                    <!-- Payment Collection Fields (If Difference > 0) -->
                                    <div id="exchange_payment_collection_fields" style="display:none;">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">Difference Payment Method</label>
                                            <select name="diff_payment_method" class="form-select form-select-sm">
                                                <option value="Cash">Cash</option>
                                                <option value="Card">Card</option>
                                                <option value="bKash">bKash</option>
                                                <option value="Nagad">Nagad</option>
                                                <option value="Rocket">Rocket</option>
                                            </select>
                                        </div>
                                        <div class="row g-2 align-items-center">
                                            <div class="col-6">
                                                <label class="form-label small fw-semibold text-muted mb-1">Received Amt</label>
                                                <input type="number" step="any" name="diff_received_amount" id="diff_received_amount" class="form-control form-control-sm text-end fw-bold text-success" placeholder="0.00">
                                            </div>
                                            <div class="col-6 text-end">
                                                <span class="small fw-semibold text-muted d-block mb-1">Change Return</span>
                                                <span class="fw-bold text-danger fs-5 d-block" id="diff_change_amount_text">৳ 0.00</span>
                                                <input type="hidden" name="diff_change_amount" id="diff_change_amount_val" value="0">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Refund Fields (If Difference < 0) -->
                                    <div id="exchange_refund_fields" style="display:none;">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">Refund Method</label>
                                            <select name="diff_payment_method" class="form-select form-select-sm">
                                                <option value="Cash">Cash</option>
                                                <option value="bKash">bKash Mobile Wallet</option>
                                                <option value="Nagad">Nagad Mobile Wallet</option>
                                                <option value="Rocket">Rocket Mobile Wallet</option>
                                                <option value="Adjust as Discount">Adjust as Discount</option>
                                            </select>
                                        </div>
                                        <div class="alert alert-soft-success py-1 small mb-0 mt-2" style="background-color: rgba(40, 167, 69, 0.1); border-color: rgba(40, 167, 69, 0.2); color: #155724;">
                                            <i class="fas fa-check-circle me-1"></i> Refund will be automatically adjusted in payments ledger.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Order Edit Action Buttons -->
                            <div class="row g-2 mt-3">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold" id="btn-update-order">
                                        <i class="fas fa-save d-block mb-1 fs-5"></i> Update Order
                                    </button>
                                </div>
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
    <div class="modal fade" id="variableModal" tabindex="-1" role="dialog" aria-labelledby="variableModalLabel"
        aria-hidden="true">
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
        $(document).ready(function () {
            $(".select2").select2();

            // Guest Customer Toggle
            $('#guest_customer').change(function () {
                if ($(this).is(':checked')) {
                    $('.new_customer').hide();
                    $('.new_customer input').removeAttr('required');
                } else {
                    $('.new_customer').show();
                    $('.new_customer input').attr('required', 'required');
                }
            }).trigger('change');

            // Live Search — filters grid by name or barcode, and auto-adds on exact barcode match
            var isSearchingBarcode = false;

            // Auto-add product to cart if exact barcode matches
            $('#search_product').on('input', function () {
                if (isSearchingBarcode) return;
                var value = $(this).val().trim();
                if (value === '') return;

                var exactMatch = false;
                $("#product_grid .product-item-wrapper").each(function () {
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
                        success: function (response) {
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
                        error: function () {
                            isSearchingBarcode = false;
                        }
                    });
                }
            });

            $('#search_product').on('keypress', function (e) {
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
                        success: function (response) {
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
                        error: function () {
                            isSearchingBarcode = false;
                            toastr.error("Error adding product");
                        }
                    });
                }
            });

            $('#search_product').on('keyup', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    return;
                }
                var value = $(this).val().trim();
                var valueLower = value.toLowerCase();

                // Regular keyup: filter the grid
                $("#product_grid .product-item-wrapper").filter(function () {
                    var nameMatch = $(this).data('name').indexOf(valueLower) > -1;

                    var codeAttr = $(this).data('code');
                    var codeMatch = false;
                    if (codeAttr !== undefined && codeAttr !== null) {
                        var codes = codeAttr.toString().split(',');
                        codeMatch = codes.some(function (code) {
                            return code.indexOf(value) > -1;
                        });
                    }

                    $(this).toggle(nameMatch || codeMatch);
                });
            });

            // Category Filter
            $('.category-btn').click(function () {
                $('.category-btn').removeClass('active btn-primary').addClass('btn-outline-secondary');
                $(this).removeClass('btn-outline-secondary').addClass('active btn-primary');

                var categoryId = $(this).data('id');
                if (categoryId == 'all') {
                    $('.product-item-wrapper').show();
                } else {
                    $('.product-item-wrapper').hide();
                    $("#product_grid .product-item-wrapper").filter(function () {
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
                    if (type == 0 || type == '0') {
                        $.ajax({
                            type: "GET",
                            url: "{{route('admin.product_variable_search')}}",
                            data: { id: id },
                            dataType: "json",
                            success: function (response) {
                                if (response.status == 'success') {
                                    // It is a variable product
                                    var html = '';
                                    $.each(response.data, function (index, item) {
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
            $(document).on("click", ".variable-select", function (e) {
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
                    error: function () {
                        toastr.error("Failed to add to cart");
                    }
                });
            }

            // Cart Actions (delegated events)
            $(document).on('click', '.cart_increment', function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                var qty = $(this).val();
                $.ajax({
                    url: "{{route('admin.order.cart_increment')}}",
                    data: { id: id, qty: qty },
                    success: function (r) {
                        if (r.status === 'limitover') {
                            toastr.error(r.message);
                        } else {
                            cart_content();
                            cart_details();
                        }
                    }
                });
            });

            $(document).on('click', '.cart_decrement', function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                var qty = $(this).val();
                $.ajax({
                    url: "{{route('admin.order.cart_decrement')}}",
                    data: { id: id, qty: qty },
                    success: function (r) { cart_content(); cart_details(); }
                });
            });

            $(document).on('click', '.cart_remove', function (e) {
                e.preventDefault();
                var id = $(this).data("id");
                $.ajax({
                    url: "{{route('admin.order.cart_remove')}}",
                    data: { id: id },
                    success: function (r) { cart_content(); cart_details(); }
                });
            });

            $(document).on('click', '.cartclear', function (e) {
                $.ajax({
                    url: "{{route('admin.order.cart_clear')}}",
                    success: function (r) { cart_content(); cart_details(); }
                });
            });

            $(document).on('change', '.product_discount', function () {
                var id = $(this).data("id");
                var discount = $(this).val();
                $.ajax({
                    url: "{{route('admin.order.product_discount')}}",
                    data: { id: id, discount: discount },
                    success: function (r) { cart_content(); cart_details(); }
                });
            });

            $("#area").on("change", function () {
                var id = $(this).val();
                $.ajax({
                    type: "GET",
                    data: { id: id },
                    url: "{{route('admin.order.cart_shipping')}}",
                    success: function (cartinfo) {
                        cart_content();
                        cart_details();
                    }
                });
            });

            // ── Cart Discount Event Listener ──
            $(document).on('change keyup', '#pos_discount_input, #pos_discount_type', function () {
                var discount = $('#pos_discount_input').val();
                var type = $('#pos_discount_type').val();
                if (discount === '' || discount < 0) discount = 0;
                $.ajax({
                    url: "{{route('admin.order.pos_discount')}}",
                    data: { discount: discount, type: type },
                    success: function (r) {
                        cart_details();
                    }
                });
            });

            // ── Exchange Difference Calculations ──
            var originalPaid = parseFloat("{{ $order->payments->sum('amount') }}") || 0;
            
            window.calculateExchangeDifference = function() {
                var newTotal = parseFloat($('#cart_total_payable_val').data('value')) || 0;
                var diff = newTotal - originalPaid;

                $('#exchange_original_paid').text('৳ ' + originalPaid.toFixed(2));
                $('#exchange_new_total').text('৳ ' + newTotal.toFixed(2));
                $('#exchange_difference_input').val(diff.toFixed(2));

                if (diff > 0) {
                    $('#exchange_diff_status').html('<div class="alert alert-warning py-2 mb-0 fw-semibold" style="background-color: rgba(255, 193, 7, 0.1); border-color: rgba(255, 193, 7, 0.2); color: #856404;"><i class="fas fa-exclamation-triangle me-1"></i> Extra Payment Due: ৳ ' + diff.toFixed(2) + '</div>');
                    $('#exchange_payment_collection_fields').show();
                    $('#exchange_refund_fields').hide();
                    $('#diff_received_amount').attr('required', true);
                    updateDiffChange();
                } else if (diff < 0) {
                    var refundAmt = Math.abs(diff);
                    $('#exchange_diff_status').html('<div class="alert alert-info py-2 mb-0 fw-semibold" style="background-color: rgba(23, 162, 184, 0.1); border-color: rgba(23, 162, 184, 0.2); color: #0c5460;"><i class="fas fa-info-circle me-1"></i> Refund to Customer: ৳ ' + refundAmt.toFixed(2) + '</div>');
                    $('#exchange_payment_collection_fields').hide();
                    $('#exchange_refund_fields').show();
                    $('#diff_received_amount').removeAttr('required').val('');
                } else {
                    $('#exchange_diff_status').html('<div class="alert alert-success py-2 mb-0 fw-semibold" style="background-color: rgba(40, 167, 69, 0.1); border-color: rgba(40, 167, 69, 0.2); color: #155724;"><i class="fas fa-check-circle me-1"></i> Balanced (No adjustment needed)</div>');
                    $('#exchange_payment_collection_fields').hide();
                    $('#exchange_refund_fields').hide();
                    $('#diff_received_amount').removeAttr('required').val('');
                }
            };

            window.updateDiffChange = function() {
                var diff = parseFloat($('#exchange_difference_input').val()) || 0;
                var received = parseFloat($('#diff_received_amount').val()) || 0;
                if (diff > 0) {
                    var change = Math.max(0, received - diff);
                    $('#diff_change_amount_text').text('৳ ' + change.toFixed(2));
                    $('#diff_change_amount_val').val(change.toFixed(2));
                    if (received >= diff) {
                        $('#diff_change_amount_text').removeClass('text-danger').addClass('text-success');
                    } else {
                        $('#diff_change_amount_text').removeClass('text-success').addClass('text-danger');
                    }
                }
            };

            $(document).on('input', '#diff_received_amount', function () {
                updateDiffChange();
            });

            // Initial call
            setTimeout(calculateExchangeDifference, 300);

            // ── AJAX Order Update Submission ──
            $(document).on('submit', '.pos_form', function (e) {
                e.preventDefault();

                var form = $(this);
                // Validate form using Parsley if it's initialized
                if (form.parsley && !form.parsley().isValid()) {
                    return;
                }

                // Check difference and received amount
                var diff = parseFloat($('#exchange_difference_input').val()) || 0;
                if (diff > 0) {
                    var received = parseFloat($('#diff_received_amount').val()) || 0;
                    if (received < diff) {
                        toastr.error("Received amount must be equal to or greater than the due difference!");
                        return;
                    }
                }

                var url = form.attr('action');
                var formData = new FormData(this);

                // Disable the submit button to prevent double-clicks
                $('#btn-update-order').prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Updating...');

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message || "Order updated successfully!");

                            // Redirect back to order list after brief delay
                            setTimeout(function() {
                                window.location.href = "{{ route('admin.orders', ['slug' => 'all']) }}";
                            }, 1000);
                        } else {
                            toastr.error(response.message || "Failed to update order.");
                            $('#btn-update-order').prop('disabled', false).html('<i class="fas fa-save d-block mb-1 fs-5"></i> Update Order');
                        }
                    },
                    error: function (xhr) {
                        var errors = xhr.responseJSON ? xhr.responseJSON.errors : null;
                        if (errors) {
                            var errorMsg = '';
                            $.each(errors, function (key, val) {
                                errorMsg += val[0] + '<br>';
                            });
                            toastr.error(errorMsg);
                        } else {
                            toastr.error("An error occurred while updating the order.");
                        }
                        $('#btn-update-order').prop('disabled', false).html('<i class="fas fa-save d-block mb-1 fs-5"></i> Update Order');
                    }
                });
            });
        });

        function cart_content() {
            $.ajax({
                type: "GET",
                url: "{{route('admin.order.cart_content')}}",
                dataType: "html",
                success: function (cartinfo) {
                    $("#cartTable").html(cartinfo);
                },
            });
        }

        function cart_details() {
            $.ajax({
                type: "GET",
                url: "{{route('admin.order.cart_details')}}",
                dataType: "html",
                success: function (cartinfo) {
                    $("#cart_details").html(cartinfo);
                    if (typeof calculateExchangeDifference === 'function') {
                        calculateExchangeDifference();
                    }
                },
            });
        }
    </script>
@endsection