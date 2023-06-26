(function($) {
    $(document).ready(function() {
        if(typeof Swiper == 'function'){
            var swiper = new Swiper(".wdm-case-studies .case-study-slider", {
                loop: true,
                slidesPerView: 3,
                spaceBetween: 25,
                centeredSlides: true,
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true
                },
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev"
                }
            });
        }
    });
})(jQuery);