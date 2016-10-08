<?php
if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}

if(!function_exists('cth_create_opening_tag')){
	function cth_create_opening_tag($value) { 

	    echo "<tr valign=\"top\">\n";
	    echo '<th scope="row">';
	    if (isset($value['name'])) {
	        echo esc_attr($value['name'] ) . "\n";
	    }
	    if (isset($value['desc']) && !(isset($value['type']) && $value['type'] == 'checkbox')) {
	        echo "<br /><span class=\"desc\" style=\"font-size:90%;font-weight:400;\">".$value['desc']."</span>";
	    }
	    if (isset($value['note'])) {
	        echo "<br /><span class=\"note\" style=\"font-size:90%;font-weight:400;font-style:italic;\">".$value['note']."</span>";
	    }
	    echo '</th>';
	    echo '<td>';
	 }
}



if(!function_exists('cth_create_closing_tag')){
	/**
	 * Creates the closing markup for each option.
	 *
	 * @param $value
	 * @return void
	 */
	function cth_create_closing_tag($value) { 
	    // if (isset($value['grouping'])) {
	    //     echo "</div>\n";
	    // }
	    // //echo "</div><!-- suf-section -->\n";
	    // echo "</div>\n";
		if (isset($value['add_desc'])/* && !(isset($value['type']) && $value['type'] == 'checkbox')*/) {
	        echo "<br /><span class=\"add_desc\" style=\"font-size:100%;font-weight:400;\">".$value['add_desc']."</span>";
	    }
	    echo "</td>\n";
	    echo "</tr>\n";
	}
}



if(!function_exists('cth_create_suf_header_3')){
	function cth_create_suf_header_3($value) { 
	    echo "<tr valign=\"top\">\n";
	    echo '<th scope="row" colspan="2">';
	    if (isset($value['name'])) {
	        echo '<h4 class="section_header">'.esc_attr($value['name'] ) . "</h4>\n";
	    }
	    if (isset($value['desc']) && !(isset($value['type']) && $value['type'] == 'checkbox')) {
	        echo "<br /><span class=\"desc\" style=\"font-size:90%;font-weight:400;\">".$value['desc']."</span>";
	    }
	    if (isset($value['note'])) {
	        echo "<br /><span class=\"note\" style=\"font-size:90%;font-weight:400;font-style:italic;\">".$value['note']."</span>";
	    }
	    echo '</th>';
	    echo '</tr>';

	    //echo '<h3 class="suf-header-3">'.$value['name']."</h3>\n"; 
	}
}


if(!function_exists('cth_create_section_for_text')){
	function cth_create_section_for_text($value) { 
	    cth_create_opening_tag($value);
	    $text = "";
	    if (get_option($value['id']) === FALSE) {
	        $text = $value['std'];
	    }
	    else {
	        $text = get_option($value['id']);
	    }
	 
	    echo '<input type="text" id="'.$value['id'].'"';
	    if(isset($value['style'])&& !empty($value['style'])){
	        echo ' style="'.$value['style'].'"';
	    }
	    if(isset($value['required'])&& $value['required']){
	        echo ' required ';
	    }
	    echo ' placeholder="'.esc_attr($value['name'] ).'" name="'.$value['id'].'" value="'.$text.'" />'."\n";
	    cth_create_closing_tag($value);
	}
}


