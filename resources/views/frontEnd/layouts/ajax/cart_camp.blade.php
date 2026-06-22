@php
    $subtotal = Cart::instance('shopping')->subtotal();
    $subtotal = str_replace(',', '', $subtotal);
    $subtotal = str_replace('.00', '', $subtotal);
    $shipping = Session::get('shipping') ? Session::get('shipping') : 0;
    $discount = Session::get('discount') ? Session::get('discount') : 0;
@endphp
<table class="cart_table table table-bordered table-striped text-center mb-0">
    <thead>
        <tr>
            <th style="width: 50%;">Product</th>
            <th style="width: 25%;">Amount</th>
            <th style="width: 25%;">Price</th>
        </tr>
    </thead>

    <tbody>
        @foreach (Cart::instance('shopping')->content() as $value)
            <tr>
                <td class="text-left">
                    <div>
                        <img src="{{ asset($value->options->image) }}"  style="height: 30px; width: 30px;">
                       <p> {{ Str::limit($value->name, 20) }}</p>
                       @if($value->options->product_size)
                       <p> Size : {{ $value->options->product_size}}</p>
                       @endif
                        @if($value->options->product_color)
                       <p> Color : {{ $value->options->product_color}}</p>
                       @endif
                    </div>
                </td>
                <td width="15%" class="cart_qty">
                    <div class="qty-cart vcart-qty">
                        <div class="quantity">
                            <button class="minus cart_decrement" data-id="{{ $value->rowId }}">-</button>
                            <input type="text" value="{{ $value->qty }}" readonly />
                            <button class="plus  cart_increment" data-id="{{ $value->rowId }}">+</button>
                        </div>
                    </div>
                </td>
                <td>৳{{ $value->price * $value->qty }}</td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="2" class="text-end px-4">Total</th>
            <td>
                <span id="net_total"><span class="alinur">৳ </span><strong>{{ $subtotal }}</strong></span>
            </td>
        </tr>
        <tr>
            <th colspan="2" class="text-end px-4">Delivery Charge</th>
            <td>
                <span id="cart_shipping_cost"><span class="alinur">৳ </span><strong>{{ $shipping }}</strong></span>
            </td>
        </tr>
        <tr>
            <th colspan="2" class="text-end px-4">TOTAL</th>
            <td>
                <span id="grand_total"><span class="alinur">৳
                    </span><strong>{{ $subtotal + $shipping }}</strong></span>
            </td>
        </tr>
    </tfoot>
</table>

