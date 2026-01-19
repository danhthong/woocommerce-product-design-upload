(function ($) {
  "use strict";

  var $input = $("#wcpdu-upload-image");
  if (!$input.length) return;

  $input.on("change", function () {
    var file = this.files && this.files[0] ? this.files[0] : null;

    var $nameSpan = $("#file-name");
    if (!$nameSpan.length) return;

    if (!file) {
      $nameSpan.text(wcpduCustomizer?.uploadLabel || "Click to upload");
      return;
    }

    $nameSpan.text(file.name);
  });

  function openModal() {
    $("#wcpdu-customizer-modal").attr("aria-hidden", "false").show();
    $("body").addClass("wcpdu-modal-open");
  }

  function closeModal() {
    $("#wcpdu-customizer-modal").attr("aria-hidden", "true").hide();
    $("body").removeClass("wcpdu-modal-open");
  }

  function getCanvasDataURL() {
    if (!window.wcpduFabricCanvas) return "";

    window.wcpduFabricCanvas.discardActiveObject();
    window.wcpduFabricCanvas.renderAll();

    try {
      return window.wcpduFabricCanvas.toDataURL({
        format: "png",
        quality: 1,
      });
    } catch (e) {
      return "";
    }
  }

  function updateWooProductGallery(dataUrl) {
    if (!dataUrl) return;

    var $gallery = $(".woocommerce-product-gallery");
    var $mainImg = $gallery.find("img.wp-post-image").first();

    if (!$mainImg.length) {
      $mainImg = $gallery.find("img").first();
    }
    if (!$mainImg.length) return;

    // Update main image + common WC zoom/lightbox attributes
    $mainImg.attr("src", dataUrl);
    $mainImg.attr("data-src", dataUrl);
    $mainImg.attr("data-large_image", dataUrl);
    $mainImg.attr("data-large_image_width", "");
    $mainImg.attr("data-large_image_height", "");
    $mainImg.attr("srcset", "");
    $mainImg.attr("sizes", "");

    // Some themes/plugins use parent <a> href for lightbox
    var $link = $mainImg.closest("a");
    if ($link.length) {
      $link.attr("href", dataUrl);
    }

    // Update the injected zoom image (e.g. ElevateZoom creates <img class="zoomImg">)
    var $zoomImg = $(".zoomImg");
    if ($zoomImg.length) {
      $zoomImg.attr("src", dataUrl);
    }

    // If zoom plugin caches background/containers, remove the zoom image to force refresh
    // (it will be recreated on next hover/move by most zoom libs)
    $zoomImg.remove();

    // Best-effort: destroy elevateZoom if present (prevents stale cache on some themes)
    try {
      if ($mainImg.data("elevateZoom")) {
        $mainImg.data("elevateZoom").destroy();
        $mainImg.removeData("elevateZoom");
        $(".zoomContainer").remove();
      }
    } catch (e) {}

    // Update first thumbnail if present
    var $thumb = $(
      ".flex-control-nav img, .woocommerce-product-gallery__thumbs img",
    ).first();
    if ($thumb.length) {
      $thumb.attr("src", dataUrl);
      $thumb.attr("data-src", dataUrl);
      $thumb.attr("srcset", "");
      $thumb.attr("sizes", "");
    }

    // Re-init WC product gallery scripts (theme-dependent)
    if ($gallery.length) {
      $gallery.trigger("woocommerce_gallery_init");
      $gallery.trigger("wc-product-gallery-after-init");
      $gallery.trigger("resize");
    }
  }

  $(document).on("click", ".wcpdu-open-customizer", function (e) {
    e.preventDefault();
    openModal();
  });

  $(document).on("click", "[data-wcpdu-modal-close]", function (e) {
    e.preventDefault();
    closeModal();
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape") {
      if ($("#wcpdu-customizer-modal").is(":visible")) {
        closeModal();
      }
    }
  });

  $(document).on("click", ".wcpdu-apply", function (e) {
    e.preventDefault();

    // Remove active object selection before exporting
    if (
      window.wcpduFabricCanvas &&
      typeof window.wcpduFabricCanvas.discardActiveObject === "function"
    ) {
      window.wcpduFabricCanvas.discardActiveObject();
      if (typeof window.wcpduFabricCanvas.renderAll === "function") {
        window.wcpduFabricCanvas.renderAll();
      }
    }

    var dataUrl = getCanvasDataURL();
    if (!dataUrl) {
      closeModal();
      return;
    }

    $("#wcpdu-custom-design").val(dataUrl);
    updateWooProductGallery(dataUrl);
    closeModal();
  });
})(jQuery);
