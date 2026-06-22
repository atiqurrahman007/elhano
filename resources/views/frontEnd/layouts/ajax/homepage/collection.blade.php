<section class="home-collection">
    <div class="container">
        <div class="row">
           <div class="col-sm-12">
               <div class="collection-item">
                   <a href="{{ $section->params['link'] ?? '#' }}">
                       <img src="{{ asset($section->params['image'] ?? 'public/frontEnd/images/no-image.png') }}" class="img-fluid w-100" alt="{{ $section->heading }}">
                   </a>
               </div>
           </div>
        </div>
    </div>
</section>
