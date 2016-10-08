jQuery(document).ready(function() {
		jQuery("a#nggv_more_results").click(function(e) { //button click to open more detail on the voting
				tb_show("", "#TB_inline?width=640&height=300&inlineId=nggvShowList&modal=true", false); //thick box seems to be included, so lets use it :)
				
				jQuery.get(nggv_ajax_url, 'gid='+nggv_gid, function(data, status) {
						if(status == 'success') {
							jQuery("div#nggvShowList_content").html(data);
						}else{
							jQuery("div#nggvShowList_content").html("There was a problem retrieving the list of votes, please try again in a momement.");
						}
				});
				e.preventDefault();
				return false; //cancel click
		});
		
		jQuery("a#nggv_more_results_close").click(function(e) {
				tb_remove();
				e.preventDefault();
				return false;
		});
		
		jQuery("a.nggv_more_results_image").click(function(e) { //button click to open more detail on the voting
				var criteriaId = parseInt(jQuery(this).data('criteria_id'));
				var pid = parseInt(this.id.substr(24));
				tb_show("", "#TB_inline?width=640&height=300&inlineId=nggvShowList&modal=true", false); //thick box seems to be included, so lets use it :)
				
				jQuery.get(nggv_ajax_url, 'pid='+pid+'&criteria_id='+criteriaId, function(data, status) {
						if(status == 'success') {
							jQuery("div#nggvShowList_content").html(data);
						}else{
							jQuery("div#nggvShowList_content").html("There was a problem retrieving the list of votes, please try again in a momement.");
						}
				});
				
				e.preventDefault();
				return false; //cancel click
		});
		
		jQuery('a.nggv_clear_image_results').click(function(e) { //button click to clear all votes per image. Just add a quick confirm to it
				if(!confirm('Are you sure you want to delete all votes for this image? This cannot be undone!')) {
					e.preventDefault();
					return false;
				}
		});
		
		jQuery('.nggv-tab-list li a').click(function(e) {
				var i = jQuery(this).parent().index();
				var parent = jQuery(this).parents('.nggv-voting-options'); //get this block to work with
				
				jQuery(parent).find('.nggv-tab-list li').removeClass('active');
				jQuery(jQuery(parent).find('.nggv-tab-list li')[i]).addClass('active');
				
				jQuery(parent).find('.nggv-tab-content').removeClass('active');
				jQuery(jQuery(parent).find('.nggv-tab-content')[i]).addClass('active');

				e.preventDefault();
				return false;
		});
		
		jQuery('tr.nggv-force-once input').change(function() {
				var $container = jQuery(this).parents('table.nggv-image-voting-options');
				
				if(jQuery(this).val() == 1 || jQuery(this).val() == 2)  {
					$container.find('.nggv-enforce-cookies input').prop('disabled', false);
					console.log($container.find('.nggv-enforce-cookies input'));
					
					$container.find('.nggv-force-once-time select').prop('disabled', false);
				}else{
					$container.find('.nggv-enforce-cookies input').attr('checked', false);
					$container.find('.nggv-enforce-cookies input').prop('disabled', true);
					
					$container.find('.nggv-force-once-time select').prop('disabled', true);
				}
		});
		jQuery('tr.nggv-force-once input:checked').change();
});