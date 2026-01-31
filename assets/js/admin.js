jQuery(function ($) {
  // Only run if the field exists (product edit screens).
  var $input = $("#_wcpdu_img_clipping");
  if (!$input.length || typeof wp === "undefined" || !wp.media) {
    return;
  }

  var frame = null;

  function setPreview(url) {
    var $preview = $(".wcpdu-img-clipping-preview");
    if (!$preview.length) return;

    if (url) {
      $preview.attr("src", url).show();
    } else {
      $preview.attr("src", "").hide();
    }
  }

  $(document).on("click", ".wcpdu-upload-img-clipping", function (e) {
    e.preventDefault();

    if (frame) {
      frame.open();
      return;
    }

    frame = wp.media({
      title: "Select a clipping mask image",
      button: { text: "Use this image" },
      multiple: false,
      library: { type: "image" },
    });

    frame.on("select", function () {
      var attachment = frame.state().get("selection").first().toJSON();
      if (!attachment || !attachment.url) return;
      $input.val(attachment.url);
      setPreview(attachment.url);
    });

    frame.open();
  });

  $(document).on("click", ".wcpdu-remove-img-clipping", function (e) {
    e.preventDefault();
    $input.val("");
    setPreview("");
  });

  // Initialize preview on load.
  setPreview($input.val());
});
