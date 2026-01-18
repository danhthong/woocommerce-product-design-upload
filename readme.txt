=== WooCommerce Product Design Upload ===
Contributors: danhthong
Tags: woocommerce, product customizer, design upload, fabricjs, image upload, product personalization
Requires at least: 6.0
Tested up to: 6.5
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Let customers upload an image, position it on a product canvas, and save the final design with the cart/order in WooCommerce.

== Description ==

WooCommerce Product Design Upload adds a simple product customizer to WooCommerce products.

Features:
* Enable/disable design upload per product.
* Customer uploads an image on the product page.
* The uploaded image is added as a movable/resizable layer on a canvas (Fabric.js).
* The product image can be used as the canvas background.
* The final merged design is exported as PNG and stored with the cart item.
* Design files can be displayed in the admin order screen (per order item).
* Lightweight lightbox support for viewing uploaded images.

This plugin is designed for stores that sell personalized products (e.g., t-shirts, mugs, phone cases, posters).

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/woocommerce-product-design-upload/`, or install the ZIP via Plugins → Add New → Upload Plugin.
2. Activate the plugin through the "Plugins" screen in WordPress.
3. Make sure WooCommerce is installed and active.
4. Edit a product and enable the customizer (see "Usage").

== Usage ==

1. Go to Products → Edit product.
2. Enable the design upload option for that product.
3. On the product page, click the "Customize" button.
4. Upload an image, move/scale it on the canvas, then click "Apply".
5. Add to cart. The merged PNG is saved and attached to the cart item and order item meta.

== Frequently Asked Questions ==

= Does this plugin require WooCommerce? =
Yes. WooCommerce must be installed and active.

= Where are uploaded/generated files stored? =
Files are stored under the WordPress uploads directory. The plugin uses a dedicated subfolder for design assets.

= Does this work with WooCommerce zoom/lightbox? =
Yes. The plugin updates the product gallery image and attempts to refresh zoom overlays (including `.zoomImg` used by some themes/plugins).

= Can I allow multiple uploaded layers? =
Currently the customizer is intended for a single uploaded image layer. You can extend it to allow multiple layers if needed.

== Screenshots ==

1. Customize button on the product page.
2. Customizer modal with canvas and uploaded image layer.
3. Admin order screen showing uploaded design files per line item.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
