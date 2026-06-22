<div class="cart_inner">
    <div class="cart_outer_top">
        <ul>
            <li><a href="javascript:void(0)" class="text-right remove_all">Clear All</a></li>
        </ul>
    </div>
    <div class="cart_item_inner">
        <div class="cart_top">
            <ul class="eccart-pro-items">
                @if(Cart::instance('shopping')->count() > 0)
                @foreach (Cart::instance('shopping')->content() as $value)
                    <li>
                        <a href="{{ route('product', $value->options->slug) }}" class="sidekka_pro_img">
                            <img src="{{ asset($value->options->image) }}" alt="product" />
                        </a>
                        <div class="ec-pro-content">
                            <a href="{{ route('product', $value->options->slug) }}" class="cart_pro_title">
                                {{ $value->name }}<br>@if($value->options->product_size) <small>Size:{{$value->options->product_size}}</small> @endif
                                @if($value->options->product_color) <small>Color:{{$value->options->product_color}}</small> @endif
                            </a>
                            <span class="cart-price"><span>৳{{ $value->price }}</span>
                                x {{ $value->qty }} item=৳{{ $value->subtotal }} </span>

                            <div class="quantity sidebar">
                                <button class="decrement cart_decrement" data-id="{{ $value->rowId }}">-</button>
                                <input type="text" min="1" max="100" name="quantity39897"
                                    value="{{ $value->qty }}" />
                                <button class="increment cart_increment" data-id="{{ $value->rowId }}">+</button>
                            </div>
                        </div>
                        <div class="delete__btn">
                             <a href="javascript:void(0)" class="remove cart_remove fs-4" data-id="{{ $value->rowId }}"
                                title="remove"><i class="fas fa-trash text-danger"></i></a>
                        </div>
                    </li>
                @endforeach
                @else
                <div class="emty__cart_image">
                    <img src="{{asset('public/frontEnd/images/cart_empty1.gif')}}" alt="">
                    <h3>Cart Is Empty</h3>
                    <p>Explore our products and add items to your cart.</p>
                </div>
                @endif


            </ul>

        </div>
    </div>
    <div class="cart_bottom">
        <div class="ec-cart-bottom">
            <div class="cart-sub-total">
                <table class="cart-table">
                    <tbody>
                        <tr>
                            <td class="text-left fw-bold fs-6">Sub-Total :</td>
                            <td class="text-right fw-bold fs-6 cart-total-price">৳{{ $total }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="cart_btns">
                <a href="{{ route('customer.checkout') }}" class="button_cart">Checkout</a>
            </div>
        </div>


    </div>
</div>
