<?php
/**
 * Client (front-end)
 */
class wpcrossfade_client extends wpcrossfade_class {
	
	function __construct() 
	{
		parent::__construct();
		parent::getOptions();								// retrive options from database
	}
	
	/**
	 * Show banner
	 * 
	 * @return 
	 * @param object $args
	 * 
	 * group				If '' show all group, else code of group (default '')
	 * crossfade_id		the id of the div tag that contains the crossfade element (default 'wp-crossfade')
	 * crossfade_class		the class of the div tag that contains the crossfade element (default 'wp-crossfade-class')
	 * sleep				number of seconds to sleep between each transition (default 2)
	 * fade					number of seconds to fade (default 1)
	 * limit				Limit rows number (default none - show all rows) 
	 * 
	 */
	function crossfade( $args = '' ) 
	{
		global $wpdb;
		
		$default = array(
			'group' => '',
			'crossfade_id' => 'wp-crossfade',
			'crossfade_class' => 'wp-crossfade-class',
			'loading_image' => plugins_url('wp-crossfade/images/loading.gif'), 
			'show_text_overlay' => 'true',
			'overlay_link_text' => 'More',
			'sleep' => '4',
			'z_index' => '2000',
			'fade' => '1',
			'clickable' => 'false',
			'dot_spacing' => '21',
			'limit' => ''
		);
		
		$new_args = wp_parse_args( $args, $default );
		$new_args['dot_spacing'] = floatval($new_args['dot_spacing']);
		$new_args['z_index'] = intval($new_args['z_index']);
		
		$q = "SELECT * FROM `" . $this->table_crossfade . "` ";
		
		if( $new_args['group'] != "") {
			$q .= " WHERE `group` = '" . $new_args['group'] . "'";
		}
		
		$q .= " ORDER BY `sorter` ASC";
		
		/**
		 * New from 2.0.0
		 * Limit rows number
		 */
		if( $new_args['limit'] != "") {
			$q .= " LIMIT 0," . $new_args['limit'] ;
		}
		
		$rows = $wpdb->get_results( $q );	
		$oRows = array();
		foreach( $rows as $row ) {
			$oRows[] = "{ src: '{$row->filename}', href: '{$row->url}', title: '".(addslashes($row->title))."', description: '".(addslashes($row->description))."' }";
		}
		$oRows = implode(",\n\t\t\t\t\t\t", $oRows);
		
		$o = "
		<script type=\"text/javascript\" src=\"".plugins_url('wp-crossfade/js/jquery.cross-fade.js')."\"></script>
		<div id=\"%%crossfade_id%%\" class=\"%%crossfade_class%%\">
			<div id=\"%%crossfade_id%%-loading\" class=\"%%crossfade_class%%-loading\">
				<img src=\"%%loading_image%%\" alt=\"Loading...\"/>
			</div>
		</div>
		<script type=\"text/javascript\">
			jQuery(document).ready(function(){
				jQuery('#%%crossfade_id%%').crossFade({
					id_name: '%%crossfade_id%%',
					class_name: '%%crossfade_class%%',
				  sleep: %%sleep%%,
				  z_index: %%z_index%%,
				  fade: %%fade%%,
					clickable: %%clickable%%,
					header: %%show_text_overlay%%,
					header_link_text: '%%overlay_link_text%%',
					dot_spacing: %%dot_spacing%%
				}, [
					{$oRows}
				]);
			});
		</script>
		";
		
		$o = preg_replace( "/%%(.*)%%/Usem", "\$new_args['\\1']", $o);
		echo $o;
	}	
} // end of class

?>