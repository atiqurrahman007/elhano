@extends('frontEnd.layouts.master')
@section('title', $subcategory->name)
@push('seo')
    <meta name="app-url" content="{{ route('subcategory', $subcategory->slug) }}" />
    <meta name="robots" content="index, follow" />
    <meta name="description" content="{{ $subcategory->meta_description }}" />
    <meta name="keywords" content="{{ $subcategory->slug }}" />
    
    <!-- Twitter Card data -->
    <meta name="twitter:card" content="product" />
    <meta name="twitter:site" content="{{ $subcategory->name }}" />
    <meta name="twitter:title" content="{{ $subcategory->name }}" />
    <meta name="twitter:description" content="{{ $subcategory->meta_description }}" />
    <meta name="twitter:creator" content="{{$generalsetting->name}}" />
    <meta property="og:url" content="{{ route('subcategory', $subcategory->slug) }}" />
    <meta name="twitter:image" content="{{ asset($subcategory->image) }}" />

    <!-- Open Graph data -->
    <meta property="og:title" content="{{ $subcategory->name }}" />
    <meta property="og:type" content="product" />
    <meta property="og:url" content="{{ route('subcategory', $subcategory->slug) }}" />
    <meta property="og:image" content="{{ asset($subcategory->image) }}" />
    <meta property="og:description" content="{{ $subcategory->meta_description }}" />
    <meta property="og:site_name" content="{{ $subcategory->name }}" />
