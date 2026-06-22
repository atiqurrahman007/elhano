<a href="{{route('customer.wishlist')}}">
  <p class="margin-shopping">
    <i class="fa-solid fa-heart"></i>
     <span>{{Cart::instance('wishlist')->count()}}</span>
   </p>
</a>