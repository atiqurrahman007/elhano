@php
    $product_discount = 0;
@endphp
@foreach($cartinfo as $key=>$value)
<tr>
    <td>
        @if($value->options->image)
        <img height="30" src="{{asset($value->options->image)}}">
        @else
        <img height="30" src="{{asset('public/images/no-image.png')}}">
        @endif
        <p>{{$value->options->product_size}}</p>
        <p>{{$value->options->product_color}}</p>
    </td>
    <td>{{$value->name}}</td>
    <td>
        <div class="qty-cart vcart-qty">
            <div class="quantity">
                <button class="minus cart_decrement" value="{{$value->qty}}" data-id="{{$value->rowId}}">-</button>
                <input type="text" value="{{$value->qty}}" readonly />
                <button class="plus cart_increment" value="{{$value->qty}}" data-id="{{$value->rowId}}">+</button>
            </div>
        </div>
    </td>
    <td>{{$value->price}}</td>
    <td class="discount">
        <input type="number" class="product_discount" value="{{$value->options->product_discount}}" placeholder="0.00" data-id="{{$value->rowId}}">
    </td>
    <td>{{($value->price - $value->options->product_discount)*$value->qty}}</td>
    <td>
        <button type="button" class="btn btn-danger btn-xs cart_remove" data-id="{{$value->rowId}}">
            <i class="fa fa-times"></i>
        </button>
    </td>
</tr>
@php
    $product_discount += $value->options->product_discount*$value->qty;
    Session::put('product_discount',$product_discount);
@endphp
@endforeach
