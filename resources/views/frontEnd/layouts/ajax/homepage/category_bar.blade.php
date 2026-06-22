<section class="category-bar">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="category-slider owl-carousel">
                     @foreach($section->data as $category)
                    <div class="cat-item">
                        <a href="{{route('category',$category->slug)}}">
                            <div class="cat-img">
                                <img src="{{asset($category->image)}}" alt="{{$category->name}}">
                            </div>
                            <div class="cat-name">
                                <p>{{$category->name}}</p>
                            </div>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
