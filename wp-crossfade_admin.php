<?php
/**
 * Admin (back-end)
 */
class wpcrossfade_admin extends wpcrossfade_class 
{
	
	function __construct() {
		parent::__construct();
		
		$this->initDefaultOption();
	}
	
	/**
	 * Init the default plugin options and re-load from WP
	 * 
	 * @return 
	 */
	function initDefaultOption() {
		$this->options 						= array();
		add_option( $this->options_key, $this->options, $this->options_title );
		
		parent::getOptions();
		
		/**
		 * Check for 1.4+
		 */
		$this->update = $this->checkTable(); 
		
		add_action('admin_menu', 	array( $this, 'add_menus') );
		
	}
	
	/**
	 * ADD OPTION PAGE TO WORDPRESS ENVIRORMENT
	 *
	 * Add callback for adding options panel
	 *
	 */
	
	function add_menus() {
		$menus = array();
		
		if (function_exists('add_object_page'))
			$menus['main'] = add_object_page('WP Crossfade', 'WP Crossfade', 8, $this->directory.'-settings', array( &$this, 'set_options_subpanel') );
		else
			$menus['main'] = add_menu_page('WP Crossfade', 'WP Crossfade', 8, $this->directory.'-settings', array(&$this,'set_options_subpanel') );

		$menus['settings'] = add_submenu_page($this->directory.'-settings', __('Settings'), __('Settings'), 8, $this->directory.'-settings', array(&$this,'set_options_subpanel') );
		
		add_action( 'admin_head-' . $menus['settings'], array( &$this, 'set_admin_head' ) );
		
		if (function_exists('add_contextual_help')) {
			add_contextual_help($menus['main'],'<p><strong>'.__('Use').':</strong></p>' .
				'<pre>wp_crossfade();</pre> or<br/>' .
				'<pre>wp_crossfade( \'group=home&sleep=2&limit=5\' );</pre><br/>' .
				'<p><strong>'.__('Options').':</strong></p>' .
				'<pre>
* group 		 If \'\' show all group, else code of group (default \'\')
* crossfade_id 		 The id of the div tag that contains the crossfade element (default \'wp-crossfade\')
* crossfade_class 	 The class of the div tag that contains the crossfade element (default \'wp-crossfade-class\')
* loading_image 	 The pre-loading image that will be displayed (default plugins_url(\'wp-crossfade/images/loading.gif\'))
* show_text_overlay 	 Displays the text overlay over the image (default true)
* overlay_link_text 	 The text or image inside the link (default More)
* sleep 		 The number of seconds to sleep between each transition (default 4)
* z_index 		 The CSS z-index of the elements (default 2000)
* fade 			 The number of seconds to fade (default 1)
* clickable 		 Should the image be clickable, if so it will go to the url provided (default false)
* dot_spacing 		 The spacing between the "dots" or image navigation (default 21)
* limit 		 Limit rows number (default none - show all rows)
			</pre>' .
			'<p><strong>'.__('Misc').':</strong></p>' .
			'<p>Sample CSS file: <a href="'.plugins_url('wp-crossfade/css/sample.css').'" target="_blank">'.plugins_url('wp-crossfade/css/sample.css').'</a></p>'
			);
		}
	}
	
	/**
	 * Draw Options Panel
	 */
	function set_options_subpanel() {
		global $wpp_options, $wpdb, $_POST;
		
		if( $this->update ) {
			$this->showUpdate();
			return;
		}
	
		$any_error = "";										// any error flag
	
		if( isset( $_POST['command_action'] ) ) {				// have to save options	
			$any_error = __('Your settings have been saved.');
	
			switch( $_POST['command_action'] ) {
				case "mysql_insert":
					$any_error = $this->mysql_insert();
					break;
				case "mysql_delete":
					$any_error = $this->mysql_delete();
					break;		
				case "mysql_update":
					$any_error = $this->mysql_update();
					break;		
			}
		}
		
		/**
		 * Show error or OK
		 */
		if( $any_error != '') echo '<div id="message" class="updated fade"><p>' . $any_error . '</p></div>';
		
		/**
		 * INSERT OPTION
		 *
		 * You can include a separate file: include ('options.php');
		 *
		 */
		?>
		
		<div class="wrap">
			<div class="icon32" id="icon-options-general"><br/></div>
		    <h2><?=$this->options_title?> ver. <?=$this->version?></h2>
		
			<h2><?php echo __('Insert new Banner')?></h2>
			<form class="form_box" name="insert_crossfade" method="post" action="" enctype="multipart/form-data">
				<input type="hidden" name="command_action" id="command_action" value="mysql_insert" />
				<input type="hidden" name="MAX_FILE_SIZE" value="1000000" />
				
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="group"><?php echo __('Image')?>:</label> <br />							
							<input type="radio" name="filetype" value="file" class="filetypeselection" checked="true" /> File
							<input type="radio" name="filetype" value="url" class="filetypeselection" /> Url
						</th>
						<td>
							<div id="filecontainer" class="imagecontainers" style="display: block">
								<input type="file" name="filename" id="filename" size="40" />
							</div>
							<div id="urlcontainer" class="imagecontainers" style="display: none">
								<input type="text" name="fileurl" id="fileurl" size="42" />
							</div>
						</td>
					</tr>
					<tr>
						<th scope="row"><label for="group"><?php echo __('Key')?>:</label></th>
						<td><input type="text" maxlength="8" name="group" id="group" value="home" size="8" style="text-align:right" /> <?php echo $this->get_combo_group() ?> (<?php echo __('Insert a key max 8 char')?>)</td>
					</tr>
					<tr>
						<th scope="row"><label for="title"><?php echo __('Title')?>:</label></th>
						<td><input type="text" name="title" id="title" value="" size="42" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="description"><?php echo __('Description')?>:</label></th>
						<td><input type="text" name="description" id="description" value="" size="52" /></td>
					</tr>
					<tr>
						<th scope="row"><label for="url">URL:</label></th>
						<td><input type="text" name="url" id="url" value="" size="32" /> <label for="url"><?php echo __('Target')?>:</label> <?php echo $this->get_target_combo() ?></td>
					</tr>
				</table>
				<div class="submit"><input class="button-primary" type="submit" value="<?php echo __('Insert')?>" /></div>
			</form>
			
			<form style="display:none" name="delete_crossfade" method="post" action="">
				<input type="hidden" name="command_action" id="command_action" value="mysql_delete" />
				<input type="hidden" name="id" id="id" value="" />
			</form>
	
			<div class="icon32" id="icon-edit"><br/></div><h2><?php echo __('Banners list')?></h2>
			<div class="tablenav">
				<div class="alignleft actions">
					<form class="form_box" name="filter_crossfade" method="post" action="">
						<?php $this->combo_group(); ?> <input class="button-secondary" type="submit" value="<?php echo __('Filter')?>"/>
						| <?php echo __('Use')?> <img align="absmiddle" alt="Drag and Drop" border="0" src="<?php echo $this->uri ?>/css/images/arrow_ns.png" /> <?php echo __('for drag and drop to change order')?>
					</form>
				</div>
			</div>		
			<?php
			
				$q = "SELECT * FROM `" . $this->table_crossfade . "`";
				
				if( isset( $_POST['group_filter']) ) {
					if( $_POST['group_filter'] != "" ) $q .= " WHERE `group` = '".$_POST['group_filter']."'";
				}
				
				$q .= " ORDER BY `sorter`, `group` ASC ";
				
				$rows = $wpdb->get_results( $q );
				
				$o = '<table class="widefat" id="list_crossfade" width="100%" cellpadding="4" cellspacing="0">
				       <thead>
					    <tr>
						 <th class="manage-column" scope="col"></th>
						 <th width="40" scope="col">'.__('Image').'</th>
						 <th scope="col">'.__('Key').'</th>
						 <th width="100%" scope="col">'.__('Title/Description').'</th>
						 <th scope="col">'.__('URL').'</th>
						 <th scope="col">'.__('Target').'</th>
						</tr>
					   </thead>
					   <tfoot>
					    <tr>
						 <th class="manage-column" scope="col"></th>
						 <th width="40" scope="col">'.__('Image').'</th>
						 <th scope="col">'.__('Key').'</th>
						 <th width="100%" scope="col">'.__('Title/Description').'</th>
						 <th scope="col">'.__('URL').'</th>
						 <th scope="col">'.__('Target').'</th>
						</tr>					   
					   </tfoot>
					   <tbody>';	
				
				$i = 0;	
				
				foreach( $rows as $row ) {
					$class = ($i%2 == 0) ? 'class="alternate"' : ''; $i++;
					$e = '<div class="inline-edit" id="edit_'.$row->id.'" style="display:none"><a name="edit-'.$row->id.'">&nbsp;</a>' .
						  '<form method="post" name="form_edit_'.$row->id.'">' .
						  '<input type="hidden" name="command_action" value="mysql_update" />' .
						  '<input type="hidden" name="id" value="'.$row->id.'" />' .
						  '<label for="group">' . __('Key') . ':</label> <input size="8" type="text" name="group" value="' . $row->group . '" /> ' . $this->get_combo_group("form_edit_".$row->id) . '<br/>' .
						  '<label for="title">' . __('Title') . ':</label> <input size="32" type="text" name="title" value="' . $row->title . '" /><br/>' .
							'<label for="description">' . __('Description') . ':</label> <input size="42" type="text" name="description" value="' . $row->description . '" /><br/>' .
						  '<label for="url">' . __('URL') . ':</label> <input type="text" name="url" size="52" value="' . $row->url . '" /> ' .
						  '<label for="target">' . __('Target') . ':</label> ' . $this->get_target_combo( $row->target ) . 
						  '<p class="submit inline-edit-save">' .
						  '<a onclick="jQuery(\'div#edit_'.$row->id.'\').hide();return false;" class="button-secondary cancel alignleft" title="'.__('Cancel').'" href="#cancel" accesskey="c">'.__('Cancel').'</a>' .
						  '<a onclick="document.forms[\'form_edit_'.$row->id.'\'].submit();" class="button-primary save alignright" title="' . __('Update') . '" href="#update" accesskey="s">' . __('Update') . '</a>' .
						  '</p>' .
						  '</form>' .
						  '</div>';
					
					$o .= '<tr ' . $class . ' id="item_' . $row->id . '">' .
						  '<th scope="row"><div class="arrow"></div></th> ' .
						  '<td width="40" align="left"> ' .
								'<div class="wp-crossfade-thumbnail-container">' .
									'<div class="wp-crossfade-thumbnail">' .
										'<img src="' . $row->filename . '" />' .
									'</div>' .
								'</div>' .
							'</td>' .
					    '<td>' . $row->group . '</td>' .
						  '<td width"100%">' . $e . "<br/>" . $row->title . "<br /><small>" . $row->description . "</small>" .
						  '<div class="row-actions">' .
						  '<span class="edit"><a class="edit_'.$row->id.'" title="Edit" href="#edit-'.$row->id.'">'.__('Edit').'</a> | </span>' .
						  '<span class="delete"><a onclick="delete_banner('.$row->id.');return false;" href="#" title="'.__('Delete').'" class="submitdelete">'.__('Delete').'</a> | </span>' .
						  '<span class="view"><a target="_blank" rel="permalink" href="' . $row->filename . '" title="'.__('View').'">'.__('View').'</a></span>' .
						  '</div>' .
						  '</td>' .
						  '<td>' . $row->url . '</td>' .
						  '<td>' . $row->target . '</td>' .
						  '</tr>';
				}
				$o .= '</tbody>
				       </table>';
			
				echo $o;
			?>
			
			<p style="text-align:center;font-family:Tahoma;font-size:10px">Developed by <a target="_blank" href="http://www.skookum.com"><img align="absmiddle" src="http://www.skookum.com/sites/all/themes/skookum3/images/skookumlogo.png" border="0" /></a>
				<br/>
				more Wordpress plugins on <a target="_blank" href="http://www.skookum.com">www.skookum.com</a>
			</p>	
	
		</div>
		
		<?php
	}
	
	/**
	 * Update previous WP Crossfade version to 1.0
	 * 
	 * @return 
	 */
	function showUpdate() {
		global $wpp_options, $wpdb, $_POST;
		/* * /
			?>
			<div class="wrap">
				<div class="icon32" id="icon-options-general"><br/></div>
			    <h2><?=$this->options_title?> ver. <?=$this->version?></h2>
				<?php
					if( isset( $_POST['toupdate'])) {
						$this->alterTable();
						?>
						<p>Update succefully!</p>
						<form method="post" action="">
							<div class="submit"><input type="submit"  value="Reload"/></div>
						</form>										
						<?php
					} else {
				?>			

				<p>This version use a different Database Table.</p>
				<p>You have to re-insert your banner.</p>
				<form method="post" action="">
					<input type="hidden" name="toupdate" />
					<div class="submit"><input type="submit"  value="Update"/></div>
				</form>
			</div>	
				<?php	
					}
		/* */
	}
	
	/**
	 * Build the select/option filter group
	 *
	 * @return 
	 */
	function combo_group() {
		global $wpdb, $_POST;
		$o = '<select onchange="document.forms[\'filter_crossfade\'].submit()" id="group_filter" name="group_filter">' .
		     '<option value="">'.__('All').'</option>';
		$q = "SELECT `group` FROM `" . $this->table_crossfade . "` GROUP BY `group` ORDER BY `group` ";
		$rows = $wpdb->get_results( $q );
		$sel = "";
		foreach( $rows as $row ) {
			if( $_POST['group_filter'] == $row->group ) $sel = 'selected="selected"'; else $sel = "";
			$o .= '<option ' . $sel . 'value="' . $row->group . '">' . $row->group . '</option>';
		}
		$o .= '</select>';
		echo $o;
	}	

	function get_combo_group($name="insert_crossfade") {
		global $wpdb, $_POST;
		$o = '<select onchange="document.forms[\''.$name.'\'].group.value=this.options[this.selectedIndex].value" id="group_filter">' .
		     '<option value=""></option>';
		$q = "SELECT `group` FROM `" . $this->table_crossfade . "` GROUP BY `group` ORDER BY `group` ";
		$rows = $wpdb->get_results( $q );
		$sel = "";
		foreach( $rows as $row ) {
			$o .= '<option value="' . $row->group . '">' . $row->group . '</option>';
		}
		$o .= '</select>';
		return $o;
	}
	
	/**
	 * Build combo menu for target
	 * 
	 * @return 
	 */
	function get_target_combo($sel="") {
		$o = '
		<select name="target" id="target">
			<option></option>
			<option '. ( ($sel=='_blank')?'selected="selected"':'' ) . '>_blank</option>
			<option '. ( ($sel=='_parent')?'selected="selected"':'' ) . '>_parent</option>
			<option '. ( ($sel=='_self')?'selected="selected"':'' ) . '>_self</option>
			<option '. ( ($sel=='_top')?'selected="selected"':'' ) . '>_top</option>
		</select>
		';
		return $o;
	}
	
	/**
	 * Hook the admin/plugin head
	 * 
	 * @return 
	 */
	function set_admin_head() {
		$aba = $this->ajax_url;
	?>
	<link rel="stylesheet" href="<?php echo $this->uri?>/css/style.css" type="text/css" media="screen, projection" />

	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js" type="text/javascript"></script>
	<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.1/jquery-ui.min.js" type="text/javascript"></script>
	<script type="text/javascript">
		<?php require_once( $this->path . '/js/main.php'); ?>
	</script>
	<?php
	}
	
	/**
	 * Performs the upload and stores the data in database
	 * 
	 * Array ( [name] 			=> test.pdf 
	 * 		   [type]			=> application/pdf 
	 * 		   [tmp_name] 		=> /tmp/phpcXS1lh 
	 *    	   [error] 			=> 0 
	 *    	   [size] 			=> 277304 ) 
	 * 
	 * @return 
	 */
	function mysql_insert() {
		global $wpp_options, $wpdb, $_POST, $_FILES;

		if( $_POST['filetype'] == 'file' ){
			if( $_FILES['filename']['error'] == 0 ) {
				$size 			= floor( $_FILES['filename']['size'] / (1024*1024) );
				$mime 			= $_FILES['filename']['type'];
				$name 			= $_FILES['filename']['name'];
				$temp 			= $_FILES['filename']['tmp_name'];
			
				$uploads		= wp_upload_bits( strtolower($name), '', '' );

				if( $uploads['error'] == FALSE ){
					if ( !move_uploaded_file( $_FILES['filename']['tmp_name'], $uploads['file'] )) {
						return ( '<div id="result">' . _('Unable to move the file') .': ' . $_FILES['filename']['name'] .
						         ' (' . $_FILES['filename']['size'] . ' '._('bytes').'), '._('to the uploads directory').': '._('Error').' ' . $_FILES['filename']['error'] . '</div>' );
					}
				} else {
					return ( '<div id="result">' . $uploads['error'] . '</div>' );
				}
			} else {
				return( '<div id="result">' . _('Unable to upload the file') .': ' . $_FILES['filename']['name'] .
				        ' (' . $_FILES['filename']['size'] . ' '._('bytes').'). '._('Error').' ' . $_FILES['filename']['error'] . '</div>' );
			}
			
			$filename = $uploads['url'];
			$realpath = $uploads['file'];
		} else {
			$filename = $_POST['fileurl'];
			$realpath = '';
		}
		
		$group 		 	= $_POST['group'];
		$title 	= $_POST['title'];
		$description 	= $_POST['description'];
		$url 		 	= $_POST['url'];
		$target 	 	= $_POST['target'];
		
		$q = "INSERT INTO `" . $this->table_crossfade . "`" .
		     " ( `group`, `title`, `description`, `url`, `filename`, `target`, `realpath` )" .
			 " VALUES ('" . $group . "', '" . $title . "', '" . $description . "', '" . $url . "', '" . $filename . "', '" . $target . "', '" . $realpath . "')";
		$wpdb->query($q);	 
		
		return( '' );
	}	

	/**
	 * Delete a banner
	 * 
	 * @return 
	 */
	function mysql_delete() {
		global $wpdb, $_POST, $_FILES;
		//
		$filename = $wpdb->get_var( "SELECT realpath FROM `" . $this->table_crossfade . "` WHERE `id` = " . $_POST['id'] );
		if(!empty($filename)){
			@unlink( $filename );
		}
		
		$q = "DELETE FROM `" . $this->table_crossfade . "` WHERE `id` = " . $_POST['id'];
		$wpdb->query($q);
		return('');
	}
	
	function mysql_update() {
		global $wpdb, $_POST, $_FILES;
		
		$q = "UPDATE `" . $this->table_crossfade . "`" .
			 "set `group` = '{$_POST['group']}', " .
			 "`title` = '{$_POST['title']}', " .
			 "`description` = '{$_POST['description']}', " .
			 "`url` = '{$_POST['url']}', " .
			 "`target` = '{$_POST['target']}' " .
		 	 " WHERE `id` = " . $_POST['id'];
		$wpdb->query($q);
		return('');
	}
	
	function register_plugin_settings( $pluginfile ) {
		add_action( 'plugin_action_links_'.basename( dirname( $pluginfile ) ) . '/' . basename( $pluginfile ), array( &$this, 'plugin_settings' ), 10, 4 );
	}
	
	function plugin_settings( $links ) {
		$settings_link = '<a href="admin.php?page=wp-crossfade-settings">' . __('Settings') . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}	

	
	/**
	 * Check if 'crossfade' table exists on the database
	 * if not exists then create it
	 * 
	 * @return 
	 */
	function checkTable() {
		global $wpdb;
		
		/**
		 * Check old wp-crossfade version
		 */
		
		$q = 'DESC `' . $this->table_crossfade . '`';
		$rows = $wpdb->get_results( $q );
		if( count( $rows ) > 0 && count( $rows ) < 8 ) {
			// previou version
			return true;
		} else {
			$this->createTable();
		}
		return false;
	}	
	
	/**
	 * Create WP Crossfade table for store banner data
	 * 
	 * @return 
	 */
	function createTable() {
		global $wpdb;
		$q = 'CREATE TABLE IF NOT EXISTS `' . $this->table_crossfade . '` (
			  `id` int(11) NOT NULL auto_increment,
			  `sorter` int(11) NOT NULL,
			  `group` varchar(8) NOT NULL,
			  `title` varchar(255) NOT NULL,
			  `description` varchar(255) NOT NULL,
			  `url` varchar(256) NOT NULL,
			  `target` varchar(32) NOT NULL,
			  `filename` varchar(255) NOT NULL,
			  `realpath` varchar(255) NOT NULL,
			  PRIMARY KEY  (`id`)
			)';
		$wpdb->query($q);		
	}
	
	/**
	 * Drop WP Crossfade table
	 * 
	 * @return 
	 */
	function dropTable() {
		global $wpdb;
		$q = 'DROP TABLE `' . $this->table_crossfade . '`';
		$wpdb->query($q);		
	}
	
	/**
	 * Alter WP Crossfade table
	 * 
	 * ALTER TABLE `crossfade` ADD `realpath` VARCHAR( 255 ) NOT NULL 
	 * 
	 * @return 
	 */
	function alterTable() {
		global $wpdb;
			
	}
	
} // end of class

?>