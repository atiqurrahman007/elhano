<div class="modal-view quick-product">
	<button class="close-modal">x</button>
	<div class="quick-product-content">
		<div class="left_review_popup">
		    <div class="prev_review shop_icon" data-id="{{$review->id-1}}">
		        <i class="fa-solid fa-angle-left"></i>
		    </div>
            <img src="{{asset($review->image)}}" alt="">
            <div class="next_review shop_icon" data-id="{{$review->id+1}}">
                <i class="fa-solid fa-angle-right"></i>
            </div>
        </div>
        <div class="right-review-data">
            <p>{{$rev_product->name}}</p>
            <a href="{{ route('product', $rev_product->slug) }}"><img src="{{asset($rev_product->image->image)}}" alt=""></a>
            <strong>Price: {{$rev_product->new_price}}</strong>
            <p>{{$review->review}}</p>
        </div>
    </div>
</div>
<script src="{{ asset('public/frontEnd/js/jquery-3.6.3.min.js') }}"></script>
<script>
	$('.close-modal').on('click',function(){
        $("#custom-modal").hide();
        $("#page-overlay").hide();
     });
   
</script>
<script>
    $(".shop_icon").on("click", function () {
        var id = $(this).data("id");
        // $("#loading").show();
        if (id) {
            $.ajax({
                type: "GET",
                data: { id},
                url: "{{route('review.popup')}}",
                success: function (data) {
                    if (data) {
                        $("#custom-modal").html(data);
                        // $("#custom-modal").show();
                        // $("#loading").hide();
                        // $("#page-overlay").show();
                    }
                },
            });
        }
    });
</script>
<!-- cart js start -->