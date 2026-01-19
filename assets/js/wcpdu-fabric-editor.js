jQuery(function ($) {
  const canvasEl = document.getElementById("wcpdu-canvas");
  if (!canvasEl || !window.fabric || !wcpduCustomizer?.productImage) {
    return;
  }

  const canvas = new fabric.Canvas("wcpdu-canvas", {
    selection: false,
    preserveObjectStacking: true,
  });

  window.wcpduFabricCanvas = canvas;

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
    // Ensure selection borders/controls are not rendered into the export
    canvas.discardActiveObject();
    canvas.renderAll();

    const dataURL = canvas.toDataURL({
      format: "png",
      quality: 1,
      enableRetinaScaling: true,
    });

    return dataURL;
  }

  /**
   * 1️⃣ Load product image as background
   */
  fabric.Image.fromURL(
    wcpduCustomizer.productImage,
    function (img) {
      const scale = Math.min(
        canvas.width / img.width,
        canvas.height / img.height,
      );

      img.set({
        originX: "center",
        originY: "center",
        left: canvas.width / 2,
        top: canvas.height / 2,
        selectable: false,
        evented: false,
      });

      img.scale(scale);
      canvas.setBackgroundImage(img, canvas.renderAll.bind(canvas));
    },
    { crossOrigin: "anonymous" },
  );

  /**
   * 2️⃣ Upload image → PREVIEW ONLY (NO AJAX)
   */
  $("#wcpdu-upload-image").on("change", function (e) {
    const file = e.target.files[0];
    if (!file || !file.type.match(/^image\//)) {
      return;
    }

    removePreviousUserImages();

    const reader = new FileReader();
    reader.onload = function (evt) {
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

        img.scaleToWidth(canvas.width * 0.4);
        canvas.add(img);
        canvas.setActiveObject(img);
        canvas.renderAll();
      });
    };

    reader.readAsDataURL(file);
  });

  /**
   * 3️⃣ Remove selected object
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
    if (
      !window.wcpduFabricCanvas ||
      typeof window.wcpduFabricCanvas.getObjects !== "function"
    ) {
      return;
    }

    var canvas = window.wcpduFabricCanvas;

    var toRemove = canvas.getObjects().filter(function (obj) {
      return obj && obj.wcpduType === "user-image";
    });

    toRemove.forEach(function (obj) {
      canvas.remove(obj);
    });

    canvas.discardActiveObject();
    canvas.renderAll();
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
   * 4️⃣ On Add to Cart → export canvas
   */
  $("form.cart").on("submit", function () {
    if (!canvas) {
      return;
    }

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
