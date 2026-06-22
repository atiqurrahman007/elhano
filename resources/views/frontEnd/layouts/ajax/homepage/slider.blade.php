@php
    $sliders = collect();
    $sliderM = collect();
    
    if(isset($section->params['images']) && is_array($section->params['images'])) {
        foreach($section->params['images'] as $key => $image) {
            $link = $section->params['links'][$key] ?? '#';
             // Create a standard object/array structure to match loop
             $sliders->push((object)['image' => $image, 'link' => $link]);
             $sliderM->push((object)['image' => $image, 'link' => $link]); // Use same for mobile for now
        }
    } elseif(isset($section->data)) {
         $sliders = $section->data->where('category_id', 1);
         $sliderM = $section->data->where('category_id', 3);
    }
@endphp
<section class="slider-section">
    <div class="home-slider-container">
        <div class="main_slider owl-carousel owl-theme desktop_view">
            @foreach ($sliders as $key => $value)
            <div class="slider-item">
                <a href="{{$value->link}}">
                    <img src="{{ asset($value->image) }}" alt="" />
                </a>
            </div>
            @endforeach
        </div>
        <div class="main_slider owl-carousel owl-theme mobile_view">
            @foreach ($sliderM as $key => $value)
            <div class="slider-item">
                <a href="{{$value->link}}">
                    <img src="{{ asset($value->image) }}" alt="" />
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
