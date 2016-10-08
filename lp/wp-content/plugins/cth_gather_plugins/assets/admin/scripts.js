jQuery('.repeatable_add_option').click(function(event){
    event.preventDefault();
    //alert('1');
    $this = jQuery(this);
    $thistr = $this.closest('tr');
    field_name = $this.attr('data-name');
    key_val = parseInt($thistr.prev('tr').attr('data-key'))+1;
    $thistr.before('<tr data-key="'+key_val+'"><td><input type="text" name="'+field_name+'['+key_val+'][name]"></td><td><input type="text" name="'+field_name+'['+key_val+'][value]"></td><td><a href="#" class="repeatable_remove_option"></a></td></tr>');
});
jQuery('.repeatable_remove_option').click(function(event){
    event.preventDefault();
    $this = jQuery(this);
    $thistr = $this.closest('tr');
    $thistr.remove();
});
jQuery('#resend_paypal_link').click(function(event){
    event.preventDefault();
    var datas = {
        action  : 'eventres_resend_paypal_link',
        nonce    : jQuery('input[name="eventres_resend_paypal_link_nonce"]').val(),
        pm_id   : jQuery(this).attr('data-pid'),
        pm_st   : jQuery(this).attr('data-pst')
    };
    jQuery.post(ajaxurl, datas , function(a) {
        jQuery('#rsp_msg').text(a.message).css('display','block');
    }, 'json');
});