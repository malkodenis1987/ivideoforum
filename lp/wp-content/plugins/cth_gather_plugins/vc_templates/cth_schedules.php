<?php
/**
 * Shortcode attributes
 * @var $atts
 * @var $el_class
 * @var $cat_ids
 * @var $cat_order_by
 * @var $cat_order
 * @var $order
 * @var $order_by
 * @var $ids
 * Shortcode class
 * @var $this WPBakeryShortCode_Cth_Schedules
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
    $schedule_cats = get_terms('cth_schedule_cat',$term_args); 

?>
<?php 
    if(count($schedule_cats)): ?>               
    <div class="nav-center">
        <!-- Nav tabs -->
        <ul class="nav nav-pills" role="tablist">
        <?php foreach($schedule_cats as $key => $schedule_cat) { ?>
            <li role="presentation"<?php if($key == 0) echo ' class="active"';?>><a href="#<?php echo sanitize_title($schedule_cat->name );?>" aria-controls="<?php echo sanitize_title($schedule_cat->name );?>" role="tab" data-toggle="tab"><?php echo esc_html($schedule_cat->name ); ?></a></li>
        <?php } ?>
        </ul>
    </div>
    <!-- Tab panes -->
    <div class="tab-content">
    <?php foreach($schedule_cats as $key => $schedule_cat) { //echo'<pre>';var_dump($schedule_cat);die;?>
    <div role="tabpanel" class="tab-pane<?php if($key == 0) echo ' active';?>" id="<?php echo sanitize_title($schedule_cat->name );?>">
        <?php
        $child_term_args = array(
            'orderby'           => $cat_order_by, 
            'order'             => $cat_order,

            'parent' => $schedule_cat->term_id,
            
        ); 
        $child_schedule_cats = get_terms('cth_schedule_cat',$child_term_args); 


        ?>
        <?php 
            if(count($child_schedule_cats)): ?>               
            <div class="nav-center">
                <!-- Nav tabs -->
                <ul class="nav nav-pills" role="tablist">
                <?php foreach($child_schedule_cats as $key => $child_schedule_cat) { ?>
                    <li role="presentation"<?php if($key == 0) echo ' class="active"';?>><a href="#<?php echo sanitize_title($child_schedule_cat->name );?>" aria-controls="<?php echo sanitize_title($child_schedule_cat->name );?>" role="tab" data-toggle="tab"><?php echo esc_html($child_schedule_cat->name ); ?></a></li>
                <?php } ?>
                </ul>
            </div>
            <!-- Tab panes -->
            <div class="tab-content">
            <?php foreach($child_schedule_cats as $key => $child_schedule_cat) { //echo'<pre>';var_dump($schedule_cat);die;?>
            <div role="tabpanel" class="tab-pane<?php if($key == 0) echo ' active';?>" id="<?php echo sanitize_title($child_schedule_cat->name );?>">
                <?php
                if(!empty($ids)){
                    $ids = explode(",", $ids);
                    $args = array(
                        'post_type' => 'cth_schedule',
                        'posts_per_page'=> -1,
                        'post__in' => $ids,
                        'order_by'=> $order_by,
                        'order'=> $order,
                    );
                }else{
                    $args = array(
                        'post_type' => 'cth_schedule',
                        'posts_per_page'=> -1,
                        'order_by'=> $order_by,
                        'order'=> $order,
                    );
                }
                
                $args['tax_query'][] = array(
                    'taxonomy' => 'cth_schedule_cat',
                    'field' => 'term_id',
                    'terms' => $child_schedule_cat->term_id,
                    'include_children'=> false
                );
                

                $schedules = new WP_Query($args);

                if($schedules->have_posts()) : ?>
                <section class="timeline">
                <?php
                while($schedules->have_posts()) : $schedules->the_post(); ?>
                    <?php 
                        $schedule_time = get_post_meta(get_the_ID(),'cth_schedule_time' , true );
                        $schedule_speaker = get_post_meta(get_the_ID(),'cth_schedule_speaker' , true );
                    ?>
                    <?php //get_template_part( 'portfolio', 'list' ); ?>
                    <div class="timeline-block">
                        <div class="timeline-bullet wow zoomIn" data-wow-delay="0s">
                        </div>
                        <!-- timeline-bullet -->
                        <div class="timeline-content">
                            <h2 class="wow flipInX" data-wow-delay="0.3s"><?php the_title( );?></h2>
                        <?php if($schedule_speaker) :?>
                            <p class="wow flipInX" data-wow-delay="0.3s"><?php _e('by ','gather'); echo esc_attr($schedule_speaker );?></p>
                        <?php endif;?>
                        <?php the_content( 'Read more...' );?>
                        <?php if($schedule_time) :?>
                            <span class="date wow flipInX" data-wow-delay="0.3s"><?php echo esc_attr($schedule_time );?></span>
                        <?php endif;?>
                        </div>
                        <!-- timeline-content -->
                    </div>

                <?php endwhile;?>
                </section>
                <?php 
                endif;


                ?>

                <?php wp_reset_postdata();?>

            </div>
            <?php } ?>
            </div>
        <?php else : ?>
            
            <?php
            if(!empty($ids)){
                $ids = explode(",", $ids);
                $args = array(
                    'post_type' => 'cth_schedule',
                    'posts_per_page'=> -1,
                    'post__in' => $ids,
                    'order_by'=> $order_by,
                    'order'=> $order,
                );
            }else{
                $args = array(
                    'post_type' => 'cth_schedule',
                    'posts_per_page'=> -1,
                    'order_by'=> $order_by,
                    'order'=> $order,
                );
            }
            
            $args['tax_query'][] = array(
                'taxonomy' => 'cth_schedule_cat',
                'field' => 'term_id',
                'terms' => $schedule_cat->term_id,
                'include_children'=> false
            );
            

            $schedules = new WP_Query($args);

            if($schedules->have_posts()) : ?>
            <section class="timeline">
            <?php
            while($schedules->have_posts()) : $schedules->the_post(); ?>
                <?php 
                    $schedule_time = get_post_meta(get_the_ID(),'cth_schedule_time' , true );
                    $schedule_speaker = get_post_meta(get_the_ID(),'cth_schedule_speaker' , true );
                ?>
                <?php //get_template_part( 'portfolio', 'list' ); ?>
                <div class="timeline-block">
                    <div class="timeline-bullet wow zoomIn" data-wow-delay="0s">
                    </div>
                    <!-- timeline-bullet -->
                    <div class="timeline-content">
                        <h2 class="wow flipInX" data-wow-delay="0.3s"><?php the_title( );?></h2>
                    <?php if($schedule_speaker) :?>
                        <p class="wow flipInX" data-wow-delay="0.3s"><?php _e('by ','gather'); echo esc_attr($schedule_speaker );?></p>
                    <?php endif;?>
                    <?php the_content( );?>
                    <?php if($schedule_time) :?>
                        <span class="date wow flipInX" data-wow-delay="0.3s"><?php echo esc_attr($schedule_time );?></span>
                    <?php endif;?>
                    </div>
                    <!-- timeline-content -->
                </div>

            <?php endwhile;?>
            </section>
            <?php 
            endif;


            ?>

            <?php wp_reset_postdata();?>


        <?php 
            endif; //end if $child_schedule_cats
        ?>


    </div>
    <?php } ?>
    </div>
<?php 
    endif; //end if $schedule_cats
?>
