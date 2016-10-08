<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $cat_ids
 * @var $cat_order_by
 * @var $cat_order
 * @var $showtabs
 * @var $order
 * @var $order_by
 * @var $ids
 * @var $effect
 * Shortcode class
 * @var $this WPBakeryShortCode_Cth_Gather_Gallery
 */
$atts = vc_map_get_attributes( $this->getShortcode(), $atts );
extract( $atts );
?>
<?php 
    if(!empty($cat_ids)){
        $term_args = array(
            'orderby'           => $cat_order_by, 
            'order'             => $cat_order,
            'exclude'           => $cat_ids,
            'parent'            => 0,
            
        ); 
    }else{
        $term_args = array(
            'orderby'           => $cat_order_by, 
            'order'             => $cat_order,
            'parent' => 0,
            
        ); 
    }
    $gallery_cats = get_terms('cth_gallery_cat',$term_args); 

?>
<?php if(count($gallery_cats)): ?> 

    <?php if($showtabs == 'yes') :?>
        <div class="nav-center bottom-space-lg">
            <!-- Nav tabs -->
            <ul class="nav nav-pills" role="tablist">
            <?php foreach($gallery_cats as $key => $gallery_cat) { ?>
                <li role="presentation"<?php if($key == 0) echo ' class="active"';?>><a href="#<?php echo sanitize_title($gallery_cat->name );?>" aria-controls="<?php echo sanitize_title($gallery_cat->name );?>" role="tab" data-toggle="tab"><?php echo esc_html($gallery_cat->name ); ?></a></li>
            <?php } ?>
            </ul>
        </div>
    <?php endif;?>
    <?php if($showtabs == 'yes') :?>
        <div class="tab-content">
    <?php else :?>
        <div class="popup-gallery">
            <div class="row">
    <?php endif;?>
    <?php foreach($gallery_cats as $key => $gallery_cat) : ?>
        <?php if($showtabs == 'yes') :?>
            <div role="tabpanel" class="tab-pane<?php if($key == 0) echo ' active';?>" id="<?php echo sanitize_title($gallery_cat->name );?>">
                <div class="popup-gallery">
                    <div class="row">
        <?php endif;?>

        <?php
            if(!empty($ids)){
                $ids = explode(",", $ids);
                $args = array(
                    'post_type' => 'cth_gallery',
                    'posts_per_page'=> -1,
                    'post__in' => $ids,
                    'order_by'=> $order_by,
                    'order'=> $order,
                );
            }else{
                $args = array(
                    'post_type' => 'cth_gallery',
                    'posts_per_page'=> -1,
                    'order_by'=> $order_by,
                    'order'=> $order,
                );
            }
            
            $args['tax_query'][] = array(
                'taxonomy' => 'cth_gallery_cat',
                'field' => 'term_id',
                'terms' => $gallery_cat->term_id,
                'include_children'=> true
            );

        ?>

        <?php

        $galleries = new WP_Query($args);

        if($galleries->have_posts()) : ?>
        <!-- <div class="popup-gallery">
            <div class="row"> -->
        <?php while($galleries->have_posts()) : $galleries->the_post(); ?>
            <?php 
                $gallery_type = get_post_meta(get_the_ID(),'cth_gallery_type' , true );
                $gallery_image = get_post_meta(get_the_ID(),'cth_gallery_image' , true );
                $grid_size = get_post_meta(get_the_ID(),'cth_gallery_image_grid_size' , true );
                $ani_delay = get_post_meta(get_the_ID(),'cth_gallery_ani_delay' , true );
            ?>
                <div class="gallery-col grid_<?php echo esc_attr($grid_size );?>">
                <?php if($gallery_type == 'single_image') :?>
                    <a href="<?php echo esc_url($gallery_image['url'] );?>" title="<?php the_title( );?>">
                <?php else : $video_link = get_post_meta(get_the_ID(),'cth_gallery_video_link' , true );?>
                    <a class="popup-video" href="<?php echo esc_url($video_link );?>" title="<?php the_title( );?>">
                        <span class="video-overlay"></span>
                        <i class="fa fa-play-circle fa-2x"></i>
                <?php endif;?>
                        <?php
                        
                        if(!empty($ani_delay)){
                            $thmb_args = array('class'=>'img-responsive wow '.$effect);
                            $thmb_args['data-wow-delay'] = $ani_delay;
                        }else{
                            $thmb_args = array('class'=>'img-responsive');
                        } ?>
                        <?php echo wp_get_attachment_image( $gallery_image['id'] , 'gallery_'.$grid_size, false, $thmb_args ); ?>
                        

                    </a>
                </div>
        <?php endwhile;?>
            <!-- </div> -->
            <!-- end .row -->
        <!-- </div> -->
        <!-- end .popup-gallery -->
        <?php 
        endif;
        ?>

        <?php wp_reset_postdata();?>
        <?php if($showtabs == 'yes') :?>
                    </div>
                    <!-- end .row -->
                </div>
                <!-- end .popup-gallery -->
            </div><!-- end .tab-pane -->
        <?php endif;?>
    <?php endforeach ;?>
    <?php if($showtabs == 'yes') :?>
        </div><!-- end .tab-content -->
    <?php else :?>
            </div>
            <!-- end .row -->
        </div>
        <!-- end .popup-gallery -->
    <?php endif;?>
<?php endif ;?>
