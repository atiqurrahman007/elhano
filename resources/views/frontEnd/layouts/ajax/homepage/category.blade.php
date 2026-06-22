<section class="homeproduct">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="section-title">
                   <h3>{{ $section->heading ?? ($section->category->name ?? '') }}</h3>
                   <a href="{{ isset($section->category) ? route('category', $section->category->slug) : '#' }}" class="view_all">View All <i class="fa-solid fa-chevron-right"></i></a>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="product_slider owl-carousel">
                    @if(isset($section->data))
                        @foreach ($section->data as $key => $value)
                        <div class="product_item wist_item">
                            @include('frontEnd.layouts.partials.product')
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>
