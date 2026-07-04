@extends('backEnd.layouts.master')
@section('title', 'POS Create')
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

        /* POS Full Screen Customizations (Standalone Mode) */
        @media (min-width: 992px) {
            body.pos-standalone-mode {
                overflow: hidden !important;
                height: 100vh !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            body.pos-standalone-mode #wrapper {
                height: 100vh !important;
                overflow: hidden !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            body.pos-standalone-mode .left-side-menu {
                display: none !important;
            }

            body.pos-standalone-mode .navbar-custom {
                display: none !important;
            }

            body.pos-standalone-mode .footer {
                display: none !important;
            }

            body.pos-standalone-mode .content-page {
                margin: 0 !important;
                padding: 0 !important;
                height: 100vh !important;
                overflow: hidden !important;
            }

            body.pos-standalone-mode .content {
                height: 100vh !important;
                padding: 0 !important;
                margin: 0 !important;
                overflow: hidden !important;
            }

            body.pos-standalone-mode .container-fluid {
                height: 100% !important;
                padding: 0 !important;
                /* Absolute zero margins/padding */
                margin: 0 !important;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            /* Target the main POS row */
            body.pos-standalone-mode .container-fluid>.row:last-child {
                flex: 1;
                height: calc(100% - 60px) !important;
                margin: 0 !important;
                padding: 10px !important;
                /* Keep margins on the cards grid */
                overflow: hidden;
                display: flex;
            }

            body.pos-standalone-mode .container-fluid>.row:last-child>div[class^="col-"] {
                height: 100% !important;
                padding: 0 5px !important;
                display: flex;
                flex-direction: column;
            }

            body.pos-standalone-mode .container-fluid>.row:last-child>div[class^="col-"]>.card {
                height: 100% !important;
                margin-bottom: 0 !important;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            body.pos-standalone-mode .container-fluid>.row:last-child>div[class^="col-"]>.card>.card-body {
                flex: 1;
                padding: 15px !important;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            /* Left Column Scroll Lock */
            body.pos-standalone-mode #product_grid {
                flex: 1;
                overflow-y: auto !important;
                margin-top: 10px;
                padding-bottom: 20px;
            }

            /* Right Column Scroll Lock */
            body.pos-standalone-mode .pos_form {
                flex: 1;
                display: flex;
                flex-direction: column;
                overflow: hidden;
            }

            body.pos-standalone-mode .cart-table-container {
                flex: 1;
                overflow-y: auto !important;
                max-height: none !important;
                border: 1px solid #eee;
                border-radius: 4px;
                margin-bottom: 10px;
            }
        }

        @media (max-width: 991.98px) {
            body.pos-standalone-mode .content-page {
                padding-top: 0 !important;
            }
        }

        body.pos-standalone-mode .left-side-menu {
            display: none !important;
        }

        body.pos-standalone-mode .footer {
            display: none !important;
        }

        body.pos-standalone-mode #wrapper {
            padding-left: 0 !important;
        }

        body.pos-standalone-mode .page-title-box {
            display: none !important;
        }

        #pos-page-header {
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            background: #fff !important;
        }

        body.pos-standalone-mode #pos-page-header {
            top: 0;
            margin: 0 !important;
            border-radius: 0 !important;
            border-left: none !important;
            border-right: none !important;
            border-top: none !important;
        }

        body:not(.pos-standalone-mode) #pos-page-header {
            top: 70px;
            margin: 15px 0 !important;
        }
    </style>
    <link href="{{asset('public/backEnd')}}/assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
@endsection

