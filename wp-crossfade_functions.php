<?php
/**
 * Wrap standard function
 * 
 * @return 
 * @param object $args[optional]
 */
function wp_crossfade( $args = '' ) {
	global $wp_crossfade_client;
	$wp_crossfade_client->crossfade( $args );
}

?>