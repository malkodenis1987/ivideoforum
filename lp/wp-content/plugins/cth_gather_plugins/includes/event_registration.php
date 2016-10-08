<?php
if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}
//for setting tabs
function cth_eventres_admin_tabs( $current = 'resform' ) {
    $tabs = array('general' => 'General', 'resform' => 'Registration Form', 'paypalemail' => 'Paypal Registration Emails', 'freeemail'=>'Free Registration Emails', 'messages' => 'Messages' );
    //echo '<div id="icon-themes" class="icon32"><br></div>';
    echo '<h2 class="nav-tab-wrapper">';
    foreach( $tabs as $tab => $name ){
        $class = ( $tab == $current ) ? ' nav-tab-active' : '';
        echo "<a class='nav-tab$class' href='?post_type=cth_eventres&page=cth_eventres&tab=$tab'>$name</a>";

    }
    echo '</h2>';
}

// Plugin Options
/** Step 2 (from text above). */
add_action( 'admin_menu', 'cth_eventres_plugin_menu' );

/** Step 1. */
function cth_eventres_plugin_menu() {
    //Create new option menu
    // add_options_page( 
    //     'Gather Event Reservation Options', // $page_title - (string) (required) The text to be displayed in the title tags of the page when the menu is selected 
    //     'Gather Event Reservation', //  $menu_title - (string) (required) The text to be used for the menu 
    //     'manage_options', // $capability - (string) (required) The capability required for this menu to be displayed to the user. 
    //     'cth_eventres', //menu_slug - (string) (required) The slug name to refer to this menu by (should be unique for this menu). 
    //     'cth_eventres_plugin_options' // $function - (callback) (optional) The function to be called to output the content for this page. 
    // );
    add_submenu_page( 
        'edit.php?post_type=cth_eventres',
        'Gather Event Registration Options', // $page_title - (string) (required) The text to be displayed in the title tags of the page when the menu is selected 
        'Settings', //  $menu_title - (string) (required) The text to be used for the menu 
        'manage_options', // $capability - (string) (required) The capability required for this menu to be displayed to the user. 
        'cth_eventres', //menu_slug - (string) (required) The slug name to refer to this menu by (should be unique for this menu). 
        'cth_eventres_plugin_options' // $function - (callback) (optional) The function to be called to output the content for this page. 
    );
    //call register settings function
    add_action( 'admin_init', 'register_cth_eventres_settings' );
}

/** Step 3. */
function cth_eventres_plugin_options() {
    if ( !current_user_can( 'manage_options' ) )  {
        wp_die( __( 'You do not have sufficient permissions to access this page.' ,'cth-gather-plugins') );
    }
    global $pagenow;
    //$settings = get_option( "ilc_theme_settings" );

    //generic HTML and code goes here

    if ( isset ( $_GET['tab'] ) ) cth_eventres_admin_tabs($_GET['tab']); else cth_eventres_admin_tabs('general');
    
    include dirname(__FILE__ ).'/eventres/plugin_options.php';
}

