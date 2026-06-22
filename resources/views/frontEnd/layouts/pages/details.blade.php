@extends('frontEnd.layouts.master')
@section('title', $details->name)
@push('seo')
    <meta name="app-url" content="{{ route('product', $details->slug) }}" />
    <meta name="robots" content="index, follow" />
    <meta name="description" content="{{ $details->meta_description }}" />
    <meta name="keywords" content="{{ $details->slug }}" />

    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product" />
    <meta name="twitter:site" content="{{ $details->name }}" />
    <meta name="twitter:title" content="{{ $details->name }}" />
    <meta name="twitter:description" content="{{ $details->meta_description }}" />
    <meta name="twitter:creator" content="{{$generalsetting->name}}" />
    <meta property="og:url" content="{{ route('product', $details->slug) }}" />
    <meta name="twitter:image" content="{{ asset($details->image->image) }}" />

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $details->name }}" />
    <meta property="og:type" content="product" />
    <meta property="og:url" content="{{ route('product', $details->slug) }}" />
    <meta property="og:image" content="{{ asset($details->image->image) }}" />
    <meta property="og:description" content="{{ $details->meta_description }}" />
    <meta property="og:site_name" content="{{ $details->name }}" />
@endpush

@section('content')
    @php
        $cartItems = Cart::instance('shopping')->content();
        $productInCart = null;
    
        if ($cartItems->contains('id', $details->id)) {
            $productInCart = $cartItems->where('id', $details->id)->first();
        }
    @endphp
    <div class="homeproduct main-details-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="details-product">
                        <div class="row">
                            <div class="col-sm-1 order-2 order-sm-1">
                                @if($details->variableimages->count() > 0)
                                    <div class="indicator_thumb @if ($details->variableimages->count() > 4) tests @endif">
                                        @foreach ($details->variableimages as $key => $value)
                                            <div class="indicator-item" data-id="{{$key}}">
                                                <p>{{ $value->name }}</p>
                                                <img src="{{ asset($value->image) }}" />
                                            </div>
                                        @endforeach
                                         @if($details->pro_video)
                                        <div class="indicator-item" data-id="{{$details->variableimages->count()}}">
                                            <img src="{{ asset('public/frontEnd/images/youtube-icon.png') }}">
                                        </div>
                                        @endif
                                    </div>
                                @else
                                    <div
                                        class="indicator_thumb @if ($details->images->count() > 4) tests @endif">
                                        @foreach ($details->images as $key => $value)
                                            <div class="indicator-item" data-id="{{ $details->pro_video ? $key: $key }}">
                                                <img src="{{ asset($value->image) }}" />
                                            </div>
                                        @endforeach
                                         @if($details->pro_video)
                                        <div class="indicator-item" data-id="{{$details->images->count()}}">
                                            <img src="{{ asset('public/frontEnd/images/youtube-icon.png') }}">
                                        </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <div class="col-sm-5 order-1 order-sm-2">
                                <div class="position-relative">
                                    @if ($details->old_price)
                                        @php 
                                            $discount = (($details->old_price - $details->new_price) * 100) / $details->old_price;
                                        @endphp
                                        <div class="discount">
                                            <p>{{ number_format($discount, 0) }}% Discount</p>           
                                        </div>
                                    @endif
                                    @if($details->variableimages->count() > 0)
                                        <div class="details_slider owl-carousel">
                                            @foreach ($details->variableimages as $value)
                                                <div class="dimage_item">
                                                    <img src="{{ asset($value->image) }}" class="block__pic" />
                                                </div>
                                            @endforeach
                                            @if($details->pro_video)
                                            <div class="dimage_item">
                                                <iframe width="100%" height="315"
                                                    src="https://www.youtube.com/embed/{{ $details->pro_video }}"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen></iframe>
                                            </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="details_slider owl-carousel">
                                            @foreach ($details->images as $value)
                                                <div class="dimage_item">
                                                    <img src="{{ asset($value->image) }}" class="block__pic" />
                                                </div>
                                            @endforeach
                                            @if($details->pro_video)
                                            <div class="dimage_item">
                                                <iframe width="100%" height="315"
                                                    src="https://www.youtube.com/embed/{{ $details->pro_video }}"
                                                    title="YouTube video player" frameborder="0"
                                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                                    allowfullscreen></iframe>
                                            </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-sm-6 order-3 order-sm-3">
                                <div class="details_right">
                                     
                                    <div class="breadcrumb">
                                        <ul>
                                            <li><a href="{{ url('/') }}">Home</a></li>
                                            <li><span>/</span></li>
                                            <li><a
                                                    href="{{ url('/category/' . $details->category->slug) }}">{{ $details->category->name }}</a>
                                            </li>
                                            @if ($details->subcategory)
                                                <li><span>/</span></li>
                                                <li><a href="#">{{ $details->subcategory ? $details->subcategory->name : '' }}</a>
                                                </li>
                                                @endif @if ($details->childcategory)
                                                <li><span>/</span></li>
                                                    <li> <a href="#">{{ $details->childcategory->name}}</a>
                                                </li>
                                                @endif
                                        </ul>
                                    </div>

                                    <div class="product">
                                        <div class="product-cart">
                                            <p class="name">{{ $details->name }}</p>
                                            <div class="product__flex__data">
                                                 <div class="genarel__data">
                                                     <div class="ratting__details"><p>Reviews({{$reviews->count()}})<i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i></p></div>
                                            @if ($details->variable_count > 0 && $details->type == 0)
                                                <p class="details-price">
                                                    @if ($details->variable->old_price)
                                                        <del>৳ <span
                                                                class="old_price">{{ $details->variable->old_price }}</span></del>
                                                    @endif ৳ <span
                                                        class="new_price">{{ $details->variable->new_price }}</span>
                                                </p>
                                            @else
                                                <p class="details-price">
                                                    @if ($details->old_price)
                                                        <del>৳{{ $details->old_price }}</del>
                                                    @endif ৳{{ $details->new_price }}
                                                </p>
                                            @endif
                                            @if($details->brand)
                                                <div class="pro_brand">
                                                    <p>Brand :
                                                        {{ $details->brand ? $details->brand->name : 'N/A' }}
                                                    </p>
                                                </div>
                                                @endif
                                                @if($details->sku)
                                                <div class="pro_brand">
                                                    <p>SKU :
                                                        {{ $details->sku}}
                                                    </p>
                                                </div>
                                                @endif
                                            <form action="{{ route('cart.store') }}" method="POST" name="formName">
                                                @csrf
                                                <input type="hidden" name="id" value="{{ $details->id }}" />
                                                <input type="hidden" name="category_id" value="{{ $details->category_id }}" />
                                                @if ($productcolors->count() > 0)
                                                    <div class="pro-color" >
                                                        <p class="color-title">Select Color </p>
                                                        <div class="color_inner">
                                                            <div class="size-container">
                                                                <div class="selector">
                                                                    @foreach ($productcolors as $key => $procolor)
                                                                        <div class="selector-item color-item"
                                                                            data-id="{{ $key }}">
                                                                            {{ $procolor->image }}
                                                                            <input type="radio"
                                                                                id="fc-option{{ $procolor->color }}"
                                                                                value="{{ $procolor->color }}"
                                                                                name="product_color"
                                                                                class="selector-item_radio emptyalert stock_color stock_check"
                                                                                required
                                                                                data-color="{{ $procolor->color }}" />
                                                                            <label
                                                                                for="fc-option{{ $procolor->color }}"
                                                                                class="selector-item_label">{{ $procolor->color }}
                                                                            </label>
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
                                                                            <input type="radio" 
                                                                                id="f-option{{ $prosize->size }}"
                                                                                value="{{ $prosize->size }}"
                                                                                name="product_size"
                                                                                class="selector-item_radio emptyalert stock_size  stock_check"
                                                                                data-size="{{ $prosize->size }}"
                                                                            @if($productInCart && $productInCart->options->product_size == $prosize->size) checked @endif
                                                                                required />
                                                                            <label for="f-option{{ $prosize->size }}"
                                                                                class="selector-item_label">{{ $prosize->size }}</label>
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endif
                                                
                                                <div class="product-stock" style="display: none;">
                                                    <p><strong>Stock: </strong><span class="stock"></span></p>
                                                </div>
                                                
                                                <div class="row">
                                                    <div class="qty-cart col-sm-6">
                                                        <div class="quantity">
                                                            <span class="minus">-</span>
                                                            <input type="text" name="qty" value="1" />
                                                            <span class="plus">+</span>
                                                        </div>
                                                    </div>
                                                    @if ($details->size_chart)
                                                    <button type="button" class="size-chart-button" data-bs-toggle="modal" data-bs-target="#sizeChartModal">
                                                        <i class="fa fa-chart-area"></i>
                                                        Size Chart
                                                    </button>
                                                    @endif
                                                </div>
                                                @php
                                                    if ($details->type == 0) {
                                                        $stockQty = $details->variables->sum('stock') ?? 0;
                                                    } else {
                                                        $stockQty = $details->stock ?? 0;
                                                    }
                                                @endphp
                                                @if($stockQty == 0)
                                                 <div class="single_product col-sm-12">
                                                    <button type="button" class="btn btn-danger px-4 my-2 " />Stock Out </button>

                                                </div>
                                                @else
                                                 <div class="mobile_none single_product col-sm-12">
                                                    <button type="button" class="btn px-4 add_cart_btn add_to_event" name="add_cart" id="add_cart_btn">Add To Cart</button>

                                                    <button type="button" class="btn px-4 order_now_btn order_now_btn_m add_to_event" name="order_now" id="order_now">Order Now</button>
                                                </div>
                                                <div class="d-flex desktop_none single_product col-sm-12">
                                                    <button type="button" class="btn px-4 add_cart_btn add_to_event" name="add_cart" id="add_cart_btn_mobile">Add To Cart</button>

                                                    <button type="button" class="btn px-4 order_now_btn order_now_btn_m add_to_event" name="order_now" id="order_now_mobile">Order Now</button>
                                                </div>
                                                @endif
                                               @if($productInCart)
                                               <div class="details-view-cart">
                                                   <a href="{{ route('customer.checkout') }}" class="details-view-button">View Cart</a>
                                               </div>
                                               @endif
                                                 </div>
                                                 </div>
                                             </div>
                                              {{--<div class="single_product col-sm-12">
                                                    @if($productInCart)
                                                    <input type="button" class="btn px-4 add_cart_btn btn-success"  value="Added to cart"/>
                                                    @else
                                                    <input type="submit" class="btn px-4 add_cart_btn"
                                                        onclick="return sendSuccess();" name="add_cart"
                                                        value="Add To Cart " id="add_cart_btn"/>
                                                    @endif
                                                </div>--}}
                                               
                                               </form>
                                                <div class="accordion mt-3" id="productDetailsAccordion">
                                                    <div class="accordion-item">
                                                        <h2 class="accordion-header" id="headingDescription">
                                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDescription" aria-expanded="false" aria-controls="collapseDescription">
                                                                Description
                                                            </button>
                                                        </h2>
                                                        <div id="collapseDescription" class="accordion-collapse collapse" aria-labelledby="headingDescription" data-bs-parent="#productDetailsAccordion">
                                                            <div class="accordion-body">
                                                                {!! $details->description !!}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                                                      </div>
                                     </div>
                                 </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <section class="related-product-section">
        <div class="container">
            <div class="row">
                <div class="related-title">
                    <h5>Related Product</h5>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <div class="product-inner owl-carousel related_slider">
                        @foreach ($products as $key => $value)
                            <div class="product_item wist_item">
                                @include('frontEnd.layouts.partials.product')
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div class="modal fade" id="sizeChartModal" tabindex="-1" aria-labelledby="sizeChartModalHeader" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <img style="height: auto; width: 100%;" src="{{ asset($details->size_chart) }}" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    @endsection 

    @push('script')
    <script src="{{ asset('public/frontEnd/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('public/frontEnd/js/zoomsl.min.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            $(".details_slider").owlCarousel({
                margin: 15,
                items: 1,
                loop: true,
                dots: false,
                nav: false,
                autoplay: false,
            });
            $(".indicator-item,.color-item").on("click", function() {
                var slideIndex = $(this).data('id');
                $('.details_slider').trigger('to.owl.carousel', slideIndex);
            });
        });
    </script>
    <!--Data Layer Start-->
    <script type="text/javascript">
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            ecommerce: null
        });
        dataLayer.push({
            event: "view_item",
            ecommerce: {
                items: [{
                    item_name: "{{ $details->name }}",
                    item_id: "{{ $details->id }}",
                    @if ($details->variable_count > 0 && $details->type == 0)
                        price: "{{ $details->variable->new_price }}",
                    @else
                        price: "{{ $details->new_price }}",
                    @endif
                    item_brand: "{{ $details->brand ? $details->brand->name : '' }}",
                    item_category: "{{ $details->category->name }}",
                    item_variant: "{{ $details->pro_unit }}",
                    currency: "BDT",
                    quantity: 1
                }],

            }
        });
    </script>

    <script>
    $('.add_to_event').click(function (e) {
        // Check if size selection is required
        let hasSize = $('.stock_size').length > 0;

        if (hasSize) {
            let selectedSize = $('input[name="product_size"]:checked').val();

            if (!selectedSize) {
                e.preventDefault();
               
                return false;
            }
        }

        // Fire GA4 Add to Cart event only if validation passed
        window.dataLayer = window.dataLayer || [];
        dataLayer.push({
            event: "add_to_cart",
            ecommerce: {
                currency: "BDT",
                value: {{ floatval(str_replace(',', '', Cart::instance('shopping')->subtotal())) }},
                items: [
                    @foreach (Cart::instance('shopping')->content() as $item)
                        {
                            item_id: "{{ $item->id }}",
                            item_name: "{{ $item->name }}",
                            price: {{ floatval($item->price) }},
                            quantity: {{ $item->qty }}
                        }@if (!$loop->last),@endif
                    @endforeach
                ]
            }
        });
    });
