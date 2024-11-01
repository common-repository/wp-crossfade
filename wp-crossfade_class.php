<?php
/**
 * Core 
 */

class wpcrossfade_class {
		
	/**
	 * @internal
	 * @staticvar
	 */
	var $version 							= "1.0.5";				// plugin version
	var $plugin_name 						= "WP Crossfade";		// plugin name
	var $options_key 						= "wp-crossfade";		// options key to store in database
	var $options_title						= "WP Crossfade";		// label for "setting" in WP
	
	var $table_crossfade					= 'crossfade';
	var $update								= false;				// flag for upgrade from 1.4 prev
	
	/**
	 * Usefull vars
	 * @internal 
	 */
	var $content_url						= "";
	var $plugin_url							= "";
	var $ajax_url							= "";
	
	var $path 								= "";
	var $file 								= "";
	var $directory							= "";
	var $uri 								= "";
	var $siteurl 							= "";
	var $wpadminurl 						= "";

	/**
	 * This properties variable are @public
	 * 
	 * @property
	 *  
	 */
	var $options							= array();

	/**
	 * @constructor 
	 */
	function __construct() {
		$this->path 						= dirname(__FILE__);
		$this->file 						= basename(__FILE__);
		$this->directory 					= basename($this->path);
		$this->uri 							= WP_PLUGIN_URL . "/" . $this->directory;
		$this->siteurl						= get_bloginfo('url');
		$this->wpadminurl					= admin_url();		
		
		$this->content_url 					= get_option('siteurl') . '/wp-content';
		$this->plugin_url 					= $this->content_url . '/plugins/' . plugin_basename( dirname(__FILE__) ) . '/';
		$this->ajax_url						= $this->plugin_url . "ajax.php";
	}
	
	/**
	 * Get option from database
	 * 
	 * @return 
	 */
	function getOptions() {
		$this->options 						= get_option( $this->options_key );
	}	
	
	/**
	 * Check the Wordpress relase for more setting
	 * 
	 * @return 
	 */
	function checkWordpressRelease() {
		global $wp_version;
		if ( strpos($wp_version, '2.7') !== false || strpos($wp_version, '2.8') !== false  ) {}
	}	
} // end of class

?>