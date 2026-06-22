@extends('frontEnd.layouts.master') 
@section('title', 'Customer Checkout') 
@push('css')
@endpush 
@section('content')
<section class="chheckout-section">
    @php
        $subtotal = Cart::instance('shopping')->subtotal();
        $subtotal = str_replace(',', '', $subtotal);
        $subtotal = str_replace('.00', '', $subtotal);
        $shipping = Session::get('shipping') ? Session::get('shipping') : 0;
        $coupon = Session::get('coupon_amount') ? Session::get('coupon_amount') : 0;
        $discount = Session::get('discount') ? Session::get('discount') : 0;
        
        $cart = Cart::instance('shopping')->content();
        $couponIds = [];
        foreach ($cart as $item) {
            if (!empty($item->options) && isset($item->options['coupon_id'])) { 
                $couponIds[] = $item->options['coupon_id'];
            }
        }
    
    @endphp
    <div class="container">
        <div class="row">
            <div class="col-sm-6 cus-order-2 order-2 order-sm-1">
                <div class="checkout-shipping">
                    <form action="{{ route('customer.ordersave') }}" method="POST" data-parsley-validate="">
                        @csrf
                        <div class="card">
                            <div class="card-header">
                                <h6 class = "check-position">Fill in the details and click on the "Confirm Order" button
                                    </h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="form-group mb-1">
                                            <label for="name"><i class="fa-solid fa-user"></i>Name *</label>
                                            <input type="text" id="name"
                                                class="form-control @error('name') is-invalid @enderror" name="name"
                                                value="{{ old('name') }}" placeholder="" required />
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- col-end -->
                                    <div class="col-sm-12">
                                        <div class="form-group mb-1">
                                            <label for="phone"><i class="fa-solid fa-phone"></i> Phone Number *</label>
                                            <input type="text" minlength="11" id="number" maxlength="11"
                                                pattern="0[0-9]+"
                                                title="please enter number only and 0 must first character"
                                                title="Please enter an 11-digit number." id="phone"
                                                class="form-control @error('phone') is-invalid @enderror" name="phone"
                                                value="{{ old('phone') }}" placeholder="" required />
                                            @error('phone')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- col-end -->
                                    <div class="col-sm-12">
                                        <div class="form-group mb-1">
                                            <label for="address"><i class="fa-solid fa-map"></i>Address  *</label>
                                            <input type="address" id="address"
                                                class="form-control @error('address') is-invalid @enderror"
                                                name="address" placeholder="" value="{{ old('address') }}" required />
                                            @error('address')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                    <!-- col-end -->
                                     <div class="col-sm-12">
                                        <div class="form-group mb-1">
                                            <label for="note">Note</label>
                                            <input type="text" id="note"
                                                class="form-control @error('note') is-invalid @enderror"
                                                name="note" placeholder="" value="{{ old('note') }}" />
                                            @error('note')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-sm-12">
                                        <div class="form-group mb-1">
                                            <label for="area" class="pb-2">Select Delivery Option</label> <br>
                                            @foreach ($shipping_charges as $key => $value)
                                            <div class="form-check form-check-inline">
                                                <input class="form-check-input" type="radio" name="area" id="area_{{ $value->id }}" value="{{ $value->id }}" required>
                                                <label class="form-check-label" for="area_{{ $value->id }}">
                                                    {{ $value->name }} (৳ {{ $value->amount }})
                                                </label>
                                            </div>
                                            @endforeach
                                            @error('area')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>
                                    </div>
                                   

                                    <!-- col-end -->
                                    <div class="col-sm-12">
                                        <div class="radio_payment">
                                            <label class="mb-3" id="payment_method">Select a Payment Option</label>
                                        </div>
                                        <div class="payment-methods">
                                            <div class="payment_item active">
                                                <input class="d-none" type="radio" name="payment_method"
                                                    id="inlineRadio1" value="Cash On Delivery" checked required />
                                                <label class="form-check-label" for="inlineRadio1">
                                                    <div class="check_icon"><i class="fa-solid fa-circle-check"></i></div>
                                                    <div class="icon"><i class="fa-solid fa-money-bill-wave"></i></div>
                                                    <div class="text">Cash on Delivery</div>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="form-group">
                                            <button class="order_place" type="submit">Confirm Order</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- card end -->

                    </form>
                </div>
            </div>
            <!-- col end -->
            <div class="col-sm-6 cust-order-1 order-1 order-sm-2">
                <div class="cart_details table-responsive-sm">
                    <div class="card">
                        <div class="card-header">
                            <h5>Order Information</h5>
                        </div>
                        <div class="card-body cartlist">
                            <table class="cart_table table table-bordered table-striped text-center mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 20%;">Delete</th>
                                        <th style="width: 40%;">Product</th>
                                        <th style="width: 20%;">Amount</th>
                                        <th style="width: 20%;">Price</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach (Cart::instance('shopping')->content() as $value)
                                        <tr>
                                            <td>
                                                <a class="cart_remove" data-id="{{ $value->rowId }}"><i
                                                        class="fas fa-trash text-danger"></i></a>
                                            </td>
                                            <td class="text-left">
                                                <a href="{{ route('product', $value->options->slug) }}"> <img
                                                        src="{{ asset($value->options->image) }}" />
                                                    {{ Str::limit($value->name, 20) }}</a>
                                                @if ($value->options->product_size)
                                                    <p>Size: {{ $value->options->product_size }}</p>
                                                @endif
                                                @if ($value->options->product_color)
                                                    <p>Color: {{ $value->options->product_color }}</p>
                                                @endif
                                            </td>
                                            <td class="cart_qty">
                                                <div class="qty-cart vcart-qty">
                                                    <div class="quantity">
                                                        <button class="minus cart_decrement"
                                                            data-id="{{ $value->rowId }}">-</button>
                                                        <input type="text" value="{{ $value->qty }}" readonly />
                                                        <button class="plus cart_increment"
                                                            data-id="{{ $value->rowId }}">+</button>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><span>৳ </span><strong>{{ $value->price }}</strong>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">Total</th>
                                        <td class="px-4">
                                            <span id="net_total"><span>৳
                                                </span><strong>{{ $subtotal }}</strong></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">Delivery Charge</th>
                                        <td class="px-4">
                                            <span id="cart_shipping_cost"><span>৳
                                            </span><strong>{{ $shipping }}</strong></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">Discount</th>
                                        <td class="px-4">
                                            <span id="cart_shipping_cost"><span>৳
                                                </span><strong>{{ $discount + $coupon }}</strong></span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-end px-4">TOTAL</th>
                                        <td class="px-4">
                                            <span id="grand_total"><span>৳
                                                </span><strong>{{ $subtotal +$shipping - ($discount + $coupon) }}</strong></span>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <form action="@if (Session::get('coupon_used')) {{ route('customer.coupon_remove') }} @else {{ route('customer.coupon') }} @endif"
                                class="checkout-coupon-form" method="POST">
                                @csrf
                              
                                <input type="hidden" name="product_coupon" value="{{ implode(',', $couponIds) }}">
                                
                                <div class="coupon">
                                    <input type="text" name="coupon_code"
                                        placeholder="@if (Session::get('coupon_used')) {{ Session::get('coupon_used') }} @else Apply Coupon @endif"
                                        class="border-0 shadow-none form-control" />
                                    <input type="submit"
                                        value="@if (Session::get('coupon_used')) remove @else apply @endif "
                                        class="border-0 shadow-none btn btn-theme" />
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
            <!-- col end -->
            

        </div>
    </div>