function register_cth_eventres_settings() { // whitelist options

	register_setting( 'cth_eventres_options-group', 'eventres_test_mode' );
    register_setting( 'cth_eventres_options-group', 'eventres_paypal_email' );
    register_setting( 'cth_eventres_options-group', 'eventres_currency' );

    register_setting( 'cth_eventres_options-group', 'eventres_item_name' );
    
    // register_setting( 'cth_eventres_options-group', 'eventres_item_price' );
    // register_setting( 'cth_eventres_options-group', 'eventres_multi_price' );
    register_setting( 'cth_eventres_options-group', 'eventres_item_prices_name' );
    register_setting( 'cth_eventres_options-group', 'eventres_item_prices' );
    register_setting( 'cth_eventres_options-group', 'eventres_item_quantity_name' );
    register_setting( 'cth_eventres_options-group', 'eventres_item_quantity' );


    register_setting( 'cth_eventres_options-group', 'eventres_terms_content' );
    register_setting( 'cth_eventres_options-group', 'eventres_hide_terms' );


    
    register_setting( 'cth_eventres_options-group', 'eventres_success_page' );
    register_setting( 'cth_eventres_options-group', 'eventres_return_page' );
    register_setting( 'cth_eventres_options-group', 'eventres_cancelled_page' );

    register_setting( 'cth_eventres_options-group', 'eventres_email_sender' );
    register_setting( 'cth_eventres_options-group', 'eventres_email_sender_email' );
    // register_setting('cth_eventres_options-group','eventres_confirming_email_to' );
    // register_setting( 'cth_eventres_options-group', '_reservation_confirm_after_booked' );
    register_setting( 'cth_eventres_options-group', 'eventres_email_content_type' );

    // // confirming
    // register_setting( 'cth_eventres_options-group', '_reservation_confirming_email' );
    // register_setting('cth_eventres_options-group','_reservation_confirming_email_subject' );
    // register_setting('cth_eventres_options-group','_reservation_confirming_email_template' );
    //confirmed
    register_setting( 'cth_eventres_options-group', 'eventres_confirmed_to_buyer_email' );
    register_setting('cth_eventres_options-group','eventres_confirmed_to_buyer_email_subject' );
    register_setting('cth_eventres_options-group','eventres_confirmed_to_buyer_email_template' );

    register_setting( 'cth_eventres_options-group', 'eventres_confirmed_to_admin_email' );
    register_setting('cth_eventres_options-group','eventres_confirmed_to_admin_email_subject' );
    register_setting('cth_eventres_options-group','eventres_confirmed_to_admin_email_template' );
    register_setting('cth_eventres_options-group','eventres_confirmed_to_admin_email_addresses' );



    register_setting( 'cth_eventres_options-group', 'eventres_failed_to_admin_email' );
    register_setting('cth_eventres_options-group','eventres_failed_to_admin_email_subject' );
    register_setting('cth_eventres_options-group','eventres_failed_to_admin_email_template' );
    register_setting('cth_eventres_options-group','eventres_failed_to_admin_email_addresses' );
    // //complete
    // register_setting( 'cth_eventres_options-group', '_reservation_complete_email' );
    // register_setting('cth_eventres_options-group','_reservation_complete_email_subject' );
    // register_setting('cth_eventres_options-group','_reservation_complete_email_template' );

    // register_setting('cth_eventres_options-group','_reservation_confirmed_page_message' );

    // new user
    //register_setting('cth_eventres_options-group','eventres_new_user' );
    register_setting('cth_eventres_options-group','eventres_resend_paypal_link_email_subject');
    register_setting('cth_eventres_options-group','eventres_resend_paypal_link_email_template');
    
    // Free email registration
    register_setting( 'cth_eventres_options-group', 'eventres_email_res_to_buyer_email' );
    register_setting('cth_eventres_options-group','eventres_email_res_to_buyer_email_subject' );
    register_setting('cth_eventres_options-group','eventres_email_res_to_buyer_email_template' );

    register_setting( 'cth_eventres_options-group', 'eventres_email_res_to_admin_email' );
    register_setting('cth_eventres_options-group','eventres_email_res_to_admin_email_subject' );
    register_setting('cth_eventres_options-group','eventres_email_res_to_admin_email_template' );
    register_setting('cth_eventres_options-group','eventres_email_res_to_admin_email_addresses' );
    
    //messages
    register_setting('cth_eventres_options-group','eventres_message_empty_fname'); 
    register_setting('cth_eventres_options-group','eventres_message_empty_lname'); 
    register_setting('cth_eventres_options-group','eventres_message_empty_email'); 
    register_setting('cth_eventres_options-group','eventres_message_empty_pass'); 
    register_setting('cth_eventres_options-group','eventres_message_empty_seats'); 
    register_setting('cth_eventres_options-group','eventres_message_empty_agree'); 
}

/* event registration - ajax call progress */
require_once dirname(__FILE__).'/eventres/ajax.php';


