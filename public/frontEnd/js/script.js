jQuery(document).ready(function () {
    "use strict";
        // main slider 
        $(".main_slider").owlCarousel({
            items: 1,
            loop: true,
            dots: false,
            autoplay: true,
            nav: true,
            autoplayHoverPause: false,
            margin: 30,
            mouseDrag: true,
            smartSpeed: 1000,
            autoplayTimeout: 3000,

            navText: ["<i class='fa-solid fa-angle-left'></i>",
                "<i class='fa-solid fa-angle-right'></i>"
            ],
            responsive: {
              0: {         
                margin: 15,
              },
              600: {
                margin: 15,
              },
              1000: {
                margin: 30,
              },
            },
        });
    $('.select2').select2();
    
    

})
