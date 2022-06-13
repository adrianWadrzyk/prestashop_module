$(document).ready(function() {
$(".vertical-slick-carousel").slick({
       vertical: true,
       slidesToShow: 3,
       slidesToScroll: 3,
       verticalSwiping: true,
       arrows: false,
      })

    $('.arrow_back').click(function(e){
        $(e.currentTarget).parents(".single-block").find('.vertical-slick-carousel').slick('slickPrev');
      })
      
      $('.arrow_next').click(function(e){
        $(e.currentTarget).parents(".single-block").find('.vertical-slick-carousel').slick('slickNext');
      })

      prestashop.on("updateCart", function(event) {
        if(event.resp.hasError) {
          alert(event.resp.errors)
        }
      })
});