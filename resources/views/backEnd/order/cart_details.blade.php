@php
    $subtotal = Cart::instance('sale')->subtotal();
    $subtotal = str_replace(',','',$subtotal);
    $subtotal = str_replace('.00', '',$subtotal);
    $shipping = Session::get('pos_shipping') ?? 0;
    $total_discount = Session::get('pos_discount') + Session::get('product_discount');
@endphp
<div class="d-flex justify-content-between mb-2">
    <span>Sub Total</span>
    <span class="fw-bold">{{$subtotal}}</span>
</div>
<div class="d-flex justify-content-between mb-2">
    <span>Shipping Fee</span>
    <span>{{$shipping}}</span>
</div>
<div class="d-flex justify-content-between mb-2 text-danger">
    <span>Discount</span>
    <span>-{{$total_discount}}</span>
</div>
<hr>
<div class="d-flex justify-content-between fs-5 fw-bold">
    <span>Total</span>
    <span>{{($subtotal + $shipping) - $total_discount}}</span>
</div>
