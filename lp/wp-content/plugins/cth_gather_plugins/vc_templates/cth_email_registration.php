<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $form_title
 * @var $button_title
 * @var $success_page
 * @var $layout
 * Shortcode class
 * @var $this WPBakeryShortCode_Cth_Email_Registration
 */
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
//$eventres_multi_price         = get_option('eventres_multi_price','yes' );
$eventres_item_prices_name      = get_option('eventres_item_prices_name' );
$eventres_item_prices           = get_option('eventres_item_prices' );
$eventres_item_quantity_name    = get_option('eventres_item_quantity_name' );
$eventres_item_quantity         = get_option('eventres_item_quantity' );
$eventres_currency              = get_option('eventres_currency' );
$eventres_terms_content         = get_option('eventres_terms_content' );
$eventres_hide_terms            = get_option('eventres_hide_terms' );

wp_enqueue_script("eventres-js", CTH_EVENTRES_DIR_URL . "assets/js/eventres.min.js", array('jquery','gather-validate-js'), false, true);
//Main Ajax script 
wp_localize_script( 'eventres-js', 'eventres_ajax', array(
    'url'           => admin_url( 'admin-ajax.php' ),
    'msg_fname'     => get_option('eventres_message_empty_fname' ),
    'msg_lname'     => get_option('eventres_message_empty_lname' ),
    'msg_email'     => get_option('eventres_message_empty_email' ),
    'msg_pass'     => get_option('eventres_message_empty_pass' ),
    'msg_seats'     => get_option('eventres_message_empty_seats' ),
    'msg_agree'     => get_option('eventres_message_empty_agree' ),
    'msg_wait'     => __('Please wait...','cth-gather-plugins'),
) );
?>
<?php if($layout == 'modal') :?>
<!-- 
 Registration Popup (EMAIL)
 ====================================== -->

<div class="modal fade" id="email-register" tabindex="-1" role="dialog" aria-labelledby="register-now-label">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-center" id="register-now-label"><?php echo esc_attr($form_title );?></h4>
            </div>
            <div class="modal-body">
<?php endif;?>
                <div class="registration-form <?php echo esc_attr($el_class );?>">
                <?php if($layout == 'normal') :?>
                    <h4 class="modal-title text-center" id="register-now-label"><?php echo esc_attr($form_title );?></h4>
                <?php endif;?>
                    <?php if(!empty($content)){
                        echo wpb_js_remove_wpautop($content);
                    }?>
                    <form method="POST" target="_top" id="email-registration-form" <?php if(!empty($success_page)) echo 'data-redirect="'.esc_url($success_page ).'"';?>>
                        
                        <div id="eventreg_msg"></div>
                        
                        <?php
                            // to make our script safe, it's a best practice to use nonce on our form to check things out
                            if ( function_exists( 'wp_nonce_field' ) ) 
                                wp_nonce_field( 'gather_event_email_registration_action', 'gather_event_email_registration_nonce' );
                        ?>
                        <div class="row">
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php _e('First Name','cth-gather-plugins');?></label>
                                    <input type="text" class="form-control" name="first_name" placeholder="<?php _e('First Name','cth-gather-plugins');?>" required>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <div class="form-group">
                                    <label><?php _e('Last Name','cth-gather-plugins');?></label>
                                    <input type="text" class="form-control" name="last_name" placeholder="<?php _e('Last Name','cth-gather-plugins');?>" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label><?php _e('Email Address','cth-gather-plugins');?></label>
                            <input type="email" class="form-control" name="email" placeholder="<?php _e('Email Address','cth-gather-plugins');?>" required>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="form-group">
                                    <label><?php echo __('Choose a ','cth-gather-plugins') . esc_attr($eventres_item_prices_name );?></label>
                                    <?php if($eventres_item_prices && count($eventres_item_prices)): ?>
                                    <select class="form-control" name="os0" required>
                                        <option value=""><?php _e('Choose...','cth-gather-plugins');?></option>
                                    <?php foreach ($eventres_item_prices as $value) { ?>
                                        <option value="<?php echo esc_attr($value['name'] );?>"><?php echo esc_attr($value['name'] ) .' - '. esc_attr($value['value'] ).' '.esc_attr( $eventres_currency );?></option>
                                    <?php } ?>
                                    </select>
                                    <?php else :?>
                                    <select class="form-control" name="os0" required>
                                        <option value=""><?php _e('Choose...','cth-gather-plugins');?></option>
                                        <option value="Default Pass">Default Pass - 99 USD</option>
                                    </select>
                                    <?php endif;?>
                                </div>
                                <input type="hidden" name="on0" value="<?php echo !empty($eventres_item_prices_name)? esc_attr($eventres_item_prices_name ) : 'Pass';?>">  
                            </div>
                            
                            <div class="col-sm-5">
                                <div class="form-group">
                                    <label><?php echo esc_attr($eventres_item_quantity_name );?></label>
                                    <?php if($eventres_item_quantity && count($eventres_item_quantity)): ?>
                                    <select class="form-control" name="item_quantity" required>
                                        <option value=""><?php _e('Choose...','cth-gather-plugins');?></option>
                                    <?php foreach ($eventres_item_quantity as $value) { ?>
                                        <option value="<?php echo esc_attr($value['value'] );?>"><?php echo esc_attr($value['name'] );?></option>
                                    <?php } ?>
                                    </select>
                                    <?php else : ?>
                                    <input type="text" name="item_quantity" value="1">
                                    <?php endif;?>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="checkbox">
                                <label>
                                <?php if($eventres_hide_terms == 'yes') :?>
                                    <input checked="checked" type="checkbox" name="agree" required style="display:none;">
                                <?php else :?>
                                    <input type="checkbox" name="agree" required>
                                <?php endif;?>
                                    <?php echo wp_kses_post( $eventres_terms_content );?>
                                </label>
                            </div>
                        </div>
                        
                        <div class="text-center top-space">
                            <button type="submit" id="reserve-btn" class="btn btn-success btn-lg"><?php echo esc_attr($button_title );?></button>
                            
                        </div>
                    </form>

                </div>
<?php if($layout == 'modal') :?>
            </div>
        </div>
    </div>
</div>
<?php endif;?>
