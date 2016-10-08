<?php
/*
+----------------------------------------------------------------+
|																 |
|	WordPress 2.8 Plugin: WP-PhotoContest Widget 1.0			 |
|	Copyright (c) 2009 Krzysztof Jurkowski						 |
|																 |
|	File Written By:											 |
|	- Krzysztof Jurkowski										 |
|																 |
|	File Information:											 |
|	- A widget with thumbnails of recently added photos			 |
|																 |
+----------------------------------------------------------------+
*/

/**
 * Registers WP-PhotoContest widget.
 *
 */
function wp_photocontest_load_widget() {
	if (class_exists('WP_Widget')) {
		register_widget( 'WP_Photocontest_Widget' );
	}
}

/**
 * WP-PhotoContest widget class.
 */
if (class_exists('WP_Widget')) {
	class WP_Photocontest_Widget extends WP_Widget {
	
		/**
		 * Widget setup.
		 */
		function WP_Photocontest_Widget() {
			/* Widget settings. */
			$widget_ops = array( 'classname' => 'WP_Photocontest_Widget', 'description' => __('WP PhotoContest widget', 'wp-photocontest'));
	
			/* Widget control settings. */
			$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'wp-photocontest-widget' );
	
			/* Create the widget. */
			$this->WP_Widget( 'wp-photocontest-widget', __('WP PhotoContest widget', 'wp-photocontest'), $widget_ops, $control_ops );
			
			/* Get a database link. */
			global $wpdb;
			if ( defined('ABSPATH') )
			{
				require_once( ABSPATH . 'wp-config.php');
			}
			else
			{
				require_once('../../../wp-config.php');
				require_once('../../../wp-includes/wp-db.php');
			}
			$this->db = $wpdb;		
		}
	
		/**
		 * How to display the widget on the screen.
		 */
		function widget( $args, $instance ) {
			// link the stylesheet
			?>
			<link id="wp-photocontest-style-css" media="all" type="text/css" href="<?php echo WP_PLUGIN_URL;?>/wp-photocontest/skins/<?php echo CONTESTS_SKIN;?>/theme.css?ver=2.8.4" rel="stylesheet">
			<?php
			extract( $args );
	
			/* Variables from the widget settings. */
			$title = apply_filters('widget_title', $instance['title'] );
			$photo_contest_id = $instance['photo_contest_id'];
			$contest_url = $instance['contest_url'];
			$contest_sort = $instance['contest_sort'];
			$contest_url_user = $instance['contest_url_user'];
			$photo_number = $instance['photo_number'];
							
			/* Before widget (defined by themes). */
			echo $before_widget;
	
			/* Display the widget title if one was input (before and after defined by themes). */
			if ( $title )
				echo $before_title . $title . $after_title;
				
			$wppc_prp = new photoContest();
			$page_data = $wppc_prp->get_page_data($photo_contest_id, 0, 0, $photo_number, $contest_sort);
	
			?>
			<div class="widget_thumbs_container">
			<?php
			
			foreach ($page_data as $page_row)
			{
				switch ($contest_url)
				{
					case 'no_url':
						?>
						<div id="photo_<?php echo $page_row['img_id'];?>" class="widget_thumb_container">
							<img src="<?php echo $page_row['pre_thumb'];?>" class="widget_thumb" />
						</div>
						<?php				
					break;
					case 'view':
						?>
						<div id="photo_<?php echo $page_row['img_id'];?>" class="widget_thumb_container">
							<a href="<?php echo WP_PLUGIN_URL;?>/wp-photocontest/view.php?post_id=<?php echo $page_row['post_id'];?>" class="widget_thumb_link"><img src="<?php echo $page_row['pre_thumb'];?>" class="widget_thumb" /></a>
						</div>
						<?php				
					break;
	
					case 'viewimg';
						?>
						<div id="photo_<?php echo $page_row['img_id'];?>" class="widget_thumb_container">
							<a href="<?php echo WP_PLUGIN_URL;?>/wp-photocontest/viewimg.php?post_id=<?php echo $page_row['post_id'];?>&img_id=<?php echo $page_row['img_id'];?>" class="widget_thumb_link"><img src="<?php echo $page_row['pre_thumb'];?>" class="widget_thumb" /></a>
						</div>
						<?php				
					break;
					
					default:
						if (!empty($contest_url_user))
						{
							?>
							<div id="photo_<?php echo $page_row['img_id'];?>" class="widget_thumb_container">
								<a href="<?php echo $contest_url_user;?>" class="widget_thumb_link"><img src="<?php echo $page_row['pre_thumb'];?>" class="widget_thumb" /></a>
							</div>
							<?php				
						}
						else
						{
							?>
							<div id="photo_<?php echo $page_row['img_id'];?>" class="widget_thumb_container">
								<img src="<?php echo $page_row['pre_thumb'];?>" class="widget_thumb" />
							</div>
							<?php						
						}
					break;
				}
			}
	
			?>
			</div>
			<?php
			
			/* After widget (defined by themes). */
			echo $after_widget;
		}
	
		/**
		 * Update the widget settings.
		 */
		function update( $new_instance, $old_instance ) {
			$instance = $old_instance;
	
			/* Strip tags for title and name to remove HTML (important for text inputs). */
			$instance['title'] = strip_tags( $new_instance['title'] );
			$instance['photo_contest_id'] = $new_instance['photo_contest_id'];
			$instance['contest_url'] = $new_instance['contest_url'];
			$instance['contest_sort'] = $new_instance['contest_sort'];			
			$instance['contest_url_user'] = $new_instance['contest_url_user'];		
			$instance['photo_number'] = $new_instance['photo_number'];
	
			return $instance;
		}
	
		/**
		 * Displays the widget settings controls on the widget panel.
		 */
		function form( $instance ) {
	
			/* Set up some default widget settings. */
			$defaults = array( 'title' => __('Recent', 'wp-photocontest'), 'photo_contest_id' => '', 'contest_url' =>  '', 'contest_url_user' =>  '', 'photo_number' =>  '', 'contest_sort' =>  'recent' );
			$instance = wp_parse_args( (array) $instance, $defaults ); 
			?>
			<p>
				<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'hybrid'); ?>:</label>
				<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
			</p>
	
			<p>
				<label for="<?php echo $this->get_field_id( 'photo_contest_id' ); ?>"><?php _e('Contest', 'wp-photocontest'); ?>:</label>
				<?php
				$contest_array_values = $this->contest_details();
				$selectstring = '<select name="'.$this->get_field_name( 'photo_contest_id' ).'" style="width:100%;" >';
				foreach ($contest_array_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($instance['photo_contest_id'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>			
			</p>
	
			<p>
				<label for="<?php echo $this->get_field_id( 'contest_sort' ); ?>"><?php _e('Sort', 'wp-photocontest'); ?>:</label>
				<?php
				$contest_url_values = array("chrono"=>"chrono","most_voted"=>"most_voted","most_viewed"=>"most_viewed","recent"=>"recent");
				$selectstring = '<select name="'.$this->get_field_name( 'contest_sort' ).'" style="width:100%;" >';
				foreach ($contest_url_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($instance['contest_sort'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
			</p>
			
			<p>
				<label for="<?php echo $this->get_field_id( 'contest_url' ); ?>"><?php _e('URL', 'wp-photocontest'); ?>:</label>
				<?php
				$contest_url_values = array("no_url"=>"Not clickable","view"=>"Contest overview","viewimg"=>"Image details",""=>"User defined:");
				$selectstring = '<select name="'.$this->get_field_name( 'contest_url' ).'" style="width:100%;" >';
				foreach ($contest_url_values as $status_k=>$status_v)
				{
					$_selected = '';
					if ($instance['contest_url'] == $status_k)
					{
						$_selected = ' selected="selected"';
					}
					$selectstring .= '<option value="'.$status_k.'" '.$_selected.'>'.$status_v.'</option>';
				}
				$selectstring .= '</select>';
				echo $selectstring;
				unset($selectstring);
				?>
				or 		
				<input id="<?php echo $this->get_field_id( 'contest_url_user' ); ?>" name="<?php echo $this->get_field_name( 'contest_url_user' ); ?>" value="<?php echo $instance['contest_url_user']; ?>" style="width:100%;" />
			</p>
						
			<p>
				<label for="<?php echo $this->get_field_id( 'photo_number' ); ?>"><?php _e('Number of pictures', 'wp-photocontest'); ?>:</label>
				<input id="<?php echo $this->get_field_id( 'photo_number' ); ?>" name="<?php echo $this->get_field_name( 'photo_number' ); ?>" value="<?php echo $instance['photo_number']; ?>" style="width:100%;" />
			</p>
	
		<?php
		}
	
		/**
		 * Returns all current contests.
		 */
		function contest_details($contest_id=false) {
	
			$return_array = array();
			$pr_qlist = "SELECT contest_id, contest_name FROM ".$this->db->prefix."photocontest_admin";
			
			if ($contest_id)
			{
				$pr_qlist .= " WHERE contest_id=".$contest_id;
			}
			
			$out		= (array) $this->db->get_results( $this->db->prepare($pr_qlist ) );
			foreach ($out as $out_object)
			{
				$return_array[$out_object->contest_id]=$out_object->contest_name;
			} 
			
			return $return_array;
			
		
		}
	
	}
}
?>