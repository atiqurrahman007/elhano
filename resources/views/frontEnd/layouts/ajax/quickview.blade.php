<div class="modal-view quick-product">
    <button class="close-modal">x</button>
    <div class="quick-product-content">
        <div class="row">
            <div class="col-sm-5">
                <div class="quick_view_img">
                    <img src="{{ asset($details->image->image) }}" alt="" class="img-fluid">
                </div>
            </div>
            <div class="col-sm-7">
                <div class="quick_view_info">
                    <h3 class="product_name">{{ $details->name }}</h3>
                    <div class="product_price">
                        @if ($details->variable_count > 0 && $details->type == 0)
                            @if ($details->variable->old_price)
                                <del>৳ {{ $details->variable->old_price }}</del>
                            @endif
                            <span class="new_price">৳ {{ $details->variable->new_price }}</span>
                        @else
                            @if ($details->old_price)
                                <del>৳ {{ $details->old_price }}</del>
                            @endif
                            <span class="new_price">৳ {{ $details->new_price }}</span>
                        @endif
                    </div>
                    
                    <form id="quickViewForm">
                        @csrf
                        <input type="hidden" name="id" value="{{ $details->id }}">
                        
                        @if ($productcolors->count() > 0)
                            <div class="pro-color">
                                <p class="color-title">Select Color</p>
                                <div class="color_inner">
                                    <div class="size-container">
                                        <div class="selector">
                                            @foreach ($productcolors as $key => $procolor)
                                                <div class="selector-item color-item" data-id="{{ $key }}">
                                                    <input type="radio" id="fc-option{{ $procolor->color }}" value="{{ $procolor->color }}" name="product_color" class="selector-item_radio stock_check" required data-color="{{ $procolor->color }}">
                                                    <label for="fc-option{{ $procolor->color }}" class="selector-item_label">{{ $procolor->color }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if ($productsizes->count() > 0)
                            <div class="pro-size">
                                <p class="color-title">Select Size</p>
                                <div class="size_inner">
                                    <div class="size-container">
                                        <div class="selector">
                                            @foreach ($productsizes as $prosize)
                                                <div class="selector-item">
                                                    <input type="radio" id="f-option{{ $prosize->size }}" value="{{ $prosize->size }}" name="product_size" class="selector-item_radio stock_check" required data-size="{{ $prosize->size }}">
                                                    <label for="f-option{{ $prosize->size }}" class="selector-item_label">{{ $prosize->size }}</label>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                        
                        <div class="product-stock" style="display: none; margin-bottom: 10px;">
                            <p><strong>Stock: </strong><span class="stock"></span></p>
                        </div>

                        <div class="qty-cart">
                            <div class="quantity">
                                <span class="minus">-</span>
                                <input type="text" name="qty" value="1">
                                <span class="plus">+</span>
                            </div>
                        </div>

                        <div class="add_cart_section" style="display: flex; gap: 10px;">
                            <button type="button" class="btn btn-primary add_cart_btn quick_add_to_cart" style="display: flex; justify-content: center; align-items: center;">Add To Cart</button>
                            <button type="button" class="btn btn-primary order_now_btn quick_order_now" style="display: flex; justify-content: center; align-items: center;">Order Now</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $('.close-modal').on('click', function() {
        $("#custom-modal").hide();
        $("#page-overlay").hide();
    });

    $(".minus").click(function() {
        var $input = $(this).parent().find("input");
        var count = parseInt($input.val()) - 1;
        count = count < 1 ? 1 : count;
        $input.val(count);
        return false;
    });

    $(".plus").click(function() {
        var $input = $(this).parent().find("input");
        $input.val(parseInt($input.val()) + 1);
        return false;
    });

    $(".stock_check").on("change", function() {
        var color = $("input[name='product_color']:checked").val();
        var size = $("input[name='product_size']:checked").val();
        var id = "{{ $details->id }}";
        
        if (id) {
             $.ajax({
                type: "GET",
                data: { id: id, color: color, size: size },
                url: "{{ route('stock_check') }}",
                dataType: "json",
                success: function(response) {
                    if (response.status) {
                        $('.product-stock').show();
                        $(".stock").html(response.product.stock);
                        $(".new_price").text('৳ ' + response.product.new_price);
                         if(response.product.old_price){
                             $(".product_price del").text('৳ ' + response.product.old_price);
                         }
                        $('.add_cart_btn').prop('disabled', false);
                         $('.order_now_btn').prop('disabled', false);
                    } else {
                        $('.product-stock').hide();
                         // Only warn if both required fields are selected, or if only one is required and it's selected
                        var colorRequired = {{ $productcolors->count() > 0 ? 'true' : 'false' }};
                        var sizeRequired = {{ $productsizes->count() > 0 ? 'true' : 'false' }};
                        
                        if((colorRequired && !color) || (sizeRequired && !size)) {
                             // waiting for other selection
                        } else {
                             toastr.warning("Stock not available for this combination");
                             $('.add_cart_btn').prop('disabled', true);
                              $('.order_now_btn').prop('disabled', true);
                        }
                    }
                }
            });
        }
    });

    $('.quick_add_to_cart').on('click', function(e) {
        e.preventDefault();
        var form = $('#quickViewForm');
        var formData = form.serialize();

        // Validation
         var colorRequired = {{ $productcolors->count() > 0 ? 'true' : 'false' }};
         var sizeRequired = {{ $productsizes->count() > 0 ? 'true' : 'false' }};
         
         if(colorRequired && !$("input[name='product_color']:checked").val()){
             toastr.error("Please select a color");
             return;
         }
         if(sizeRequired && !$("input[name='product_size']:checked").val()){
             toastr.error("Please select a size");
             return;
         }

        $.ajax({
            type: "POST",
            url: "{{ route('cart.store') }}",
            data: formData,
            success: function(data) {
                if(data.status == 'success') {
                    cart_count();
                    mobile_cart();
                    cart_right_count();
                    cart_left_count();
                    $("#custom-modal").hide();
                    $("#page-overlay").hide();
                    $("#success-modal").show();
                    // $("#page-overlay").show(); // Removed overlay
                    setTimeout(function() {
                        $("#success-modal").fadeOut();
                    }, 5000);
                } else {
                     toastr.error(data.message || "Something went wrong");
                }
            },
             error: function(xhr) {
                toastr.error("Failed to add to cart");
            }
        });
    });
    
     $('.quick_order_now').on('click', function(e) {
        e.preventDefault();
        var form = $('#quickViewForm');
        var formData = form.serialize();

        // Validation
         var colorRequired = {{ $productcolors->count() > 0 ? 'true' : 'false' }};
         var sizeRequired = {{ $productsizes->count() > 0 ? 'true' : 'false' }};
         
         if(colorRequired && !$("input[name='product_color']:checked").val()){
             toastr.error("Please select a color");
             return;
         }
         if(sizeRequired && !$("input[name='product_size']:checked").val()){
             toastr.error("Please select a size");
             return;
         }

        $.ajax({
            type: "POST",
            url: "{{ route('cart.store') }}",
            data: formData,
            success: function(data) {
                 if(data.status == 'success') {
                    window.location.href = "{{ route('customer.checkout') }}";
                } else {
                     toastr.error(data.message || "Something went wrong");
                }
            },
             error: function(xhr) {
                toastr.error("Failed to add to cart");
            }
        });
    });
</script>
