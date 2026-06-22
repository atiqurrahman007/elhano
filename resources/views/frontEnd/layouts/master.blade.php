<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <meta name="google-site-verification" content="4Qlp6iDe-RxlKf3CJzpDzegbM5IlNbK10kFMSMF1pps" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <title>@yield('title') - {{$generalsetting->name}}</title>
        <!-- App favicon -->
        <link rel="shortcut icon" href="{{asset($generalsetting->favicon)}}" alt="Websolution IT" />
        <meta name="author" content="Websolution IT" />
        <link rel="canonical" href="" />
        @stack('seo') @stack('css')
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/bootstrap.min.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/select2.min.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/animate.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/all.min.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/owl.carousel.min.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/owl.theme.default.min.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/mobile-menu.css')}}" />
        <!-- toastr css -->
        <link rel="stylesheet" href="{{asset('public/backEnd/')}}/assets/css/toastr.min.css" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/wsit-menu.css')}}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/style.css')}}?v={{ filemtime(public_path('frontEnd/css/style.css')) }}" />
        <link rel="stylesheet" href="{{asset('public/frontEnd/css/responsive.css')}}?v={{ filemtime(public_path('frontEnd/css/responsive.css')) }}" />
        <script src="{{asset('public/frontEnd/js/jquery-3.7.1.min.js')}}"></script>
       
       {{--
        @foreach($pixels as $pixel)
        <!-- Facebook Pixel Code -->
        <script>
            !(function (f, b, e, v, n, t, s) {
                if (f.fbq) return;
                n = f.fbq = function () {
                    n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments);
                };
                if (!f._fbq) f._fbq = n;
                n.push = n;
                n.loaded = !0;
                n.version = "2.0";
                n.queue = [];
                t = b.createElement(e);
                t.async = !0;
                t.src = v;
                s = b.getElementsByTagName(e)[0];
                s.parentNode.insertBefore(t, s);
            })(window, document, "script", "https://connect.facebook.net/en_US/fbevents.js");
            fbq("init", "{{{$pixel->code}}}");
            fbq("track", "PageView");
        </script>
        <noscript>
            <img height="1" width="1" style="display: none;" src="https://www.facebook.com/tr?id={{{$pixel->code}}}&ev=PageView&noscript=1" />
        </noscript>
        <!-- End Facebook Pixel Code -->
        @endforeach
        
        --}}

        @foreach($gtm_code as $gtm)
        <!-- Google tag (gtag.js) -->
        <script>
            (function (w, d, s, l, i) {
                w[l] = w[l] || [];
                w[l].push({ "gtm.start": new Date().getTime(), event: "gtm.js" });
                var f = d.getElementsByTagName(s)[0],
                    j = d.createElement(s),
                    dl = l != "dataLayer" ? "&l=" + l : "";
                j.async = true;
                j.src = "https://www.googletagmanager.com/gtm.js?id=" + i + dl;
                f.parentNode.insertBefore(j, f);
            })(window, document, "script", "dataLayer", "GTM-{{ $gtm->code }}");
        </script>
        <!-- End Google Tag Manager -->
        @endforeach
    </head>
    <body class="gotop">

        @php $subtotal = Cart::instance('shopping')->subtotal(); @endphp
        <div class="mobile-menu" >
            <div class="mobile-menu-logo">
                <div class="logo-image">
                    <img src="{{asset($generalsetting->dark_logo)}}" alt="" />
                </div>
                <div class="mobile-menu-close">
                    <i class="fa fa-times"></i>
                </div>
            </div>
            <ul class="first-nav">
                @foreach($categories as $scategory)
                <li class="parent-category">
                    <a href="{{route('category',$scategory->slug)}}" class="menu-category-name {{$scategory->highlight == 1 ? 'special_cat' : ''}}">
                        <img src="{{asset($scategory->image)}}" alt="" class="side_cat_img" />
                        {{$scategory->name}}
                    </a>
                    @if($scategory->subcategories->count() > 0)
                    <span class="menu-category-toggle">
                        <i class="fa fa-caret-down"></i>
                    </span>
                    @endif
                    <ul class="second-nav" style="display: none;">
                        @foreach($scategory->subcategories as $subcategory)
                        <li class="parent-subcategory">
                            <a href="{{route('subcategory',$subcategory->slug)}}" class="menu-subcategory-name">{{$subcategory->name}}</a>
                            @foreach($subcategory->childcategories as $childcat)
                            <li class="childcategory"><a href="{{route('products',$childcat->slug)}}" class="menu-childcategory-name">{{$childcat->name}}</a></li>
                            @endforeach
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endforeach
                <li class="parent-category">
                     <a href="{{route('all_collection')}}" class="menu-category-name"> <img src="{{asset($generalsetting->white_logo)}}" alt="" class="side_cat_img" /> All Collection </a>
                                        
                </li>
            </ul>
            <div class="mobilemenu-bottom">
                <ul>
                    @if(Auth::guard('customer')->user())
                    <li class="for_order">
                        <a href="{{route('customer.account')}}">
                            <i class="fa-regular fa-user"></i>
                            {{Str::limit(Auth::guard('customer')->user()->name,14)}}
                        </a>
                    </li>
                    @else
                    <li class="for_order">
                        <a href="{{route('customer.login')}}">Login / Sign Up</a>
                    </li>
                 @endif
                 <li>
                      <a href="{{route('customer.order_track')}}"> Order Track </a>
                   </li>
                 <li>
                      <a href="{{route('contact')}}">Contact Us </a>
                   </li>
                </ul>
            </div>
        </div>
        <header>
            <!-- mobile header start -->
            <div class="mobile-header" id="m_navbar_top">
                <div class="mobile-logo">
                    <div class="menu-bar">
                        <a class="toggle">
                            <i class="fa-solid fa-bars"></i>
                        </a>
                    </div>
                    <div class="menu-logo">
                        <a href="{{route('home')}}"><img src="{{asset($generalsetting->white_logo)}}" alt="" /></a>
                    </div>
                    <div class="shopping_wishlist" id="wishlist-qty">
                        <a href="{{route('customer.wishlist')}}">
                          <p class="margin-shopping">
                            <i class="fa-solid fa-heart"></i>
                             <span>{{Cart::instance('wishlist')->count()}}</span>
                           </p>
                        </a>
                    </div>
                    <div class="menu-bag">
                        <a href="{{route('customer.checkout')}}" class="margin-shopping">
                            <i class="fa-solid fa-cart-shopping"></i>
                            <span class="mobilecart-qty">{{Cart::instance('shopping')->count()}}</span>
                        </a>
                    </div>
                    <div class="search__bar_mb"><li><a class="search_toggle"><i class="fas fa-search"></i></a></li></div>
                </div>
            </div>
            <!-- mobile header end -->

            <!-- main header start -->
            <div class="main-header" id="navbar_top">
                <div class="top-header">
                    <div class="container">
                        <div class="row">
                            <div class="col-sm-6 text-left">
                                <ul class="top-list">
                                    <li><a href="mailto:{{$contact->hotmail}}"><i class="fa-solid fa-envelope"></i> {{$contact->hotmail}}</a></li>
                                    <li><a href="tel:{{$contact->hotline}}"><i class="fa-solid fa-phone"></i> {{$contact->hotline}}</a></li>
                                </ul>
                            </div>
                            <div class="col-sm-6 text-end">
                                <ul class="top-list justify-content-end">
                                    <li><a href="{{route('customer.wishlist')}}">My Wishlist ({{Cart::instance('wishlist')->count()}})</a></li>
                                    <li><a href="{{route('customer.order_track')}}">Track Order</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="logo-area">
                    <div class="container">
                        <div class="row align-items-center">
                            <div class="col-sm-2">
                                <div class="logo-header">
                                    <div class="main-logo">
                                        <a href="{{route('home')}}"><img src="{{asset($generalsetting->dark_logo)}}" alt="" /></a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-5">
                                <div class="main-menu">
                                    <ul>
                                        @foreach ($categories as $category)
                                        <li>
                                            <a href="{{route('category',$category->slug)}}" class="products-toggle" style="color: black;">
                                                {{$category->name}}
                                            </a>
                                            @if($category->subcategories->count() > 0)
                                            <div class="mega_menu">
                                                <div class="mega-menu-inner">
                                                    <!-- Column 1: Clean Vertical List of Child Categories -->
                                                    <div class="mega-menu-sidebar">
                                                        <ul class="mega-sidebar-list">
                                                            @foreach ($category->subcategories as $subcat)
                                                            <li>
                                                                <a href="{{ route('subcategory', $subcat->slug) }}">
                                                                    {{ $subcat->name }} <i class="fa fa-angle-right"></i>
                                                                </a>
                                                            </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>

                                                    <!-- Column 2: New Arrival Products Grid -->
                                                    <div class="mega-menu-content">
                                                        <h5 class="mega-content-title">New Arrivals</h5>
                                                        <div class="mega-products-grid">
                                                            @foreach($category->products as $new_item)
                                                            <div class="mega-product-card">
                                                                <a href="{{ route('product', $new_item->slug) }}">
                                                                    <div class="mega-card-img">
                                                                        <img src="{{ asset($new_item->image ? $new_item->image->image : 'public/frontEnd/images/no-image.png') }}" alt="{{ $new_item->name }}">
                                                                    </div>
                                                                    <div class="mega-card-info">
                                                                        <h6>{{ Str::limit($new_item->name, 25) }}</h6>
                                                                        <span class="price">৳ {{ $new_item->new_price }}</span>
                                                                    </div>
                                                                </a>
                                                            </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            @endif
                                        </li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="main-search">
                                    <form action="{{route('search')}}">
                                        <input type="text" placeholder="Search product..." class="search_click keyword" name="keyword">
                                        <button><i class="fa-solid fa-search"></i></button>
                                    </form>
                                    <div class="search_result"></div>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="header-list-items">
                                    <ul>
                                        <li class="cart-dialog" id="cart-qty">
                                            <a href="{{route('customer.checkout')}}">
                                                <p class="margin-shopping">
                                                    <i class="fa-solid fa-cart-shopping"></i>
                                                    <span class="cart-badge">{{Cart::instance('shopping')->count()}}</span>
                                                </p>
                                            </a>
                                        </li>
                                        @if(Auth::guard('customer')->user())
                                        <li class="for_order">
                                            <a href="{{route('customer.account')}}">
                                                <i class="fa-regular fa-user"></i>
                                                <span class="user-name">{{Str::limit(Auth::guard('customer')->user()->name,14)}}</span>
                                            </a>
                                        </li>
                                        @else
                                        <li class="for_order login-btn">
                                            <a href="{{route('customer.login')}}">
                                                <i class="fa-regular fa-user"></i> Login / SignUp
                                            </a>
                                        </li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- main-header end -->
        </header>
        <div id="content">
            @yield('content')
        </div>
        <!-- content end -->
        <footer>
            <div class="footer-top">
                <div class="container">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="footer-about">
                                <a href="{{route('home')}}">
                                    <img src="{{asset($generalsetting->dark_logo)}}" alt="" />
                                </a>
                                <p class="address-design"><i class="fa-solid fa-house-chimney"></i><span>{{$contact->address}}</span></p>
                                <p class="address-design"><i class="fa-solid fa-phone-volume"></i><a href="tel:{{$contact->hotline}}" class="footer-hotlint">{{$contact->hotline}}</a></p>
                                <p class="address-design"><i class="fa-solid fa-envelope-circle-check"></i><a href="mailto:{{$contact->hotmail}}" class="footer-hotlint">{{$contact->hotmail}}</a></p>
                                 <ul class="social_link mt-2">
                                    @foreach($socialicons as $value)
                                    <li>
                                        <a  href="{{$value->link}}"><i class="{{$value->icon}}"></i></a>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <!-- col end -->
                        <div class="col-sm-4">
                            <div class="footer-menu">
                                <ul>
                                    <li class="title "><a>Useful Link</a></li>
                                    @foreach($pages as $page)
                                    <li><a href="{{route('page',['slug'=>$page->slug])}}">{{$page->name}}</a></li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <!-- col end -->
                        <div class="col-sm-4">
                            <div class="footer-menu">
                                <ul>
                                    <li class="title"><a>Customer Link</a></li>
                                    <li><a href="{{route('customer.register')}}">Register</a></li>
                                    <li><a href="{{route('customer.login')}}">Login</a></li>
                                    <li><a href="{{route('customer.forgot.password')}}">Forgot Password?</a></li>
                                    <li><a href="{{route('contact')}}">Contact</a></li>
                                </ul>
                            </div>
                        </div>

                        <!-- col end -->
                       
                        <!-- col end -->
                    </div>
                    <div class="col-sm-12">
                        <div class="fotter_develop">
                             <p>Copyright © {{ date('Y') }} {{$generalsetting->name}}. All rights reserved. Developed By <a href="https://roasdata.com">RoasData Tech</a>
                        </div>
                    </div>
                </div>
            </div>
          
        </footer>
        <!--=====-->
        <div class="fixed_whats">
            <a href="https://api.whatsapp.com/send?phone={{$contact->whatsapp}}" target="_blank"><i class="fa-brands fa-whatsapp"></i></a>
        </div>
        <div class="fixed_mesenger">
            <a href="{{$contact->mesenger}}" target="_blank"><i class="fa-brands fa-facebook-messenger"></i></a>
        </div>

        <div class="scrolltop" style="">
            <div class="scroll">
                <i class="fa fa-angle-up"></i>
            </div>
        </div>

        <!-- /. fixed sidebar -->
          <div class="search_inner">
            <div class="search_head">
                <p>Search Product</p>
                <a class="search_close"><i data-feather="x"></i></a>
            </div>
            <div class="search_body">
                <form action="{{route('search')}}">
                    <div class="form-group">
                        <select name="category_id" class="form-control search_click scategory">
                            <option value="">All &#9660; <i class="fa-solid fa-search"></i></option>
                            @foreach($categories as $key=>$value)
                               <option value="{{$value->id}}">{{$value->name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group form_inner">
                        <input type="text" class="search_click keyword" name="keyword">
                        <button><i class="fa-solid fa-search"></i></button>
                    </div>
                </form>
            </div>
            <div class="search_result"></div>
        </div>
        
        
        <div id="floating-cart" style="top:55%; box-shadow: 0 5px 10px #626262; background-color: #ed145b;"
            data-bs-toggle="offcanvas" href="#offcanvasExample2" role="button" aria-controls="offcanvasExample">
            @php
                $count = Cart::instance('shopping')->count();
                $total = Cart::instance('shopping')->subtotal();
            @endphp
            <div class="floating-bag">
                <i class="fa fa-shopping-bag" aria-hidden="true"></i>
                <div class="cart-count" id="bag-items"><span class="cart-item-count">{{ $count }}</span>
                    ITEMS</div>
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
        </div>
        
          <div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasExample2"
            aria-labelledby="offcanvasExampleLabel">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title" id="offcanvasExampleLabel">Cart Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body position_relative" id="add-data">
                @include('frontEnd.layouts.ajax.cart_right_data')
            </div>
        </div>
        
        <div id="custom-modal"></div>
        <div id="success-modal" style="display: none; position: fixed; z-index: 9999; top: 0; left: 0; width: 100%; height: 100%;">
            @include('frontEnd.layouts.ajax.cart_success_modal')
        </div>
        <div id="page-overlay"></div>
        <div id="loading"><div class="custom-loader"></div></div>
        <script src="{{asset('public/frontEnd/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('public/frontEnd/js/owl.carousel.min.js')}}"></script>
        <script src="{{asset('public/frontEnd/js/mobile-menu.js')}}"></script>
        <script src="{{asset('public/frontEnd/js/wsit-menu.js')}}"></script>
        <script src="{{asset('public/frontEnd/js/mobile-menu-init.js')}}"></script>
        <script src="{{asset('public/frontEnd/js/wow.min.js')}}"></script>
         <!-- feather icon -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/feather-icons/4.29.0/feather.min.js"></script>
        <script>
            feather.replace();
        </script>
        <script src="{{asset('public/frontEnd/js/script.js')}}"></script>

        <!-- select2 -->
        <script src="{{ asset('public/frontEnd/') }}/js/select2.min.js"></script>
        <script>
            $(".select2").select2();
        </script>

        <script>
            new WOW().init();
        </script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>


        <script src="{{asset('public/backEnd/')}}/assets/js/toastr.min.js"></script>
        {!! Toastr::message() !!} @stack('script')
        <script>
            $(".shop_icon").on("click", function () {
                var id = $(this).data("id");
                $("#loading").show();
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id},
                        url: "{{route('review.popup')}}",
                        success: function (data) {
                            if (data) {
                                $("#custom-modal").html(data);
                                $("#custom-modal").show();
                                $("#loading").hide();
                                $("#page-overlay").show();
                            }
                        },
                    });
                }
            });
        </script>
        <!-- quick view end -->
       <script>
        $('.wishlist_store').on('click',function(){
        var id = $(this).data('id'); 
        var qty = 1;   
        $("#loading").show();
        if(id){
            $.ajax({
               type:"GET",
               data:{'id':id,'qty':qty?qty:1},
               url:"{{route('wishlist.store')}}",
               success:function(data){               
                if(data){
                    $("#loading").hide();
                    toastr.success('success', 'Product added in wishlist');
                    return wishlist_count()+mobile_wishlist_count();
                }
               }
            });
         }  
       });

        $('.wishlist_remove').on('click',function(){
        var id = $(this).data('id');   
        $("#loading").show();
        if(id){
            $.ajax({
               type:"GET",
               data:{'id':id},
               url:"{{route('wishlist.remove')}}",
               success:function(data){               
                if(data){
                    $("#wishlist").html(data);
                    $("#loading").hide();
                    //return wishlist_count();
                }
               }
            });
         }  
       });
        function wishlist_count(){
            $.ajax({
               type:"GET",
               url:"{{route('wishlist.count')}}",
               success:function(data){               
                if(data){
                    $("#wishlist-qty").html(data);
                }else{
                   $("#wishlist-qty").empty();
                }
               }
            }); 
       };
       
       $(document).on("click", '.remove_all', function(e) {
            e.preventDefault(); 
            $.ajax({
                type: "GET",
                url: "{{ route('cart_all.clear') }}",
                success: function(data) {
                    if (data.success) { 
                        cart_count();
                        mobile_cart();
                        cart_left_count();
                        cart_right_count();
                        toastr.success("Cart clear successfully.");

                    }
                },
                error: function(xhr) {
                    toastr.warning("Something went wrong while clearing the cart.");
                }
            });
        });

    </script>
        <script>
            $(".addcartbutton").on("click", function () {
                var id = $(this).data("id");
                var qty = 1;
                if (id) {
                    $.ajax({
                        cache: "false",
                        type: "GET",
                        url: "{{url('add-to-cart')}}/" + id + "/" + qty,
                        dataType: "json",
                        success: function (data) {
                            if (data) {
                                toastr.success("Success", "Product add to cart successfully");
                                return cart_count() + mobile_cart() +  cart_right_count() + cart_left_count();
                            }
                        },
                    });
                }
            });
            $(document).on('click', '.cart_store', function(e) {
            e.preventDefault();
                var id = $(this).data("id");
                var qty = $(this).parent().find("input").val();
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id, qty: qty ? qty : 1 },
                        url: "{{route('cart.store')}}",
                        success: function (data) {
                            if (data) {
                                toastr.success("Success", "Product add to cart succfully");
                                return cart_count() + mobile_cart() +  cart_right_count() + cart_left_count();
                            }
                        },
                    });
                }
            });

           $(document).on('click', '.cart_remove', function(e) {
            e.preventDefault();
                var id = $(this).data("id");
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.remove')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                return cart_count() + mobile_cart() +  cart_right_count() + cart_left_count() + cart_summary();
                            }
                        },
                    });
                }
            });

             $(document).on('click', '.cart_increment', function(e) {
            e.preventDefault()
                var id = $(this).data("id");
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.increment')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                return cart_count() + mobile_cart() +  cart_right_count() + cart_left_count();
                            }
                        },
                    });
                }
            });
            
            
             $(document).on('click', '.cart_decrement', function(e) {
              e.preventDefault()
                var id = $(this).data("id");
                if (id) {
                    $.ajax({
                        type: "GET",
                        data: { id: id },
                        url: "{{route('cart.decrement')}}",
                        success: function (data) {
                            if (data) {
                                $(".cartlist").html(data);
                                return cart_count() + mobile_cart() +  cart_right_count() + cart_left_count();
                            }
                        },
                    });
                }
            });


            function cart_count() {
                $.ajax({
                    type: "GET",
                    url: "{{route('cart.count')}}",
                    success: function (data) {
                        if (data) {
                            $("#cart-qty").html(data);
                        } else {
                            $("#cart-qty").empty();
                        }
                    },
                });
            }
            
            function cart_right_count() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('cart_right.count') }}",
                    success: function(data) {
                        if (data) {
                            $("#add-data").html(data);
                        } else {
                            $("#add-data").empty();
                        }
                    },
                });
            }
             function cart_left_count() {
                $.ajax({
                    type: "GET",
                    url: "{{ route('cart_left.count') }}",
                    success: function(data) {
                        if (data) {
                            $("#floating-cart").html(data);
                        } else {
                            $("#floating-cart").empty();
                        }
                    },
                });
            }
            function mobile_cart() {
                $.ajax({
                    type: "GET",
                    url: "{{route('mobile.cart.count')}}",
                    success: function (data) {
                        if (data) {
                            $(".mobilecart-qty").html(data);
                        } else {
                            $(".mobilecart-qty").empty();
                        }
                    },
                });
            }
            function cart_summary() {
                $.ajax({
                    type: "GET",
                    url: "{{route('shipping.charge')}}",
                    dataType: "html",
                    success: function (response) {
                        $(".cart-summary").html(response);
                    },
                });
            }
        </script>
        <!-- cart js end -->
        <script>
            $(".search_click").on("keyup change", function () {
                var category = $(".scategory").val();
                var keyword = $(".keyword").val();
               
                $.ajax({
                    type: "GET",
                    data: {category:category,keyword: keyword },
                    url: "{{route('livesearch')}}",
                    success: function (products) {
                        if (products) {
                            $("#loading").hide();
                            $(".search_result").html(products);
                        } else {
                            $(".search_result").empty();
                        }
                    },
                });
            });
        </script>
      
        <script>
            $(".district").on("change", function () {
                var id = $(this).val();
                $.ajax({
                    type: "GET",
                    data: { id: id },
                    url: "{{route('districts')}}",
                    success: function (res) {
                        if (res) {
                            $(".area").empty();
                            $(".area").append('<option value="">Select..</option>');
                            $.each(res, function (key, value) {
                                $(".area").append('<option value="' + key + '" >' + value + "</option>");
                            });
                        } else {
                            $(".area").empty();
                        }
                    },
                });
            });
        </script>
        <script>
            $(".toggle").on("click", function () {
                $("#page-overlay").show();
                $(".mobile-menu").addClass("active");
            });
            $(".search_toggle").on("click", function () {
                $("#page-overlay").show();
                $(".search_inner").addClass("active");
            });

            $(".search_close").on("click", function () {
                $("#page-overlay").hide();
                $(".search_inner").removeClass("active");
            });
            $("#page-overlay").on("click", function () {
                $("#page-overlay").hide();
                $("#custom-modal").hide();
                $(".search_inner").removeClass("active");
                $(".mobile-menu").removeClass("active");
                $(".feature-products").removeClass("active");
            });

            $(".mobile-menu-close").on("click", function () {
                $("#page-overlay").hide();
                $(".mobile-menu").removeClass("active");
            });

            $(".mobile-filter-toggle").on("click", function () {
                $("#page-overlay").show();
                $(".feature-products").addClass("active");
            });
        </script>
        <script>
            $(document).ready(function () {
                $(".parent-category").each(function () {
                    const menuCatToggle = $(this).find(".menu-category-toggle");
                    const secondNav = $(this).find(".second-nav");

                    menuCatToggle.on("click", function () {
                        menuCatToggle.toggleClass("active");
                        secondNav.slideToggle("fast");
                        $(this).closest(".parent-category").toggleClass("active");
                    });
                });
                $(".parent-subcategory").each(function () {
                    const menuSubcatToggle = $(this).find(".menu-subcategory-toggle");
                    const thirdNav = $(this).find(".third-nav");

                    menuSubcatToggle.on("click", function () {
                        menuSubcatToggle.toggleClass("active");
                        thirdNav.slideToggle("fast");
                        $(this).closest(".parent-subcategory").toggleClass("active");
                    });
                });
            });
        </script>

        <script>
            var menu = new MmenuLight(document.querySelector("#menu"), "all");

            var navigator = menu.navigation({
                selectedClass: "Selected",
                slidingSubmenus: true,
                // theme: 'dark',
                title: "ক্যাটাগরি",
            });

            var drawer = menu.offcanvas({
                // position: 'left'
            });
            document.querySelector('a[href="#menu"]').addEventListener("click", (evnt) => {
                evnt.preventDefault();
                drawer.open();
            });
        </script>

        <script>
            $(window).scroll(function () {
                if ($(this).scrollTop() > 50) {
                    $(".scrolltop:hidden").stop(true, true).fadeIn();
                } else {
                    $(".scrolltop").stop(true, true).fadeOut();
                }
            });
            $(function () {
                $(".scroll").click(function () {
                    $("html,body").animate({ scrollTop: $(".gotop").offset().top }, "1000");
                    return false;
                });
            });
        </script>
        <script>
            $(".filter_btn").click(function () {
                $(".filter_sidebar").addClass("active");
                $("body").css("overflow-y", "hidden");
            });
            $(".filter_close").click(function () {
                $(".filter_sidebar").removeClass("active");
                $("body").css("overflow-y", "auto");
            });
        </script>

        <script>
            $(document).ready(function () {
                $(".logoslider").owlCarousel({
                    margin: 0,
                    loop: true,
                    dots: false,
                    nav: false,
                    autoplay: true,
                    autoplayTimeout: 6000,
                    animateOut: "fadeOut",
                    animateIn: "fadeIn",
                    smartSpeed: 3000,
                    autoplayHoverPause: true,
                    responsiveClass: true,
                    responsive: {
                        0: {
                            items: 1,
                            nav: false,
                            dots: false,
                        },
                        600: {
                            items: 1,
                            nav: false,
                            dots: false,
                        },
                        1000: {
                            items: 1,
                            nav: false,
                            loop: true,
                            dots: false,
                        },
                    },
                });

                $(".category-slider").owlCarousel({
                    margin: 10,
                    loop: true,
                    dots: false,
                    nav: false,
                    autoplay: true,
                    autoplayTimeout: 6000,
                    smartSpeed: 1000,
                    responsiveClass: true,
                    responsive: {
                        0: {
                            items: 3,
                        },
                        480: {
                            items: 4,
                        },
                        600: {
                            items: 5,
                        },
                        1000: {
                            items: 8,
                        },
                    },
                });
            });
        </script>
        <script src="{{ asset('public/frontEnd/js/owl.carousel.min.js') }}"></script>

        <!-- Google Tag Manager (noscript) -->
        @foreach($gtm_code as $gtm)
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-{{$gtm->code}}" height="0" width="0" style="display: none; visibility: hidden;"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
        @endforeach

        <script>
            function copyCouponCode() {
                var couponCode = document.getElementById("couponCode").innerText;
                var tempInput = document.createElement("input");
                tempInput.value = couponCode;
                document.body.appendChild(tempInput);
                tempInput.select();
                tempInput.setSelectionRange(0, 99999);
                document.execCommand("copy");
                document.body.removeChild(tempInput);
                toastr.success('Coupon Code copied successfully!');
            }
        </script>
        <script>
            document.addEventListener("DOMContentLoaded", function(){
              window.addEventListener('scroll', function() {
                  if (window.scrollY >50) {
                    document.getElementById('navbar_top').classList.add('fixed-top');
                    navbar_height = document.querySelector('.main-header').offsetHeight;
                    document.body.style.paddingTop = navbar_height + 'px';
                  } else {
                    document.getElementById('navbar_top').classList.remove('fixed-top');
                    document.body.style.paddingTop = '0';
                  } 
              });
            });
            /*=== Main Menu Fixed === */
            document.addEventListener("DOMContentLoaded", function(){
              window.addEventListener('scroll', function() {
                  if (window.scrollY >0) {
                    document.getElementById('m_navbar_top').classList.add('fixed-top');
                    navbar_height = document.querySelector('.mobile-header').offsetHeight;
                    document.body.style.paddingTop = navbar_height + 'px';
                  } else {
                    document.getElementById('m_navbar_top').classList.remove('fixed-top');
                    document.body.style.paddingTop = '0';
                  } 
              });
            });
            /*=== Main Menu Fixed === */
        </script>
        <script>
            $(document).on('click', '.add_to_cart_ajax', function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                var type = $(this).data('type');
                var url = $(this).data('url');

                if (type == 'variable') {
                    if (id) {
                        $.ajax({
                            type: "GET",
                            data: { id: id },
                            url: url,
                            success: function(data) {
                                if (data) {
                                    $("#custom-modal").html(data);
                                    $("#custom-modal").show();
                                    $("#page-overlay").show();
                                }
                            }
                        });
                    }
                } else {
                    if (id) {
                        $.ajax({
                            type: "POST",
                            data: {
                                id: id,
                                qty: 1,
                                _token: "{{ csrf_token() }}"
                            },
                            url: "{{ route('cart.store') }}",
                            success: function(data) {
                                if (data.status == 'success') {
                                    cart_count();
                                    mobile_cart();
                                    cart_right_count();
                                    cart_left_count();
                                    $("#success-modal").show();
                                    // $("#page-overlay").show(); // Removed overlay
                                    setTimeout(function() {
                                        $("#success-modal").fadeOut();
                                    }, 5000);
                                } else {
                                     toastr.error(data.message || "Failed to add to cart");
                                }
                            },
                             error: function(xhr) {
                                toastr.error("Failed to add to cart");
                            }
                        });
                    }
                }
            });

            // Close success modal
            $(document).on('click', '.close-success-modal', function() {
                $("#success-modal").hide();
                $("#page-overlay").hide();
            });
        </script>
    </body>
</html>
