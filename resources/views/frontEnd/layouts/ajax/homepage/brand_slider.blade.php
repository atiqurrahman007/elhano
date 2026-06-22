<section class="brand-section mt-4 mb-4">
    <div class="container">
        <div class="row">
             <div class="col-sm-12">
                <div class="section-title">
                   <h3>{{ $section->heading }}</h3>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="brand_slider owl-carousel owl-theme">
                    @foreach($section->data as $brand)
                    <div class="brand-item">
                        <a href="{{route('brand', $brand->slug)}}">
                            <img src="{{asset($brand->image)}}" alt="{{$brand->name}}" style="height: 80px; object-fit: contain;">
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>

<script>
    $(document).ready(function () {
        $(".brand_slider").owlCarousel({
            margin: 15,
            loop: true,
            dots: false,
            nav: false,
            autoplay: true,
            autoplayTimeout: 4000,
            responsive: {
                0: { items: 3 },
                600: { items: 4 },
                1000: { items: 6 }
            }
        });
    });
</script>