</script>

    <script type="text/javascript">
        $(document).ready(function() {
            $('#order_now').click(function() {
                if(typeof gtag !== 'undefined') {
                    gtag("event", "add_to_cart", {
                        currency: "BDT",
                        value: "1.5",
                        items: [
                            @foreach (Cart::instance('shopping')->content() as $cartInfo)
                                {
                                    item_id: "{{ $details->id }}",
                                    item_name: "{{ $details->name }}",
                                    price: "{{ $details->new_price }}",
                                    currency: "BDT",
                                    quantity: {{ $cartInfo->qty ?? 1 }}
                                },
                            @endforeach
                        ]
                    });
                }
            });
        });
    </script>

    <!-- Data Layer End-->
    <script>
        $(document).ready(function() {
            $(".related_slider").owlCarousel({
                margin: 10,
                items: 5,
                loop: true,
                dots: true,
                nav: false,
                autoplay: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
                responsiveClass: true,
                responsive: {
                    0: {
                        items: 2,
                        nav: false,
                    },
                    600: {
                        items: 3,
                        nav: false,
                    },
                    1000: {
                        items: 5,
                    },
                },
            });
            // $('.owl-nav').remove();
        });
    </script>
    <script>
        $(document).ready(function() {
            $(".minus").click(function() {
                var $input = $(this).parent().find("input");
                var count = parseInt($input.val()) - 1;
                count = count < 1 ? 1 : count;
                $input.val(count);
                $input.change();
                return false;
            });
            $(".plus").click(function() {
                var $input = $(this).parent().find("input");
                $input.val(parseInt($input.val()) + 1);
                $input.change();
                return false;
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.add_to_event').on('click', function(e) {
                e.preventDefault();
                
                var isOrderNow = $(this).hasClass('order_now_btn');
                var form = $('form[name="formName"]');
                
                // Client-side validation
                var sizeRequired = $('.stock_size').length > 0;
                var colorRequired = $('.stock_color').length > 0;
                
                if (sizeRequired) {
                    var selectedSize = $('input[name="product_size"]:checked').val();
                    if (!selectedSize) {
                        toastr.warning("Please select any size");
                        return;
                    }
                }
                
                if (colorRequired) {
                    var selectedColor = $('input[name="product_color"]:checked').val();
                    if (!selectedColor) {
                        toastr.error("Please select any color");
                        return;
                    }
                }

                var formData = form.serialize();
                // Append flag if it is "Order Now" to handle redirection in controller if needed, 
                // but since we handle redirection in JS, we just need to know success.
                
                $.ajax({
                    type: "POST",
                    url: "{{ route('cart.store') }}",
                    data: formData,
                    success: function(data) {
                        if (data.status == 'success') {
                            cart_count();
                            mobile_cart();
                            cart_right_count();
                            cart_left_count();
                            
                            // GA4 Tracking
                             window.dataLayer = window.dataLayer || [];
                             dataLayer.push({
                                 event: "add_to_cart",
                                 ecommerce: {
                                     currency: "BDT",
                                     value: {{ floatval(str_replace(',', '', Cart::instance('shopping')->subtotal())) }}, // Note: this might be slightly off as it's rendered server-side, ideally we get updated total from server response
                                     items: [
                                        // We can't easily iterate PHP cart here for the new item only without server response data
                                        // For now, keeping it simple or relying on existing dataLayer push if it was working? 
                                        // The previous implementation had a mixed PHP/JS approach which doesn't update dynamically for the NEW item via AJAX.
                                        // Proper GA4 tracking for AJAX requires the server to return the added item details.
                                     ]
                                 }
                             });

                            if (isOrderNow) {
                                window.location.href = "{{ route('customer.checkout') }}";
                            } else {
                                $("#success-modal").show();
                                // $("#page-overlay").show(); // Removed overlay
                                setTimeout(function() {
                                    $("#success-modal").fadeOut();
                                }, 5000);
                            }
                        } else {
                            toastr.error(data.message || "Failed to add to cart");
                        }
                    },
                    error: function(xhr) {
                        toastr.error("Failed to update cart");
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $(".rating label").click(function() {
                $(".rating label").removeClass("active");
                $(this).addClass("active");
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $(".thumb_slider").owlCarousel({
                margin: 15,
                items: 4,
                loop: true,
                dots: false,
                autoplay: true,
                nav: true,
                autoplayTimeout: 6000,
                autoplayHoverPause: true,
            });
        });
    </script>

    <script type="text/javascript">
        $(".block__pic").imagezoomsl({
            zoomrange: [3, 3]
        });
    </script>
    <script>
        $(".stock_check").on("click", function() {
            var color = $(".stock_color:checked").data('color');
            var size = $(".stock_size:checked").data('size');
            var id = {{ $details->id }};
            console.log(color);
            if (id) {
                $.ajax({
                    type: "GET",
                    data: {
                        id: id,
                        color: color,
                        size: size
                    },
                    url: "{{ route('stock_check') }}",
                    dataType: "json",
                    success: function(response) {
                        if (response.status) {
                            $('.product-stock').show();
                            $(".stock").html(response.product.stock);
                            $(".old_price").text(response.product.old_price);
                            $(".new_price").text(response.product.new_price);
                            // cart button enable
                            $('.add_cart_btn').prop('disabled', false);
                            $('.order_now_btn').prop('disabled', false);
                        } else {
                            $('.product-stock').hide();
                            toastr.warning("Please select another color or size");
                            $(".stock").empty();
                            // cart button disabled
                            $('.add_cart_btn').prop('disabled', true);
                            $('.order_now_btn').prop('disabled', true);
                        }


                    }
                });
            }
        });
    </script>

 <!--Microdata -->
<script type="application/ld+json">
{
  "@context":"https://schema.org",
  "@type":"Product",
  "productID":"{{$details->id}}",
  "name":"{{$details->name}}",
  "description":"{!!$details->description!!}",
  "url":"{{Request::fullUrl()}}",
  "image":"{{asset($details->image->image)}}",
  "brand":"@if($details->brand_id !=NULL) {{$details->brand->brandName}} @endif",
  "offers": [
    {
      "@type": "Offer",
     @if ($details->variable_count > 0 && $details->type == 0)
      "price": "{{$details->variable->new_price}}",
      @else
      "price": "{{$details->new_price}}",
      @endif
      "priceCurrency": "BDT",
      "itemCondition": "https://schema.org/NewCondition",
      "availability": "https://schema.org/InStock"
    }
  ],
  "additionalProperty": [{
    "@type": "PropertyValue",
    "propertyID": "item_group_id",
    "value": "{{$details->name}}"
  }]
}
</script> 

@endpush
