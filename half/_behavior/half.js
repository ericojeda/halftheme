(function ($) {
  "use strict";
  Drupal.behaviors.eci = {
    attach: function (context, settings) {

    	

$('a[href^="#"]').click(function(event) {

        var target = $( $(this).attr('href') );

        if( target.length > 0) {
            event.preventDefault();
            $('html, body').animate({
                scrollTop: target.offset().top
            }, 1000);

            target.css('background-color','rgba(100,100,100,.7)');
  
        }

    });


    }
 }
})(jQuery);