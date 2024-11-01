<?php
require_once('../../../wp-config.php');
$_db = @mysql_connect ( DB_HOST, DB_USER, DB_PASSWORD ); mysql_select_db( DB_NAME );
foreach($_POST["item"] as $key => $value){  
	$sql = "UPDATE `crossfade` SET `sorter` = {$key} WHERE id = {$value}";  
	$result = mysql_query($sql);  
}  
?>