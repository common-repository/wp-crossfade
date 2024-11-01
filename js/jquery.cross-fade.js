/*
 *
 ****************
 *
 * Copyright 2007-2009 by Tobia Conforto <tobia.conforto@gmail.com>
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU General
 * Public License as published by the Free Software Foundation; either version 2 of the License, or (at your
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
 * for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program; if not, write to
 * the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Versions: 0.1    2007-08-19  Initial release
 *                  2008-08-21  Re-released under GPL v2
 *           0.1.1  2008-09-18  Compatibility with prototype.js
 *           0.2    2008-10-15  Linkable images, contributed by Tim Rainey <tim@zmlabs.com>
 *           0.3    2008-10-22  Added option to repeat the animation a number of times, then stop
 *           0.3.1  2008-11-11  Better error messages
 *           0.3.2  2008-11-11  Fixed a couple of CSS bugs, contributed by Erwin Bot <info@ixgcms.nl>
 *           0.3.3  2008-12-14  Added onclick option
 *           0.3.4  2009-03-12  Added shuffle option, contributed by Ralf Santbergen <ralf_santbergen@hotmail.com>
 *           0.3.5  2009-03-12  Fixed usage of href parameter in 'Ken Burns' mode
 */

jQuery.fn.crossFade = function(opts, plan) {
	var self = this,
			self_width = this.width(),
			self_height = this.height();

	// generic utilities
	function format(str) {
		for (var i = 1; i < arguments.length; i++)
			str = str.replace(new RegExp('\\{' + (i-1) + '}', 'g'), arguments[i]);
		return str;
	}

	function abort() {
		arguments[0] = 'crossFade: ' + arguments[0];
		throw format.apply(null, arguments);
	}

	// first preload all the images, while getting their actual width and height
	(function(proceed) {

		var n_loaded = 0;
		function loop(i, img) {
			// for (i = 0; i < plan.length; i++) but with independent var i, img (for the closures)
			img.onload = function(e) {
				n_loaded++;
				plan[i].width = img.width;
				plan[i].height = img.height;
				plan[i].image = img;
				if (n_loaded == plan.length){
					proceed();
				}
			}
			img.src = plan[i].src;
			if (i + 1 < plan.length)
				loop(i + 1, new Image());
		}
		loop(0, new Image());

	})(function() {  // then proceed

		// utility to parse "from" and "to" parameters
		function parse_position_param(param) {
			var zoom = 1;
			var tokens = param.replace(/^\s*|\s*$/g, '').split(/\s+/);
			if (tokens.length > 3) throw new Error();
			if (tokens[0] == 'center')
				if (tokens.length == 1)
					tokens = ['center', 'center'];
				else if (tokens.length == 2 && tokens[1].match(/^[\d.]+x$/i))
					tokens = ['center', 'center', tokens[1]];
			if (tokens.length == 3)
				zoom = parseFloat(tokens[2].match(/^([\d.]+)x$/i)[1]);
			var pos = tokens[0] + ' ' + tokens[1];
			if (pos == 'left top'      || pos == 'top left')      return { xrel:  0, yrel:  0, zoom: zoom };
			if (pos == 'left center'   || pos == 'center left')   return { xrel:  0, yrel: .5, zoom: zoom };
			if (pos == 'left bottom'   || pos == 'bottom left')   return { xrel:  0, yrel:  1, zoom: zoom };
			if (pos == 'center top'    || pos == 'top center')    return { xrel: .5, yrel:  0, zoom: zoom };
			if (pos == 'center center')                           return { xrel: .5, yrel: .5, zoom: zoom };
			if (pos == 'center bottom' || pos == 'bottom center') return { xrel: .5, yrel:  1, zoom: zoom };
			if (pos == 'right top'     || pos == 'top right')     return { xrel:  1, yrel:  0, zoom: zoom };
			if (pos == 'right center'  || pos == 'center right')  return { xrel:  1, yrel: .5, zoom: zoom };
			if (pos == 'right bottom'  || pos == 'bottom right')  return { xrel:  1, yrel:  1, zoom: zoom };
			return {
				xrel: parseInt(tokens[0].match(/^(\d+)%$/)[1]) / 100,
				yrel: parseInt(tokens[1].match(/^(\d+)%$/)[1]) / 100,
				zoom: zoom
			};
		}

		// utility to compute the css for a given phase between p.from and p.to
		// phase = 1: begin fade-in,  2: end fade-in,  3: begin fade-out,  4: end fade-out
		function position_to_css(p, phase) {
			switch (phase) {
				case 1:
					var pos = 0;
					break;
				case 2:
					var pos = fade_ms / (p.time_ms + 2 * fade_ms);
					break;
				case 3:
					var pos = 1 - fade_ms / (p.time_ms + 2 * fade_ms);
					break;
				case 4:
					var pos = 1;
					break;
			}
			return {
				left:   Math.round(p.from.left   + pos * (p.to.left   - p.from.left  )),
				top:    Math.round(p.from.top    + pos * (p.to.top    - p.from.top   )),
				width:  Math.round(p.from.width  + pos * (p.to.width  - p.from.width )),
				height: Math.round(p.from.height + pos * (p.to.height - p.from.height))
			};
		}

		// check global params
		if (! opts.fade)
			abort('missing fade parameter.');
		if (opts.speed && opts.sleep)
			abort('you cannot set both speed and sleep at the same time.');
		// conversion from sec to ms; from px/sec to px/ms
		var fade_ms = Math.round(opts.fade * 1000);
		if (opts.sleep)
			var sleep = Math.round(opts.sleep * 1000);
		if (opts.speed)
			var speed = opts.speed / 1000,
					fade_px = Math.round(fade_ms * speed);

		// set container css
		self.empty().css({
			overflow: 'hidden',
			padding: 0
		});
		if (! self.css('position').match(/absolute|relative|fixed/))
			self.css({ position: 'relative' });
		if (! self.width() || ! self.height())
			abort('container element does not have its own width and height');

		// random sorting
		if (opts.shuffle)
			plan.sort(function() {
				return Math.random() - 0.5;
			});
			
		var spacing = opts.dot_spacing;
		var dotContainer = jQuery('<div class="'+opts.class_name+'-dot-container">&nbsp;</div>');
		
		// prepare each image
		for (var i = 0; i < plan.length; ++i) {

			var p = plan[i];
			if (! p.src)
				abort('missing src parameter in picture {0}.', i + 1);

			if (speed) { // speed/dir mode

				// check parameters and translate speed/dir mode into full mode (from/to/time)
				switch (p.dir) {
					case 'up':
						p.from = { xrel: .5, yrel: 0, zoom: 1 };
						p.to   = { xrel: .5, yrel: 1, zoom: 1 };
						var slide_px = p.height - self_height - 2 * fade_px;
						break;
					case 'down':
						p.from = { xrel: .5, yrel: 1, zoom: 1 };
						p.to   = { xrel: .5, yrel: 0, zoom: 1 };
						var slide_px = p.height - self_height - 2 * fade_px;
						break;
					case 'left':
						p.from = { xrel: 0, yrel: .5, zoom: 1 };
						p.to   = { xrel: 1, yrel: .5, zoom: 1 };
						var slide_px = p.width - self_width - 2 * fade_px;
						break;
					case 'right':
						p.from = { xrel: 1, yrel: .5, zoom: 1 };
						p.to   = { xrel: 0, yrel: .5, zoom: 1 };
						var slide_px = p.width - self_width - 2 * fade_px;
						break;
					default:
						abort('missing or malformed "dir" parameter in picture {0}.', i + 1);
				}
				if (slide_px <= 0)
					abort('picture number {0} is too short for the desired fade duration.', i + 1);
				p.time_ms = Math.round(slide_px / speed);

			} else if (! sleep) { // full mode

				// check and parse parameters
				if (! p.from || ! p.to || ! p.time)
					abort('missing either speed/sleep option, or from/to/time params in picture {0}.', i + 1);
				try {
					p.from = parse_position_param(p.from)
				} catch (e) {
					abort('malformed "from" parameter in picture {0}.', i + 1);
				}
				try {
					p.to = parse_position_param(p.to)
				} catch (e) {
					abort('malformed "to" parameter in picture {0}.', i + 1);
				}
				if (! p.time)
					abort('missing "time" parameter in picture {0}.', i + 1);
				p.time_ms = Math.round(p.time * 1000)
			}

			// precalculate left/top/width/height bounding values
			if (p.from)
				jQuery.each([ p.from, p.to ], function(i, from_to) {
					from_to.width  = Math.round(p.width  * from_to.zoom);
					from_to.height = Math.round(p.height * from_to.zoom);
					from_to.left   = Math.round((self_width  - from_to.width)  * from_to.xrel);
					from_to.top    = Math.round((self_height - from_to.height) * from_to.yrel);
				});

			// append the image (or anchor) element to the container
			var elm;
			if (opts.clickable && p.href)
				if(p.image){
					elm = jQuery( format('<a href="{0}"></a>', p.href) );
					imgObj = jQuery(p.image);
					imgObj.appendTo(elm);
				} else {
					elm = jQuery( format('<a href="{0}"><img src="{1}"/></a>', p.href, p.src) );
				}
			else
				elm = jQuery( ((p.image) ? p.image : format('<img src="{0}"/>', p.src)) );
			if (p.onclick)
				elm.click(p.onclick);
			
			if( speed ){
				elm.css({
					position: 'absolute',
					top: 0,
					left: 0
				});
			}
			
			
			var container = jQuery('<div id="'+opts.id_name+'-container-'+i+'" class="'+opts.class_name+'-container"></div>');
			jQuery('#'+opts.id_name+'-loading').fadeOut(500);
			container.appendTo(self);
			
			// Add the header if the information exists
			if( opts.header ){
				var headerStr = "";
				headerStr += "<div id=\""+opts.id_name+"-text-container\" class=\""+opts.class_name+"-text-container\"><div id=\""+opts.id_name+"-text\" class=\""+opts.class_name+"-text\">";
				
				if( jQuery.trim(p.title) != '' )
					headerStr += "<h3>"+p.title+"</h3> ";
					
				if( jQuery.trim(p.description) != '' )
					headerStr += "<span>"+p.description+"</span> ";
				
				if( jQuery.trim(p.href) != '' )
					headerStr += "<a href=\""+p.href+"\">"+opts.header_link_text+"</a> ";

				headerStr += "</div></div>";
				var headerElement = jQuery(headerStr);
				headerElement.appendTo(container);
			}
			
			var crossfadeDot = jQuery('<div id="'+opts.id_name+'-dot-'+i+'" class="'+opts.class_name+'-dot" rel="'+i+'" style="position: absolute; right: '+((spacing*i))+'px; bottom: 0px; cursor: pointer;">&nbsp;</div>');
			var crossfadeDotSelected = jQuery('<div id="'+opts.id_name+'-dot-selected-'+i+'" class="'+opts.class_name+'-dot-selected" rel="'+i+'" style="position: absolute; right: '+((spacing*i))+'px; bottom: 0px; cursor: pointer;">&nbsp;</div>');
			var elementContainer = jQuery('<div id="'+opts.id_name+'-image-container-'+i+'" class="'+opts.class_name+'-image-container" style="width: '+p.width+'px; height: '+p.height+'px;"></div>');
			elm.appendTo(elementContainer);
			elementContainer.appendTo(container);
			crossfadeDot.appendTo(dotContainer);
			crossfadeDotSelected.appendTo(dotContainer);
			crossfadeDot.click(function(){dotOnclick(this)});
			crossfadeDotSelected.click(function(){dotOnclick(this)});
		} // end for loop

		function dotOnclick(obj) {
			var rel = (plan.length-parseInt(jQuery(obj).attr('rel'))-1);
			animation(rel);
		}
		
		speed = undefined;  // speed mode has now been translated to full mode

		// find images to animate and set initial css attributes
		var imgs = self.find('div.'+opts.class_name+'-container').css({
			position: 'absolute',
			visibility: 'hidden',
			opacity: 0,
			top: 0,
			left: 0
		});
		
		// Append the dot container to the crossfade container
		dotContainer.appendTo(self);

		// create animation chain
		var LOOP_TIMEOUT = null;
		var CURRENT_SLIDE = null;
		var PREVIOUS_SLIDE = null;
		var countdown = opts.loop;
		
		function getNextIndex(curr_ind){
			var ind = curr_ind+1;
			if( ind < plan.length ){
				return parseInt(ind);
			} else {
			 return 0;	
			}
		}
		
		function processDotAnimation(){
			jQuery('#'+opts.id_name+'-dot-selected-'+(plan.length-CURRENT_SLIDE-1)).css({ opacity: 1 }); //.animate({ opacity: 1 }, fade_ms, 'linear');
			if( PREVIOUS_SLIDE != null && PREVIOUS_SLIDE != CURRENT_SLIDE ){
				jQuery('#'+opts.id_name+'-dot-selected-'+(plan.length-PREVIOUS_SLIDE-1)).css({ opacity: 0 }); //.animate({ opacity: 0 }, fade_ms, 'linear');
			}
		}
		
		function showSlide(){
			// show the current slide
			jQuery('#'+opts.id_name+'-container-'+CURRENT_SLIDE).css({ visibility: 'visible', opacity: 0 }).stop().animate({ opacity: 1 }, fade_ms, 'linear', function(){});
		}
		
		function hideLastSlide(){
			// if there is a previous slide
			if( PREVIOUS_SLIDE != null && PREVIOUS_SLIDE != CURRENT_SLIDE ){
				// hide it!
				jQuery('#'+opts.id_name+'-container-'+PREVIOUS_SLIDE).stop().animate({ opacity: 0 }, fade_ms, 'linear', function(){ jQuery(this).css({ visibility: 'hidden' }); } );
			}
		}
		
		function animation(ind)
		{
			// clear out the timeout
			if(LOOP_TIMEOUT){ clearTimeout(LOOP_TIMEOUT); }
			if(ind != CURRENT_SLIDE){
				// set the current slide to be the next slide
				CURRENT_SLIDE = ind;
				// hide last slide
				hideLastSlide();
				// process the dot animation
				processDotAnimation();
				// show the slide
				showSlide();
			}
			// set the previous slide to be the current slide
			PREVIOUS_SLIDE = CURRENT_SLIDE;
			// if sleep not set, then set it to 10
			if(!sleep){ sleep = 10; }
			// get the next index
			var nextInd = getNextIndex(ind);			
			// setup the self func
			var selfFunc = function(){ animation(nextInd) };
			// set the timeout
			LOOP_TIMEOUT = setTimeout(selfFunc, sleep);
		}
		// Start the animation
		animation(0);

	});

	return self;
};
