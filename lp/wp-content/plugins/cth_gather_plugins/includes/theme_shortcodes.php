<?php
if ( ! defined('ABSPATH') ) {
    die('Please do not load this file directly!');
}
add_shortcode('gallery', '__return_false');
if(!function_exists('faicon_sc')) {

	function faicon_sc( $atts, $content="" ) {
	
		extract(shortcode_atts(array(
			   'name' =>"magic",
			   'class'=>'',
		 ), $atts));

		$name = str_replace("fa fa-", "", $name);
		$name = str_replace("fa-", "", $name);

		$classes = 'fa fa-'.$name;
		if(!empty($class)){
			$classes .= ' '.$class;
		}
		
		return '<i class="'.$classes.'"></i>'. $content;
	 
	}
		
	add_shortcode( 'faicon', 'faicon_sc' ); //Icon
}

if (!function_exists('row_sc')) {
	$columnArray = array();

	function row_sc( $atts, $content="" ){
		global $columnArray;
		$id = '';
		$params = shortcode_atts(array(
			  'id' => '',
			  'class' => '',
		 ), $atts);
		
		if ($params['id']) 
			$id = 'id="' . $params['id'] . '"'; 
		$class = 'row';
		if(!empty($params['class'])){
			$class .= ' '.$params['class'];
		}

		$class = 'class="'.$class.'"';
		
		do_shortcode( $content );
		
		//Row
		$html = '<div '. $class . ' ' . $id . '>';
		//Columns
		foreach ($columnArray as $key=>$col){
			if(!empty($col['class'])){
				$class = $col['class'];
			}else{
				$class = 'col-md-12';
			}

			$class = 'class="'.$class.'"';

			$html .='<div ' . $class . '>' . do_shortcode($col['content']) . '</div>';
		}

		$html .='</div>';
	
		$columnArray = array();	
		return $html;
	}
	
	add_shortcode( 'row', 'row_sc' );
		
	//Column Items
	function column_sc( $atts, $content="" ){
		global $columnArray;

		if(is_array($atts)){
			$class = isset($atts['class']) ? $atts['class'] : 'col-md-12';
			
		}else{
			$class = 'col-md-12';
			
		}


		$columnArray[] = array(
			'class'=>$class,
			'content'=> $content
		);
	}

	add_shortcode( 'column', 'column_sc' );	 //Row
}
if (!function_exists('popup_gallery_sc')) {
	$galleryArray = array();

	function popup_gallery_sc( $atts, $content="" ){
		global $galleryArray;
		
		do_shortcode( $content );
		
		//Gallery
		ob_start();
		?>
		<ul class="inline-gallery popup-gallery">
		<?php 
		//Gallery Item
		foreach ($galleryArray as $key=>$gal){ ?>
            <li>
                <div class="box-item">
                    <a href="<?php echo esc_url($gal['content'] );?>">
	                    <span class="overlay"></span>
	                    <img src="<?php echo esc_url($gal['content'] );?>" alt="" class="res-image">
                    </a>
                </div>
            </li>
        <?php } ?>  
        </ul>
		
		<?php 
		

		$galleryArray = array();	
		return ob_get_clean();
	}
	
	add_shortcode( 'popup_gallery', 'popup_gallery_sc' );
		
	//Gallery Items
	function popup_gallery_item_sc( $atts, $content="" ){
		global $galleryArray;

		$galleryArray[] = array(
			'content'=> $content
		);
	}

	add_shortcode( 'popup_gallery_item', 'popup_gallery_item_sc' );	 //gallery item
}