function cththemes_register_cpt_Cth_Event_Reservation() {
    
    $r_labels = array( 
        'name' => __( 'Registration', 'cth-gather-plugins' ),
        'singular_name' => __( 'Registration', 'cth-gather-plugins' ),
        'add_new' => __( 'Add New Registration', 'cth-gather-plugins' ),
        'add_new_item' => __( 'Add New Registration', 'cth-gather-plugins' ),
        'edit_item' => __( 'Edit Registration', 'cth-gather-plugins' ),
        'new_item' => __( 'New Registration', 'cth-gather-plugins' ),
        'view_item' => __( 'View Registration', 'cth-gather-plugins' ),
        'search_items' => __( 'Search Registrations', 'cth-gather-plugins' ),
        'not_found' => __( 'No Registrationa found', 'cth-gather-plugins' ),
        'not_found_in_trash' => __( 'No Registrations found in Trash', 'cth-gather-plugins' ),
        'parent_item_colon' => __( 'Parent Registration:', 'cth-gather-plugins' ),
        'menu_name' => __( 'Gather Event Registrations', 'cth-gather-plugins' ),
    );

    $r_args = array( 
        'labels' => $r_labels,
        'hierarchical' => false,
        'description' => 'List Registrations',
        //'supports' => array( 'title', 'editor'/*, 'custom-fields','comments', 'post-formats'*/),
        'supports' => false,
        //'taxonomies' => array('reservation-cat'),
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 20,
        'menu_icon'   => 'dashicons-chart-line',
        //'menu_icon' => plugin_dir_url( __FILE__ ) .'assets/admin_ico_reservation.png', 
        'show_in_nav_menus' => false,
        'publicly_queryable' => false,
        'exclude_from_search' => true,
        'has_archive' => false,
        'query_var' => false, //false for can not use /?{query_var}={single_post_slug} 
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );

    register_post_type( 'cth_eventres', $r_args );
}

//Register Portfolio 
add_action( 'init', 'cththemes_register_cpt_Cth_Event_Reservation' );

if(!function_exists('cth_gather_plugins_eventres_columns_head')){
    function cth_gather_plugins_eventres_columns_head($defaults) {
        unset($defaults['date']);
        unset($defaults['title']);
        $defaults['ID'] 			= __('Registration','cth-gather-plugins');
        $defaults['pur_pass']       =  __('Pass','cth-gather-plugins');
        $defaults['pur_status'] 	= __('Status','cth-gather-plugins');
        
        $defaults['pur_quantity'] 	= __('Quantity','cth-gather-plugins');
        $defaults['pur_amount'] 	= __('Amount','cth-gather-plugins');
        $defaults['pur_currency'] 	= __('Currency','cth-gather-plugins');
        $defaults['pur_date'] 	= __('Date','cth-gather-plugins');
        // $defaults['reservation_time'] = __('Reservation Time','cth-reservation');
        // $defaults['reservation_status'] = __('Reservation Status','cth-reservation');

        return $defaults;
    }
}

if(!function_exists('cth_gather_plugins_eventres_columns_content')){
    function cth_gather_plugins_eventres_columns_content($column_name, $post_ID) {
        //echo '<pre>';var_dump(get_post_meta( $post_ID, '', false ));die;
        if ($column_name == 'ID') {
            //$res_pre_fname = get_post_meta( $post_ID, 'pre_fname', true );
            //echo intval($res_date);
            echo '<div class="tips">';
            echo '<a href="'.admin_url('post.php?post='.$post_ID.'&action=edit' ).'"><strong>#'.$post_ID.'</strong></a>';
            echo __(' by ','cth-gather-plugins'). '<strong>'.get_post_meta( $post_ID, 'pur_fname', true ) .' '. get_post_meta( $post_ID, 'pur_lname', true ).'</strong>';
            echo '<br><small class="meta email"><a href="mailto:'.get_post_meta( $post_ID, 'pur_email', true ).'">'.get_post_meta( $post_ID, 'pur_email', true ).'</a></small>';
            echo '</div>';
        }
        if ($column_name == 'pur_status') {
            echo '<strong>'.get_post_meta( $post_ID, 'pur_status', true ).'</strong>';
            
        }
        if ($column_name == 'pur_pass') {
            echo '<strong>'.get_post_meta( $post_ID, 'pur_pass', true ).'</strong>';
            
        }
        if ($column_name == 'pur_quantity') {
            echo '<strong>'.get_post_meta( $post_ID, 'pur_quantity', true ).'</strong>';
            
        }
        if ($column_name == 'pur_amount') {
        	$res_pur_price = (float)get_post_meta( $post_ID, 'pur_price', true );
        	$res_pur_quantity = (int)get_post_meta( $post_ID, 'pur_quantity', true );
            echo '<strong>'.$res_pur_price*$res_pur_quantity.'</strong>';
            
        }
        if ($column_name == 'pur_currency') {
            echo '<strong>'.get_post_meta( $post_ID, 'pur_currency', true ).'</strong>';
            
        }
        if ($column_name == 'pur_date') {
            echo '<strong>'.get_post_meta( $post_ID, 'pur_date', true ).'</strong>';
            
        }
    }
}


