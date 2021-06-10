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
      if ($('#article-toc').length) {

        if ($(window).outerWidth() < Drupal.behaviors.helstra_theme.breakpoints['md']) {
          $('.article-table-of-contents').css("margin-bottom", -$('.article-table-of-contents').height() + "px");
          $('.article__toc').addClass("sticky");
        } 

        $('.article__toc button').on('mousedown', function(event) {
          event.preventDefault();
          if ($(this).hasClass('focused')) {
            $(this).removeClass('focused');
            $(':focus').blur();
          } else {
            $(this).addClass('focused');
            $(this).focus();
          }
        });

        $('.article__toc button').on('blur', function(event) {
          if ($(this).hasClass('focused')) {
            $(this).removeClass('focused');
          }
        });

        $('#article-toc').on('focusout', function() {
          $('.article__toc button').removeClass('focused');
        });

        $('#article-toc').on('focusin', function() {
          $('.article__toc button').addClass('focused');
        });

        $(window).on("load resize", function() {
          if ($(window).outerWidth() < Drupal.behaviors.helstra_theme.breakpoints['md']) {
            $('.article-table-of-contents').css("margin-bottom", -$('.article-table-of-contents').height() + "px");
            $('.article__toc').addClass("sticky");
          } else {
            $('.article__toc').removeClass("sticky ");
          }
        });

        if($('#article-toc').scrollTop() + $('#article-toc').innerHeight() < $('#article-toc')[0].scrollHeight) {
          $('#article-toc').addClass('off-bottom');
        }

        $('#article-toc').on('scroll', function() {
          if($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight) {
            $('#article-toc').removeClass('off-bottom');
          } else {
            $('#article-toc').addClass('off-bottom');
          }
        });
      }
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