(function ($) {
  'use strict';

  function openLightbox(src) {
    $('#wcpdu-lightbox img').attr('src', src);
    $('#wcpdu-lightbox').fadeIn(200);
  }

  function closeLightbox() {
    $('#wcpdu-lightbox').fadeOut(200);
    $('#wcpdu-lightbox img').attr('src', '');
  }

  $(document).on('click', '[data-wcpdu-lightbox]', function (e) {
    e.preventDefault();
    openLightbox($(this).attr('href'));
  });

  $(document).on('click', '.wcpdu-lightbox-overlay, .wcpdu-lightbox-close', closeLightbox);

  $(document).on('keyup', function (e) {
    if (e.key === 'Escape') {
      closeLightbox();
    }
  });

})(jQuery);
