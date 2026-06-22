<div class="floating-bag">
    <i class="fa fa-shopping-bag" aria-hidden="true"></i>
    <div class="cart-count" id="bag-items">
        <span class="cart-item-count">{{ $count }} </span> ITEMS
    </div>
</div>

<div class="floating-bag-amount">
    <div id="bag-amount">
        <span class="odometer odometer-auto-theme">
            <div class="odometer-inside">
                <span class="odometer-digit item-total-price total-price">
                    <span class="odometer-digit-spacer cart-total-price">৳ {{ $total }}</span>
                    <span class="odometer-digit-inner"><span class="odometer-ribbon"></span></span>
                </span>
            </div>
        </span>
    </div>
</div>
