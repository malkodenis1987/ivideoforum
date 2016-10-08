<?php 
if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}
$eventres_options = array( 
        array(
            "name" => "General Settings",
            "type" => "sub-section-3",
            //"category" => "header-styles",
            "setting_tab" => 'general'
        ),
        array(
        	"name" => "Test Mode",
	        "desc"=>"While in test mode no live transactions are processed. To fully use test mode, you must have a sandbox (test) account for the payment gateway you are testing.",
	        "id" => "eventres_test_mode",
	        "type" => "checkbox",
	        "parent" => "paypal_settings",
	        "value"=>'1',//for checkbox only
	        "std" => "", //for checkbox only
	        "setting_tab" => 'general'
        	
        ),
        array(
	        "name" => "Paypal email",
	        "desc" => "Enter your PayPal account's email",
	        "id" => "eventres_paypal_email",
	        "type" => "text",
	        "parent" => "paypal_settings",
	        "std" => "",
	        "style"=>'width:50%;',
	        "setting_tab" => 'general'
	    ),
	    array(
	    	"name" => "Currency",
            "desc" => "Choose your currency. Note that some payment gateways have currency restrictions.",
            "id" => "eventres_currency",
            "type" => "select",
            
            "options" => 	array(
            					"USD" => "US Dollars ($)", 
            					"EUR" => "Euros (€)",
            					"GBP" => "Pounds Sterling (£)",
            					"AUD" => "Australian Dollars ($)",
            					"BRL" => "Brazilian Real (R$)",
            					"CAD" => "Canadian Dollars ($)",
            					"CZK" => "Czech Koruna",
            					"DKK" => "Danish Krone",
            					"HKD" => "Hong Kong Dollar ($)",
            					"HUF" => "Hungarian Forint",
            					"ILS" => "Israeli Shekel (₪)",
            					"JPY" => "Japanese Yen (¥)",
            					"MYR" => "Malaysian Ringgits",
            					"MXN" => "Mexican Peso ($)",
            					"NZD" => "New Zealand Dollar ($)",
            					"NOK" => "Norwegian Krone",
            					"PHP" => "Philippine Pesos",
            					"PLN" => "Polish Zloty",
            					"SGD" => "Singapore Dollar ($)",
            					"SEK" => "Swedish Krona",
            					"CHF" => "Swiss Franc",
            					"TWD" => "Taiwan New Dollars",
            					"THB" => "Thai Baht (฿)",
            					"INR" => "Indian Rupee (₹)",
            					"TRY" => "Turkish Lira (₺)",
            					"RIAL" => "Iranian Rial (﷼)",
            					"RUB" => "Russian Rubles",

            				),
            "parent" => "paypal_settings",
            "std" => 'USD',
            "setting_tab" => 'general'
    	),
		
		array(
            "name" => "Item Information",
            "type" => "sub-section-3",
            //"category" => "header-styles",
            "setting_tab" => 'resform'
        ),

        array(
	        "name" => "Item Name",
	        "desc" => "<a href=\"".CTH_EVENTRES_DIR_URL.'assets/img/item_name.png'."\" target=\"_blank\"><img src=\"".CTH_EVENTRES_DIR_URL.'assets/img/item_name.png'."\" style=\"max-width:100%;height:auto;\"></a>",
	        "id" => "eventres_item_name",
	        "type" => "text",
	        "parent" => "item_infos",
	        "std" => "Gather Event Pass",
	        "style"=>'width:50%;',
            "required"=>true,
            "setting_tab" => 'resform'
	    ),
    	array(
	        "name" => "Option Prices Label",
	        "desc" => "",
	        "id" => "eventres_item_prices_name",
	        "type" => "text",
	        "parent" => "item_infos",
	        "std" => "Pass",
	        "style"=>'width:50%;',
	        "setting_tab" => 'resform'
	    ),
    	array(
	    	"name" => "Option Prices",
            "desc" => "You can specify a maximum of 10 option selections",
            "id" => "eventres_item_prices",
            "type" => "repeatable",
            
            "fields" => 	array(
            					"text" => "option", 
            					"text" => "name",

            				),
            "parent" => "item_infos",
            "std" => 'yes',
            "setting_tab" => 'resform'
    	),
    	array(
	        "name" => "Quantity Label",
	        "desc" => "",
	        "id" => "eventres_item_quantity_name",
	        "type" => "text",
	        "parent" => "item_infos",
	        "std" => "No. of Seats",
	        "style"=>'width:50%;',
	        "setting_tab" => 'resform'
	    ),

        array(
            "name" => "Item Quantity",
            "desc" => "",
            "id" => "eventres_item_quantity",
            "type" => "repeatable",
            
            "fields" =>     array(
                                "text" => "option", 
                                "text" => "name",

                            ),
            "parent" => "item_infos",
            "std" => 'yes',
            "setting_tab" => 'resform'
        ),

        array(
	        "name" => "Terms Content",
	        "desc" => "",
	        "id" => "eventres_terms_content",
	        "type" => "textarea",
	        "rows"=>'3',
	        "cols"=>'100',
	        "parent" => "item_infos",
	        "std" => ' I agree to the <a href="#">Terms</a> and <a href="#">Privacy Policy</a>',
	        "style"=>'width:100%;',
	        "setting_tab" => 'resform'
	    ),
	    array(
	    	"name" => "Hide Agree Terms",
            "desc" => "",
            "id" => "eventres_hide_terms",
            "type" => "select",
            
            "options" => 	array(
            					"no" => "No", 
            					"yes" => "Yes",

            				),
            "parent" => "item_infos",
            "std" => 'no',
            "setting_tab" => 'resform'
    	),

	    

        array(
	        "name" => "Confirm Page",
	        "desc" => "This page will be used to complete the purchases. The <code>[eventres_confirm]</code> short code should be on this page.",
	        "id" => "eventres_success_page",
	        "type" => "page_select",
	        "parent" => "item_infos",
	        "std" => "cth_eventres_confirm_page_id",
	        "default_title" => "Gather Purchase Confirm",
	        "setting_tab" => 'paypalemail'

	        //"style"=>'width:50%;'
	    ),
	    array(
	        "name" => "Return Page",
	        "desc" => "This is the page buyers are sent to after completing their purchases.",
	        "id" => "eventres_return_page",
	        "type" => "page_select",
	        "parent" => "item_infos",
	        "std" => "cth_eventres_return_page_id",
	        "default_title" => "Gather Return Confirm",
	        "setting_tab" => 'paypalemail'

	        //"style"=>'width:50%;'
	    ),
	    array(
	        "name" => "Cancelled Page",
	        "desc" => "This is the page buyers are sent to if their transaction is cancelled or fails. The <code>[eventres_cancelled]</code> short code should be on this page.",
	        "id" => "eventres_cancelled_page",
	        "type" => "page_select",
	        "parent" => "item_infos",
	        "std" => "cth_eventres_cancelled_page_id",
	        "default_title" => "Gather Purchase Cancelled",
	        //"style"=>'width:50%;'
	        "setting_tab" => 'paypalemail'
	    ),

	        array(
	        "name" => "Email Settings",
	        "type" => "sub-section-3",
	        //"category" => "header-styles",
	        "setting_tab" => 'paypalemail'
	    ),
	    array(
	        "name" => "Sender Name",
	        "desc" => "The name purchase receipts are said to come from. This should probably be your site or shop name.",
	        "id" => "eventres_email_sender",
	        "type" => "text",
	        "parent" => "email_setup",
	        "std" => "Gather Event",
	        "style"=>"width:50%;",
	        "setting_tab" => 'paypalemail'
	    ),
	    array(
	        "name" => "Sender Email",
	        "desc" => "Email to send purchase receipts from. This will act as the \"from\" and \"reply-to\" address.",
	        "id" => "eventres_email_sender_email",
	        "type" => "text",
	        "parent" => "email_setup",
	        "std" => "contact.cththemes@gmail.com",
	        "style"=>"width:50%;",
	        "setting_tab" => 'paypalemail'
	    ),
	    array("name" => "Email Template",
	            "desc" => "Choose a template",
	            "id" => "eventres_email_content_type",
	            "type" => "select",
	            //"options" => array("yes" => __('Yes','cth-reservation'), "no" => __('No','cth-reservation')),
	            "options" => array("yes" => "HTML Template", "no" => "Plain Text only"),
	            "parent" => "email_setup",
	            "std" => 'yes',
	            "setting_tab" => 'paypalemail'
	    ),
    array(
        "name" => "Purchase Success Email Settings",
        "type" => "sub-section-3",
        //"category" => "header-styles",
        "setting_tab" => 'paypalemail'
    ),
    array("name" => "Send Purchase Receipt email to buyer",
            "desc" => "",
            "id" => "eventres_confirmed_to_buyer_email",
            "type" => "select",
            //"options" => array("yes" => __('Yes','cth-reservation'), "no" => __('No','cth-reservation')),
            "options" => array("yes" => "Yes", "no" => "No"),
            "parent" => "comfrimed_email_setup",
            "std" => 'yes',
            "setting_tab" => 'paypalemail'
    ),
    
    array(
        "name" => "Purchase Email Subject",
        "desc" => "Enter the subject line for the purchase receipt email",
        "id" => "eventres_confirmed_to_buyer_email_subject",
        "type" => "text",
        "parent" => "comfrimed_email_setup",
        "std" => "Your Seat is reserved for the event",
        "style"=>"width:100%;",
        "setting_tab" => 'paypalemail'
    ),

    array(
        "name" => "Purchase Receipt",
        "desc" => "",
        "add_desc" => "	Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:<br>
        				{first_name} - The buyer's first name<br>
						{last_name} - The buyer's last name<br>
						{payer_email} - The buyer's paypal email address<br>
						{receiver_email} - The receive's paypal email address<br>
						{item_name} - Item Name<br>
						{item_number} - A unique ID number for the item<br>
						{payment_id} - The unique ID number for this purchase<br>
						{quantity} - The quantity<br>
        				{payment_amount} - The total price of the purchase<br>
        				{payment_currency} - The currency<br>
        				{event_pass} - The selected option's name<br>
        				{date} - The date of the purchase<br>
						{txn_id} - Transaction ID<br>
						{sitename} - Your site name<br>",
        "id" => "eventres_confirmed_to_buyer_email_template",
        "type" => "editor",
        "cols"=> "100", //textarea only
        "rows"=> "15", //textarea only
        "parent" => "complete_email_setup",
        "std" => '<p style="text-align: left;">Hello,</p>
<p style="text-align: left;">Thank you so much for your registration. we look forward to see you there.</p>
<p style="text-align: left;"><em>Your Event Pass information</em></p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;"><strong>Email:</strong> {payer_email}</p>
<p style="text-align: left;"><strong>Pass:</strong> {event_pass}</p>
<p style="text-align: left;"><strong>Amount:</strong> {payment_amount}{payment_currency}</p>
<p style="text-align: left;"><strong>Seats reserved:</strong> {quantity}</p>
<p style="text-align: left;"><strong>PayPal Transaction ID</strong> : {txn_id}</p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;"><strong>Venue Location :</strong> https://goo.gl/maps/cSuf7</p>',
		"setting_tab" => 'paypalemail'
    ),


    array(
        "name" => "New Sale Notifications",
        "type" => "sub-section-3",
        //"category" => "header-styles",
        "setting_tab" => 'paypalemail'
    ),
    array("name" => "Send New Sale Notification to admin emails",
            "desc" => "",
            "id" => "eventres_confirmed_to_admin_email",
            "type" => "select",
            //"options" => array("yes" => __('Yes','cth-reservation'), "no" => __('No','cth-reservation')),
            "options" => array("yes" => "Yes", "no" => "No"),
            "parent" => "comfrimed_email_setup",
            "std" => 'yes',
            "setting_tab" => 'paypalemail'
    ),
    
    array(
        "name" => "Sale Notification Subject",
        "desc" => "Enter the subject line for the sale notification email",
        "id" => "eventres_confirmed_to_admin_email_subject",
        "type" => "text",
        "parent" => "comfrimed_email_setup",
        "std" => "New registration of Gather Event",
        "style"=>"width:100%;",
        "setting_tab" => 'paypalemail'
    ),

    array(
        "name" => "Sale Notification",
        "desc" => "",
        "add_desc" => "	Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:<br>
				{first_name} - The buyer's first name<br>
				{last_name} - The buyer's last name<br>
				{payer_email} - The buyer's paypal email address<br>
				{receiver_email} - The receive's paypal email address<br>
				{item_name} - Item Name<br>
				{item_number} - A unique ID number for the item<br>
				{payment_id} - The unique ID number for this purchase<br>
				{quantity} - The quantity<br>
				{payment_amount} - The total price of the purchase<br>
				{payment_currency} - The currency<br>
				{event_pass} - The selected option's name<br>
				{date} - The date of the purchase<br>
				{txn_id} - Transaction ID<br>
				{sitename} - Your site name<br>",
        "id" => "eventres_confirmed_to_admin_email_template",
        "type" => "editor",
        "cols"=> "100", //textarea only
        "rows"=> "15", //textarea only
        "parent" => "complete_email_setup",
        "std" => '<p style="text-align: left;">Hello,</p>
<p style="text-align: left;">There is a new registration for Gather Event</p>
<p style="text-align: left;">Attendee information</p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;"><strong>Email:</strong> {payer_email}</p>
<p style="text-align: left;"><strong>Pass:</strong> {event_pass}</p>
<p style="text-align: left;"><strong>Amount:</strong> {payment_amount}{payment_currency}</p>
<p style="text-align: left;"><strong>Seats reserved:</strong> {quantity}</p>
<p style="text-align: left;"><strong>PayPal Transaction ID:</strong> {txn_id}</p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;">(this is an automated message)</p>',
		"setting_tab" => 'paypalemail'
    ),
	
	array(
        "name" => "Sale Notification Emails",
        "desc" => "Enter the email address(es) that should receive a notification anytime a sale is made, one per line",
        "id" => "eventres_confirmed_to_admin_email_addresses",
        "type" => "textarea",
        "cols"=> "100", //textarea only
        "rows"=> "5", //textarea only
        "parent" => "complete_email_setup",
        "std" => 'contact.cththemes@gmail.com',
        'style'=>'width:100%;',
        "setting_tab" => 'paypalemail'
    ),

	array(
        "name" => "New Registration Failed Notifications",
        "type" => "sub-section-3",
        //"category" => "header-styles",
        "setting_tab" => 'paypalemail'
    ),
    array("name" => "Send New Registration Failed Notification to admin emails",
            "desc" => "",
            "id" => "eventres_failed_to_admin_email",
            "type" => "select",
            //"options" => array("yes" => __('Yes','cth-reservation'), "no" => __('No','cth-reservation')),
            "options" => array("yes" => "Yes", "no" => "No"),
            "parent" => "comfrimed_email_setup",
            "std" => 'yes',
            "setting_tab" => 'paypalemail'
    ),
    
    array(
        "name" => "New Registration Failed Notification Subject",
        "desc" => "",
        "id" => "eventres_failed_to_admin_email_subject",
        "type" => "text",
        "parent" => "comfrimed_email_setup",
        "std" => "Event Registration failed because of Invalid Payment",
        "style"=>"width:100%;",
        "setting_tab" => 'paypalemail'
    ),

    array(
        "name" => "Failed Notification",
        "desc" => "",
        "add_desc" => "	Enter the email that is sent to users after completing a successful purchase. HTML is accepted. Available template tags:<br>
				{first_name} - The buyer's first name<br>
				{last_name} - The buyer's last name<br>
				{payer_email} - The buyer's paypal email address<br>
				{receiver_email} - The receive's paypal email address<br>
				{item_name} - Item Name<br>
				{item_number} - A unique ID number for the item<br>
				{payment_id} - The unique ID number for this purchase<br>
				{quantity} - The quantity<br>
				{payment_amount} - The total price of the purchase<br>
				{payment_currency} - The currency<br>
				{event_pass} - The selected option's name<br>
				{date} - The date of the purchase<br>
				{txn_id} - Transaction ID<br>
				{sitename} - Your site name<br>",
        "id" => "eventres_failed_to_admin_email_template",
        "type" => "editor",
        "cols"=> "100", //textarea only
        "rows"=> "15", //textarea only
        "parent" => "complete_email_setup",
        "std" => '<p style="text-align: left;">Dear Administrator,</p>
<p style="text-align: left;">A payment for event registration has been made but is flagged as INVALID.
Please verify the payment manualy and contact the buyer.</p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;"><strong>Email:</strong> {payer_email}</p>
<p style="text-align: left;"><strong>Pass:</strong> {event_pass}</p>
<p style="text-align: left;"><strong>Amount:</strong> {payment_amount}{payment_currency}</p>
<p style="text-align: left;"><strong>Seats reserved:</strong> {quantity}</p>
<p style="text-align: left;"><strong>PayPal Transaction ID:</strong> {txn_id}</p>
<p style="text-align: left;">-------------------------</p>',
		"setting_tab" => 'paypalemail'
	),

	array(
        "name" => "Failed Notification Emails",
        "desc" => "Enter the email address(es) that should receive a notification anytime a sale is falied, one per line",
        "id" => "eventres_failed_to_admin_email_addresses",
        "type" => "textarea",
        "cols"=> "100", //textarea only
        "rows"=> "5", //textarea only
        "parent" => "complete_email_setup",
        "std" => 'contact.cththemes@gmail.com',
        'style'=>'width:100%;',
        "setting_tab" => 'paypalemail'
    ),
	array(
        "name" => "Resend Paypal Link",
        "type" => "sub-section-3",
        //"category" => "header-styles",
        "setting_tab" => 'paypalemail'
    ),

	array(
        "name" => "Resend Email Subject",
        "desc" => "",
        "id" => "eventres_resend_paypal_link_email_subject",
        "type" => "text",
        "parent" => "resend_email_setup",
        "std" => "Your payment have never been completed",
        "style"=>"width:100%;",
        "setting_tab" => 'paypalemail'
    ),

	array(
        "name" => "Resend Email",
        "desc" => "",
        "add_desc" => "	Enter the email that is sent to user after you click to Resend Paypal Link button from backend. Available template tags:<br>
				{paypal_link} - The link to Paypal page<br>
				{first_name} - The buyer's first name<br>
				{last_name} - The buyer's last name<br>
				{payer_email} - The buyer's email address<br>
				{receiver_email} - The receive's paypal email address<br>
				{item_name} - Item Name<br>
				{item_number} - A unique ID number for the item<br>
				{payment_id} - The unique ID number for this purchase<br>
				{quantity} - The quantity<br>
				{payment_amount} - The total price of the purchase<br>
				{payment_currency} - The currency<br>
				{event_pass} - The selected option's name<br>
				{date} - The date of the purchase<br>
				{sitename} - Your site name<br>",
        "id" => "eventres_resend_paypal_link_email_template",
        "type" => "textarea",
        "cols"=> "100", //textarea only
        "rows"=> "15", //textarea only
        "parent" => "resend_email_setup",
        "std" => 'Hi {first_name} {last_name}
You have registered a pass on our Gather Event Conference, but it have never been completed.
-------------------------
Email: {payer_email}
Pass: {event_pass}
Amount: {payment_amount}{payment_currency}
Seats reserved: {quantity}
-------------------------
To complete the payment please copy link bellow to your browser, after purchase complete you will be back to our payment confirm page for some administrator action.
Paypal Link: {paypal_link}',	
	"setting_tab" => 'paypalemail'

	),

	
	array(
        "name" => "Email Registration Form Settings",
        "type" => "sub-section-3",
        //"category" => "header-styles",
        "setting_tab" => 'freeemail'
    ),
    array("name" => "Send New Registration Info to Buyer",
            "desc" => "",
            "id" => "eventres_email_res_to_buyer_email",
            "type" => "select",
            //"options" => array("yes" => __('Yes','cth-reservation'), "no" => __('No','cth-reservation')),
            "options" => array("yes" => "Yes", "no" => "No"),
            "parent" => "email_res_setup",
            "std" => 'yes',
            "setting_tab" => 'freeemail'
    ),
    
    array(
        "name" => "To Buyer Email Subject",
        "desc" => "Enter the subject line for the buyer email",
        "id" => "eventres_email_res_to_buyer_email_subject",
        "type" => "text",
        "parent" => "email_res_setup",
        "std" => "Your Seat is reserved for the event",
        "style"=>"width:100%;",
        "setting_tab" => 'freeemail'
    ),

    array(
        "name" => "Email",
        "desc" => "",
        "add_desc" => "	Enter the email that is sent to user after an email registration was made. HTML is accepted. Available template tags:<br>
        				{first_name} - The buyer's first name<br>
						{last_name} - The buyer's last name<br>
						{payer_email} - The buyer's paypal email address<br>
						{receiver_email} - The receive's paypal email address<br>
						{item_name} - Item Name<br>
						{item_number} - A unique ID number for the item<br>
						{payment_id} - The unique ID number for this purchase<br>
						{quantity} - The quantity<br>
        				{payment_amount} - The total price of the purchase<br>
        				{payment_currency} - The currency<br>
        				{event_pass} - The selected option's name<br>
        				{date} - The date of the purchase<br>
						{sitename} - Your site name<br>",
        "id" => "eventres_email_res_to_buyer_email_template",
        "type" => "editor",
        "cols"=> "100", //textarea only
        "rows"=> "15", //textarea only
        "parent" => "email_res_setup",
        "std" => '<p style="text-align: left;">Hello,</p>
<p style="text-align: left;">Thank you so much for your registration. we look forward to see you there.</p>
<p style="text-align: left;"><em>Your Event Pass information</em></p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;"><strong>Email:</strong> {payer_email}</p>
<p style="text-align: left;"><strong>Pass:</strong> {event_pass}</p>
<p style="text-align: left;"><strong>Amount:</strong> {payment_amount}{payment_currency}</p>
<p style="text-align: left;"><strong>Seats reserved:</strong> {quantity}</p>
<p style="text-align: left;">-------------------------</p>
<p style="text-align: left;"><strong>Venue Location :</strong> https://goo.gl/maps/cSuf7</p>',
	"setting_tab" => 'freeemail'
    ),

	array("name" => "Send New Registration to admin emails",
            "desc" => "",
            "id" => "eventres_email_res_to_admin_email",
            "type" => "select",
            //"options" => array("yes" => __('Yes','cth-reservation'), "no" => __('No','cth-reservation')),
            "options" => array("yes" => "Yes", "no" => "No"),
            "parent" => "email_res_setup",
            "std" => 'yes',
            "setting_tab" => 'freeemail'
    ),
    
    array(
        "name" => "New Registration Email Subject",
        "desc" => "",
        "id" => "eventres_email_res_to_admin_email_subject",
        "type" => "text",
        "parent" => "email_res_setup",
        "std" => "New Event Registration",
        "style"=>"width:100%;",
        "setting_tab" => 'freeemail'
    ),

    array(
        "name" => "Email Template",
        "desc" => "",
        "add_desc" => "	Enter the email that is sent to admins after an email registration was made. HTML is accepted. Available template tags:<br>
				{first_name} - The buyer's first name<br>
				{last_name} - The buyer's last name<br>
				{payer_email} - The buyer's paypal email address<br>
				{receiver_email} - The receive's paypal email address<br>
				{item_name} - Item Name<br>
				{item_number} - A unique ID number for the item<br>
				{payment_id} - The unique ID number for this purchase<br>
				{quantity} - The quantity<br>
				{payment_amount} - The total price of the purchase<br>
				{payment_currency} - The currency<br>
				{event_pass} - The selected option's name<br>
				{date} - The date of the purchase<br>
				{sitename} - Your site name<br>",
        "id" => "eventres_email_res_to_admin_email_template",
        "type" => "editor",
        "cols"=> "100", //textarea only
        "rows"=> "15", //textarea only
        "parent" => "email_res_setup",
        "std" => '<p style="text-align: left;">Hello,</p>
<p style="text-align: left;">{first_name} {last_name} is registered for the event. See details below.</p>
<p style="text-align: left;"><strong>Name:</strong> {first_name} {last_name}</p>
<p style="text-align: left;"><strong>Email:</strong> {payer_email}</p>
<p style="text-align: left;"><strong>Selected Pass:</strong> {event_pass}</p>
<p style="text-align: left;"><strong>No. of Seats:</strong> {quantity}</p>
<p style="text-align: left;"><strong>Amount:</strong> {payment_amount}{payment_currency}</p>
<p style="text-align: left;">This registration was submitted on <strong>{sitename}</strong></p>',
		"setting_tab" => 'freeemail'
	),

	array(
        "name" => "New Registration Emails",
        "desc" => "Enter the email address(es) that should receive a notification anytime a new registration was made",
        "id" => "eventres_email_res_to_admin_email_addresses",
        "type" => "textarea",
        "cols"=> "100", //textarea only
        "rows"=> "5", //textarea only
        "parent" => "email_res_setup",
        "std" => 'contact.cththemes@gmail.com',
        'style'=>'width:100%;',
        "setting_tab" => 'freeemail'
    ),

	
	array(
        "name" => "Messages",
        "type" => "sub-section-3",
        //"category" => "header-styles",
        "setting_tab" => 'messages'
    ),

	array(
        "name" => "Empty First Name Message",
        "desc" => "",
        "id" => "eventres_message_empty_fname",
        "type" => "text",
        "parent" => "messages_setup",
        "std" => "Your first name",
        "style"=>"width:100%;",
        "setting_tab" => 'messages'
    ),
    array(
        "name" => "Empty Last Name Message",
        "desc" => "",
        "id" => "eventres_message_empty_lname",
        "type" => "text",
        "parent" => "messages_setup",
        "std" => "Your last name",
        "style"=>"width:100%;",
        "setting_tab" => 'messages'
    ),
    array(
        "name" => "Invalid Email Message",
        "desc" => "",
        "id" => "eventres_message_empty_email",
        "type" => "text",
        "parent" => "messages_setup",
        "std" => "We need your email address",
        "style"=>"width:100%;",
        "setting_tab" => 'messages'
    ),
    array(
        "name" => "Empty Pass Message",
        "desc" => "",
        "id" => "eventres_message_empty_pass",
        "type" => "text",
        "parent" => "messages_setup",
        "std" => "Choose your Pass",
        "style"=>"width:100%;",
        "setting_tab" => 'messages'
    ),
    array(
        "name" => "Invalid Quantity Message",
        "desc" => "",
        "id" => "eventres_message_empty_seats",
        "type" => "text",
        "parent" => "messages_setup",
        "std" => "How many seats",
        "style"=>"width:100%;",
        "setting_tab" => 'messages'
    ),
    array(
        "name" => "Uncheck Agreement Message",
        "desc" => "",
        "id" => "eventres_message_empty_agree",
        "type" => "text",
        "parent" => "messages_setup",
        "std" => "Please accept our terms and privacy policy",
        "style"=>"width:100%;",
        "setting_tab" => 'messages'
    ),
);

