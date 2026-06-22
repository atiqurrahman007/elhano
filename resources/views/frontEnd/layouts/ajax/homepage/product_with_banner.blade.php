<section class="section-padding product-with-banner">
    <div class="container">
        <div class="row">
            <div class="col-12 mb-3">
                 <div class="section-title">
                    <h3>
                        {{ $section->heading ?? $section->title }}
                    </h3>
                    <a href="{{ $section->params['link'] ?? '#' }}" class="view_all">View All <i class="fa-solid fa-arrow-right"></i></a>
                 </div>
            </div>
        </div>
        <div class="row">
            <!-- Banner Column -->
            <div class="col-xl-5 col-lg-5 col-md-5 mb-4 mb-md-0">
                <div class="banner-wrapper h-100">
                    <a href="{{ $section->params['link'] ?? '#' }}" class="d-block h-100">
                         <img src="{{ asset($section->params['image'] ?? 'public/frontEnd/images/no-image.png') }}" alt="{{ $section->heading ?? 'Banner' }}" class="img-fluid w-100 h-100" style="object-fit: cover; border-radius: 5px; min-height: 400px;">
                    </a>
                </div>
            </div>
            
            <!-- Products Column -->
            <div class="col-xl-7 col-lg-7 col-md-7">
                <div class="row">
                    @foreach($section->data as $value)
                    <div class="col-xl-3 col-lg-4 col-6">
                        @include('frontEnd.layouts.partials.product')
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
