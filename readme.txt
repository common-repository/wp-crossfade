=== wp-crossfade ===
Contributors: skookumlabs
Donate link: http://www.skookum.com
Tags: Banner, Manage, Image, crossfade, fade, Slider
Requires at least: 2.7.1
Tested up to: 2.8.2
Stable tag: 1.0.5

wp-crossfade is a image banner manager w/crossfade and titles.

== Description ==

wp-crossfade is a image banner manager with crossfade functionality.
 
In your template insert: `<?php wp_crossfade(); ?>`

Or you can add different paramaters:

`<?php wp_crossfade( 'group=home&limit=4&dot_spacing=55' ); ?>`

**Params:**

`
* group 		 If '' show all group, else code of group (default '')
* crossfade_id 		 the id of the div tag that contains the crossfade element (default 'wp-crossfade')
* crossfade_class 		 the class of the div tag that contains the crossfade element (default 'wp-crossfade-class')
* loading_image 		 The pre-loading image that will be displayed (default plugins_url('wp-crossfade/images/loading.gif'))
* show_text_overlay 		 Displays the text overlay over the image (default true)
* overlay_link_text 		 The text or image inside the link (default More)
* sleep 		 The number of seconds to sleep between each transition (default 4)
* z_index 		 The CSS z-index of the elements (default 2000)
* fade 		 The number of seconds to fade (default 1)
* clickable 		 Should the image be clickable, if so it will go to the url provided (default false)
* dot_spacing 		 The spacing between the "dots" or image navigation (default 21)
* limit 		 Limit rows number (default none - show all rows)
`

== Installation ==

1. Upload `wp-crossfade` directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Add the jQuery script to your header template: `<?php wp_print_scripts( 'jquery' ); ?>`
3. Place `<?php wp_crossfade( 'group=home&limit=4&dot_spacing=55' ); ?>` in your templates
4. Sample css file: wp-crossfade/css/sample.css

== Frequently Asked Questions == 

= Can I customize output? =
Yes, please refer to the params, also the sample css file: `wp-crossfade/css/sample.css`


== Screenshots ==

1. The images are pre-loaded while the loading.gif is displayed. note: you can change the location of the loading image with the option `loading_image`
2. An example of a banner. note: The `dots` or squares in the image are completely customizable, and clicking on each one takes you to that image.
3. The admin section contains a place to upload the image or enter the image url, and specify the title and subtitle as well as the url for the link. note: image can be clickable when the option `clickable` is set to `true`

== Other Notes ==

= Acknowledgements =
* [WP-BANNERIZE](http://wordpress.org/extend/plugins/wp-bannerize/) for the plugin as a starting point
* [CrossSlide - jQuery plugin](http://www.gruppo4.com/~tobia/cross-slide.shtml) for the plugin
* [Skookum](http://www.skookum.com) for the support

== Changelog ==

= 1.0.5 =
* Removed the absolute position of the image if not using the direction and speed movement

= 1.0.4 =
* Removed the height and width from the slide containers for greater control in styling

= 1.0.3 =
* Added a div wrapper for images to contain them

= 1.0.2 =
* Added ability to specify image url instead of uploading image

= 1.0.1 =
* Added addslashes to the title and description

= 1.0.0 =
* Converted the plugin WP-BANNERIZE to be wp-crossfade
