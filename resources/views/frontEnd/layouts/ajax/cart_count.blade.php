@php
    $subtotal = Cart::instance('shopping')->subtotal();
    $subtotal = str_replace(',', '', $subtotal);
    $subtotal = str_replace('.00', '', $subtotal);
    view()->share('subtotal', $subtotal);
@endphp
<a href="{{ route('customer.checkout') }}">
    <p class="margin-shopping">
        <i class="fa-solid fa-cart-shopping"></i>
        <span>{{ Cart::instance('shopping')->count() }}</span>

    </p>
</a>
<div class="cshort-summary">
    <ul>
        @foreach (Cart::instance('shopping')->content() as $key => $value)
            <li><a href=""><img src="{{ asset($value->options->image) }}" alt=""></a></li>
            <li><a href="">{{Str::limit($value->name, 30)}}</a></li>
            <li>Qty: {{ $value->qty }}</li>
           <li>
                <p>৳{{$value->price}}</p>
                <button class="remove-cart cart_remove" data-id="{{$value->rowId}}"><i class="fa-regular fa-trash-can trash_icon" title="Delete this item"></i></button>
            </li>
        @endforeach
    </ul>
    <p><strong>TOTAL : ৳{{ $subtotal }}</strong></p>
    <a href="{{ route('customer.checkout') }}" class="go_cart"> Order Now</a>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
<script>
    feather.replace()
</script>
