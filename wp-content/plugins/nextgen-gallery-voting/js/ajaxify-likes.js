function nggv_dis_like_click(e) {
		var container = jQuery(this).parents('.nggv_container');
		
		container.find('img.nggv-star-loader').show();
		
		var url = jQuery(this).attr('href');
		url += '&ajaxify=1';
		
		jQuery.ajax({
				url: url,
				data: '',
				success: function(data, textStatus, XMLHttpRequest) {
					var ajaxVer = data.indexOf('var nggv_ajax_ver'); //bit of a hack to improve ajax with minification, but keeping backwards compat for older users
					if(ajaxVer > -1) { //means it was actually found, the ver num is irrelevent for now
						eval(data);
					}else{
						var start = data.indexOf("<!-- NGGV START AJAX RESPONSE -->") + 41; //find the start of the outputting by the ajax url (stupid wordpress and poor buffering options blah blah)
						var end = data.indexOf("<script><!-- NGGV END AJAX RESPONSE -->");
						
						var js = data.substr(start, (end-start));
						js = js.replace(/(\r\n|\n|\r)/gm,'');
						
						eval(js); //the array of voters gets echoed out at the ajax url
					}
					
					if(typeof(nggv_js) == 'object') {
						var msg = '';
						if(nggv_js.saved) {
							jQuery(document).focus();
							container.find('.nggv-vote-form').html(nggv_js.voting_form);
						}else{
							if(nggv_js.msg) {
								msg = nggv_js.msg
							}else{ //there should always be a msg, but just in case lets default one
								msg = jQuery('#ngg-genric-err-msg').val();
							}
							
							//if we got markup back, replace the voting form with it
							if(nggv_js.voting_form) {
								jQuery(document).focus();
								container.find('.nggv-vote-form').html(nggv_js.voting_form);
							}
						}
					}else{
						msg = jQuery('#ngg-genric-err-msg').val();
					}
					
					if(msg) {
						//the 'continer' div and 'nggv-error' div are on the same dom level, making them siblings
						container.find('div.nggv-error').show();
						container.find('div.nggv-error').html(msg);
					}
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					container.find('div.nggv-error').show();
					container.find('div.nggv-error').html(jQuery('#ngg-genric-err-msg').val());
				},
				complete: function() {
					container.find('img.nggv-star-loader').hide();
				}
		});
		
		e.preventDefault();
		return false;
}

jQuery(document).ready(function() {
		jQuery('a.nggv-link-like, a.nggv-link-dislike').click(nggv_dis_like_click);
});