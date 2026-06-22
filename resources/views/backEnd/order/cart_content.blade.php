@php
    $product_discount = 0;
@endphp
@foreach($cartinfo as $key=>$value)
<tr>
    <td>
        <div class="d-flex align-items-center">
            @if($value->options->image)
            <img src="{{asset($value->options->image)}}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
            @else
            <img src="{{asset('public/images/no-image.png')}}" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
            @endif
            <div>
                <div class="fw-bold" style="font-size: 0.85rem;">{{$value->name}}</div>
                <small class="text-muted">
                    @if($value->options->product_size) {{$value->options->product_size}} @endif
                    @if($value->options->product_color) {{$value->options->product_color}} @endif
                </small>
            </div>
        </div>
    </td>
    <td>
        <div class="input-group input-group-sm" style="width: 100px;">
            <button class="btn btn-outline-secondary btn-sm cart_decrement" type="button" data-id="{{$value->rowId}}">-</button>
            <input type="text" class="form-control text-center" value="{{$value->qty}}" readonly style="padding: 0;">
            <button class="btn btn-outline-secondary btn-sm cart_increment" type="button" data-id="{{$value->rowId}}">+</button>
        </div>
    </td>
    <td class="text-end">
        {{$value->price * $value->qty}}
        @if($value->options->product_discount > 0)
        <div class="text-danger small">-{{$value->options->product_discount * $value->qty}}</div>
        @endif
    </td>
    <td class="text-center">
        <button type="button" class="btn btn-link text-danger p-0 cart_remove" data-id="{{$value->rowId}}">
            <i class="fas fa-times"></i>
        </button>
    </td>
</tr>

@php
    $product_discount += $value->options->product_discount*$value->qty;
    Session::put('product_discount',$product_discount);
@endphp
@endforeach