if (!function_exists('cth_gallery_sc')) {
	$cthGalleryArray = array();

	function cth_gallery_sc( $atts, $content="" ){
		global $cthGalleryArray;

		$params = shortcode_atts(array(
			  'padding' => 'true',
			  'columns' => 'three-coulms',
			  'showfilter' => 'true',
			  'el_class' => '',
		), $atts);

		
		do_shortcode( $content );

		if($params['showfilter'] === 'true') {
		    $filter_tags = array();
		    foreach ($cthGalleryArray as $key => $gl) {
		        if($gl['filter']){
		            $filter_tags = array_merge($filter_tags,array_map('trim', explode(",", trim($gl['filter'])) ));
		        }
		    }
		    $filter_tags = array_unique($filter_tags);
		}

		
		//Gallery
		ob_start();
		?>
			<?php if($params['showfilter'] === 'true') :?>
			<div class="gallery-filters">
	            <a href="#" class="gallery-filter gallery-filter-active"  data-filter="*"><?php _e('All','gather');?></a>		
	           	<?php foreach ($filter_tags as $key => $tag) { ?>
		        <a href="#" class="gallery-filter " data-filter=".<?php echo esc_attr(str_replace(" ", "-", strtolower($tag)) );?>"><?php echo esc_attr($tag );?> </a>
		        <?php } ?>
	        </div>
	        <div class="bold-separator"><span>circle</span></div>
		    <?php endif;?>

			
	        
	        <div class="row">
	            <div class="col-md-12">
	            <?php
	            if($params['padding'] === 'true') {
	            	echo '<div class="gallery-items-wrapper">';
	            } ?>

	                <div class="gallery-items <?php echo esc_attr($params['columns'] );?><?php echo ($params['padding'] === 'true')? ' grid-small-pad': ' grid-no-pad';?> popup-gallery">
	                    <?php 
						//Gallery Item
						foreach ($cthGalleryArray as $key=>$gl): ?>
	                    
	                    <div class="gallery-item <?php echo esc_attr(str_replace(array(" ",","), array("-"," "), strtolower($gl['filter'])) );?> <?php echo esc_attr($gl['el_class'] );?>">
	                        <div class="grid-item-holder">
	                            <div class="box-item">
	                            <?php if($gl['popup'] === 'image-popup') :?>
	                            	<?php if(!empty($gl['large'])) :?>
	                                <a href="<?php echo wp_get_attachment_url($gl['large'] );?>" title="<?php echo esc_attr($gl['title'] );?>">
	                                <?php else :?>
	                                <a href="<?php echo wp_get_attachment_url($gl['thumb'] );?>" title="<?php echo esc_attr($gl['title'] );?>">
	                                <?php endif;?>
	                                	<span class="overlay"></span>
	                                	<i class="fa fa-search"></i>

	                            <?php elseif($gl['popup'] === 'popup-youtube') : ?>
									<a href="<?php echo esc_url($gl['video_link'] );?>" class="popup-youtube" title="<?php echo esc_attr($gl['title'] );?>">
                                        <span class="overlay"></span> 
                                        <i class="fa fa-play-circle"></i>

	                            <?php elseif($gl['popup'] === 'popup-vimeo') :?>
									<a href="<?php echo esc_url($gl['video_link'] );?>" class="popup-vimeo" title="<?php echo esc_attr($gl['title'] );?>">
                                        <span class="overlay"></span> 
                                        <i class="fa fa-play-circle"></i>

	                            <?php endif;?>
	                                	<?php echo wp_get_attachment_image($gl['thumb'],'full');?>
	                                
	                                </a>
	                            </div>
	                        </div>
	                    </div>

	                    <?php 
	                    endforeach; ?>
	                </div>
	                <!-- end gallery items -->	
	            <?php
	            if($params['padding'] === 'true') {
	            	echo '</div>';
	            } ?>
	            </div>
	        </div><!-- end .row -->
		<?php 
		

		$cthGalleryArray = array();	
		return ob_get_clean();
	}
	
	add_shortcode( 'cth_gallery', 'cth_gallery_sc' );
		
	//Gallery Items
	function cth_gallery_item_sc( $atts, $content="" ){
		global $cthGalleryArray;

		extract(shortcode_atts(array(
			   'title' =>"",
			   'thumb' =>"",
			   'filter'=>'',
			   'popup'=>'image-popup',
			   'large'=>'',
			   'gallery_imgs'=>'',
			   'video_link'=>'',
			   'el_class'=>'',
		), $atts));


		$cthGalleryArray[] = array(
			'title'=> $title,
			'thumb'=> $thumb,
			'filter'=> $filter,
			'popup'=> $popup,
			'large'=> $large,
			'gallery_imgs'=> $gallery_imgs,
			'video_link'=> $video_link,
			'el_class'=> $el_class,
			'content'=> $content,
		);
	}

	add_shortcode( 'cth_gallery_item', 'cth_gallery_item_sc' );	 //gallery item
}
