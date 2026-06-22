<div class="product_item_inner">
    @php
        if ($value->type == 0) {
            $stockQty = $value->variables->sum('stock') ?? 0;
        } else {
            $stockQty = $value->stock ?? 0;
        }
        $discount = ($value->old_price > 0)
            ? ((($value->old_price - $value->new_price) * 100) / $value->old_price)
            : 0;
    @endphp
    
    <div class="product-img-wrapper">
        <a href="{{ route('product', $value->slug) }}">
            <img src="{{ asset($value->image ? $value->image->image : '') }}" alt="{{ $value->name }}" />
        </a>

        @if ($value->old_price)
            <div class="discount-badge">
                <p>{{ number_format($discount, 0) }}% OFF</p>
            </div>
        @endif
        @if ($stockQty == 0)
            <div class="stockout-badge">
                <p>Stock Out</p>
            </div>
        @endif

        <div class="product-actions">
            @if ($value->variable_count > 0 && $value->type == 0)
            <a href="javascript:void(0)" data-id="{{ $value->id }}" data-url="{{ route('ajax.quick_view') }}" class="add-cart-btn add_to_cart_ajax" data-type="variable">
                <i class="fa-solid fa-shopping-bag"></i>
            </a>
            @else
            <a href="javascript:void(0)" data-id="{{ $value->id }}" class="add-cart-btn add_to_cart_ajax" data-type="simple">
                <i class="fa-solid fa-shopping-bag"></i>
            </a>
            @endif
             <a class="wishlist_store" data-id="{{$value->id}}"><i class="fa-regular fa-heart"></i></a>
        </div>
    </div>
    
    <div class="product-info">
        <h4 class="product-title">
            <a href="{{ route('product', $value->slug) }}">{{ Str::limit($value->name, 40) }}</a>
        </h4>
        <div class="product-price">
             @if ($value->variable_count > 0 && $value->type == 0)
                <p>
                    @if ($value->variable->old_price)
                        <del>৳ {{ $value->variable->old_price }}</del>
                    @endif
                    <span>৳ {{ $value->variable->new_price }}</span>
                </p>
            @else
                <p>
                    @if ($value->old_price)
                        <del>৳ {{ $value->old_price }}</del>
                    @endif
                    <span>৳ {{ $value->new_price }}</span>
                </p>
            @endif
        </div>
    </div>
</div>