@section('content')
    <div class="container-fluid">
        <!-- POS Header Row -->
        <div id="pos-page-header" class="row align-items-center mb-2"
            style="flex-shrink: 0; background: #fff; padding: 10px 15px; border-radius: 8px; border: 1px solid #eee;">
            <div class="col-6">
                <h4 class="mb-0 text-dark fw-bold"><i class="fas fa-calculator text-primary me-2"></i> POS - Point of Sale
                </h4>
            </div>
            <div class="col-6 text-end">
                <button id="pos-fullscreen-btn" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-expand me-1"></i> Full Screen
                </button>
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

            <!-- Right Column: Cart -->
            <div class="col-lg-5">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="card-title mb-0"><i class="fas fa-shopping-cart me-1"></i> Current Order</h5>
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-warning btn-sm text-white" id="hold-order-btn"><i
                                        class="fas fa-pause me-1"></i> Hold</button>
                                <button type="button" class="btn btn-info btn-sm position-relative ms-1 me-1 text-white"
                                    id="view-holds-btn">
                                    <i class="fas fa-list me-1"></i> Holds
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        id="holds-count">0</span>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm cartclear"><i
                                        class="fas fa-trash-alt"></i> Clear</button>
                            </div>
                        </div>

                        <!-- Customer Selection -->
                        <form action="{{route('admin.order.store')}}" method="POST" class="pos_form"
                            data-parsley-validate="" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <div class="form-check mb-2">
                                    <label class="form-check-label" for="guest_customer">
                                        Guest Customer
                                    </label>
                                    <input class="form-check-input" type="checkbox" name="guest_customer" value="1"
                                        id="guest_customer" checked>
                                </div>

                                <div class="new_customer">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" name="name" placeholder="Customer Name">
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <input type="text" class="form-control" name="phone" placeholder="Phone Number">
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

                            <!-- POS Payment Status Summary -->
                            <div class="row text-center mt-3 py-2 bg-light rounded g-0 border">
                                <div class="col-4 border-end">
                                    <div class="text-muted small fw-semibold">Total Payable</div>
                                    <div class="fs-5 fw-bold text-dark" id="summary_total">৳ 0</div>
                                </div>
                                <div class="col-4 border-end">
                                    <div class="text-muted small fw-semibold">Paid Amount</div>
                                    <div class="fs-5 fw-bold text-success" id="summary_paid">৳ 0</div>
                                </div>
                                <div class="col-4">
                                    <div class="text-muted small fw-semibold">Due Amount</div>
                                    <div class="fs-5 fw-bold text-danger" id="summary_due">৳ 0</div>
                                </div>
                            </div>

                            <!-- Hidden inputs to submit payment details -->
                            <input type="hidden" name="payment_method" id="input_payment_method" value="Cash">
                            <input type="hidden" name="payment_status" id="input_payment_status" value="paid">
                            <input type="hidden" name="paid_amount" id="input_paid_amount" value="0">
                            <input type="hidden" name="received_amount" id="input_received_amount" value="0">
                            <input type="hidden" name="change_amount" id="input_change_amount" value="0">
                            <!-- Container for split payments inputs -->
                            <div id="split_payments_inputs" style="display:none;"></div>

                            <!-- POS Payment Action Buttons -->
                            <div class="row g-2 mt-3">
                                <div class="col-4">
                                    <button type="button" class="btn btn-info text-white w-100 py-3 fw-bold btn-payment-action" id="btn-payment-multiple">
                                        <i class="fas fa-credit-card d-block mb-1 fs-5"></i> Multiple
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn btn-success w-100 py-3 fw-bold btn-payment-action" id="btn-payment-cash">
                                        <i class="fas fa-money-bill-wave d-block mb-1 fs-5"></i> Cash
                                    </button>
                                </div>
                                <div class="col-4">
                                    <button type="button" class="btn w-100 py-3 fw-bold text-white btn-payment-action" id="btn-payment-payall" style="background-color: #6f42c1;">
                                        <i class="fas fa-money-bill-alt d-block mb-1 fs-5"></i> Pay All
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

    <!-- Holds List Modal -->
    <div class="modal fade" id="holdsModal" tabindex="-1" role="dialog" aria-labelledby="holdsModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white" id="holdsModalLabel"><i class="fas fa-pause me-2"></i> Held Orders
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-striped mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">Hold Note / ID</th>
                                    <th>Date/Time</th>
                                    <th>Customer</th>
                                    <th>Items</th>
                                    <th>Total</th>
                                    <th class="pe-3 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody id="holds-list-tbody">
                                <!-- Populated by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cash Payment Modal -->
    <div class="modal fade" id="cashPaymentModal" tabindex="-1" role="dialog" aria-labelledby="cashPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title text-white" id="cashPaymentModalLabel"><i class="fas fa-money-bill-wave me-2"></i> Cash Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <span class="text-muted d-block small">Total Payable</span>
                        <h2 class="fw-bold text-dark" id="cash_modal_payable">৳ 0.00</h2>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Cash Received</label>
                        <input type="number" class="form-control form-control-lg text-end fw-bold text-success" id="cash_received_input" placeholder="0.00" min="0" step="any" autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Quick Denominations</label>
                        <div class="d-flex flex-wrap gap-2" id="quick_cash_denominations">
                            <!-- Populated dynamically based on total -->
                        </div>
                    </div>
                    <div class="p-3 bg-light rounded text-center mb-3">
                        <span class="text-muted d-block small">Change Return</span>
                        <h3 class="fw-bold text-danger" id="cash_modal_change">৳ 0.00</h3>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success text-white px-4" id="submit_cash_payment"><i class="fas fa-check-circle me-1"></i> Submit & Print</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Multiple Payment Modal -->
    <div class="modal fade" id="multiplePaymentModal" tabindex="-1" role="dialog" aria-labelledby="multiplePaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title text-white" id="multiplePaymentModalLabel"><i class="fas fa-credit-card me-2"></i> Split / Multiple Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3 align-items-center">
                        <div class="col-6 text-center border-end">
                            <span class="text-muted d-block small">Total Payable</span>
                            <h3 class="fw-bold text-dark" id="multiple_modal_payable">৳ 0.00</h3>
                        </div>
                        <div class="col-6 text-center">
                            <span class="text-muted d-block small">Remaining / Due</span>
                            <h3 class="fw-bold text-danger" id="multiple_modal_due">৳ 0.00</h3>
                        </div>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Payment Method</th>
                                    <th style="width: 250px;">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><i class="fas fa-money-bill-wave text-success me-2"></i> Cash</td>
                                    <td><input type="number" class="form-control text-end fw-bold split-amount" data-method="Cash" placeholder="0.00" min="0" step="any"></td>
                                </tr>
                                <tr>
                                    <td><i class="fas fa-credit-card text-primary me-2"></i> Card</td>
                                    <td><input type="number" class="form-control text-end fw-bold split-amount" data-method="Card" placeholder="0.00" min="0" step="any"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge text-white px-2 py-1 me-2" style="background-color: #e2136e; font-size: 0.75rem; border-radius: 4px;">bKash</span> bKash Mobile Wallet
                                    </td>
                                    <td><input type="number" class="form-control text-end fw-bold split-amount" data-method="bKash" placeholder="0.00" min="0" step="any"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge text-white px-2 py-1 me-2" style="background-color: #f7941d; font-size: 0.75rem; border-radius: 4px;">Nagad</span> Nagad Mobile Wallet
                                    </td>
                                    <td><input type="number" class="form-control text-end fw-bold split-amount" data-method="Nagad" placeholder="0.00" min="0" step="any"></td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="badge text-white px-2 py-1 me-2" style="background-color: #8c3c96; font-size: 0.75rem; border-radius: 4px;">Rocket</span> Rocket Mobile Wallet
                                    </td>
                                    <td><input type="number" class="form-control text-end fw-bold split-amount" data-method="Rocket" placeholder="0.00" min="0" step="any"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-info text-white px-4" id="submit_multiple_payment" disabled><i class="fas fa-check-circle me-1"></i> Submit & Print</button>
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

            // Live Search — filters grid by name or barcode, and auto-adds on exact barcode match (either automatically on typing, or on Enter key)
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

            // ── Holds LocalStorage & UI Event Listeners ──
            function getHeldOrders() {
                return JSON.parse(localStorage.getItem('pos_held_orders') || '[]');
            }

            function saveHeldOrders(holds) {
                localStorage.setItem('pos_held_orders', JSON.stringify(holds));
                updateHoldsCount();
            }

            function updateHoldsCount() {
                var count = getHeldOrders().length;
                $('#holds-count').text(count);
                if (count > 0) {
                    $('#holds-count').removeClass('bg-secondary').addClass('bg-danger');
                } else {
                    $('#holds-count').removeClass('bg-danger').addClass('bg-secondary');
                }
            }

            // Initialize hold counter
            updateHoldsCount();

            // Click "Hold Order"
            $('#hold-order-btn').on('click', function () {
                var cartRows = $('#cartTable tr').not('#empty-table-row');
                if (cartRows.length === 0 || $('#cartTable').text().indexOf('No products selected') > -1) {
                    toastr.warning("Cart is empty. Cannot hold empty order.");
                    return;
                }

                var note = prompt("Enter a reference note or customer name to hold this order:");
                if (note === null) return;
                note = note.trim();
                if (note === "") {
                    note = "Table " + (getHeldOrders().length + 1);
                }

                $.ajax({
                    type: "GET",
                    url: "{{ route('admin.order.cart_json') }}",
                    dataType: "json",
                    success: function (response) {
                        if (!response.items || response.items.length === 0) {
                            toastr.warning("Cart is empty.");
                            return;
                        }

                        var holdOrder = {
                            id: 'hold_' + Date.now(),
                            note: note,
                            time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                            date: new Date().toLocaleDateString(),
                            customer: {
                                name: $('input[name="name"]').val(),
                                phone: $('input[name="phone"]').val(),
                                address: $('input[name="address"]').val(),
                                area: $('#area').val(),
                                guest: $('#guest_customer').is(':checked')
                            },
                            cart: response
                        };

                        var holds = getHeldOrders();
                        holds.push(holdOrder);
                        saveHeldOrders(holds);

                        // Clear active cart & customer fields
                        $.ajax({
                            url: "{{route('admin.order.cart_clear')}}",
                            success: function () {
                                $('input[name="name"]').val('');
                                $('input[name="phone"]').val('');
                                $('#guest_customer').prop('checked', true).trigger('change');
                                $('#pos_discount_input').val(0);
                                $('#pos_discount_type').val('flat');

                                cart_content();
                                cart_details();
                                toastr.success("Order put on hold successfully!");
                            }
                        });
                    },
                    error: function () {
                        toastr.error("Failed to hold order");
                    }
                });
            });

            // Click "View Holds"
            $('#view-holds-btn').on('click', function () {
                var holds = getHeldOrders();
                var tbody = $('#holds-list-tbody');
                tbody.empty();

                if (holds.length === 0) {
                    tbody.append('<tr><td colspan="6" class="text-center py-4 text-muted">No orders currently on hold.</td></tr>');
                } else {
                    $.each(holds, function (index, hold) {
                        var itemsCount = hold.cart.items.length;
                        var subtotal = parseFloat(hold.cart.pos_shipping || 0);
                        $.each(hold.cart.items, function (i, item) {
                            subtotal += (parseFloat(item.price) * parseInt(item.qty));
                        });
                        var totalDiscount = parseFloat(hold.cart.pos_discount || 0) + parseFloat(hold.cart.product_discount || 0);
                        var grandTotal = Math.max(0, subtotal - totalDiscount);

                        var customerName = hold.customer.guest ? '<span class="badge bg-secondary">Guest</span>' : (hold.customer.name || 'N/A');
                        var customerPhone = hold.customer.phone ? '<br><small class="text-muted">' + hold.customer.phone + '</small>' : '';

                        var tr = '<tr>' +
                            '<td class="ps-3 fw-bold text-primary">' + hold.note + '</td>' +
                            '<td>' + hold.date + '<br><small class="text-muted">' + hold.time + '</small></td>' +
                            '<td>' + customerName + customerPhone + '</td>' +
                            '<td>' + itemsCount + ' item(s)</td>' +
                            '<td class="fw-bold">৳ ' + grandTotal.toLocaleString() + '</td>' +
                            '<td class="pe-3 text-end">' +
                            '<div class="btn-group btn-group-sm">' +
                            '<button type="button" class="btn btn-success retrieve-hold-btn text-white" data-id="' + hold.id + '"><i class="fas fa-play me-1"></i> Retrieve</button>' +
                            '<button type="button" class="btn btn-outline-danger delete-hold-btn" data-id="' + hold.id + '"><i class="fas fa-trash-alt"></i></button>' +
                            '</div>' +
                            '</td>' +
                            '</tr>';
                        tbody.append(tr);
                    });
                }
                $('#holdsModal').modal('show');
            });

            // Retrieve Hold Order
            $(document).on('click', '.retrieve-hold-btn', function () {
                var holdId = $(this).data('id');
                var holds = getHeldOrders();
                var hold = holds.find(function (h) { return h.id === holdId; });

                if (!hold) {
                    toastr.error("Hold order not found.");
                    return;
                }

                $.ajax({
                    type: "POST",
                    url: "{{ route('admin.order.cart_hold_retrieve') }}",
                    data: {
                        _token: "{{ csrf_token() }}",
                        items: hold.cart.items,
                        pos_discount: hold.cart.pos_discount,
                        pos_discount_type: hold.cart.pos_discount_type || 'flat',
                        pos_shipping: hold.cart.pos_shipping
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.status === 'success') {
                            if (hold.customer.guest) {
                                $('#guest_customer').prop('checked', true).trigger('change');
                            } else {
                                $('#guest_customer').prop('checked', false).trigger('change');
                                $('input[name="name"]').val(hold.customer.name);
                                $('input[name="phone"]').val(hold.customer.phone);
                            }

                            $('#pos_discount_input').val(hold.cart.pos_discount || 0);
                            $('#pos_discount_type').val(hold.cart.pos_discount_type || 'flat');

                            cart_content();
                            cart_details();

                            var newHolds = holds.filter(function (h) { return h.id !== holdId; });
                            saveHeldOrders(newHolds);

                            $('#holdsModal').modal('hide');
                            toastr.success("Order retrieved successfully!");
                        } else {
                            toastr.error("Failed to retrieve order items.");
                        }
                    },
                    error: function () {
                        toastr.error("Server error retrieving order");
                    }
                });
            });

            // Delete Hold Order
            $(document).on('click', '.delete-hold-btn', function () {
                if (!confirm("Are you sure you want to delete this held order?")) return;

                var holdId = $(this).data('id');
                var holds = getHeldOrders();
                var newHolds = holds.filter(function (h) { return h.id !== holdId; });
                saveHeldOrders(newHolds);

                $('#view-holds-btn').trigger('click');
                toastr.success("Held order deleted successfully.");
            });

            // ── Full Screen Toggle Logic ──
            function toggleFullScreen() {
                if (!document.fullscreenElement &&
                    !document.mozFullScreenElement && !document.webkitFullscreenElement && !document.msFullscreenElement) {
                    if (document.documentElement.requestFullscreen) {
                        document.documentElement.requestFullscreen();
                    } else if (document.documentElement.msRequestFullscreen) {
                        document.documentElement.msRequestFullscreen();
                    } else if (document.documentElement.mozRequestFullScreen) {
                        document.documentElement.mozRequestFullScreen();
                    } else if (document.documentElement.webkitRequestFullscreen) {
                        document.documentElement.webkitRequestFullscreen(Element.ALLOW_KEYBOARD_INPUT);
                    }
                } else {
                    if (document.exitFullscreen) {
                        document.exitFullscreen();
                    } else if (document.msExitFullscreen) {
                        document.msExitFullscreen();
                    } else if (document.mozCancelFullScreen) {
                        document.mozCancelFullScreen();
                    } else if (document.webkitExitFullscreen) {
                        document.webkitExitFullscreen();
                    }
                }
            }

            function updateFullscreenButton() {
                var isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
                var btn = $('#pos-fullscreen-btn');
                if (isFullscreen) {
                    $('body').addClass('pos-standalone-mode');
                    btn.html('<i class="fas fa-compress me-1"></i> Exit Full');
                    btn.removeClass('btn-outline-primary').addClass('btn-primary text-white');
                } else {
                    $('body').removeClass('pos-standalone-mode');
                    btn.html('<i class="fas fa-expand me-1"></i> Full Screen');
                    btn.removeClass('btn-primary text-white').addClass('btn-outline-primary');
                }
            }

            $(document).on('fullscreenchange webkitfullscreenchange mozfullscreenchange MSFullscreenChange', function () {
                updateFullscreenButton();
            });

            $('#pos-fullscreen-btn').on('click', function (e) {
                e.preventDefault();
                toggleFullScreen();
            });

            // ── Auto Full Screen on page load / first interaction ──
            function autoFullScreen() {
                if (document.documentElement.requestFullscreen) {
                    document.documentElement.requestFullscreen().catch(err => {
                        console.log("Auto-fullscreen blocked on initial load, waiting for first click.");
                        // Fallback to first user click anywhere on the page
                        $(document).one('click', function () {
                            toggleFullScreen();
                        });
                    });
                }
            }

            // Trigger auto-fullscreen
            autoFullScreen();

            // ── Full Screen Restoration after Print ──
            var wasFullscreenBeforePrint = false;

            window.addEventListener('beforeprint', function () {
                var isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
                if (isFullscreen) {
                    wasFullscreenBeforePrint = true;
                }
            });

            window.addEventListener('afterprint', function () {
                if (wasFullscreenBeforePrint) {
                    restoreFullScreen();
                    wasFullscreenBeforePrint = false;
                }
            });

            function restoreFullScreen() {
                setTimeout(function () {
                    var isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullScreenElement || document.msFullscreenElement;
                    if (!isFullscreen) {
                        var promise = null;
                        if (document.documentElement.requestFullscreen) {
                            promise = document.documentElement.requestFullscreen();
                        } else if (document.documentElement.webkitRequestFullscreen) {
                            promise = document.documentElement.webkitRequestFullscreen();
                        } else if (document.documentElement.mozRequestFullScreen) {
                            promise = document.documentElement.mozRequestFullScreen();
                        } else if (document.documentElement.msRequestFullscreen) {
                            promise = document.documentElement.msRequestFullscreen();
                        }

                        if (promise) {
                            promise.catch(function (err) {
                                console.log("Fullscreen restoration rejected:", err);
                                // Fallback: restore on next user interaction (click, keydown, mousedown)
                                $(document).one('click keydown mousedown', function () {
                                    if (!document.fullscreenElement && !document.webkitFullscreenElement && !document.mozFullScreenElement && !document.msFullscreenElement) {
                                        toggleFullScreen();
                                    }
                                });
                            });
                        }
                    }
                }, 300);
            }

            // ── AJAX Order Submission & Auto Print ──
            $(document).on('submit', '.pos_form', function (e) {
                e.preventDefault();

                var form = $(this);
                // Validate form using Parsley if it's initialized
                if (form.parsley && !form.parsley().isValid()) {
                    return;
                }

                var url = form.attr('action');
                var formData = new FormData(this);

                $.ajax({
                    type: "POST",
                    url: url,
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        if (response.status === 'success') {
                            toastr.success(response.message || "Order placed successfully!");

                             // Trigger print via background iframe (no new page/tab)
                             var printFrame = document.getElementById('pos-print-iframe');
                             if (!printFrame) {
                                 printFrame = document.createElement('iframe');
                                 printFrame.id = 'pos-print-iframe';
                                 printFrame.style.position = 'fixed';
                                 printFrame.style.right = '0';
                                 printFrame.style.bottom = '0';
                                 printFrame.style.width = '0';
                                 printFrame.style.height = '0';
                                 printFrame.style.border = '0';
                                 document.body.appendChild(printFrame);
                             }

                             printFrame.onload = function () {
                                 if (printFrame.contentWindow) {
                                     printFrame.contentWindow.addEventListener('beforeprint', function () {
                                         var isFullscreen = document.fullscreenElement || document.webkitFullscreenElement || document.mozFullscreenElement || document.msFullscreenElement;
                                         if (isFullscreen) {
                                             wasFullscreenBeforePrint = true;
                                         }
                                     });
                                     printFrame.contentWindow.addEventListener('afterprint', function () {
                                         if (wasFullscreenBeforePrint) {
                                             restoreFullScreen();
                                             wasFullscreenBeforePrint = false;
                                         }
                                     });
                                 }
                             };

                             printFrame.src = "{{ url('admin/order-print') }}?order_ids[]=" + response.order_id;

                            // Reset customer fields
                            $('input[name="name"]').val('');
                            $('input[name="phone"]').val('');
                            $('#guest_customer').prop('checked', true).trigger('change');
                            $('#pos_discount_input').val(0);
                            $('#pos_discount_type').val('flat');

                            // Refresh POS cart content and details
                            cart_content();
                            cart_details();
                        } else {
                            toastr.error(response.message || "Failed to place order.");
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
                            toastr.error("An error occurred while placing the order.");
                        }
                    }
                });
            });

            // ── POS Payment Operations ──
            
            window.updatePaymentSummary = function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                $('#summary_total').text('৳ ' + total.toFixed(2));
                
                var method = $('#input_payment_method').val();
                var paid = parseFloat($('#input_paid_amount').val() || 0);
                
                if (method === 'Cash' || paid === 0) {
                    paid = total;
                    $('#input_paid_amount').val(total);
                }
                
                var due = Math.max(0, total - paid);
                $('#summary_paid').text('৳ ' + paid.toFixed(2));
                $('#summary_due').text('৳ ' + due.toFixed(2));
            };

            // Initial call to sync summary on page load
            setTimeout(window.updatePaymentSummary, 500);

            // Click Cash Button
            $('#btn-payment-cash').on('click', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                if (total <= 0) {
                    toastr.warning("Cart is empty.");
                    return;
                }
                $('#cash_modal_payable').text('৳ ' + total.toFixed(2));
                $('#cash_received_input').val('').attr('placeholder', total.toFixed(2));
                $('#cash_modal_change').text('৳ 0.00').removeClass('text-success').addClass('text-danger');
                
                // Populate quick denominations
                var denomContainer = $('#quick_cash_denominations');
                denomContainer.empty();
                var nextRound = Math.ceil(total / 50) * 50;
                var denoms = [total, nextRound, nextRound + 50, nextRound + 100, Math.ceil(total / 500) * 500, Math.ceil(total / 1000) * 1000];
                // remove duplicates and filter out values smaller than total
                denoms = [...new Set(denoms)].filter(v => v >= total && v > 0);
                $.each(denoms, function(i, val) {
                    denomContainer.append('<button type="button" class="btn btn-outline-success btn-sm quick-denom-btn" data-value="' + val + '">৳ ' + val + '</button>');
                });
                
                $('#cashPaymentModal').modal('show');
            });

            // Click quick denomination buttons
            $(document).on('click', '.quick-denom-btn', function () {
                var val = parseFloat($(this).data('value'));
                $('#cash_received_input').val(val).trigger('input');
            });

            // Calculate Cash Change Return
            $('#cash_received_input').on('input', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                var received = parseFloat($(this).val()) || 0;
                var change = Math.max(0, received - total);
                $('#cash_modal_change').text('৳ ' + change.toFixed(2));
                if (received >= total) {
                    $('#cash_modal_change').removeClass('text-danger').addClass('text-success');
                } else {
                    $('#cash_modal_change').removeClass('text-success').addClass('text-danger');
                }
            });

            // Submit Cash Payment
            $('#submit_cash_payment').on('click', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                var received = parseFloat($('#cash_received_input').val());
                if (isNaN(received) || received < total) {
                    toastr.error("Cash received must be equal to or greater than Total Payable!");
                    return;
                }
                
                var change = Math.max(0, received - total);

                // Set hidden inputs
                $('#input_payment_method').val('Cash');
                $('#input_payment_status').val('paid');
                $('#input_paid_amount').val(total);
                $('#input_received_amount').val(received);
                $('#input_change_amount').val(change);
                $('#split_payments_inputs').empty();
                
                $('#cashPaymentModal').modal('hide');
                
                // Submit Form
                $('.pos_form').submit();
            });

            // Click Pay All Button
            $('#btn-payment-payall').on('click', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                if (total <= 0) {
                    toastr.warning("Cart is empty.");
                    return;
                }
                
                // Set hidden inputs
                $('#input_payment_method').val('Cash');
                $('#input_payment_status').val('paid');
                $('#input_paid_amount').val(total);
                $('#input_received_amount').val(total);
                $('#input_change_amount').val(0);
                $('#split_payments_inputs').empty();
                
                // Submit Form
                $('.pos_form').submit();
            });

            // Click Multiple Button
            $('#btn-payment-multiple').on('click', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                if (total <= 0) {
                    toastr.warning("Cart is empty.");
                    return;
                }
                $('#multiple_modal_payable').text('৳ ' + total.toFixed(2));
                $('#multiple_modal_due').text('৳ ' + total.toFixed(2));
                $('.split-amount').val('');
                $('#submit_multiple_payment').attr('disabled', true);
                $('#multiplePaymentModal').modal('show');
            });

            // Split payments inputs listener
            $('.split-amount').on('input', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                var totalPaid = 0;
                $('.split-amount').each(function () {
                    var val = parseFloat($(this).val()) || 0;
                    totalPaid += val;
                });
                
                var remaining = Math.max(0, total - totalPaid);
                $('#multiple_modal_due').text('৳ ' + remaining.toFixed(2));
                
                // Enable submit only if total paid is equal to or exceeds total payable
                if (totalPaid >= total) {
                    $('#multiple_modal_due').removeClass('text-danger').addClass('text-success');
                    $('#submit_multiple_payment').attr('disabled', false);
                } else {
                    $('#multiple_modal_due').removeClass('text-success').addClass('text-danger');
                    $('#submit_multiple_payment').attr('disabled', true);
                }
            });

            // Submit Multiple Payment
            $('#submit_multiple_payment').on('click', function () {
                var total = parseFloat($('#cart_total_payable_val').data('value') || 0);
                var totalPaid = 0;
                var splitInputs = $('#split_payments_inputs');
                splitInputs.empty();
                
                var index = 0;
                $('.split-amount').each(function () {
                    var val = parseFloat($(this).val()) || 0;
                    if (val > 0) {
                        var method = $(this).data('method');
                        splitInputs.append('<input type="hidden" name="split_payments[' + index + '][method]" value="' + method + '">');
                        splitInputs.append('<input type="hidden" name="split_payments[' + index + '][amount]" value="' + val + '">');
                        totalPaid += val;
                        index++;
                    }
                });
                
                if (totalPaid < total) {
                    toastr.error("Total paid amount must be equal to or greater than Total Payable!");
                    return;
                }
                
                // Set hidden inputs
                $('#input_payment_method').val('Multiple');
                $('#input_payment_status').val('paid');
                $('#input_paid_amount').val(total); // Cap at total payable
                
                $('#multiplePaymentModal').modal('hide');
                
                // Submit Form
                $('.pos_form').submit();
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
                    if (typeof window.updatePaymentSummary === 'function') {
                        window.updatePaymentSummary();
                    }
                },
            });
        }
    </script>
@endsection