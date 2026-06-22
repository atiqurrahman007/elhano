<section class="homeproduct">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="section-title">
                   <h3>{{ $section->heading }}</h3>
                   <a href="{{route('all_collection')}}" class="view_all">View All <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="product_slider owl-carousel">
                    @foreach ($section->data as $key => $value)
                    <div class="product_item wist_item">
                        @include('frontEnd.layouts.partials.product')
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