add_filter('manage_cth_eventres_posts_columns', 'cth_gather_plugins_eventres_columns_head');
add_action('manage_cth_eventres_posts_custom_column', 'cth_gather_plugins_eventres_columns_content', 10, 2);

add_filter( 'manage_edit-cth_eventres_sortable_columns', 'cth_gather_plugins_eventres_sortable_columns' );
if(!function_exists('cth_gather_plugins_eventres_sortable_columns')){
    function cth_gather_plugins_eventres_sortable_columns( $columns ) {
        $columns['ID'] = 'ID';
        $columns['pur_status'] = 'pur_status';
        $columns['pur_date'] = 'pur_date';
        $columns['pur_pass'] = 'pur_pass';
        //$columns['pur_amount'] = 'pur_amount';
        $columns['pur_quantity'] = 'pur_quantity';
     
        return $columns;
    }
}

add_action( 'pre_get_posts', 'cth_gather_plugins_eventres_orderby' );
function cth_gather_plugins_eventres_orderby( $query ) {
    if( ! is_admin() )
        return;
 
    $orderby = $query->get( 'orderby');
 
    if( 'pur_status' == $orderby ) {
        $query->set('meta_key','pur_status');
        $query->set('orderby','meta_value');
    }elseif( 'pur_date' == $orderby ) {
        $query->set('meta_key','pur_date');
        $query->set('orderby','meta_value_num');
    }
    elseif( 'pur_pass' == $orderby ) {
        $query->set('meta_key','pur_pass');
        $query->set('orderby','meta_value');
    }
    elseif( 'pur_quantity' == $orderby ) {
        $query->set('meta_key','pur_quantity');
        $query->set('orderby','meta_value_num');
    }

}