@endpush
@section('content')
    <section class="product-section">
        <div class="container">
            <div class="sorting-section">
                <div class="row">
                    <div class="col-sm-6">
                        <div class="category-breadcrumb d-flex align-items-center">
                            <a href="{{ route('home') }}">Home</a>
                            <span>/</span>
                            <strong>{{ $subcategory->name }}</strong>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="showing-data">
                                    <span>Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of
                                        {{ $products->total() }} Results</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="filter_sort">
                                    <div class="filter_btn">
                                        Filter
                                    </div>
                                    <div class="page-sort">
                                        @include('frontEnd.layouts.partials.sort_form')
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-3 filter_sidebar">
                    <div class="filter_close "><i class="fa fa-long-arrow-left"></i> Filter</div>
                    <form action="" class="attribute-submit">
                        {{--<div class="sidebar_item wraper__item">
                            <div class="accordion" id="filter_sidebar1">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseFilter2" aria-expanded="true"
                                            aria-controls="collapseOne">
                                            Gender
                                        </button>
                                    </h2>
                                    <div id="collapseFilter2" class="accordion-collapse collapse show"
                                        data-bs-parent="#filter_sidebar1">
                                        <div class="accordion-body cust_according_body">
                                            <div class="filter-body">
                                                <ul class="space-y-3">
                                                    <li class="subcategory-filter-list">
                                                        <label for="Men" class="subcategory-filter-label">
                                                        <input 
                                                            class="gender-checkbox form-attribute"
                                                            id="Men"
                                                            name="gender[]" 
                                                            value="Men"
                                                            type="checkbox"
                                                            @if (is_array(request()->get('gender')) && in_array('Men', request()->get('gender'))) checked @endif
                                                        />
                                                        <p class="subcategory-filter-name">
                                                            Men
                                                        </p>
                                                    </label>

                                                    </li>
                                                    <li class="subcategory-filter-list">
                                                        <label for="Women" class="subcategory-filter-label">
                                                        <input 
                                                            class="gender-checkbox form-attribute"
                                                            id="Women"
                                                            name="gender[]" 
                                                            value="Women"
                                                            type="checkbox"
                                                            @if (is_array(request()->get('gender')) && in_array('Women', request()->get('gender'))) checked @endif
                                                        />
                                                        <p class="subcategory-filter-name">
                                                            Women
                                                        </p>
                                                    </label>
                                                    </li>
                                                    <li class="subcategory-filter-list">
                                                       <label for="Kids" class="subcategory-filter-label">
                                                        <input 
                                                            class="gender-checkbox form-attribute"
                                                            id="Kids"
                                                            name="gender[]" 
                                                            value="Kids"
                                                            type="checkbox"
                                                            @if (is_array(request()->get('gender')) && in_array('Kids', request()->get('gender'))) checked @endif
                                                        />
                                                        <p class="subcategory-filter-name">
                                                            Kids
                                                        </p>
                                                    </label>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>--}}
                        <!--sidebar item end-->
                            <div class="sidebar_item wraper__item">
                            <div class="accordion" id="filter_sidebar">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseFilter" aria-expanded="true"
                                            aria-controls="collapseOne">
                                            Filter
                                        </button>
                                    </h2>
                                    <div id="collapseFilter" class="accordion-collapse collapse show"
                                        data-bs-parent="#filter_sidebar">
                                        <div class="accordion-body cust_according_body">
                                            <div class="filter-body">
                                                <ul class="space-y-3 grid__this_three">
                                                   @foreach ($sizes as $size)
                                                    <li class="subcategory-filter-list">
                                                        <label for="{{ $size->name . '-' . $size->id }}" class="subcategory-filter-label">
                                                            <input 
                                                                class="form-size form-attribute"
                                                                id="{{ $size->name . '-' . $size->id }}"
                                                                name="size[]" 
                                                                value="{{ $size->name }}"
                                                                type="checkbox"
                                                                @if (is_array(request()->get('size')) && in_array($size->name, request()->get('size'))) checked @endif
                                                            />
                                                            <p class="size-filter-name">{{ $size->name }}</p>
                                                        </label>
                                                    </li>
                                                @endforeach

                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--sidebar item end-->
                        <div class="sidebar_item wraper__item mt-3 mb-3">
                            <div class="accordion" id="filter_sidebar2">
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#collapseFilter1" aria-expanded="true"
                                            aria-controls="collapseOne">
                                            Prices
                                        </button>
                                    </h2>
                                    <div id="collapseFilter1" class="accordion-collapse collapse show"
                                        data-bs-parent="#filter_sidebar2">
                                        <div class="accordion-body cust_according_body">
                                            <div class="filter-body">
                                                <ul class="space-y-3">
                                                 @foreach ($price_ranges as $key => $range)
                                                    <li class="subcategory-filter-list">
                                                        <label for="min_price{{$key}}" class="subcategory-filter-label">
                                                            <input 
                                                                class="form-checkbox form-attribute"
                                                                id="min_price{{$key}}"
                                                                name="min_price[]" 
                                                                value="{{ $range['start'] }}"
                                                                data-max="{{ $range['end'] }}"
                                                                type="checkbox"
                                                                @if (is_array(request()->get('min_price')) && in_array($range['start'], request()->get('min_price'))) checked @endif
                                                            />
                                                            <p class="subcategory-filter-name">
                                                                {{ $range['start'] }}৳ - {{ $range['end'] }}৳
                                                            </p>
                                                        </label>
                                                    </li>
                                                @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--sidebar item end-->
                    
                    </form>
                </div>
                <div class="col-sm-9">
                    <div class="category-product {{$products->total() == 0 ? 'no-product' : ''}}">
                        @forelse($products as $key => $value)
                            <div class="product_item wist_item">
                                @include('frontEnd.layouts.partials.product')
                            </div>
                        @empty
                        <div class="no-found">
                            <img src="{{asset('public/frontEnd/images/not-found.png')}}" alt="">
                        </div>
                        @endforelse
                    </div>
                    <div class="custom_paginate mt-4">
                        {{ $products->appends(request()->query())->links('pagination::bootstrap-4') }}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@push('script')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js"></script>
    <script>
        $("#price-range").click(function() {
            $(".price-submit").submit();
        })
        $(".form-attribute").on('change click',function(){
            $(".attribute-submit").submit();
        })
        $(".sort").change(function() {
            $(".sort-form").submit();
        })
        $(".form-checkbox").change(function() {
            $(".subcategory-submit").submit();
        })
         $(".gender-checkbox").change(function() {
            $(".subcategory-submit").submit();
        })


        $(".form-checkbox").change(function () {
        const url = new URL(window.location.href);

        const minPrice = $(this).val();
        const maxPrice = $(this).data("max");

        let minPrices = url.searchParams.getAll("min_price[]");
        let maxPrices = url.searchParams.getAll("max_price[]");

        if (this.checked) {
            minPrices.push(minPrice);
            maxPrices.push(maxPrice);
        } else {
            const index = minPrices.indexOf(minPrice);
            if (index !== -1) {
                minPrices.splice(index, 1);
                maxPrices.splice(index, 1);
            }
        }

        url.searchParams.delete("min_price[]");
        url.searchParams.delete("max_price[]");

        minPrices.forEach((price) => url.searchParams.append("min_price[]", price));
        maxPrices.forEach((price) => url.searchParams.append("max_price[]", price));

        window.location.href = url.toString();
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
@endpush
