/* ================================================
   Paypal Form Validation
   ================================================ */

// validate Registration Form
jQuery("#paypal-regn").validate({
    rules: {
        first_name: "required",
        last_name: "required",
        email: {
            required: true,
            email: true
        },
        os0: "required",
        item_price: "required",
        quantity: "required",
        agree: "required"
    },
    messages: {
        first_name: eventres_ajax.msg_fname,
        last_name: eventres_ajax.msg_lname,
        email: eventres_ajax.msg_email,
        os0: eventres_ajax.msg_pass,
        item_price: "Choose your Pass",
        quantity: eventres_ajax.msg_seats,
        agree: eventres_ajax.msg_agree
    },
    submitHandler: function(form) {
        jQuery("#reserve-btn").attr("disabled", true);
        jQuery('#eventreg_msg').css('display','none');
        ajaxEventReg(form);
    }
});

function ajaxEventReg(form){
    contents = jQuery(form).serialize();
    contents += '&action=gather_event_paypal_registration';
    jQuery.post( eventres_ajax.url, contents, function( data ){
        
        //console.log(data);
        if(data.success == 1){
            window.location = data.message;
        }else{
            jQuery('#eventreg_msg').html(data.message).css('display','block');
            jQuery("#reserve-btn").attr("disabled", false);
        }
    }, 'json');
}

jQuery("#email-registration-form").submit(function(e) {
    e.preventDefault();
}).validate({
    rules: {
        first_name: "required",
        last_name: "required",
        email: {
            required: true,
            email: true
        },
        os0: "required",
        quantity: "required",
        agree: "required"
    },
    messages: {
        first_name: eventres_ajax.msg_fname,
        last_name: eventres_ajax.msg_lname,
        email: eventres_ajax.msg_email,
        os0: eventres_ajax.msg_pass,
        quantity: eventres_ajax.msg_seats,
        agree: eventres_ajax.msg_agree
    },
    submitHandler: function(form) {

        jQuery("#reserve-btn").attr("disabled", true);
        jQuery('#eventreg_msg').css('display','none');
        ajaxEmailEventReg(form);

        // jQuery(".js-register-btn").attr("disabled", true);

        /* 
        CHECK PAGE FOR REDIRECT (Thank you page)
        ---------------------------------------- */

        // var redirect = $('#email-registration-form').data('redirect');
        // var noredirect = false;
        // if (redirect == 'none' || redirect == "" || redirect == null) {
        //     noredirect = true;
        // }

        // jQuery("#js-register-result").html('<p class="help-block">Please wait...</p>');

        /* 
        FETCH SUCCESS / ERROR MSG FROM HTML DATA-ATTR
        --------------------------------------------- */

        // var success_msg = jQuery('#js-register-result').data('success-msg');
        // var error_msg = jQuery('#js-register-result').data('error-msg');

        // var dataString = jQuery(form).serialize();

        /* 
         AJAX POST
         --------- */

        // jQuery.ajax({
        //     type: "POST",
        //     data: dataString,
        //     url: "php/register.php",
        //     cache: false,
        //     success: function(d) {
        //         jQuery(".form-group").removeClass("has-success");
        //         if (d == 'success') {
        //             if (noredirect) {
        //                 jQuery('#js-register-result').fadeIn('slow').html('<div class="alert alert-success top-space">' + success_msg + '</div>').delay(3000).fadeOut('slow');
        //             } else {
        //                 window.location.href = redirect;
        //             }
        //         } else {
        //             jQuery('#js-register-result').fadeIn('slow').html('<div class="alert alert-danger top-space">' + error_msg + '</div>').delay(3000).fadeOut('slow');
        //         }
        //         jQuery(".js-register-btn").attr("disabled", false);
        //     }
        // });
        // return false;

    }
});
function ajaxEmailEventReg(form){
    var redirect = jQuery(form).data('redirect');
    var noredirect = false;
    if (redirect == 'none' || redirect == "" || redirect == null) {
        noredirect = true;
    }
    jQuery('#eventreg_msg').fadeIn('slow').html(eventres_ajax.msg_wait);

    contents = jQuery(form).serialize();
    contents += '&action=gather_event_email_registration';

    jQuery.post( eventres_ajax.url, contents, function( data ){
        //jQuery('#eventreg_msg').html('').fadeOut('slow');
        //console.log(data);
        if(data.success == 1){
            //window.location = data.message;
            if (noredirect) {
                jQuery('#eventreg_msg').html(data.message).delay(5000).fadeOut('slow');
            } else {
                window.location.href = redirect;
            }
            //jQuery('#eventreg_msg').html(data.message).css('display','block');
        }else{
            jQuery('#eventreg_msg').html(data.message).delay(5000).fadeOut('slow');
            //jQuery('#eventreg_msg').html(data.message).css('display','block');
            jQuery("#reserve-btn").attr("disabled", false);
        }
    }, 'json');
}