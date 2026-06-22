@extends('frontEnd.layouts.master')
@section('title', $generalsetting->meta_title)
@push('seo')
<meta name="app-url" content="" />
<meta name="robots" content="index, follow" />
<meta name="description" content="{{$generalsetting->meta_description}}" />
<meta name="keywords" content="{{$generalsetting->meta_keyword}}" />
<!-- Open Graph data -->
<meta property="og:title" content="{{$generalsetting->meta_title}}" />
<meta property="og:type" content="website" />
<meta property="og:url" content="" />
<meta property="og:image" content="{{ asset($generalsetting->white_logo) }}" />
<meta property="og:description" content="{{$generalsetting->meta_description}}" />
@endpush @push('css')
<link rel="stylesheet" href="{{ asset('public/frontEnd/css/owl.carousel.min.css') }}" />
<link rel="stylesheet" href="{{ asset('public/frontEnd/css/owl.theme.default.min.css') }}" />
@endpush @section('content')

@foreach($sections as $section)
    @if($section->section_key == 'slider')
        @include('frontEnd.layouts.ajax.homepage.slider', ['section' => $section])
    @elseif($section->section_key == 'category_bar')
        @include('frontEnd.layouts.ajax.homepage.category_bar', ['section' => $section])
    @elseif($section->section_key == 'product_grid')
        @include('frontEnd.layouts.ajax.homepage.product_grid', ['section' => $section])
    @elseif($section->section_key == 'brand_slider')
        @include('frontEnd.layouts.ajax.homepage.brand_slider', ['section' => $section])
    @elseif($section->section_key == 'product_with_banner')
        @include('frontEnd.layouts.ajax.homepage.product_with_banner', ['section' => $section])
    @endif
@endforeach

<div class="footer-gap"></div>
@endsection @push('script')
<script src="{{ asset('public/frontEnd/js/owl.carousel.min.js') }}"></script>
<script>
    $(document).ready(function () {
        // Main/Category Slider (if used in partial)
        $(".category-slider1").owlCarousel({
            margin: 15,
            loop: true,
            dots: false,
            nav: false,
            autoplay: true,
            autoplayTimeout: 6000,
            autoplayHoverPause: true,
            responsiveClass: true,
            navText: ["<i class='fa-solid fa-angle-left'></i>", "<i class='fa-solid fa-angle-right'></i>"],
            responsive: {
                0: {
                    items: 3,
                },
                600: {
                    items: 4,
                },
                1000: {
                    items: 8,
                },
            },
        });

        // Product Slider
        $(".product_slider").owlCarousel({
            margin: 15,
            loop: true,
            dots: false,
            nav: true,
            autoplay: true,
            autoplayTimeout: 6000,
            autoplayHoverPause: true,
            responsiveClass: true,
            navText: ["<i class='fa-solid fa-angle-left'></i>", "<i class='fa-solid fa-angle-right'></i>"],
            responsive: {
                0: {
                    items: 2,
                },
                600: {
                    items: 3,
                },
                1000: {
                    items: 5,
                },
            },
        });
       
    });
</script>
@endpush
