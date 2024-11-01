<?php
/*
Plugin Name: wp-crossfade
Plugin URI: http://wordpress.org/extend/plugins/wp-crossfade/
Description: wp-crossfade is a image banner manager with crossfade functionality. For more info and plugins visit <a href="http://www.skookum.com">Skookum Labs</a>.
Version: 1.0.5
Author: skookumlabs
Author URI: http://www.skookum.com
Disclaimer: Use at your own risk. No warranty expressed or implied is provided.
 
	Copyright 2009 Skookum

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.
	
	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.
	
	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
	
	Coverted from WP-BANNAERIZE ver. 2.0.0 
	Orginal Author: Giovambattista Fazioli
	Orginal Author URI: http://labs.saidmade.com
	
	CHANGE LOG
	http://wordpress.org/extend/plugins/wp-crossfade/changelog/

*/

require_once( 'wp-crossfade_class.php');

if( is_admin() ) {
	require_once( 'wp-crossfade_admin.php' );
	//
	$wp_crossfade_admin = new wpcrossfade_admin();
	$wp_crossfade_admin->register_plugin_settings( __FILE__ );
} else {
	require_once( 'wp-crossfade_client.php');
	$wp_crossfade_client = new wpcrossfade_client();
	require_once( 'wp-crossfade_functions.php');
}

?>