function cth_gather_plugins_eventres_add_meta_box() {

    $screens = array( 'cth_eventres');

    foreach ( $screens as $screen ) {

        add_meta_box(
            'cth_eventres_payment_details',
            __( 'Registration Details', 'cth-gather-plugins' ),
            'cth_gather_plugins_eventres_meta_box_payment_details_callback',
            $screen,
            'normal',
            'core'
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_eventres_customer_details',
            __( 'Customer Details', 'cth-gather-plugins' ),
            'cth_gather_plugins_eventres_meta_box_customer_details_callback',
            $screen,
            'normal',
            'core'
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_eventres_payment_status',
            __( 'Registration Status', 'cth-gather-plugins' ),
            'cth_gather_plugins_eventres_meta_box_paymeny_status_callback',
            $screen,
            'side',
            'high'
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_eventres_payment_meta',
            __( 'Registration Meta', 'cth-gather-plugins' ),
            'cth_gather_plugins_eventres_meta_box_payment_meta_callback',
            $screen,
            'normal',
            'core'
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
        add_meta_box(
            'cth_eventres_payment_note',
            __( 'Registration Note', 'cth-gather-plugins' ),
            'cth_gather_plugins_eventres_meta_box_payment_note_callback',
            $screen,
            'normal',
            'core'
            //,'normal', //('normal', 'advanced', or 'side')
            //'core'//('high', 'core', 'default' or 'low') 
        );
    }

    
}
add_action( 'add_meta_boxes', 'cth_gather_plugins_eventres_add_meta_box' );

function cth_gather_plugins_eventres_meta_box_payment_details_callback( $post ) {

	
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $option_prices       = get_option('eventres_item_prices' );


    $pur_pass 			= get_post_meta( $post->ID, 'pur_pass', true );
    $pur_price 			= get_post_meta( $post->ID, 'pur_price', true );
    $pur_quantity 		= get_post_meta( $post->ID, 'pur_quantity', true );
    ?>
    <table class="form-table eventres-payment-details">
    	<thead>
    		<tr>
    			<th class="pm-pass"><?php _e( 'Pass', 'cth-gather-plugins' );?></th>
    			<th class="pm-price"><?php _e( 'Price', 'cth-gather-plugins' );?></th>
    			<th class="pm-quantity"><?php _e( 'Quantity', 'cth-gather-plugins' );?></th>
    			<th class="pm-amount"><?php _e( 'Amount', 'cth-gather-plugins' );?></th>
    			<th class="pm-currency"><?php _e( 'Currency', 'cth-gather-plugins' );?></th>
    		</tr>
    	</thead>
    	<tbody>
    		<tr>
    			<td class="pm-pass">
    				<select id="pur_pass" name="pur_pass">
		        <?php
		        foreach ($option_prices as $pr_arr) {
		            $selected = '';
		            if($pur_pass === $pr_arr['name']){
		                $selected = ' selected="selected"';
		            }
		            echo '<option value="'.$pr_arr['name'].'"'.$selected.'>'.$pr_arr['name'].'</option>';
		        }
		        ?>
		        	</select>
		        <?php //echo esc_attr($pur_pass );?></td>
    			<td class="pm-price"><span id="pm-price-span"><?php echo esc_attr($pur_price );?></span><input type="hidden" id="pur_price" name="pur_price" value="<?php echo esc_attr($pur_price );?>"></td>
    			<td class="pm-quantity"><input type="number" id="pur_quantity" name="pur_quantity" value="<?php echo esc_attr($pur_quantity );?>"></td>
    			<td class="pm-amount"><span id="pm-amount-span"><?php echo $pur_price*$pur_quantity;?></span></td>
    			<td class="pm-currency"><?php echo get_option('eventres_currency' );?></td>
    		</tr>
    	</tbody>
    </table>
    <script>
    	jQuery(document).ready(function($) {
    		var option_prices = new Array();
		    <?php 
		    $op_in = 0;
		    foreach ($option_prices as $value) { ?>
		    	option_prices[<?php echo $op_in;?>] = <?php echo $value['value'];?>;
		    <?php
		    $op_in++;
		    } ?>
		  	$("#pur_pass").change(function() {
			    // I personally prefer using console.log(), but if you want you can still go with the alert().
			    pr_val = option_prices[$(this).children('option:selected').index()];
			    $('#pm-price-span').text(pr_val);
			    $('#pur_price').val(pr_val);
			    $('#pm-amount-span').text(parseFloat(pr_val)*parseInt($('#pur_quantity').val() ));
		  	});
		  	$("#pur_quantity").change(function() {
		  		if($(this).val() < 0){
		  			$(this).val(0);
		  		}
		  		$('#pm-amount-span').text(parseInt($(this).val())*parseFloat($('#pur_price').val() ));
		  	});
		});
    </script>
    <?php   
}
function cth_gather_plugins_eventres_meta_box_customer_details_callback( $post ) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $pur_fname = get_post_meta( $post->ID, 'pur_fname', true );
    $pur_lname = get_post_meta( $post->ID, 'pur_lname', true );
    $pur_email = get_post_meta( $post->ID, 'pur_email', true );
    
    echo '<table class="form-table"><tbody>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'First Name: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="pur_fname" name="pur_fname" value="' . esc_attr( $pur_fname ) . '" size="25" />';
    echo '</td></tr>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'Last Name: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="pur_lname" name="pur_lname" value="' . esc_attr( $pur_lname ) . '" size="25" />';
    echo '</td></tr>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'Email: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<input type="text" id="pur_email" name="pur_email" value="' . esc_attr( $pur_email ) . '" size="50" />';
    echo '</td></tr>';

    echo '</tbody></table>';  
}
function cth_gather_plugins_eventres_meta_box_paymeny_status_callback( $post ) {

    // default reservation status
    $defauls = array(
    	'Pending'=>__('Pending','cth-gather-plugins'), 
    	'Completed'=>__('Completed','cth-gather-plugins'), 
    	'Failed'=>__('Failed','cth-gather-plugins'), 
    	'Refunded'=>__('Refunded','cth-gather-plugins') 
    );

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'pur_status', true );

    echo '<table class="form-table"><tbody><tr>';
    echo '<th style="width:20%">';
        echo '<label for="pur_status">';
        _e( 'Reservation Status', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>';
        echo '<select id="pur_status" name="pur_status">';
        foreach ($defauls as $key => $val) {
            $selected = '';
            if($value === $key){
                $selected = ' selected="selected"';
            }
            echo '<option value="'.$key.'"'.$selected.'>'.$val.'</option>';
        }
        echo '</select>';
    echo '</td></tr>';
    if($value != 'Completed'){
        wp_nonce_field( 'eventres_resend_paypal_link_action', 'eventres_resend_paypal_link_nonce' );
        echo '<tr><td colspan="2" style="padding-left:0px;"><span id="rsp_msg"></span><br><a href="#" id="resend_paypal_link" data-pid="'.$post->ID.'"  data-pst="'.$value.'" class="button button-large mb5">'.__('Resend Paypal Link','cth-gather-plugins').'</a><br>Click button above to send Paypal link to the user so that he can complete this payment.</td></tr>';
    }
    echo '</tbody></table>';  
}
function cth_gather_plugins_eventres_meta_box_payment_meta_callback( $post ) {
    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $pur_gateway = get_post_meta( $post->ID, 'pur_gateway', true );
    $pur_key = get_post_meta( $post->ID, 'pur_key', true );
    $pur_custom = get_post_meta( $post->ID, 'pur_custom', true );
    
    echo '<table class="form-table"><tbody>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'Gateway: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>'.$pur_gateway.'</td></tr>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'Key: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>'.$pur_key.'</td></tr>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'Custom: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>'.$pur_custom.'</td></tr>';

    echo '<tr><th style="width:20%">';
        echo '<label>';
        _e( 'Transaction ID: ', 'cth-gather-plugins' );
        echo '</label> ';
    echo '</th>';
    echo '<td>'.get_post_meta( $post->ID, 'pur_txn_id', true ).'</td></tr>';

    echo '</tbody></table>';  
}
function cth_gather_plugins_eventres_meta_box_payment_note_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'cth_gather_plugins_save_meta_box_data', 'cth_gather_plugins_meta_box_nonce' );

    /*
     * Use get_post_meta() to retrieve an existing value
     * from the database and use the value for the form.
     */
    $value = get_post_meta( $post->ID, 'pur_note', true );
    //echo '<div class="payment_note_value">'.$value.'</div>';

    echo '<textarea name="pur_note" id="pur_note" cols="30" rows="3" style="width:100%;">'.$value.'</textarea>';
 	
}
/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function cth_gather_plugins_eventres_save_meta_box_datas( $post_id ) {

    /*
     * We need to verify this came from our screen and with proper authorization,
     * because the save_post action can be triggered at other times.
     */

    // Check if our nonce is set.
    if ( ! isset( $_POST['cth_gather_plugins_meta_box_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['cth_gather_plugins_meta_box_nonce'], 'cth_gather_plugins_save_meta_box_data' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'cth_eventres' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    } else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }




    if(isset($_POST['pur_pass'])){
    	update_post_meta( $post_id, 'pur_pass', sanitize_text_field( $_POST['pur_pass'] ) );
    }
    if(isset($_POST['pur_price'])){
    	//for new payment
    	if(empty($_POST['pur_price'])){
    		update_post_meta( $post_id, 'pur_price', '99' );
    	}else{
    		update_post_meta( $post_id, 'pur_price', sanitize_text_field( $_POST['pur_price'] ) );
    	}
    	
    }
    if(isset($_POST['pur_quantity'])){
    	//for new payment
    	if(empty($_POST['pur_quantity'])){
    		update_post_meta( $post_id, 'pur_quantity', '1' );
    	}else{
    		update_post_meta( $post_id, 'pur_quantity', sanitize_text_field( $_POST['pur_quantity'] ) );
    	}
    	
    }
    if(isset($_POST['pur_fname'])){
    	//for new payment
    	if(empty($_POST['pur_fname'])){
    		update_post_meta( $post_id, 'pur_fname', 'First Name' );
    	}else{
    		update_post_meta( $post_id, 'pur_fname', sanitize_text_field( $_POST['pur_fname'] ) );
    	}
    	
    }
    if(isset($_POST['pur_lname'])){
    	//for new payment
    	if(empty($_POST['pur_lname'])){
    		update_post_meta( $post_id, 'pur_lname', 'Last Name' );
    	}else{
    		update_post_meta( $post_id, 'pur_lname', sanitize_text_field( $_POST['pur_lname'] ) );
    	}
    	
    }
    if(isset($_POST['pur_email'])){
    	//for new payment
    	if(empty($_POST['pur_email'])){
    		update_post_meta( $post_id, 'pur_email', 'Email Address' );
    	}else{
    		update_post_meta( $post_id, 'pur_email', sanitize_text_field( $_POST['pur_email'] ) );
    	}
    	
    }
    if(isset($_POST['pur_note'])){
    	update_post_meta( $post_id, 'pur_note', sanitize_text_field( $_POST['pur_note'] ) );
    }

    // new payment
    if(!get_post_meta( $post_id, 'pur_date', true )){
    	update_post_meta( $post_id, 'pur_date', date( 'Y-m-d H:i:s', current_time( 'timestamp' ) ) );
    }
    if(!get_post_meta( $post_id, 'pur_currency', true )){
    	update_post_meta( $post_id, 'pur_currency', get_option('eventres_currency' ) );
    }

    if(!get_post_meta( $post_id, 'pur_custom', true )){
    	update_post_meta( $post_id, 'pur_custom', uniqid() );
    }
    if(!get_post_meta( $post_id, 'pur_gateway', true )){
    	update_post_meta( $post_id, 'pur_gateway', 'Admin Registration' );
    }

   	if(!get_post_meta( $post_id, 'pur_key', true )){
   		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
    	update_post_meta( $post_id, 'pur_key', strtolower( md5( 'email_address_instead' . date( 'Y-m-d H:i:s' ) . $auth_key . uniqid( 'gatherevent', true ) ) ) );
    }


    $pur_status_data = sanitize_text_field( $_POST['pur_status'] );

    $origin_status = 'New_Registration';
    if(get_post_meta( $post_id, 'pur_status', true )){
        $origin_status = get_post_meta( $post_id, 'pur_status', true );
    }

    if($pur_status_data !== $origin_status){
    	update_post_meta( $post_id, 'pur_status', $pur_status_data );
    	do_action('eventres_status_from_'.$origin_status.'_to_'.$pur_status_data, $post_id );
    }
    
}
add_action( 'save_post', 'cth_gather_plugins_eventres_save_meta_box_datas' );
//add_action('eventres_status_from_New_Registration_to_Pending','eventres_status_from_New_Registration_to_Pending_calback' );
if(!function_exists('eventres_status_from_New_Registration_to_Pending_calback')){
	function eventres_status_from_New_Registration_to_Pending_calback($post_id){
		$sender_option = get_option('eventres_email_sender','Gather Event' );
    	$sender_email_option = get_option('eventres_email_sender_email','contact.cththemes@gmail.com' );

    	// to buyer
    	$buyer_mail_to = get_post_meta( $post_id, 'pur_email', true );
    	$buyer_mail_subject = get_option('eventres_confirmed_to_buyer_email_subject','Your Seat is reserved for the event'); 
		$to_buyer_email_template = get_option('eventres_confirmed_to_buyer_email_template'); 

        if(preg_match_all("/{([\w-_]+)[^\w-_]*}/", $to_buyer_email_template, $matches)!= FALSE){
            $fieldsPattern = array();//$matches[0];
            $fieldsReplace = array();
            foreach ($matches[1] as $key => $fn) {
                $fieldsPattern[] = "/{(".$fn.")[^\w-_]*}/";

                if(isset($$fn)){
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
        

	}
}
function cth_gather_plugins_eventres_register_vc_elements(){
    if(function_exists('vc_map')){
        vc_map( array(
            "name"      => __("Gather Paypal Registration", 'cth-gather-plugins'),
            "base"      => "cth_paypal_registration",
            "class"     => "",
            "icon" => CTH_EVENTRES_DIR_URL . "assets/cth-icon.png",
            "category"=>"Gather",
            "html_template" => CTH_EVENTRES_DIR.'vc_templates/cth_paypal_registration.php',
            "params"    => array(
                array(
                    "type" => "textfield", 
                    "heading" => __("Form Title", "cth-gather-plugins"), 
                    "holder"=>'div',
                    "param_name" => "form_title", 
                    "value" => "Event Registration",
                    "description" => ''
                ), 
                array(
                    "type" => "textarea", 
                    "heading" => __("Form Text Before", "cth-gather-plugins"), 
                    "param_name" => "content",
                    "holder"=>'div', 
                    "value" => "",
                    "description" => ''
                ), 

                array(
                    "type" => "textfield", 
                    "heading" => __("Button Title", "cth-gather-plugins"), 
                    "param_name" => "button_title", 
                    "value" => "Reserve my Seat",
                    "description" => ''
                ),

                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Layout', 'cth-gather-plugins'), 
                    "param_name" => "layout", 
                    "value" => array(
                        __('Modal', 'cth-gather-plugins') => 'modal',
                        __('Normal', 'cth-gather-plugins') => 'normal', 
                        
                    ), 
                    "description" => __("When set this option to Modal you need a modal trigger button to get this form display.", 'gather'),
                    "default" => 'normal',
                ), 
                
                array(
                    "type" => "textfield",
                    "heading" => __("Extra class name", "gather"),
                    "param_name" => "el_class",
                    "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "cth-gather-plugins")
                ),

            )));


        if ( class_exists( 'WPBakeryShortCode' ) ) {
            class WPBakeryShortCode_Cth_Paypal_Registration extends WPBakeryShortCode {}
        }

        vc_map( array(
            "name"      => __("Gather Email Registration", 'cth-gather-plugins'),
            "base"      => "cth_email_registration",
            "class"     => "",
            "icon" => CTH_EVENTRES_DIR_URL . "assets/cth-icon.png",
            "category"=>"Gather",
            "html_template" => CTH_EVENTRES_DIR.'vc_templates/cth_email_registration.php',
            "params"    => array(
                array(
                    "type" => "textfield", 
                    "heading" => __("Form Title", "cth-gather-plugins"), 
                    "holder"=>'div',
                    "param_name" => "form_title", 
                    "value" => "Event Registration",
                    "description" => ''
                ), 
                array(
                    "type" => "textarea", 
                    "heading" => __("Form Text Before", "cth-gather-plugins"), 
                    "param_name" => "content",
                    "holder"=>'div', 
                    "value" => "",
                    "description" => ''
                ), 

                array(
                    "type" => "textfield", 
                    "heading" => __("Button Title", "cth-gather-plugins"), 
                    "param_name" => "button_title", 
                    "value" => "Reserve my Seat",
                    "description" => ''
                ),

                array(
                    "type" => "textfield",
                    "heading"=> __('Success page','cth-gather-plugins'),
                    "param_name" => "success_page", 
                    "value"=>"",
                    "description" => __('The page link will redirect to when new registration complete.','cth-gather-plugins')
                ),

                array(
                    "type" => "dropdown", 
                    "class" => "", 
                    "heading" => __('Layout', 'cth-gather-plugins'), 
                    "param_name" => "layout", 
                    "value" => array(
                        __('Modal', 'cth-gather-plugins') => 'modal',
                        __('Normal', 'cth-gather-plugins') => 'normal', 
                        
                    ), 
                    "description" => __("When set this option to Modal you need a modal trigger button to get this form display.", 'gather'),
                    "default" => 'normal',
                ), 
                
                array(
                    "type" => "textfield",
                    "heading" => __("Extra class name", "gather"),
                    "param_name" => "el_class",
                    "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "cth-gather-plugins")
                ),

            )));


        if ( class_exists( 'WPBakeryShortCode' ) ) {
            class WPBakeryShortCode_Cth_Email_Registration extends WPBakeryShortCode {}
        }

    }
}
add_action('init','cth_gather_plugins_eventres_register_vc_elements' );

function cth_gather_plugins_custom_wp_mail_from_name( $original_email_from ) {
    $sender_option = get_option('eventres_email_sender','Gather Event' );
    return $sender_option;
}

//page shortcodes
require_once dirname(__FILE__).'/eventres/shortcodes.php';