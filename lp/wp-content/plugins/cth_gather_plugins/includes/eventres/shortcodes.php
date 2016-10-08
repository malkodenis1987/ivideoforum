<?php
if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}
if(!function_exists('eventres_confirm_sc')) {

	function eventres_confirm_sc( $atts, $content="" ) {

		/* CONFIGURATION HELP : You can Change :
   
		     ADMIN EMAIL
		     MAIL CONTENTS
		     SANDBOX MODE 
		     DEBUG MODE
		*/

		$eventres_test_mode = get_option('eventres_test_mode' );


		// CONFIG: Enable debug mode. This means we'll log requests into 'ipn.log' in the same directory.
		// Especially useful if you encounter network errors or other intermittent problems with IPN (validation).
		// Set this to 0 once you go live or don't require logging.
		define("DEBUG", $eventres_test_mode);

		// Set to 0 once you're ready to go live

		define("USE_SANDBOX", $eventres_test_mode);


		define("LOG_FILE", "./ipn.log");


		// Read POST data
		// reading posted data directly from $_POST causes serialization
		// issues with array data in POST. Reading raw POST data from input stream instead.
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);

		$myPost = array();
		foreach ($raw_post_array as $keyval) {
			$keyval = explode ('=', $keyval);
			if (count($keyval) == 2)
				$myPost[$keyval[0]] = urldecode($keyval[1]);
		}
		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc')) {
			$get_magic_quotes_exists = true;
		}
		foreach ($myPost as $key => $value) {
			if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
				$value = urlencode(stripslashes($value));
			} else {
				$value = urlencode($value);
			}
			$req .= "&$key=$value";
		}

		// Post IPN data back to PayPal to validate the IPN data is genuine
		// Without this step anyone can fake IPN data

		if(USE_SANDBOX == true) {
			$paypal_url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
		} else {
			$paypal_url = "https://www.paypal.com/cgi-bin/webscr";
		}

		$ch = curl_init($paypal_url);
		if ($ch == FALSE) {
			return FALSE;
		}

		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);

		if(DEBUG == true) {
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
		}

		// CONFIG: Optional proxy configuration
		//curl_setopt($ch, CURLOPT_PROXY, $proxy);
		//curl_setopt($ch, CURLOPT_HTTPPROXYTUNNEL, 1);

		// Set TCP timeout to 30 seconds
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));

		// CONFIG: Please download 'cacert.pem' from "http://curl.haxx.se/docs/caextract.html" and set the directory path
		// of the certificate as shown below. Ensure the file is readable by the webserver.
		// This is mandatory for some environments.

		//$cert = __DIR__ . "./cacert.pem";
		//curl_setopt($ch, CURLOPT_CAINFO, $cert);

		$res = curl_exec($ch);
		if (curl_errno($ch) != 0) // cURL error
			{
			if(DEBUG == true) {	
				error_log(date('[Y-m-d H:i e] '). "Can't connect to PayPal to validate IPN message: " . curl_error($ch) . PHP_EOL, 3, LOG_FILE);
			}
			curl_close($ch);
			exit;

		} else {
				// Log the entire HTTP response if debug is switched on.
				if(DEBUG == true) {
					error_log(date('[Y-m-d H:i e] '). "HTTP request of validation request:". curl_getinfo($ch, CURLINFO_HEADER_OUT) ." for IPN payload: $req" . PHP_EOL, 3, LOG_FILE);
					error_log(date('[Y-m-d H:i e] '). "HTTP response of validation request: $res" . PHP_EOL, 3, LOG_FILE);
				}
				curl_close($ch);

		}

		// Inspect IPN validation result and act accordingly
		// Split response headers and payload, a better way for strcmp
		$tokens = explode("\r\n\r\n", trim($res));
		$res = trim(end($tokens));

		

		if (strcmp ($res, "VERIFIED") === 0) {
			// check whether the payment_status is Completed
			// check that txn_id has not been previously processed
			// check that receiver_email is your PayPal email
			// check that payment_amount/payment_currency are correct
			// process payment and mark item as paid.

			// assign posted variables to local variables
			$first_name 		= $_POST['first_name'];
			$last_name 			= $_POST['last_name'];
			//$last_name 		= $_POST['last_name'];

			$item_name 			= $_POST['item_name'];
			$item_number 		= $_POST['item_number'];
			$payment_status 	= $_POST['payment_status'];
			$payment_amount 	= $_POST['mc_gross'];
			$payment_currency 	= $_POST['mc_currency'];
			$txn_id 			= $_POST['txn_id'];
			$receiver_email 	= $_POST['receiver_email'];
			$payer_email 		= $_POST['payer_email'];
			$event_pass 		= $_POST['option_selection1'];
			$quantity 			= $_POST['quantity'];
			$payment_id 		= $_POST['custom'];//reservation id
			$date 				= $_POST['payment_date'];
			$sitename 			= get_option( 'blogname' );

			//IPN checkes:
			//
			//1. Verify that you are the intended recipient of the IPN message.
			if($receiver_email !== get_option('eventres_paypal_email' )){
				//return __('You pays for wrong people.','cth-gather-plugins');
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "You pays for wrong people.". PHP_EOL, 3, LOG_FILE);
				}
			}

			//2. Verify that the IPN is not a duplicate. 
			if(get_post_meta( $payment_id, 'pur_txn_id', true ) == FALSE){
				//if transaction id is not in reservation meta data
				if(!add_post_meta( $payment_id, 'pur_txn_id', $txn_id, true )){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not add Transaction ID to reservation meta data.". PHP_EOL, 3, LOG_FILE);
					}
					//return __('Can not add Transaction ID to reservation meta data.','cth-gather-plugins');
				}
				if(!add_post_meta( $payment_id, 'pur_payment_status', $payment_status, true )){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not add Payment Status to reservation meta data.". PHP_EOL, 3, LOG_FILE);
					}
					//return __('Can not add Payment Status to reservation meta data.','cth-gather-plugins');
				}
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Transaction ID and Payment Status was added to the reservation post". PHP_EOL, 3, LOG_FILE);
				}
			}else{
				if(get_post_meta($payment_id, 'pur_txn_id',true ) != $txn_id ){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "The IPN is a duplicate. Transaction ID not match.". PHP_EOL, 3, LOG_FILE);
					}
					//return __('The IPN is a duplicate. Transaction ID not match.','cth-gather-plugins');
				}
				if(get_post_meta($payment_id, 'pur_payment_status',true ) == $payment_status ){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "The IPN is a duplicate. Payment Status is duplicate.". PHP_EOL, 3, LOG_FILE);
					}
					//return __('The IPN is a duplicate. Payment Status is duplicate.','cth-gather-plugins');
				}
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Transaction ID match and Payment Status changed.". PHP_EOL, 3, LOG_FILE);
				}
			}

			//3. Ensure that you receive an IPN whose payment status is "completed" before shipping merchandise or enabling download of digital goods.

			//4. Verify that the payment amount in an IPN matches the price you intend to charge. 
			if($payment_status === 'Pending'){
				//The payment is pending. See pending_reason for more information.
				if(!update_post_meta( $payment_id, 'pur_status', 'Pending')){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not update pur_status to Pending.". PHP_EOL, 3, LOG_FILE);
					}
				}
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Payment Status is Pending.". PHP_EOL, 3, LOG_FILE);
				}
			}else if($payment_status === 'Completed'){
				//The payment has been completed, and the funds have been added successfully to your account balance.
				if(!update_post_meta( $payment_id, 'pur_status', 'Completed')){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not update pur_status to Completed.". PHP_EOL, 3, LOG_FILE);
					}
				}
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Payment Status is Completed.". PHP_EOL, 3, LOG_FILE);
				}

				$sender_option = get_option('eventres_email_sender','Gather Event' );
            	$sender_email_option = get_option('eventres_email_sender_email','contact.cththemes@gmail.com' );

            	//$eventres_confirmed_to_buyer_email = get_option('eventres_confirmed_to_buyer_email','yes');
            	if(get_option('eventres_confirmed_to_buyer_email','yes') === 'yes') :
	            	// to buyer
	            	if(get_post_meta( $payment_id, 'pur_email', true ) == FALSE){
	            		$buyer_mail_to = $payer_email;
	            	}else{
	            		$buyer_mail_to = get_post_meta( $payment_id, 'pur_email', true );
	            	}
	            	$buyer_mail_subject = get_option('eventres_confirmed_to_buyer_email_subject','Your Seat is reserved for the event'); 
					$to_buyer_email_template = get_option('eventres_confirmed_to_buyer_email_template'); 

	                if(preg_match_all("/{([\w-_]+)[^\w-_]*}/", $to_buyer_email_template, $matches)!= FALSE){
	                    $fieldsPattern = array();//$matches[0];
	                    $fieldsReplace = array();
	                    foreach ($matches[1] as $key => $fn) {
	                        $fieldsPattern[] = "/{(".$fn.")[^\w-_]*}/";

	                        if( isset($$fn) && in_array($fn, array('first_name','last_name','payer_email','receiver_email','item_name','item_number','quantity','payment_currency','payment_amount','event_pass','date','sitename','txn_id','payment_id') ) ){
	                        	$fieldsReplace[] = $$fn;  //'['.$fn.']';
	                        }else{
	                        	$fieldsReplace[] = '{'.$fn.'}';
	                        }

	                        
	                    }


	                    $to_buyer_email_template = preg_replace($fieldsPattern, $fieldsReplace, $to_buyer_email_template);
	                }


	                $headers = array();
	                if(get_option('eventres_email_content_type','yes' ) == 'yes'){
	                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
	                }
	                //$headers[] = 'From: '. $sender_option.' ' . '<'.$sender_email_option.'>';
	                add_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
	                $headers[] = 'Reply-To: '. $sender_option.' ' . '<'.$sender_email_option.'>';

	                //$e_return = wp_mail( $mail_to, $mail_subject , $message, $headers);
	                wp_mail( $buyer_mail_to, $buyer_mail_subject , $to_buyer_email_template, $headers);
	              	
	              	remove_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );

	            endif;

                // to admin
            	if(get_option('eventres_confirmed_to_admin_email','yes') === 'yes') :
	            	//$admin_mail_subject = get_option('eventres_confirmed_to_admin_email_subject','New registration of Gather Event'); 
	            	$admin_mail_subject = get_option('eventres_confirmed_to_admin_email_subject','New registration of Gather Event'); 
					$to_admin_email_template = get_option('eventres_confirmed_to_admin_email_template'); 
					$to_admin_email_addresses = get_option('eventres_confirmed_to_admin_email_addresses'); 
					

	                if(preg_match_all("/{([\w-_]+)[^\w-_]*}/", $to_admin_email_template, $matches)!= FALSE){
	                    $fieldsPattern = array();//$matches[0];
	                    $fieldsReplace = array();
	                    foreach ($matches[1] as $key => $fn) {
	                        $fieldsPattern[] = "/{(".$fn.")[^\w-_]*}/";

	                        if( isset($$fn) && in_array($fn, array('first_name','last_name','payer_email','receiver_email','item_name','item_number','quantity','payment_currency','payment_amount','event_pass','date','sitename','txn_id','payment_id') ) ){
	                        	$fieldsReplace[] = $$fn;  //'['.$fn.']';
	                        }else{
	                        	$fieldsReplace[] = '{'.$fn.'}';
	                        }

	                        
	                    }


	                    $to_admin_email_template = preg_replace($fieldsPattern, $fieldsReplace, $to_admin_email_template);
	                }


	                $headers = array();
	                if(get_option('eventres_email_content_type','yes' ) == 'yes'){
	                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
	                }
	                add_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
	                

	                $to_admin_email_addresses = preg_split( '/\r\n|\r|\n/', trim($to_admin_email_addresses) );
	                wp_mail( array_filter($to_admin_email_addresses,'trim'), $admin_mail_subject , $to_admin_email_template, $headers);
	                
	                remove_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
	            

	            endif;

				if(DEBUG == true) {
					error_log(date('[Y-m-d H:i e] '). "Verified IPN: $req ". PHP_EOL, 3, LOG_FILE);
				}
			}else if($payment_status ==='Failed'){
				//The payment has failed. This happens only if the payment was made from your customer's bank account.
				if(!update_post_meta( $payment_id, 'pur_status', 'Failed')){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not update pur_status to Failed.". PHP_EOL, 3, LOG_FILE);
					}
				}
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Payment Status is Failed.". PHP_EOL, 3, LOG_FILE);
				}
			}else if($payment_status === 'Processed'){
				//A payment has been accepted.
				if(!update_post_meta( $payment_id, 'pur_status', 'Processed')){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not update pur_status to Processed.". PHP_EOL, 3, LOG_FILE);
					}
				}
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Payment Status is Processed.". PHP_EOL, 3, LOG_FILE);
				}
			}else{
				if(!update_post_meta( $payment_id, 'pur_status', $payment_status)){
					if(DEBUG == true) {	
						error_log(date('[Y-m-d H:i e] '). "Can not update pur_status to ".$payment_status. PHP_EOL, 3, LOG_FILE);
					}
				}
			}
				
			 
		
		} else if (strcmp ($res, "INVALID") === 0) {
			// log for manual investigation
			// Add business logic here which deals with invalid IPN messages

			// assign posted variables to local variables
			$first_name 		= $_POST['first_name'];
			$last_name 			= $_POST['last_name'];
			//$last_name 		= $_POST['last_name'];

			$item_name 			= $_POST['item_name'];
			$item_number 		= $_POST['item_number'];
			$payment_status 	= $_POST['payment_status'];
			$payment_amount 	= $_POST['mc_gross'];
			$payment_currency 	= $_POST['mc_currency'];
			$txn_id 			= $_POST['txn_id'];
			$receiver_email 	= $_POST['receiver_email'];
			$payer_email 		= $_POST['payer_email'];
			$event_pass 		= $_POST['option_selection1'];
			$quantity 			= $_POST['quantity'];
			$payment_id 		= $_POST['custom'];//reservation id
			$date 				= $_POST['payment_date'];
			$sitename 			= get_option( 'blogname' );


			
			if(!update_post_meta( $payment_id, 'pur_status', 'Failed')){
				if(DEBUG == true) {	
					error_log(date('[Y-m-d H:i e] '). "Can not update pur_status to Failed.". PHP_EOL, 3, LOG_FILE);
				}
			}


			if(get_option('eventres_failed_to_admin_email','yes') === 'yes') :
				// PAYMENT INVALID & INVESTIGATE MANUALY!

				$sender_option = get_option('eventres_email_sender','Gather Event' );
	            $sender_email_option = get_option('eventres_email_sender_email','contact.cththemes@gmail.com' );
	            // to admin
            	
            	
            	$admin_failed_mail_subject = get_option('eventres_failed_to_admin_email_subject','Event Registration failed because of Invalid Payment'); 
				$to_admin_failed_email_template = get_option('eventres_failed_to_admin_email_template'); 
				$to_admin_email_addresses = get_option('eventres_failed_to_admin_email_addresses'); 

                if(preg_match_all("/{([\w-_]+)[^\w-_]*}/", $to_admin_failed_email_template, $matches)!= FALSE){
                    $fieldsPattern = array();//$matches[0];
                    $fieldsReplace = array();
                    foreach ($matches[1] as $key => $fn) {
                        $fieldsPattern[] = "/{(".$fn.")[^\w-_]*}/";

                        if( isset($$fn) && in_array($fn, array('first_name','last_name','payer_email','receiver_email','item_name','item_number','quantity','payment_currency','payment_amount','event_pass','date','sitename','txn_id','payment_id') ) ){
                        	$fieldsReplace[] = $$fn;  //'['.$fn.']';
                        }else{
                        	$fieldsReplace[] = '{'.$fn.'}';
                        }
                        
                    }


                    $to_admin_failed_email_template = preg_replace($fieldsPattern, $fieldsReplace, $to_admin_failed_email_template);
                }


                $headers = array();
                if(get_option('eventres_email_content_type','yes' ) == 'yes'){
                    $headers[] = 'Content-Type: text/html; charset=UTF-8';
                }
                
                add_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
                

                
                $to_admin_email_addresses = preg_split( '/\r\n|\r|\n/', trim($to_admin_email_addresses) );
                wp_mail( array_filter($to_admin_email_addresses,'trim'), $admin_failed_mail_subject , $to_admin_failed_email_template, $headers);
                
                remove_filter( 'wp_mail_from_name', 'cth_gather_plugins_custom_wp_mail_from_name' );
		 	
		 	endif;
			
		 
			if(DEBUG == true) {
				error_log(date('[Y-m-d H:i e] '). "Invalid IPN: $req" . PHP_EOL, 3, LOG_FILE);
			}
		}

		return '';
	 
	}
		
	add_shortcode( 'eventres_confirm', 'eventres_confirm_sc' ); //Icon
}

if(!function_exists('eventres_cancelled_sc')) {

	function eventres_cancelled_sc( $atts, $content="" ) {

		return '';
	}
		
	add_shortcode( 'eventres_cancelled', 'eventres_cancelled_sc' ); //Icon
}