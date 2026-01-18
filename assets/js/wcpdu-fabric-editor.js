jQuery(function ($) {
  const canvasEl = document.getElementById("wcpdu-canvas");
  if (!canvasEl || !window.fabric || !wcpduCustomizer?.productImage) {
    return;
  }

  const canvas = new fabric.Canvas("wcpdu-canvas", {
    selection: false,
    preserveObjectStacking: true,
  });

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
          wcpduType: "user-image", // 👈 đánh dấu
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
  $(document).on("click", ".wcpdu-remove-object", function () {
    const obj = canvas.getActiveObject();
    if (!obj || obj === canvas.backgroundImage) {
      return;
    }

    canvas.remove(obj);
    canvas.discardActiveObject();
    canvas.renderAll();
  });

  /**
   * 4️⃣ On Add to Cart → export canvas
   */
  $("form.cart").on("submit", function () {
    if (!canvas) {
      return;
    }

    const dataURL = canvas.toDataURL({
      format: "png",
      quality: 1,
    });

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