if(!function_exists('cth_create_section_for_textarea')){
	function cth_create_section_for_textarea($value) { 
	    cth_create_opening_tag($value);
	    echo '<textarea name="'.$value['id'].'" type="textarea" cols="'.esc_attr($value['cols'] ).'" rows="'.esc_attr($value['rows'] ).'"';
	    if(isset($value['style'])&& !empty($value['style'])){
	        echo ' style="'.$value['style'].'" ';
	    }
	    echo '>'."\n";
	    if ( get_option( $value['id'] ) != "") {
	        echo get_option( $value['id'] );
	    }
	    else {
	        echo $value['std'];
	    }
	    echo '</textarea>';
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_editor')){
	function cth_create_section_for_editor($value) { 
	    cth_create_opening_tag($value);

	    //echo '<textarea name="'.$value['id'].'" type="textarea" cols="'.esc_attr($value['cols'] ).'" rows="'.esc_attr($value['rows'] ).'">'."\n";
	    if ( get_option( $value['id'] ) != "") {
	        $e_content = get_option( $value['id'] );
	    }
	    else {
	        $e_content = $value['std'];
	    }
	    /**
	     * 2.
	     * This code renders an editor box and a submit button.
	     * The box will have 15 rows, the quicktags won't load
	     * and the PressThis configuration is used.
	     */
	    $args = array(
	        'textarea_rows' => esc_attr($value['rows'] ),
	        'teeny' => true,
	        'quicktags' => true
	    );
	     
	    wp_editor( $e_content, $value['id'], $args );
	    //submit_button( 'Save content' );

	    //echo '</textarea>';
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_ace_editor')){
	function cth_create_section_for_ace_editor($value) {
	    cth_create_opening_tag($value);
	    if (!isset($value['ace_mode'])) {
	        $value['ace_mode'] = 'html';
	    }
	    if (isset($value['ace_style'])) {
	        $ace_style = 'style="' . $value['ace_style'] . '"';
	    } 
	    else {
	        $ace_style = 'style="min-height:300px;border:1px solid #bbb;"';
	    }
	    if ( get_option( $value['id'] ) != "") {
	        $e_content = get_option( $value['id'] );
	    }
	    else {
	        $e_content = $value['std'];
	    }

	    echo '<div id="'.$value['id'].'" ' . $ace_style . '></div>';

	    echo '<input name="' . esc_attr($value['id']) . '" class="cth_ace_field" type="hidden" value="' . esc_attr($e_content) . '" />';
	    echo '<script src="' . CTH_EVENTRES_DIR_URL . 'assets/js/ace/src-min-noconflict/ace.js" type="text/javascript" charset="utf-8"></script>';
	    echo '<script src="' . CTH_EVENTRES_DIR_URL . 'assets/js/ace/src-min-noconflict/mode-' . esc_attr($value['ace_mode']) . '.js" type="text/javascript" charset="utf-8"></script>';
	    echo '<script>';
	        echo 'var '.$value['id'].' = ace.edit("'.$value['id'].'");';
	        echo $value['id'].'.getSession().setMode("ace/mode/' . esc_attr($value['ace_mode']) . '");';
	        echo $value['id'].'.setValue( jQuery("#'.$value['id'].'").next(".cth_ace_field").val() );';
	        echo $value['id'].'.getSession().on("change", function(e) {';
	                echo 'jQuery("#'.$value['id'].'").next(".cth_ace_field").val( '.$value['id'].'.getValue() );';
	        echo '});';
	    echo '</script>';
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_color_picker')){
	function cth_create_section_for_color_picker($value) { 
	    cth_create_opening_tag($value);
	    $color_value = "";
	    if (get_option($value['id']) === FALSE) {
	        $color_value = $value['std'];
	    }
	    else {
	        $color_value = get_option($value['id']);
	    }
	 
	    echo '<div class="color-picker">'."\n";
	    echo '<input type="text" id="'.$value['id'].'" name="'.$value['id'].'" value="'.$color_value.'" class="color" />';
	    echo ' « Click to select color<br/>'."\n";
	    echo "<strong>Default: <font color='".$value['std']."'> ".$value['std']."</font></strong>";
	    echo " (You can copy and paste this into the box above)\n";
	    echo "</div>\n";
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_radio')){
	function cth_create_section_for_radio($value) { 
	    cth_create_opening_tag($value);
	    foreach ($value['options'] as $option_value => $option_text) {
	        $checked = ' ';
	        if (get_option($value['id']) == $option_value) {
	            $checked = ' checked="checked" ';
	        }
	        else if (get_option($value['id']) === FALSE && $value['std'] == $option_value){
	            $checked = ' checked="checked" ';
	        }
	        else {
	            $checked = ' ';
	        }
	        echo '<div class="mnt-radio" style="display:inline-block;padding:0 10px 0 0;"><input type="radio" name="'.$value['id'].'" value="'.
	            $option_value.'" '.$checked."/>".$option_text."</div>\n";
	    }
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_checkbox')){
	function cth_create_section_for_checkbox($value) { 
	    cth_create_opening_tag($value);
	    $checked = '';

	    //echo '<pre>';var_dump(get_option($value['id'] ));die;

	    if(get_option($value['id']) === $value['value']){
	    	$checked = ' checked="checked" ';
	    }
	    // elseif($value['std'] == 'checked'){
	    // 	$checked = ' checked="checked" ';
	    // }

	    echo '<div><input type="checkbox" name="'.$value['id'].'" value="'.$value['value'].'" '.$checked."/> ".$value['desc'].'</div>';


	    // foreach ($value['options'] as $option_value => $option_text) {
	    //     $checked = ' ';
	    //     if (get_option($value['id']) == $option_value) {
	    //         $checked = ' checked="checked" ';
	    //     }
	    //     else if (get_option($value['id']) === FALSE && $value['std'] == $option_value){
	    //         $checked = ' checked="checked" ';
	    //     }
	    //     else {
	    //         $checked = ' ';
	    //     }
	    //     echo '<div class="mnt-radio" style="display:inline-block;padding:0 10px 0 0;"><input type="radio" name="'.$value['id'].'" value="'.
	    //         $option_value.'" '.$checked."/>".$option_text."</div>\n";
	    // }
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_multi_select')){
	function cth_create_section_for_multi_select($value) { 
	    cth_create_opening_tag($value);
	    echo '<ul class="mnt-checklist" id="'.$value['id'].'" >'."\n";
	    foreach ($value['options'] as $option_value => $option_list) {
	        $checked = " ";
	        if (get_option($value['id']."_".$option_value)) {
	            $checked = " checked='checked' ";
	        }
	        echo "<li>\n";
	        echo '<input type="checkbox" name="'.$value['id']."_".$option_value.'" value="true" '.$checked.' class="depth-'.($option_list['depth']+1).'" />'.$option_list['title']."\n";
	        echo "</li>\n";
	    }
	    echo "</ul>\n";
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_select')){
	function cth_create_section_for_select($value) { 
	    cth_create_opening_tag($value);
	        echo "<select id='".$value['id']."' class='post_form' name='".$value['id']."'>\n";
	            
	            foreach ($value['options'] as $option_value => $option_list) {
	                $selected = ' ';
	                
		            if (get_option($value['id']) == $option_value) {
		                $selected = ' selected="selected" ';
		            }
		            else if (get_option($value['id']) === FALSE && $value['std'] == $option_value){
		                $selected = ' selected="selected" ';
		            }
		    
	                echo '<option value="'.$option_value.'" '.$selected.'/>'.$option_list."</option>\n";
	            }   
	        echo "</select>\n </div>";
	    cth_create_closing_tag($value);
	}
}

if(!function_exists('cth_create_section_for_repeatable')){
	function cth_create_section_for_repeatable($value) { 
	    cth_create_opening_tag($value);
	    $repeat_vs = get_option($value['id']);
	    
	    	echo '
	    	<table class="repeatable_table">
	    		<tr>
	    			<th>Option Name</th>
	    			<th>Option Value</th>
	    			<th></th>

	    		</tr>';
	    	if($repeat_vs && !empty($repeat_vs)){
	    		foreach ($repeat_vs as $key => $val) {
	    			echo '<tr data-key="'.$key.'">';
	    			if(isset($val['name'])){
	    				echo '<td><input type="text" name="'.$value['id'].'['.$key.'][name]" value="'.$val['name'].'"></td>';
	    			}
	    			if(isset($val['value'])){
	    				echo '<td><input type="text" name="'.$value['id'].'['.$key.'][value]" value="'.$val['value'].'"></td>';
	    			}
	    			echo '<td><a href="#" class="repeatable_remove_option">Remove</a></td></tr>';
	    		}
	    	}else{
	    		echo 	'<tr data-key="0">
			    			<td><input type="text" name="'.$value['id'].'[0][name]"></td>
			    			<td><input type="text" name="'.$value['id'].'[0][value]"></td>
		    			</tr>';
	    	}
	    	//echo'<pre>';var_dump($value['fields']);die;

	    	echo '<tr><td><a href="#" class="repeatable_add_option" data-name="'.$value['id'].'" data-fields="'/*.rawurlencode(json_encode($value['fields']))*/.'">Add new option</a><td><td></td></tr>';
	    		
	    	echo '</table>';
	    	
	        // echo "<select id='".$value['id']."' class='post_form' name='".$value['id']."'>\n";
	            
	        //     foreach ($value['options'] as $option_value => $option_list) {
	        //         $selected = ' ';
	                
		       //      if (get_option($value['id']) == $option_value) {
		       //          $selected = ' selected="selected" ';
		       //      }
		       //      else if (get_option($value['id']) === FALSE && $value['std'] == $option_value){
		       //          $selected = ' selected="selected" ';
		       //      }
		    
	        //         echo '<option value="'.$option_value.'" '.$selected.'/>'.$option_list."</option>\n";
	        //     }   
	        // echo "</select>\n </div>";
	    cth_create_closing_tag($value);
	}
}
if(!function_exists('cth_create_section_for_list_pages_select')){
	function cth_create_section_for_list_pages_select($value) { 
		$all_page_ids = get_all_page_ids();
	    cth_create_opening_tag($value);
	    if(!empty($all_page_ids)){
	    echo "<select id='".$value['id']."' class='post_form' name='".$value['id']."'>\n";
	    	foreach ($all_page_ids as $key => $p_id) {
	    		$p_p = get_post($p_id);
	    		if($p_p->post_status == 'publish'){
	    			$selected = ' ';
	                
		            if (get_option($value['id']) == $p_id) {
		                $selected = ' selected="selected" ';
		            }
		            else if (get_option($value['id']) === FALSE && $value['default_title'] == $p_p->post_title){
		                $selected = ' selected="selected" ';
		            }
		    
	                echo '<option value="'.$p_id.'" '.$selected.'/>'.$p_p->post_title."</option>\n";
	    		}
	    		
	    	}
	    echo "</select>\n </div>";
	    } 
	        
	    cth_create_closing_tag($value);
	}
}