jQuery(document).ready(function() {

	jQuery('.sv-delete-gallery').click(function() {
		return confirm('Are you sure you want to delete this gallery?');
	});
	jQuery('.sv-delete').click(function() {
		return confirm('Are you sure you want to delete all galleries and options?');
	});
	jQuery('.sv-reset').click(function() {
		return confirm('Are you sure you want to reset the default values of the gallery configuration options to their original values?');
	});

	jQuery('#e_library').change(function(e) {
		if (jQuery('#e_library').val() == 'media') {
			jQuery('#toggle-media').show();
			jQuery('#toggle-flickr').hide();
			jQuery('#toggle-nextgen').hide();
			jQuery('#toggle-picasa').hide();
			jQuery('#toggle-folder').hide();
		}
		if (jQuery('#e_library').val() == 'flickr') {
			jQuery('#toggle-media').hide();
			jQuery('#toggle-flickr').show();
			jQuery('#toggle-nextgen').hide();
			jQuery('#toggle-picasa').hide();
			jQuery('#toggle-folder').hide();
		}
		if (jQuery('#e_library').val() == 'nextgen') {
			jQuery('#toggle-media').hide();
			jQuery('#toggle-flickr').hide();
			jQuery('#toggle-nextgen').show();
			jQuery('#toggle-picasa').hide();
			jQuery('#toggle-folder').hide();
		}
		if (jQuery('#e_library').val() == 'picasa') {
			jQuery('#toggle-media').hide();
			jQuery('#toggle-flickr').hide();
			jQuery('#toggle-nextgen').hide();
			jQuery('#toggle-picasa').show();
			jQuery('#toggle-folder').hide();
		}
		if (jQuery('#e_library').val() == 'folder') {
			jQuery('#toggle-media').hide();
			jQuery('#toggle-flickr').hide();
			jQuery('#toggle-nextgen').hide();
			jQuery('#toggle-picasa').hide();
			jQuery('#toggle-folder').show();
		}
	});

	jQuery('#e_library').trigger('change');

});
