function init_betternotes_sortable() {
	jQuery('#betternotes').sortable({
		containment: 'parent',
		stop: function(e, ui) {
			jQuery('#betternotes .betternote-wrapper').each(function(i, id) {
				jQuery(this).find('input.betternote_order').val(i+1);
			});
		}
	});
}

jQuery(document).ready(function() {
	
	// If there are no notes, let's hide this thing...
	if(jQuery('.betternote-copy').length == 0) {
		jQuery('#betternotes').hide();
	}
	else
	{
		init_betternotes_sortable();
	}
	
	// Hook the add button
	jQuery('#betternote-add-new a').click(function() {
		betternote_index = jQuery('.betternote-copy').length;
		new_betternote = '';
		new_betternote += '<div class="betternote-wrapper">';
		new_betternote += '	<div class="betternote-inner">';
		new_betternote += '		<div class="betternote-actions">';
		new_betternote += '			<a href="#" class="betternote-handle">Move</a>';
		new_betternote += '			<a href="#" class="betternote-delete">Delete</a>';
		new_betternote += '		</div>';
		new_betternote += '		<div class="betternote-copy">';
		new_betternote += '			<textarea id="betternote_' + betternote_index + '" name="betternote_' + betternote_index + '" class="theEditor betternote betternote-copy"></textarea>';
		new_betternote += '		</div>';
		new_betternote += '		<div class="betternotes-data">';
		new_betternote += '			<input type="hidden" name="betternote_id_' + betternote_index + '" id="betternote_id_' + betternote_index + '" value="' + betternote_index + '" />';
		new_betternote += '			<input type="hidden" class="betternote_order" name="betternote_order_' + betternote_index + '" id="betternote_order_' + betternote_index + '" value="' + betternote_index + '" />';
		new_betternote += '		</div>';
		new_betternote += '	</div>';
		new_betternote += '</div>';
		
		jQuery('#betternotes').append(new_betternote);
		
		if(jQuery('.betternote-copy').length > 0) {

			// We've got some notes
			jQuery('#betternotes').show();

			// Cleanup
			jQuery('#betternotes').sortable('destroy');
			
			// Init sortable
			init_betternotes_sortable();
			
		}
		
		if( typeof(tinyMCEPreInit.mceInit) != 'undefined' )
		{		
			tinyMCEPreInit.mceInit.height = '300';
			tinyMCEPreInit.mceInit.theme_advanced_resizing = false;
			tinyMCE.init(tinyMCEPreInit.mceInit);
		} else {
			alert("There was an error initializing TinyMCE");
		}
		
		return false;
	});

	
	
	// Hook our delete links
	jQuery('.betternote-delete').live('click', function() {
		var betternotedelete = confirm('Are you sure you want to delete this note?');
		if(betternotedelete) {
			betternote_parent = jQuery(this).parent().parent();
			betternote_parent.slideUp(function() {
				betternote_parent.remove();
			});
			jQuery('.betternote-wrapper').each(function(i, id) {
				jQuery(this).find('input.betternote_order').val(i+1);
			});
			if(jQuery('.betternote-copy').length == 0) {
				jQuery('#betternotes').slideUp(function() {
					jQuery('#betternotes').hide();
				});
			}
		}
		return false;
	});

	
});