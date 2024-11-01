/**
 * @author Giovambattista Fazioli
 */
jQuery(document).ready(function(){
	jQuery('table#list_crossfade tbody tr').css('width',jQuery('table#list_crossfade').width() );
	jQuery('table#list_crossfade tbody').sortable({
				axis:"y",
				cursor:"n-resize",
				stop:function() {
					jQuery.ajax({
					type: "POST",
					url: "<?=$this->ajax_url?>",
					data: jQuery("table#list_crossfade tbody").sortable("serialize")	})
				}
	});
	
	// edit
	jQuery('span.edit a').click(function() {
		jQuery('div#' + jQuery(this).attr('class') ).show();
	});
	
	jQuery('input.filetypeselection').click(function(){
		jQuery('div.imagecontainers').hide();
		jQuery('#'+this.value+'container').show();
	});
});

function delete_banner( id ) {
	if( confirm('WARINING!!\n\nDo you want delete this banner?') ) {
		var f = document.forms['delete_crossfade'];
		f.id.value = id;
		f.submit();
	}
}