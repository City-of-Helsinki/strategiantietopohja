;(function ($, Drupal, window, document) {

  Drupal.behaviors.helstra_theme = {

    // Foundation breakpoints
    // ---------------------
    breakpoints: {
      'md': 768,
      'lg': 992,
    },

    // Sticky mobile TOC
    // ------------
    mobileTOC: function() {
      setTimeout(function() {
        $('.article-table-of-contents').css("margin-bottom", (-64 - $('.article-table-of-contents').height()) + "px");
      }, 500);
  
      $('.article__toc button').on("click", function () {
        $('.article-table-of-contents').toggleClass("open");
        $(this).blur();
      });

      $(window).on("load resize", function() {
        if ($(window).outerWidth() < Drupal.behaviors.helstra_theme.breakpoints['md']) {
          $('.article-table-of-contents').css("margin-bottom", (-64 - $('.article-table-of-contents').height()) + "px");
          $('.article__toc').addClass("sticky");
        } else {
          $('.article__toc').removeClass("sticky ");
          $('.article-table-of-contents').removeClass("open");
        }
      });
    },

    smoothScroll: function() {
      $('a[href^="#"]').on('click', function (event) {
        event.preventDefault();
        anchor = $(this).attr('href').replace(".", "\\.");

        $('html, body').animate({
            scrollTop: $(anchor).offset().top - 20
        }, 500);
      });
    },

    attach: function () {
      Drupal.behaviors.helstra_theme.mobileTOC();
      Drupal.behaviors.helstra_theme.smoothScroll();
    }
  };

} (jQuery, Drupal, window, document));