<?php
if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}

add_action( 'wp_ajax_nopriv_gather_event_paypal_registration', 'cththemes_gather_event_paypal_registration_callback' );
add_action( 'wp_ajax_gather_event_paypal_registration', 'cththemes_gather_event_paypal_registration_callback' );
function cththemes_gather_event_paypal_registration_callback() {
    global $wpdb;

    $register_new_user = false;

    
    
    //$error = '';
    $json = array();
    $json['success'] = 0;
    //$success = '';
    $nonce = $_POST['gather_event_paypal_registration_nonce'];
    
    if ( ! wp_verify_nonce( $nonce, 'gather_event_paypal_registration_action' ) )
        die ( '<p class="error">Security checked!, Cheatn huh?</p>' );

    // Post datas
    $res_post_datas = array();

    $res_post_datas['first_name'] = trim($_POST['first_name']);
    $res_post_datas['last_name'] = trim($_POST['last_name']);
    $res_post_datas['email'] = trim($_POST['email']);

    // for option prices
    $paypal_options = array();
    $paypal_options['on0'] = trim($_POST['on0']);
    $paypal_options['os0'] = trim($_POST['os0']);
    $prices_option = get_option('eventres_item_prices');
    
    if(!empty($prices_option)){
    	$op_in = 0;
    	foreach ($prices_option as $pr_arr) {
    		$paypal_options['option_select'.$op_in] = trim($pr_arr['name']);
    		$paypal_options['option_amount'.$op_in] = trim($pr_arr['value']);
    		if($paypal_options['os0'] == trim($pr_arr['name'])){
    			//for reservation meta data store
    			$res_post_datas['price'] = trim($pr_arr['value']);
    		}

    		$op_in++;
    	}
    }else{
    	$res_post_datas['price'] = '99';
    	$paypal_options['option_select0'] = 'Default Pass';
    	$paypal_options['option_amount0'] = '99';
    }

    

    $res_post_datas['quantity'] = trim($_POST['item_quantity']);

    //$cth_captcha_code = esc_sql($_POST['cth_captcha_code']); 
    if( empty($res_post_datas['first_name'] ) ) {
        $json['message'] = __('First name is required.','cth-gather-plugins');
    }else if( empty( $res_post_datas['last_name'] ) ) {
        $json['message'] = __('Last name is required.','cth-gather-plugins');
    }else if( empty( $res_post_datas['email'] ) ) {
        $json['message'] = __('Email is required.','cth-gather-plugins');
    }else {

        /* event reservation datas */
        $auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
        $pur_key = strtolower( md5( $res_post_datas['email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'gatherevent', true ) ) );  // Unique key

        $res_datas = array();
        if(isset($res_post_datas['first_name'])|| isset($res_post_datas['last_name'])){
            $res_datas['post_title'] = $res_post_datas['first_name'] .' '.$res_post_datas['last_name'];
        }else{
            $res_datas['post_title'] = $res_post_datas['email'];
        }
        
        //$res_datas['post_name'] = '';
        $res_datas['post_content'] = '';
        //$res_datas['post_author'] = '0';// default 0 for no author assigned
        $res_datas['post_status'] = 'publish';
        $res_datas['post_type'] = 'cth_eventres';

        $res_meta_datas = array();
        $res_meta_datas['pur_pass']     = trim($_POST['os0']);
        $res_meta_datas['pur_price']     = $res_post_datas['price'];
        $res_meta_datas['pur_quantity']     = $res_post_datas['quantity'];
        $res_meta_datas['pur_date']     = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
        $res_meta_datas['pur_currency']     = get_option('eventres_currency' );

        $res_meta_datas['pur_key'] = $pur_key;
        $res_meta_datas['pur_custom'] = uniqid();
        $res_meta_datas['pur_gateway']     = 'Paypal Standard';
        $res_meta_datas['pur_status']     = 'Pending';

        
        $res_meta_datas['pur_fname'] = $res_post_datas['first_name'];
        $res_meta_datas['pur_lname'] = $res_post_datas['last_name'];
        $res_meta_datas['pur_email'] = $res_post_datas['email'];

        $res_id = wp_insert_post($res_datas ,true );
                    

        if (is_wp_error($res_id)) {
            $json['message'] = $res_id->get_error_message();
        }else{
            foreach ($res_meta_datas as $key => $value) {
                if ( !add_post_meta( $res_id, $key, $value , true ) ) {
                    $json['message'] = sprintf(__('Insert reservation %s data failure','cth-gather-plugins'),$key);
                    wp_send_json($json );
                }
            }


            // only send to paypal if the pending payment is created successfully
            $listener_url = get_permalink(get_option('eventres_success_page' ) );// trailingslashit( home_url() ).'?cth-listener=IPN';
            $cancel_url = get_permalink(get_option('eventres_cancelled_page' ) );// trailingslashit( home_url() ).'?cth-listener=IPN';
            $return_url = get_permalink(get_option('eventres_return_page' ) );

            // one time payment
            $eventres_test_mode = get_option('eventres_test_mode' );

            if( $eventres_test_mode ) {
                $paypal_redirect = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
            } else {
                $paypal_redirect = 'https://www.paypal.com/cgi-bin/webscr/?';
            }
            $paypal_args = array(
                'cmd' => '_xclick',
                //'amount' => $res_post_datas['price'],
                'item_name' => get_option('eventres_item_name' ),
                'item_number' => $res_meta_datas['pur_custom'],
                'quantity' => $res_post_datas['quantity'],
                'currency_code' => get_option('eventres_currency'),
                'custom' => $res_id,
                'business' => get_option('eventres_paypal_email'),
                
                'email' => $res_post_datas['email'],
                'first_name'=>$res_post_datas['first_name'],
                'last_name'=>$res_post_datas['last_name'],
                
                'no_shipping' => '1',
                'no_note' => '1',
                
                 
                'charset' => 'UTF-8',
                
                'rm' => '2',//return method / 2: mean post
                'cancel_return'=>$cancel_url,
                'return' => $return_url,
                'notify_url' => $listener_url
            );
			// merge with $paypal_options array
			$paypal_args = array_merge($paypal_args, $paypal_options);
            $paypal_redirect .= http_build_query($paypal_args);


            $json['success']  = 1;    
            $json['message']  = $paypal_redirect;
        }   

        
    }
    // return proper result
    wp_send_json($json );
}


add_action( 'wp_ajax_nopriv_eventres_resend_paypal_link', 'cththemes_eventres_resend_paypal_link_callback' );
add_action( 'wp_ajax_eventres_resend_paypal_link', 'cththemes_eventres_resend_paypal_link_callback' );
function cththemes_eventres_resend_paypal_link_callback() {

    $json = array();
    $json['success'] = 0;
    $nonce = $_POST['nonce'];
    //wp_send_json($_POST );
    
    if ( ! wp_verify_nonce( $nonce, 'eventres_resend_paypal_link_action' ) )
        die ( '<p class="error">Security checked!, Cheatn huh?</p>' );

    $payment_id = (int)$_POST['pm_id'];
    $pm_st = $_POST['pm_st'];



    // for option prices
    $paypal_options = array();
    $paypal_options['on0'] = get_option('eventres_item_prices_name' ,'Pass');
    $paypal_options['os0'] = get_post_meta( $payment_id, 'pur_pass', true );
    $prices_option = get_option('eventres_item_prices');
    
    if(!empty($prices_option)){
        $op_in = 0;
        foreach ($prices_option as $pr_arr) {
            $paypal_options['option_select'.$op_in] = trim($pr_arr['name']);
            $paypal_options['option_amount'.$op_in] = trim($pr_arr['value']);
            if($paypal_options['os0'] == trim($pr_arr['name'])){
                //for reservation meta data store
                $pm_price = trim($pr_arr['value']);
            }
            $op_in++;
        }
    }else{
        $pm_price = '99';
        $paypal_options['option_select0'] = 'Default Pass';
        $paypal_options['option_amount0'] = '99';
    }


    // only send to paypal if the pending payment is created successfully
    $listener_url = get_permalink(get_option('eventres_success_page' ) );// trailingslashit( home_url() ).'?cth-listener=IPN';
    $cancel_url = get_permalink(get_option('eventres_cancelled_page' ) );// trailingslashit( home_url() ).'?cth-listener=IPN';
    $return_url = get_permalink(get_option('eventres_success_page' ) );

    // one time payment
    $eventres_test_mode = get_option('eventres_test_mode' );

    if( $eventres_test_mode ) {
        $paypal_link = 'https://www.sandbox.paypal.com/cgi-bin/webscr/?';
    } else {
        $paypal_link = 'https://www.paypal.com/cgi-bin/webscr/?';
    }
    /* email template variable */
    $first_name = get_post_meta( $payment_id, 'pur_fname', true );
    $last_name = get_post_meta( $payment_id, 'pur_lname', true );
    $payer_email = get_post_meta( $payment_id, 'pur_email', true );
    $receiver_email = get_option('eventres_paypal_email');
    $item_name = get_option('eventres_item_name' );
    $item_number = get_post_meta( $payment_id, 'pur_custom', true );
    $quantity = get_post_meta( $payment_id, 'pur_quantity', true );
    $payment_currency = get_option('eventres_currency');
    $payment_amount = (float)$pm_price * (int)$quantity;
    $event_pass = get_post_meta( $payment_id, 'pur_pass', true );
    $date           = get_post_meta( $payment_id, 'pur_date', true );
    $sitename           = get_option( 'blogname' );

    $paypal_args = array(
        'cmd' => '_xclick',
        //'amount' => $res_post_datas['price'],
        'item_name' => $item_name,
        'item_number' => $item_number,
        'quantity' => $quantity,
        'currency_code' => $payment_currency,
        'custom' => $payment_id,
        'business' => $receiver_email,
        
        'email' => $payer_email,
        'first_name'=>$first_name,
        'last_name'=>$last_name,
        
        'no_shipping' => '1',
        'no_note' => '1',
        
         
        'charset' => 'UTF-8',
        
        'rm' => '2',//return method / 2: mean post
        'cancel_return'=>$cancel_url,
        'return' => $return_url,
        'notify_url' => $listener_url
    );

    // merge with $paypal_options array
    $paypal_args = array_merge($paypal_args, $paypal_options);
    $paypal_link .= http_build_query($paypal_args);


    $sender_option = get_option('eventres_email_sender','Gather Event' );
    $sender_email_option = get_option('eventres_email_sender_email','contact.cththemes@gmail.com' );

    // to buyer
    $resend_paypal_link_mail_subject = get_option('eventres_resend_paypal_link_email_subject','Your payment have never been completed'); 
    $resend_paypal_link_email_template = get_option('eventres_resend_paypal_link_email_template'); 

    if(preg_match_all("/{([\w_]+)[^\w_]*}/", $resend_paypal_link_email_template, $matches)!= FALSE){
        $fieldsPattern = array();//$matches[0];
        $fieldsReplace = array();
        foreach ($matches[1] as $key => $fn) {
            $fieldsPattern[] = "/{(".$fn.")[^\w_]*}/";

            if( isset( $$fn )&&in_array( $fn, array('first_name','last_name','payer_email','receiver_email','item_name','item_number','quantity','payment_currency','payment_amount','event_pass','date','sitename','paypal_link','payment_id') ) ){
                $fieldsReplace[] = $$fn;  //'['.$fn.']';
            }else{
                $fieldsReplace[] = '{'.$fn.'}';
            }
            
        }


        $resend_paypal_link_email_template = preg_replace($fieldsPattern, $fieldsReplace, $resend_paypal_link_email_template);
    }


    $headers = array();
    // if(get_option('eventres_email_content_type','yes' ) == 'yes'){
    //     $headers[] = 'Content-Type: text/html; charset=UTF-8';
    // }
    $headers[] = 'Content-Type: text/plain; charset=UTF-8';
    //$headers[] = 'From: '. $sender_option.' ' . '<'.$sender_email_option.'>';
    add_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
    $headers[] = 'Reply-To: '. $sender_option.' ' . '<'.$sender_email_option.'>';

    if (!wp_mail( $payer_email, $resend_paypal_link_mail_subject , $resend_paypal_link_email_template, $headers)) {
        $json['message']  = 'Email send failed';
    }else{
        $json['message']  = 'Email sent';
    }
    
    remove_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );

    wp_send_json($json );
}
add_action( 'wp_ajax_nopriv_gather_event_email_registration', 'cththemes_gather_event_email_registration_callback' );
add_action( 'wp_ajax_gather_event_email_registration', 'cththemes_gather_event_email_registration_callback' );
function cththemes_gather_event_email_registration_callback() {
    global $wpdb;

    $register_new_user = false;

    
    
    //$error = '';
    $json = array();
    $json['success'] = 0;
    //$success = '';
    $nonce = $_POST['gather_event_email_registration_nonce'];
    
    if ( ! wp_verify_nonce( $nonce, 'gather_event_email_registration_action' ) )
        die ( '<p class="error">Security checked!, Cheatn huh?</p>' );

    // Post datas
    $res_post_datas = array();

    $res_post_datas['first_name'] = trim($_POST['first_name']);
    $res_post_datas['last_name'] = trim($_POST['last_name']);
    $res_post_datas['email'] = trim($_POST['email']);

    // for option prices
    $paypal_options = array();
    $paypal_options['on0'] = trim($_POST['on0']);
    $paypal_options['os0'] = trim($_POST['os0']);
    $prices_option = get_option('eventres_item_prices');
    
    if(!empty($prices_option)){
        $op_in = 0;
        foreach ($prices_option as $pr_arr) {
            $paypal_options['option_select'.$op_in] = trim($pr_arr['name']);
            $paypal_options['option_amount'.$op_in] = trim($pr_arr['value']);
            if($paypal_options['os0'] == trim($pr_arr['name'])){
                //for reservation meta data store
                $res_post_datas['price'] = trim($pr_arr['value']);
            }

            $op_in++;
        }
    }else{
        $res_post_datas['price'] = '99';
        $paypal_options['option_select0'] = 'Default Pass';
        $paypal_options['option_amount0'] = '99';
    }
    $res_post_datas['quantity'] = trim($_POST['item_quantity']); 
    if( empty($res_post_datas['first_name'] ) ) {
        $json['message'] = __('First name is required.','cth-gather-plugins');
    }else if( empty( $res_post_datas['last_name'] ) ) {
        $json['message'] = __('Last name is required.','cth-gather-plugins');
    }else if( empty( $res_post_datas['email'] ) ) {
        $json['message'] = __('Email is required.','cth-gather-plugins');
    }else {

        /* event reservation datas */
        $auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
        $pur_key = strtolower( md5( $res_post_datas['email'] . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'gatherevent', true ) ) );  // Unique key

        $res_datas = array();
        if(isset($res_post_datas['first_name'])|| isset($res_post_datas['last_name'])){
            $res_datas['post_title'] = $res_post_datas['first_name'] .' '.$res_post_datas['last_name'];
        }else{
            $res_datas['post_title'] = $res_post_datas['email'];
        }
        
        //$res_datas['post_name'] = '';
        $res_datas['post_content'] = '';
        //$res_datas['post_author'] = '0';// default 0 for no author assigned
        $res_datas['post_status'] = 'publish';
        $res_datas['post_type'] = 'cth_eventres';

        $res_meta_datas = array();
        $res_meta_datas['pur_pass']     = trim($_POST['os0']);
        $res_meta_datas['pur_price']     = $res_post_datas['price'];
        $res_meta_datas['pur_quantity']     = $res_post_datas['quantity'];
        $res_meta_datas['pur_date']     = date( 'Y-m-d H:i:s', current_time( 'timestamp' ) );
        $res_meta_datas['pur_currency']     = get_option('eventres_currency' );

        $res_meta_datas['pur_key'] = $pur_key;
        $res_meta_datas['pur_custom'] = uniqid();
        $res_meta_datas['pur_gateway']     = 'Email Registration';
        $res_meta_datas['pur_status']     = 'Pending';

        
        $res_meta_datas['pur_fname'] = $res_post_datas['first_name'];
        $res_meta_datas['pur_lname'] = $res_post_datas['last_name'];
        $res_meta_datas['pur_email'] = $res_post_datas['email'];

        $res_id = wp_insert_post($res_datas ,true );
                    

        if (is_wp_error($res_id)) {
            $json['message'] = $res_id->get_error_message();
        }else{
            foreach ($res_meta_datas as $key => $value) {
                if ( !add_post_meta( $res_id, $key, $value , true ) ) {
                    $json['message'] = sprintf(__('Insert reservation %s data failure','cth-gather-plugins'),$key);
                    wp_send_json($json );
                }
            }


            /* email template variable */
            $first_name             = $res_post_datas['first_name'];
            $last_name              = $res_post_datas['last_name'];
            $payer_email            = $res_post_datas['email'];
            $receiver_email         = get_option('eventres_paypal_email');
            $item_name              = get_option('eventres_item_name' );
            $item_number            = $res_meta_datas['pur_custom'];
            $quantity               = $res_post_datas['quantity'];
            $payment_currency       = get_option('eventres_currency');
            $payment_amount         = (float)$res_post_datas['price'] * (int)$quantity;
            $event_pass             = $res_meta_datas['pur_pass'];
            $date                   = $res_meta_datas['pur_date'];
            $sitename               = get_option( 'blogname' );

            $sender_option = get_option('eventres_email_sender','Gather Event' );
            $sender_email_option = get_option('eventres_email_sender_email','contact.cththemes@gmail.com' );

            // to buyer
            if(get_option('eventres_email_res_to_buyer_email','yes' ) === 'yes') : 
                $eventres_email_res_to_buyer_email_subject = get_option('eventres_email_res_to_buyer_email_subject','Your Seat is reserved for the event'); 
                $eventres_email_res_to_buyer_email_template = get_option('eventres_email_res_to_buyer_email_template'); 

                if(preg_match_all("/{([\w_]+)[^\w_]*}/", $eventres_email_res_to_buyer_email_template, $matches)!= FALSE){
                    $fieldsPattern = array();//$matches[0];
                    $fieldsReplace = array();
                    foreach ($matches[1] as $key => $fn) {
                        $fieldsPattern[] = "/{(".$fn.")[^\w_]*}/";

                        if( isset( $$fn )&&in_array( $fn, array('first_name','last_name','payer_email','receiver_email','item_name','item_number','quantity','payment_currency','payment_amount','event_pass','date','sitename','paypal_link','payment_id') ) ){
                            $fieldsReplace[] = $$fn;  //'['.$fn.']';
                        }else{
                            $fieldsReplace[] = '{'.$fn.'}';
                        }
                        
                    }


                    $eventres_email_res_to_buyer_email_template = preg_replace($fieldsPattern, $fieldsReplace, $eventres_email_res_to_buyer_email_template);
                }


                $headers = array();
                if(get_option('eventres_email_content_type','yes' ) == 'yes'){
                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                }
                
                //$headers[] = 'From: '. $sender_option.' ' . '<'.$sender_email_option.'>';
                add_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
                $headers[] = 'Reply-To: '. $sender_option.' ' . '<'.$sender_email_option.'>';

                if (!wp_mail( $payer_email, $eventres_email_res_to_buyer_email_subject , $eventres_email_res_to_buyer_email_template, $headers)) {
                    $json['success']  = 0;  
                    $json['message']  = __('Email send failed','cth-gather-plugins');
                }else{
                    $json['success']  = 1;  
                    $json['message']  = __('Email sent','cth-gather-plugins');
                }
                
                remove_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );

            endif;

            //to admin
            if(get_option('eventres_email_res_to_admin_email','yes' )=== 'yes') :
                $eventres_email_res_to_admin_email_subject = get_option('eventres_email_res_to_admin_email_subject','New Event Registration: $first_name $last_name'); 
                $eventres_email_res_to_admin_email_template = get_option('eventres_email_res_to_admin_email_template'); 
                $email_res_to_admin_email_addresses = get_option('eventres_email_res_to_admin_email_addresses'); 
                if(preg_match_all("/{([\w_]+)[^\w_]*}/", $eventres_email_res_to_admin_email_template, $matches)!= FALSE){
                    $fieldsPattern = array();//$matches[0];
                    $fieldsReplace = array();
                    foreach ($matches[1] as $key => $fn) {
                        $fieldsPattern[] = "/{(".$fn.")[^\w_]*}/";

                        if( isset( $$fn )&&in_array( $fn, array('first_name','last_name','payer_email','receiver_email','item_name','item_number','quantity','payment_currency','payment_amount','event_pass','date','sitename','paypal_link','payment_id') ) ){
                            $fieldsReplace[] = $$fn;  //'['.$fn.']';
                        }else{
                            $fieldsReplace[] = '{'.$fn.'}';
                        }
                        
                    }


                    $eventres_email_res_to_admin_email_template = preg_replace($fieldsPattern, $fieldsReplace, $eventres_email_res_to_admin_email_template);
                }


                $headers = array();
                if(get_option('eventres_email_content_type','yes' ) == 'yes'){
                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                }
                
                //$headers[] = 'From: '. $sender_option.' ' . '<'.$sender_email_option.'>';
                add_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
                $headers[] = 'Reply-To: '. $sender_option.' ' . '<'.$sender_email_option.'>';
                
                $email_res_to_admin_email_addresses = preg_split( '/\r\n|\r|\n/', trim($email_res_to_admin_email_addresses) );

                if (!wp_mail( array_filter($email_res_to_admin_email_addresses ,'trim'), $eventres_email_res_to_admin_email_subject , $eventres_email_res_to_admin_email_template, $headers) ) {
                    $json['success']  = 0;  
                    $json['message']  = __('Email send failed','cth-gather-plugins');
                }else{
                    $json['success']  = 1;  
                    $json['message']  = __('Email sent','cth-gather-plugins');
                }
                
                remove_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );

            endif;

        }   
    }
    wp_send_json($json );
}