function cth_eventres_create_form($options,$tab = null) { 
    
    foreach ($options as $value) {
    	if(!isset($tab)){
    		echo "<table class=\"form-table\">\n";
    		switch ( $value['type'] ) {
	            case "sub-section-3":
	                cth_create_suf_header_3($value);
	                break;
	 
	            case "text":
	                cth_create_section_for_text($value);
	                break;
	 
	            case "textarea":
	                cth_create_section_for_textarea($value);
	                break;
	            
	            case "editor":
	                cth_create_section_for_editor($value);
	                break;

	            case "ace_editor":
	                cth_create_section_for_ace_editor($value);
	                break;

	            case "multi-select":
	                cth_create_section_for_multi_select($value);
	                break;
	 
	            case "radio":
	                cth_create_section_for_radio($value);
	                break;

	            case "checkbox":
	                cth_create_section_for_checkbox($value);
	                break;
	 
	            case "color-picker":
	                cth_create_section_for_color_picker($value);
	                break;
	            case "select":
	                cth_create_section_for_select($value);
	                break;
	            case "repeatable":
	            	cth_create_section_for_repeatable($value);
	            	break;
	            case "page_select":
	            	cth_create_section_for_list_pages_select($value);
	            	break;

	            	
	        }
	        echo "</table>";
    	}else if($tab == $value['setting_tab']){
    		echo "<table class=\"form-table\">\n";
    		switch ( $value['type'] ) {
	            case "sub-section-3":
	                cth_create_suf_header_3($value);
	                break;
	 
	            case "text":
	                cth_create_section_for_text($value);
	                break;
	 
	            case "textarea":
	                cth_create_section_for_textarea($value);
	                break;
	            
	            case "editor":
	                cth_create_section_for_editor($value);
	                break;

	            case "ace_editor":
	                cth_create_section_for_ace_editor($value);
	                break;

	            case "multi-select":
	                cth_create_section_for_multi_select($value);
	                break;
	 
	            case "radio":
	                cth_create_section_for_radio($value);
	                break;

	            case "checkbox":
	                cth_create_section_for_checkbox($value);
	                break;
	 
	            case "color-picker":
	                cth_create_section_for_color_picker($value);
	                break;
	            case "select":
	                cth_create_section_for_select($value);
	                break;
	            case "repeatable":
	            	cth_create_section_for_repeatable($value);
	            	break;
	            case "page_select":
	            	cth_create_section_for_list_pages_select($value);
	            	break;
	            	
	        }
	        echo "</table>";
    	}else{
    		echo "<table style=\"display:none;\" class=\"form-table\">\n";
    		switch ( $value['type'] ) {
	            case "sub-section-3":
	                cth_create_suf_header_3($value);
	                break;
	 
	            case "text":
	                cth_create_section_for_text($value);
	                break;
	 
	            case "textarea":
	                cth_create_section_for_textarea($value);
	                break;
	            
	            case "editor":
	                cth_create_section_for_editor($value);
	                break;

	            case "ace_editor":
	                cth_create_section_for_ace_editor($value);
	                break;

	            case "multi-select":
	                cth_create_section_for_multi_select($value);
	                break;
	 
	            case "radio":
	                cth_create_section_for_radio($value);
	                break;

	            case "checkbox":
	                cth_create_section_for_checkbox($value);
	                break;
	 
	            case "color-picker":
	                cth_create_section_for_color_picker($value);
	                break;
	            case "select":
	                cth_create_section_for_select($value);
	                break;
	            case "repeatable":
	            	cth_create_section_for_repeatable($value);
	            	break;
	            case "page_select":
	            	cth_create_section_for_list_pages_select($value);
	            	break;
	            	
	        }
	        echo "</table>";
    	}
        
    }

    
}
?>
<div class="wrap">
	<!-- <h2><?php _e('Gather Event Registration Settings','cth-gather-plugins');?></h2> -->
	<form method="post" action="options.php">
	    <?php settings_fields( 'cth_eventres_options-group' ); ?>
	    <?php //do_settings_sections( 'my-cool-plugin-settings-group' ); ?>
	    <?php
		if ( $pagenow == 'edit.php' && $_GET['post_type'] == 'cth_eventres' && $_GET['page'] == 'cth_eventres' ){

		   	if ( isset ( $_GET['tab'] ) ) $tab = $_GET['tab'];
		   	else $tab = 'general';

		   	cth_eventres_create_form($eventres_options,$tab);

		   	submit_button();
		   
		}

		?>

	        <?php
	            //cth_eventres_create_form($eventres_options);
	        ?>
	    <?php //submit_button(); ?>
	</form>
</div><!-- end wrap -->