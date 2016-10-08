jQuery(document).ready(function() {
		jQuery('#nggv_date_from, #nggv_date_to').datepicker({
				dateFormat : 'yy-mm-dd'
		});
		
		jQuery('a.nggv-top-vote-item').click(function(e) {
				jQuery(this).parents('tr').next().toggle();
				e.preventDefault();
		});
		
		/*
		jQuery('a.nggv-delete-vote').click(function(e) {
				if(!confirm('Are you sure you want to delete this vote? This cannot be undone!')) {
					e.preventDefault();
				}
		});
		*/
});