</section>
@endsection
@push('script')
<script src="{{ asset('public/frontEnd/') }}/js/parsley.min.js"></script>
<script src="{{ asset('public/frontEnd/') }}/js/form-validation.init.js"></script>
<script>
    $('input[name="area"]').on("change", function() {
        var id = $(this).val();
        $.ajax({
            type: "GET",
            data: {
                id: id
            },
            url: "{{ route('shipping.charge') }}",
            dataType: "html",
            success: function(response) {
                $(".cartlist").html(response);
            },
        });
    });
</script>

<script type="text/javascript">
    dataLayer.push({
        ecommerce: null
    }); // Clear the previous ecommerce object.
    dataLayer.push({
        event: "view_cart",
        ecommerce: {
            items: [
                @foreach (Cart::instance('shopping')->content() as $cartInfo)
                    {
                        item_name: "{{ $cartInfo->name }}",
                        item_id: "{{ $cartInfo->id }}",
                        price: "{{ $cartInfo->price }}",
                        item_brand: "{{ $cartInfo->options->brand }}",
                        item_category: "{{ $cartInfo->options->category }}",
                        item_size: "{{ $cartInfo->options->size }}",
                        item_color: "{{ $cartInfo->options->color }}",
                        currency: "BDT",
                        quantity: {{ $cartInfo->qty ?? 0 }}
                    },
                @endforeach
            ]
        }
    });
</script>
<script type="text/javascript">
    // Clear the previous ecommerce object.
    dataLayer.push({
        ecommerce: null
    });

    // Push the begin_checkout event to dataLayer.
    dataLayer.push({
        event: "begin_checkout",
        ecommerce: {
            items: [
                @foreach (Cart::instance('shopping')->content() as $cartInfo)
                    {
                        item_name: "{{ $cartInfo->name }}",
                        item_id: "{{ $cartInfo->id }}",
                        price: "{{ $cartInfo->price }}",
                        item_brand: "{{ $cartInfo->options->brands }}",
                        item_category: "{{ $cartInfo->options->category }}",
                        item_size: "{{ $cartInfo->options->size }}",
                        item_color: "{{ $cartInfo->options->color }}",
                        currency: "BDT",
                        quantity: {{ $cartInfo->qty ?? 0 }}
                    },
                @endforeach
            ]
        }
    });
</script>
<!-- jQuery -->
<script src="{{asset('public/frontEnd/js/jquery-3.7.1.min.js')}}"></script>
@endpush
