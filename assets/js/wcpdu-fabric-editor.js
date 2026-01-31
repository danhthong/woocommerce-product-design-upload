jQuery(function ($) {
  const canvasEl = document.getElementById("wcpdu-canvas");
  if (!canvasEl || !window.fabric || !window.wcpduCustomizer?.productImage) {
    return;
  }

  const canvas = new fabric.Canvas("wcpdu-canvas", {
    selection: false,
    preserveObjectStacking: true,
  });

  window.wcpduFabricCanvas = canvas;

  const dataImgClipping = canvasEl.getAttribute("data-img-clipping") || "";

  // We keep ONE loaded mask image as a source, then CLONE it when applying clipPath.
  let clipMaskSource = null;
  let frontOverlay = null;

  /**
   * Load clipping mask source image (only once), then return a NEW clipPath instance each time.
   * Important:
   * - Fabric's clipPath keeps the OPAQUE part. If your mask PNG is a "frame" (outside opaque, inside transparent),
   *   you MUST set inverted: true so the transparent "window" becomes the allowed area.
   * - Do NOT reuse the same fabric object instance as clipPath and as an overlay on canvas.
   */
  function loadClipMask(done) {
    if (!dataImgClipping) {
      done(null);
      return;
    }

    // If already loaded source, just clone a fresh clipPath from it.
    if (clipMaskSource) {
      const clip = fabric.util.object.clone(clipMaskSource);
      clip.set({
        originX: "center",
        originY: "center",
        left: canvas.width / 2,
        top: canvas.height / 2,
        absolutePositioned: true,
        inverted: true, // ✅ key fix for "frame" PNGs
      });
      clip.scaleToWidth(canvas.width);
      clip.scaleToHeight(canvas.height);
      done(clip);
      return;
    }

    fabric.Image.fromURL(
      dataImgClipping,
      function (maskImg) {
        // Store source (not used directly as clipPath).
        maskImg.set({
          originX: "center",
          originY: "center",
          left: canvas.width / 2,
          top: canvas.height / 2,
          selectable: false,
          evented: false,
          absolutePositioned: true,
        });

        maskImg.scaleToWidth(canvas.width);
        maskImg.scaleToHeight(canvas.height);

        clipMaskSource = maskImg;

        // Return a fresh clipPath clone
        const clip = fabric.util.object.clone(clipMaskSource);
        clip.set({
          originX: "center",
          originY: "center",
          left: canvas.width / 2,
          top: canvas.height / 2,
          absolutePositioned: true,
          inverted: true, // ✅ key fix
        });
        clip.scaleToWidth(canvas.width);
        clip.scaleToHeight(canvas.height);

        done(clip);
      },
      { crossOrigin: "anonymous" }
    );
  }

  /**
   * Create overlay "frame" image (optional) that sits on TOP for visual guidance.
   * This is separate from clipPath, so it must be its own fabric.Image instance.
   */
  function ensureFrontOverlay() {
    if (!dataImgClipping) return;

    if (frontOverlay) {
      canvas.bringToFront(frontOverlay);
      return;
    }

    fabric.Image.fromURL(
      dataImgClipping,
      function (overlayImg) {
        overlayImg.set({
          originX: "center",
          originY: "center",
          left: canvas.width / 2,
          top: canvas.height / 2,
          selectable: false,
          evented: false,
          absolutePositioned: true,
          hoverCursor: "default",
        });

        overlayImg.scaleToWidth(canvas.width);
        overlayImg.scaleToHeight(canvas.height);

        frontOverlay = overlayImg;
        canvas.add(frontOverlay);
        canvas.bringToFront(frontOverlay);
        canvas.renderAll();
      },
      { crossOrigin: "anonymous" }
    );
  }

  function removePreviousUserImages() {
    const toRemove = canvas.getObjects().filter(function (obj) {
      return obj && obj.wcpduType === "user-image";
    });

    toRemove.forEach(function (obj) {
      canvas.remove(obj);
    });

    canvas.discardActiveObject();
    canvas.renderAll();
  }

  function exportCanvasPNG() {
    canvas.discardActiveObject();
    canvas.renderAll();

    return canvas.toDataURL({
      format: "png",
      quality: 1,
      enableRetinaScaling: true,
    });
  }

  /**
   * 1) Load product image as background
   */
  fabric.Image.fromURL(
    wcpduCustomizer.productImage,
    function (img) {
      const scale = Math.min(canvas.width / img.width, canvas.height / img.height);

      img.set({
        originX: "center",
        originY: "center",
        left: canvas.width / 2,
        top: canvas.height / 2,
        selectable: false,
        evented: false,
      });

      img.scale(scale);

      canvas.setBackgroundImage(img, function () {
        canvas.renderAll();
        ensureFrontOverlay();
      });
    },
    { crossOrigin: "anonymous" }
  );

  /**
   * 2) Upload image → Preview only (NO AJAX)
   */
  $("#wcpdu-upload-image").on("change", function (e) {
    const file = e.target.files[0];
    if (!file || !file.type.match(/^image\//)) {
      return;
    }

    removePreviousUserImages();

    const reader = new FileReader();
    reader.onload = function (evt) {
      loadClipMask(function (clipPath) {
        fabric.Image.fromURL(evt.target.result, function (img) {
          img.set({
            left: canvas.width / 2,
            top: canvas.height / 2,
            originX: "center",
            originY: "center",
            cornerColor: "#2271b1",
            borderColor: "#2271b1",
            cornerSize: 10,
            transparentCorners: false,
            wcpduType: "user-image",
          });

          // ✅ Apply clipPath (NEW instance each time)
          if (clipPath) {
            img.clipPath = clipPath;
          }

          img.scaleToWidth(canvas.width * 0.4);

          canvas.add(img);
          canvas.setActiveObject(img);

          // Keep overlay on top
          ensureFrontOverlay();
          if (frontOverlay) {
            canvas.bringToFront(frontOverlay);
          }

          canvas.renderAll();
        });
      });
    };

    reader.readAsDataURL(file);
  });

  /**
   * 3) Remove selected object
   */
  function restoreUploadLabel() {
    var $p = $("#file-name");
    if (!$p.length) return;

    var defaultSpanText = $p.data("default-span");
    var defaultHtml = $p.data("default-html");

    if (!defaultSpanText) {
      defaultSpanText = $p.find("span").first().text();
      $p.data("default-span", defaultSpanText);
    }

    if (!defaultHtml) {
      defaultHtml = $p.html();
      $p.data("default-html", defaultHtml);
    }

    $p.html(defaultHtml);
  }

  function removeUserImageLayer() {
    if (!window.wcpduFabricCanvas || typeof window.wcpduFabricCanvas.getObjects !== "function") {
      return;
    }

    var c = window.wcpduFabricCanvas;

    var toRemove = c.getObjects().filter(function (obj) {
      return obj && obj.wcpduType === "user-image";
    });

    toRemove.forEach(function (obj) {
      c.remove(obj);
    });

    c.discardActiveObject();
    c.renderAll();
  }

  // Store the default label HTML once (on DOM ready).
  restoreUploadLabel();

  // On file select, only replace the <span> text with the filename.
  $(document).on("change", "#wcpdu-upload-image", function () {
    var file = this.files && this.files[0] ? this.files[0] : null;
    if (!file) return;

    $("#file-name span").first().text(file.name);
  });

  // Clear image button: remove layer + clear input + restore the full label markup.
  $(document).on("click", ".wcpdu-remove-object", function (e) {
    e.preventDefault();

    $("#wcpdu-upload-image").val("");
    restoreUploadLabel();
    removeUserImageLayer();
  });

  /**
   * 4) On Add to Cart → export canvas
   */
  $("form.cart").on("submit", function () {
    if (!canvas) return;

    const dataURL = exportCanvasPNG();

    let $input = $("#wcpdu-custom-design");
    if (!$input.length) {
      $input = $("<input>", {
        type: "hidden",
        id: "wcpdu-custom-design",
        name: "wcpdu_custom_design",
      }).appendTo(this);
    }

    $input.val(dataURL);
  });
});
