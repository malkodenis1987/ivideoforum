<?php
/*
Plugin Name: Flexible upload
Plugin URI: http://blog.japonophile.com/flexible-upload/
Description: Resize picture at upload and make thumbnail creation configurable, optionally include a watermark to your uploaded images. Support Lightbox-like plugins.
Version: 1.13
Author: Antoine Choppin
Author URI: http://blog.japonophile.com/
*/

/*
  Flexible Upload plugin for Wordpress
  Copyright 2007-2008  Antoine Choppin  (email: antoine@japonophile.com)

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along
  with this program; if not, write to the Free Software Foundation, Inc.,
  51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

/*
 Revision History:

 0.1   Initial version (Wordpress 2.0.x)
 1.0   Adapted to support Wordpress 2.1 too
 1.1   Made alignment feature optional
 1.2   Added support for WP versions older than 2.0.4
       Fixed iframe size (WP 2.0.x only)
 1.3   Added basic option page and help page
       Supported other plugins (Greybox, Thickbox) and opening in a new window
       Supported to resize against width/height/smallest side
 1.4   Supported top-right watermark and rotated watermark
 1.5   Do not enlarge pictures
       Do not insert "lightbox" tag for non-image attachments
 1.6   Multi-language support (fr_FR, ja_JP)
       Support for HighslideJS and LightWindow
       Make watermark optional and configurable for each picture
       Multi-file upload (only for WP 2.1+)
 1.7   Fix multi-language to use plugin domain
 1.8   Fix truncated img tag after title
       Fix problem with image deletion
 1.9   Fix display property not working with IE
 1.10  Major change: Adapt to WP2.5
       Implement disable wpautop to allow using captions with visual editor
       Use standard CSS (alignleft, alignright, centered) for alignment
       Fix small bugs here and there
       Support German (thx to raphaelhofer.com <email@raphaelhofer.com>)
       Support Danish (thx to Georg S. Adamsen <g.s.adamsen@gmail.com>)
 1.11  Fix title bug, improve title & caption handling
 1.12  Fix bug in options (WP <2.5)
       Support watermark in thumbnails and alpha blending PNG-24 watermarks
        (thanks to Michael Yates for his contribution)
       Make alt tag configurable
       Update i18n
 1.13  Fix broken "Send to Editor" button in WP2.3.3
       Unescape special characters in caption
*/

/*
Features:
=========
 - automatically resize picture at upload
 - create thumbnail of the desired size
 - include watermark in every uploaded picture
 - support for picture alignment (left/right/center)
 - support for picture caption (not when using tinyMCE)
 - include Lightbox "rel" tag

Supported WP versions:
======================
 - WP 2.0.x (2.0, 2.0.1, 2.0.2, 2.0.4, 2.0.5, 2.0.6, 2.0.7, 2.0.8, 2.0.9)
 - WP 2.1.x (2.1, 2.1.2)
 - WP 2.2.x
 - WP 2.3.x
 - WP 2.5

Usage:
======
 - unzip flexible-upload.zip in your wp-content/plugins directory
 - go to Flexible Upload option page in the admin menu and configure it
   to suit your needs
 - [optional] install Lightbox2 plugin or similar
*/

define('FUP_DEFAULT_LARGE_OPTION',  'fup_default_large_max');
define('FUP_DEFAULT_THUMB_OPTION',  'fup_default_thumb_max');
define('FUP_RESIZE_SIDE_OPTION',    'fup_resize_side');
define('FUP_THUMB_SIDE_OPTION',     'fup_thumb_side');
define('FUP_ALIGNMENT_MODE_OPTION', 'fup_alignment_mode');
define('FUP_DEFAULT_ALIGN_OPTION',  'fup_default_alignment');
define('FUP_WATERMARK_PIC_OPTION',  'fup_watermark_picture');
define('FUP_WATERMARK_THUMB_OPTION','fup_watermark_thumbnail');
define('FUP_DEFAULT_WAT_LOC_OPTION','fup_default_wat_location');
define('FUP_DEFAULT_WAT_ORI_OPTION','fup_default_wat_orientation');
define('FUP_WAT_OPT_PER_PIC_OPTION','fup_wat_opt_per_picture');
define('FUP_PIC_TARGET_OPTION',     'fup_picture_target');
define('FUP_JPEG_QUALITY_OPTION',   'fup_jpeg_quality');
define('FUP_IMAGE_TITLE_OPTION',    'fup_image_title');
define('FUP_IMAGE_ALT_OPTION',      'fup_image_alt');
define('FUP_DISABLE_WPAUTOP_OPTION','fup_disable_wpautop');
define('FUP_DEFAULT_CAPTION_OPTION','fup_default_caption');

//Check install directory
$fup_directory = 'wp-content/plugins/flexible-upload/';
if ((!strstr(dirname(__FILE__).'/', $fup_directory)) &&
    (!strstr(dirname(__FILE__).'\\', str_replace('/', '\\', $fup_directory)))) {
    trigger_error(sprintf(__('<b>Flexible Upload is not installed in the proper directory!</b><br />It won\'t work until installed in <b>%s</b><br />', 'fup'), $fup_directory), E_USER_ERROR);
    return;
}

global $wp_version;
global $fup_wpver;
// WP ME support, thanks to Bono-san http://bono.s201.xrea.com/
$fup_wpver = str_replace("ME", "", $wp_version);
$fup_wpver = explode('.', $fup_wpver);
$fup_rel_dir = 'wp-content/plugins/flexible-upload/';

// Multi-language support
if (defined('WPLANG') && function_exists('load_plugin_textdomain')) {
    load_plugin_textdomain('fup', $fup_rel_dir.'languages');
}

if (($fup_wpver[0] == 2) && ($fup_wpver[1] < 1)) {
    // When called in the plugin context, define the filter to use this
    // script, instead of the existing inline-uploading.php script
    function flexible_upload_iframe_src($frame_src) {
        global $fup_rel_dir;
        return str_replace('inline-uploading.php', get_bloginfo('wpurl')
                           .'/'.$fup_rel_dir.'flexible-upload-wp20.php',
                           $frame_src);
    }

    add_filter('uploading_iframe_src', 'flexible_upload_iframe_src');
}
else if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 1) && ($fup_wpver[1] < 5)) {
    require_once(ABSPATH.$fup_rel_dir.'flexible-upload-wp21.php');
}
else if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 5)) {
    require_once(ABSPATH.$fup_rel_dir.'flexible-upload-wp25.php');
}
// else WP version not supported

/**
 * Activate plugin
 */
function fup_activate() {
    global $fup_wpver;

    // Set default options
    if (get_option(FUP_DEFAULT_LARGE_OPTION) == false) {
        add_option(FUP_DEFAULT_LARGE_OPTION, 640);
    }
    if (get_option(FUP_DEFAULT_THUMB_OPTION) == false) {
        add_option(FUP_DEFAULT_THUMB_OPTION, 200);
    }
    if (get_option(FUP_RESIZE_SIDE_OPTION) == false) {
        add_option(FUP_RESIZE_SIDE_OPTION, 'largest_side');
    }
    if (get_option(FUP_THUMB_SIDE_OPTION) == false) {
        add_option(FUP_THUMB_SIDE_OPTION, 'largest_side');
    }
    if (get_option(FUP_ALIGNMENT_MODE_OPTION) == false) {
        add_option(FUP_ALIGNMENT_MODE_OPTION, 'css');
    }
    if (get_option(FUP_DEFAULT_ALIGN_OPTION) == false) {
        add_option(FUP_DEFAULT_ALIGN_OPTION, 'left');
    }
    if (get_option(FUP_PIC_TARGET_OPTION) == false) {
        add_option(FUP_PIC_TARGET_OPTION, 'lightbox');
    }
    if (get_option(FUP_WATERMARK_PIC_OPTION) == false) {
        add_option(FUP_WATERMARK_PIC_OPTION, '');
    }
    if (get_option(FUP_WATERMARK_THUMB_OPTION) == false) {
        add_option(FUP_WATERMARK_THUMB_OPTION, 'false');
    }	
    if (get_option(FUP_DEFAULT_WAT_LOC_OPTION) == false) {
        add_option(FUP_DEFAULT_WAT_LOC_OPTION, 'BR');
    }
    if (get_option(FUP_DEFAULT_WAT_ORI_OPTION) == false) {
        add_option(FUP_DEFAULT_WAT_ORI_OPTION, 'none');
    }
    if (get_option(FUP_WAT_OPT_PER_PIC_OPTION) == false) {
        add_option(FUP_WAT_OPT_PER_PIC_OPTION, 'false');
    }
    if (get_option(FUP_JPEG_QUALITY_OPTION) == false) {
        add_option(FUP_JPEG_QUALITY_OPTION, '75');
    }
    if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 1)) {
        if (get_option(FUP_IMAGE_TITLE_OPTION) == false) {
            add_option(FUP_IMAGE_TITLE_OPTION, 'title');
        }
    }
    if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 5)) {
        if (get_option(FUP_IMAGE_ALT_OPTION) == false) {
            add_option(FUP_IMAGE_ALT_OPTION, 'title');
        }
        if (get_option(FUP_DEFAULT_CAPTION_OPTION) == false) {
            add_option(FUP_DEFAULT_CAPTION_OPTION, 'none');
        }
        if (get_option(FUP_DISABLE_WPAUTOP_OPTION) == false) {
            add_option(FUP_DISABLE_WPAUTOP_OPTION, 'false');
        }
    }
}

add_action('activate_flexible-upload/flexible-upload.php', 'fup_activate');

global $fup_thumb_basename, $fup_key;
$fup_thumb_basename = '';
$fup_key = '';

/**
 * fup_resize_and_thumbnail
 *
 * - create a thumbnail if needed
 * - resize image if needed
 * - incrust watermark if needed
 */
function fup_resize_and_thumbnail($file) {

    global $fup_thumb_basename, $fup_key;

    $resized_file = $file.".resized";

    $imgresize_select = $_POST['imgresize_select'.$fup_key];
    $imgresize_size = $_POST['imgresize_size'.$fup_key];
    $thumbnail_select = $_POST['thumbnail_select'.$fup_key];
    $thumbnail_size = $_POST['thumbnail_size'.$fup_key];

    // Resize image if needed
    if ($imgresize_select && ($imgresize_size > 0)) {
        $max_size = fup_determine_max_size($file, 'imgresize');
        if ($max_size) {
            $thumb = fup_create_thumbnail($file, $max_size);
            fup_crop_image($thumb, 'imgresize');
            // Rename the resized file, because the thumbnail will have the same name
            if ( @file_exists($thumb) ) {
                rename($thumb, $resized_file);
            }
        }
    }

    // Create thumbnail if needed
    if ($thumbnail_select && ($thumbnail_size > 0)) {
        $max_size = fup_determine_max_size($file, 'thumbnail');
        if ($max_size) {
            $thumb = fup_create_thumbnail($file, $max_size);
            fup_crop_image($thumb, 'thumbnail');
            if ( @file_exists($thumb) ) {
            	// Optionally watermark the thumbnail 
				if (get_option(FUP_WATERMARK_THUMB_OPTION) == 'true')
					fup_incrust_watermark($thumb);
	            // Store the thumbnail name for updating metadata later
                $fup_thumb_basename = basename($thumb);
            }
        }
     }
    else {
        // Reset thumbnail name
        $fup_thumb_basename = '';
    }

    // Replace the original file by the resized image
    if ( @file_exists($resized_file) ) {
        unlink($file);
        rename($resized_file, $file);
    }

    // Create watermark if needed
    fup_incrust_watermark($file);
}

/**
 * fup_update_attachment_metadata
 * 
 * If a thumbnail has been created, update its name in the attachment metadata
 * The 'wp_generate_attachment_metadata' only exists for WP2.1, so this
 * function needs to be called explicitely in WP2.0.x
 */
function fup_update_attachment_metadata($metadata) {

	global $fup_thumb_basename;

	if ($fup_thumb_basename != '') {
		$metadata['thumb'] = $fup_thumb_basename;
	}

	return $metadata;
}

/**
 * fup_incrust_watermark
 * 
 * Incrust the watermark signature file specified in the options
 * into the image file specified as argument.
 */
function fup_incrust_watermark($file) {

    global $fup_key;

    $watermark = ABSPATH.get_option(FUP_WATERMARK_PIC_OPTION);

    $watermark_location = $_POST['watermark_location'.$fup_key];
    $watermark_rotation = $_POST['watermark_rotation'.$fup_key];

    if (get_option(FUP_WATERMARK_PIC_OPTION) != '' &&
        file_exists($watermark) &&
        $watermark_location != 'NO') {
        // Determine rotation angle
        // Thanks to Eric Nakagawa for his contribution
        if ((get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'true' &&
             $watermark_rotation == 'ROT') ||
            (get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'false' &&
             get_option(FUP_DEFAULT_WAT_ORI_OPTION) == 'ROT')) {
            $angle = -90;
        } else {
            $angle = 0;
        }
        // Load source image and (rotated) watermark
        list($imgwidth, $imgheight, $imgtype) = getimagesize($file);
        list($wat_img, $watwidth, $watheight) =
        	fup_get_rotated_image($watermark, $angle);
        if ($wat_img && ($imgwidth > $watwidth) && ($imgheight > $watheight)) {
            $image = fup_create_image_from_file($file, $imgtype);
            if (!$image) {
            	return; // Type not supported
            }
            // Support alpha blending PNG-24 watermarks
            // Thanks to Michael Yates for his contribution
            $supportpng24 = false;
            if (strtolower(substr($watermark, -3)) == 'png') {
                // Ensure image is true color
                $tempimage = imagecreatetruecolor($imgwidth, $imgheight);
                imagecopy($tempimage, $image, 0, 0, 0, 0, $imgwidth, $imgheight);       
                $image = $tempimage;
                // Setup alpha blending						
                imagealphablending($image, true);
                imagealphablending($wat_img, false);
                imagesavealpha($wat_img, true);
                $supportpng24 = true;
            }			
            // Determine if watermarking upper right or bottom right
            // Thanks to Eric Nakagawa for his contribution
            if ((get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'true' &&
                 $watermark_location == 'TR') ||
                (get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'false' &&
                 get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'TR')) {
             	if ($supportpng24) {
                    imagecopy($image, $wat_img, ($imgwidth - $watwidth),
                              0, 0, 0, $watwidth, $watheight);				
                }
                else {
                    imagecopymerge($image, $wat_img, ($imgwidth - $watwidth),
                                   0, 0, 0, $watwidth, $watheight, 100);
                }
            } else { // if ($watermark_location == 'BR') 
             	if ($supportpng24) {
                    imagecopy($image, $wat_img, ($imgwidth - $watwidth),
                              ($imgheight - $watheight), 0, 0,
                              $watwidth, $watheight);
                }
                else {
                    imagecopymerge($image, $wat_img, ($imgwidth - $watwidth),
                                   ($imgheight - $watheight), 0, 0,
                                   $watwidth, $watheight, 100);
                }
            }
            fup_write_image_to_file($image, $file, $imgtype);
        }
    }
}

/**
 * fup_determine_max_size
 *
 * Determine the maximum size for the largest side of the picture
 * to be resized, based on the 'Resize against' parameter entered
 * by the user, for either image resize or thumbnail creation.
 * - file: image file to be resized
 * - type: 'imgresize' or 'thumbnail'
 * If the image is already smaller than the maximum size, this
 * function returns false, meaning the picture should not be 
 * resized.
 */
function fup_determine_max_size($file, $type) {

    global $fup_key;

    $max_size = false;    
    $orig_size = getimagesize($file);

    $side = $_POST[$type.'_side'.$fup_key];
    $size = $_POST[$type.'_size'.$fup_key];

    if ($side == 'largest_side') {
        $max_size = $size;
        if (($max_size > $orig_size[0]) && ($max_size > $orig_size[1])) {
            $max_size = false;
        }
    }
    elseif ($side == 'width') {
        if ($orig_size[0] >= $orig_size[1]) {
            $max_size = $size;
            if ($max_size > $orig_size[0]) {
                $max_size = false;
            }
        }
        else {
            $max_size = $size*$orig_size[1]/$orig_size[0];
            if ($max_size > $orig_size[1]) {
                $max_size = false;
            }
        }
    }
    elseif ($side == 'height') {
        if ($orig_size[1] >= $orig_size[0]) {
            $max_size = $size;
            if ($max_size > $orig_size[1]) {
                $max_size = false;
            }
        }
        else {
            $max_size = $size*$orig_size[0]/$orig_size[1];
            if ($max_size > $orig_size[0]) {
                $max_size = false;
            }
        }
    }
    elseif ($side == 'smallest_side') {
        if ($orig_size[1] >= $orig_size[0]) {
            $max_size = $size*$orig_size[1]/$orig_size[0];
            if ($max_size > $orig_size[1]) {
                $max_size = false;
            }
        }
        else {
            $max_size = $size*$orig_size[0]/$orig_size[1];
            if ($max_size > $orig_size[0]) {
                $max_size = false;
            }
        }
    }

    return $max_size;
}

/**
 * fup_crop_image
 * 
 * Crop the image or thumbnail if needed
 * Thanks to Christer Olsson http://www.ljusaideer.se for his contribution
 */
function fup_crop_image($file, $type) {

    global $fup_key;

    $side = $_POST[$type.'_side'.$fup_key];
    $size = $_POST[$type.'_size'.$fup_key];

    if ($side == 'smallest_side') {
        $cropped_size = $size;
        $imgsize = getimagesize($file);
        $imgwidth = $imgsize[0];
        $imgheight = $imgsize[1];
        $imgtype = $imgsize[2];
        $image = fup_create_image_from_file($file, $imgtype);
        if (!$image) {
            return $file; // Type not supported
        }
        if (function_exists('imageantialias')) {
            imageantialias($image, TRUE);
        }
        if ($imgwidth > $imgheight) {
            $x = ceil(($imgwidth - $imgheight) / 2 );
            $imgwidth = $imgheight;
        } elseif ($imgheight > $imgwidth) {
            $y = ceil(($imgheight - $imgwidth) / 2);
            $imgheight = $imgwidth;
        }
        $new_im = imagecreatetruecolor($cropped_size, $cropped_size);
        imagecopyresampled($new_im, $image, 0, 0, $x, $y,
                           $cropped_size, $cropped_size,
                           $imgwidth, $imgheight);
        fup_write_image_to_file($new_im, $file, $imgtype);
    }
}

/**
 * fup_get_rotated_image
 * 
 * Returns an image rotated by a given angle (CCW)
 */
function fup_get_rotated_image($filename, $angle) {

  // Read source image
  list($width, $height, $type, $attr) = getimagesize($filename);
  $source = fup_create_image_from_file($filename, $type);

  if (!$source) {
  	return array(false, 0, 0); // Unsupported type
  }
  if ($angle == 0) {
  	return array($source, $width, $height);
  }

  // Create target image
  $timage = imagecreatetruecolor($width, $height);
  $bg = imagecolortransparent($source);
  imagefill($timage, 0, 0, $bg);
  imagecopy($timage, $source, 0, 0, 0, 0, $width, $height);

  // Rotate target image
  $dest = imagerotate($timage, $angle, $bg);
  $tg = imagecolortransparent($dest, $bg);

  return array($dest, imagesx($dest), imagesy($dest));
}

/**
 * fup_create_image_from_file
 * Helper to create an image from GIF/JPG/PNG file
 */
function fup_create_image_from_file($file, $imgtype) {
	
    if (function_exists('imagegif') && $imgtype == 1) {
        return imagecreatefromgif($file);
    }
    elseif (function_exists('imagejpeg') && $imgtype == 2) {
        return imagecreatefromjpeg($file);
    }
    elseif (function_exists('imagepng') && $imgtype == 3) {
        return imagecreatefrompng($file);
    }
}

/**
 * fup_write_image_to_file
 * Helper to save a GIF/JPG/PNG image to file
 */
function fup_write_image_to_file($image, $file, $imgtype) {

    if ($imgtype == 1) {
        imagegif($image, $file);
    }
    elseif ($imgtype == 2) {
        $jpegquality = get_option(FUP_JPEG_QUALITY_OPTION);
        $jpegquality = $jpegquality ? $jpegquality : 75;
        imagejpeg($image, $file, $jpegquality);
    }
    elseif ($imgtype == 3) {
        imagepng($image, $file);
    }
}

function fup_create_thumbnail( $file, $max_side, $effect = '' ) {

    // 1 = GIF, 2 = JPEG, 3 = PNG

	// Temporarily extend memory limit to allow processing large images
    $original_mem_limit = ini_get('memory_limit');
    ini_set('memory_limit', -1);

    if ( file_exists( $file ) ) {
        $type = getimagesize( $file );

        // if the associated function doesn't exist - then it's not
        // handle. duh. i hope.

        if (!function_exists( 'imagegif' ) && $type[2] == 1 ) {
            $error = __( 'Filetype not supported. Thumbnail not created.', 'fup' );
        }
        elseif (!function_exists( 'imagejpeg' ) && $type[2] == 2 ) {
            $error = __( 'Filetype not supported. Thumbnail not created.', 'fup' );
        }
        elseif (!function_exists( 'imagepng' ) && $type[2] == 3 ) {
            $error = __( 'Filetype not supported. Thumbnail not created.', 'fup' );
        } else {
            // create the initial copy from the original file
            if ( $type[2] == 1 ) {
                $image = imagecreatefromgif( $file );
            }
            elseif ( $type[2] == 2 ) {
                $image = imagecreatefromjpeg( $file );
            }
            elseif ( $type[2] == 3 ) {
                $image = imagecreatefrompng( $file );
            }

            if ( function_exists( 'imageantialias' ))
                imageantialias( $image, TRUE );

            $image_attr = getimagesize( $file );

            // figure out the longest side
            if ( $image_attr[0] > $image_attr[1] ) {
                $image_width = $image_attr[0];
                $image_height = $image_attr[1];
                $image_new_width = $max_side;

                $image_ratio = $image_width / $image_new_width;
                $image_new_height = $image_height / $image_ratio;
                //width is > height
            } else {
                $image_width = $image_attr[0];
                $image_height = $image_attr[1];
                $image_new_height = $max_side;

                $image_ratio = $image_height / $image_new_height;
                $image_new_width = $image_width / $image_ratio;
                //height > width
            }

            $thumbnail = imagecreatetruecolor( $image_new_width, $image_new_height);
            @ imagecopyresampled( $thumbnail, $image, 0, 0, 0, 0, $image_new_width, $image_new_height, $image_attr[0], $image_attr[1] );

            // If no filters change the filename, we'll do a default transformation.
            if ( basename( $file ) == $thumb = apply_filters( 'thumbnail_filename', basename( $file ) ) )
                $thumb = preg_replace( '!(\.[^.]+)?$!', '.thumbnail' . '$1', basename( $file ), 1 );

            $thumbpath = str_replace( basename( $file ), $thumb, $file );

            // move the thumbnail to its final destination
            if ( $type[2] == 1 ) {
                if (!imagegif( $thumbnail, $thumbpath ) ) {
                        $error = __( "Thumbnail path invalid", 'fup' );
                }
            }
            elseif ( $type[2] == 2 ) {
                $jpegquality = get_option(FUP_JPEG_QUALITY_OPTION);
                $jpegquality = $jpegquality ? $jpegquality : 75;
                if (!imagejpeg( $thumbnail, $thumbpath, $jpegquality ) ) {
                        $error = __( "Thumbnail path invalid", 'fup' );
                }
            }
            elseif ( $type[2] == 3 ) {
                if (!imagepng( $thumbnail, $thumbpath ) ) {
                        $error = __( "Thumbnail path invalid", 'fup' );
                }
            }
        }
    } else {
        $error = __( 'File not found', 'fup' );
    }

    // Revert to original limit
    ini_set('memory_limit', $original_mem_limit);

    if (!empty ( $error ) ) {
        return $error;
    } else {
        return apply_filters( 'wp_create_thumbnail', $thumbpath );
    }
}

/**
 * fup_get_link_target
 * 
 * Returns the appropriate 'rel', 'style' or 'target' element
 * to specify where/how the picture should appear. 
 */
function fup_get_link_target($post_id = '') {
	global $fup_wpver;
    $target = '';

    if (($post_id == '') && ($fup_wpver[1] >= 1) && ($fup_wpver[1] < 5)) {
        $post_id = "' + this.postID + '";
    }

    switch(get_option(FUP_PIC_TARGET_OPTION)) {
        case 'lightbox':
            $target = 'rel="lightbox[pics'.$post_id.']" ';
            break;

        case 'greybox':
            $target = 'rel="gb_imageset[pics'.$post_id.']" ';
            break;

        case 'gb-plugin':
            $target = 'class="GB" ';
            break;

        case 'thickbox':
            $target = 'class="thickbox" ';
            break;

        case 'highslide':
            $target = 'class="highslide"  onclick="return hs.expand(this)" ';
            break;

        case 'lightwindow':
            $target = 'class="lightwindow" ';
            break;

        case 'blank':
            $target = 'target="_blank" ';
            break;
    }

    return $target;
}

/**
 * add_fup_config_page
 * 
 * Adds Flexible Upload configuration page to the Option admin tab
 */
function add_fup_config_page() {
    add_options_page('Flexible Upload Options', 'Flexible Upload', 8,
                     basename(__FILE__), 'fup_config_page');
}

add_action('admin_menu', 'add_fup_config_page');

/**
 * fup_config_page
 * 
 * This is Flexible Upload configuration page.
 */
function fup_config_page() {

	global $fup_rel_dir, $fup_wpver;

    if ('update' == $_POST['action']) {
        if ( function_exists('current_user_can') &&
             !current_user_can('manage_options') ) {
            die(__('You do not have sufficient permission to manage options.', 'fup'));
        }

        $option_update_error = '';

        if (($_POST[FUP_DEFAULT_LARGE_OPTION] >= 0) &&
            ($_POST[FUP_DEFAULT_LARGE_OPTION] <= 4096)) {
            update_option(FUP_DEFAULT_LARGE_OPTION,
                          $_POST[FUP_DEFAULT_LARGE_OPTION]);
        }
        else {
            $option_update_error .= ($option_update_error!='' ? '<br />' : '');
            $option_update_error .= sprintf(__('Wrong value %d for %s', 'fup'),
                                            $_POST[FUP_DEFAULT_LARGE_OPTION],
                                            FUP_DEFAULT_LARGE_OPTION);
        }

        if (($_POST[FUP_DEFAULT_THUMB_OPTION] >= 0) &&
            ($_POST[FUP_DEFAULT_THUMB_OPTION] <= 4096)) {
            update_option(FUP_DEFAULT_THUMB_OPTION,
                          $_POST[FUP_DEFAULT_THUMB_OPTION]);
        }
        else {
            $option_update_error .= ($option_update_error!='' ? '<br />' : '');
            $option_update_error .= sprintf(__('Wrong value %d for %s', 'fup'),
                                            $_POST[FUP_DEFAULT_THUMB_OPTION],
                                            FUP_DEFAULT_THUMB_OPTION);
        }

        switch ($_POST[FUP_RESIZE_SIDE_OPTION]) {
            case 'largest_side':
            case 'width':
            case 'height':
            case 'smallest_side':
                update_option(FUP_RESIZE_SIDE_OPTION,
                              $_POST[FUP_RESIZE_SIDE_OPTION]);
                break;

            default:
                $option_update_error .= ($option_update_error!=''?'<br />':'');
                $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                $_POST[FUP_RESIZE_SIDE_OPTION],
                                                FUP_RESIZE_SIDE_OPTION);
        }

        switch ($_POST[FUP_THUMB_SIDE_OPTION]) {
            case 'largest_side':
            case 'width':
            case 'height':
            case 'smallest_side':
                update_option(FUP_THUMB_SIDE_OPTION,
                              $_POST[FUP_THUMB_SIDE_OPTION]);
                break;

            default:
                $option_update_error .= ($option_update_error!=''?'<br />':'');
                $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                $_POST[FUP_THUMB_SIDE_OPTION],
                                                FUP_THUMB_SIDE_OPTION);
        }

        switch ($_POST[FUP_ALIGNMENT_MODE_OPTION]) {
            case 'none':
            case 'css':
            case 'img':
                update_option(FUP_ALIGNMENT_MODE_OPTION,
                              $_POST[FUP_ALIGNMENT_MODE_OPTION]);
                break;

            default:
                $option_update_error .= ($option_update_error!=''?'<br />':'');
                $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                               $_POST[FUP_ALIGNMENT_MODE_OPTION],
                                                FUP_ALIGNMENT_MODE_OPTION);
        }

        switch ($_POST[FUP_DEFAULT_ALIGN_OPTION]) {
            case 'none':
            case 'left':
            case 'right':
            case 'center':
                update_option(FUP_DEFAULT_ALIGN_OPTION,
                              $_POST[FUP_DEFAULT_ALIGN_OPTION]);
                break;

            default:
                $option_update_error .= ($option_update_error!=''?'<br />':'');
                $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                               $_POST[FUP_DEFAULT_ALIGN_OPTION],
                                                FUP_DEFAULT_ALIGN_OPTION);
        }

        switch ($_POST[FUP_PIC_TARGET_OPTION]) {
            case 'none':
            case 'lightbox':
            case 'greybox':
            case 'gb-plugin':
            case 'thickbox':
            case 'highslide':
            case 'lightwindow':
            case 'blank':
                update_option(FUP_PIC_TARGET_OPTION,
                              $_POST[FUP_PIC_TARGET_OPTION]);
                break;

            default:
                $option_update_error .= ($option_update_error!=''?'<br />':'');
                $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                $_POST[FUP_PIC_TARGET_OPTION],
                                                FUP_PIC_TARGET_OPTION);
        }

        update_option(FUP_WATERMARK_PIC_OPTION,
                      $_POST[FUP_WATERMARK_PIC_OPTION]);

        if (isset($_POST[FUP_WATERMARK_THUMB_OPTION]) &&
            $_POST[FUP_WATERMARK_THUMB_OPTION] == 'true') {
            update_option(FUP_WATERMARK_THUMB_OPTION, 'true');
        }
        else {
            update_option(FUP_WATERMARK_THUMB_OPTION, 'false');
        }
		
        switch ($_POST[FUP_DEFAULT_WAT_LOC_OPTION]) {
            case 'NO':
            case 'BR':
            case 'TR':
                update_option(FUP_DEFAULT_WAT_LOC_OPTION,
                              $_POST[FUP_DEFAULT_WAT_LOC_OPTION]);
                break;
                                                                                
            default:
                $option_update_error .= ($option_update_error!=''?'<br />':'');
                $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                $_POST[FUP_DEFAULT_WAT_LOC_OPTION],
                                                FUP_DEFAULT_WAT_LOC_OPTION);
        }
                                                                                
        if (isset($_POST[FUP_DEFAULT_WAT_ORI_OPTION]) &&
            $_POST[FUP_DEFAULT_WAT_ORI_OPTION] == 'ROT') {
            update_option(FUP_DEFAULT_WAT_ORI_OPTION, 'ROT');
        }
        else {
            update_option(FUP_DEFAULT_WAT_ORI_OPTION, 'none');
        }

        if (isset($_POST[FUP_WAT_OPT_PER_PIC_OPTION]) &&
            $_POST[FUP_WAT_OPT_PER_PIC_OPTION] == 'true') {
            update_option(FUP_WAT_OPT_PER_PIC_OPTION, 'true');
        }
        else {
            update_option(FUP_WAT_OPT_PER_PIC_OPTION, 'false');
        }

        if (($_POST[FUP_JPEG_QUALITY_OPTION] >= 0) &&
            ($_POST[FUP_JPEG_QUALITY_OPTION] <= 100)) {
            update_option(FUP_JPEG_QUALITY_OPTION,
                          $_POST[FUP_JPEG_QUALITY_OPTION]);
        }
        else {
            $option_update_error .= ($option_update_error!='' ? '<br />' : '');
            $option_update_error .= sprintf(__('Wrong value %d for %s', 'fup'),
                                            $_POST[FUP_JPEG_QUALITY_OPTION],
                                            FUP_JPEG_QUALITY_OPTION);
        }

        if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 1)) {
            switch ($_POST[FUP_IMAGE_TITLE_OPTION]) {
                case 'title':
                case 'description':
                case 'longdesc':
                case 'none':
                    update_option(FUP_IMAGE_TITLE_OPTION,
                                  $_POST[FUP_IMAGE_TITLE_OPTION]);
                    break;

                default:
                    $option_update_error .= ($option_update_error!=''?'<br />':'');
                    $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                    $_POST[FUP_IMAGE_TITLE_OPTION],
                                                    FUP_IMAGE_TITLE_OPTION);
            }
        }

        if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 5)) {
            switch ($_POST[FUP_IMAGE_ALT_OPTION]) {
                case 'title':
                case 'description':
                case 'longdesc':
                case 'none':
                    update_option(FUP_IMAGE_ALT_OPTION,
                                  $_POST[FUP_IMAGE_ALT_OPTION]);
                    break;

                default:
                    $option_update_error .= ($option_update_error!=''?'<br />':'');
                    $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                    $_POST[FUP_IMAGE_ALT_OPTION],
                                                    FUP_IMAGE_TITLE_OPTION);
            }
            switch ($_POST[FUP_DEFAULT_CAPTION_OPTION]) {
                case 'title':
                case 'description':
                case 'longdesc':
                case 'none':
                    update_option(FUP_DEFAULT_CAPTION_OPTION,
                                  $_POST[FUP_DEFAULT_CAPTION_OPTION]);
                    break;
                                                                                
                default:
                    $option_update_error .= ($option_update_error!=''?'<br />':'');
                    $option_update_error .= sprintf(__('Wrong value %s for %s', 'fup'),
                                                    $_POST[FUP_DEFAULT_CAPTION_OPTION],
                                                    FUP_DEFAULT_CAPTION_OPTION);
            }
        }

        if (isset($_POST[FUP_DISABLE_WPAUTOP_OPTION]) &&
            $_POST[FUP_DISABLE_WPAUTOP_OPTION] == 'true') {
            update_option(FUP_DISABLE_WPAUTOP_OPTION, 'true');
        }
        else {
            update_option(FUP_DISABLE_WPAUTOP_OPTION, 'false');
        }

        if ($option_update_error) {
            echo '<div class="error"><p>'
                 .__('Error encountered while updating options: ', 'fup')
                 .$option_update_error.'</p></div>';
        }
        else {
            echo '<div class="updated"><p>'
                 .__('Options updated.', 'fup').'</p></div>';
        }
    }
?>
<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
<div class="wrap">

  <h2><?php _e('Help', 'fup'); ?></h2>

  <p><?php printf(__('Consult the local %shelp page%s.', 'fup'),
   '<a href="'.get_option('siteurl').'/'.$fup_rel_dir
   .'flexible-upload-help.html">', '</a>'); ?></p>
  <p>&nbsp;</p>

  <h2><?php _e('System Configuration', 'fup'); ?></h2>

  <ul>
  <li><?php printf(__('System memory limit: <b>%s</b>', 'fup'), ini_get('memory_limit')); ?></li>
  <li><?php printf(__('Maximum uploadable file size: <b>%s</b>', 'fup'), ini_get('upload_max_filesize')); ?><br />
  <?php printf(__('Make sure this size is large enough, you won\'t be able to upload an image whose file size is bigger than this value. (see %sPHP help about upload_max_filesize%s)', 'fup'), '<a href="http://www.php.net/manual/en/ini.core.php#ini.upload-max-filesize">', '</a>'); ?></li>
  <li><?php printf(__('Maximum POST size: <b>%s</b>', 'fup'), ini_get('post_max_size')); ?><br />
  <?php printf(__('If you want to upload more than one image at a time, make sure this limit is greater than the maximum file size x the number of images you want to upload at once. (see %sPHP help about post_max_size%s)', 'fup'), '<a href="http://www.php.net/manual/en/ini.core.php#ini.post-max-size">', '</a>'); ?></li>
  </ul>

  <h2><?php _e('Flexible Upload Options', 'fup'); ?></h2>

  <fieldset class='options'>
    <legend><?php _e('Default settings', 'fup'); ?></legend>

    <table class="editform" cellspacing="2" cellpadding="5" width="100%">
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DEFAULT_LARGE_OPTION ?>"><?php _e('Default maximum for resizing:', 'fup'); ?></label>
        </th>
        <td>
          <?php
          echo "<input type='text' size='8' ";
          echo "name='".FUP_DEFAULT_LARGE_OPTION."' ";
          echo "id='".FUP_DEFAULT_LARGE_OPTION."' ";
          echo "value='".get_option(FUP_DEFAULT_LARGE_OPTION)."' />";
          _e(' (px)', 'fup'); echo "\n";
          ?><br />
          <?php _e('Specify the default size to which you want to resize your pictures (leave blank if you don\'t want to resize your pictures).  You will be able to change this for each picture.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_RESIZE_SIDE_OPTION ?>"><?php
                     _e('Resize against:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_RESIZE_SIDE_OPTION
               ."' id='".FUP_RESIZE_SIDE_OPTION."'>\n"
               ."<option value='largest_side'";
          if(get_option(FUP_RESIZE_SIDE_OPTION) == 'largest_side')
              echo " selected='selected'";
          echo ">".__('largest side', 'fup')."</option>\n"
               ."<option value='width'";
          if(get_option(FUP_RESIZE_SIDE_OPTION) == 'width')
              echo" selected='selected'";
          echo ">".__('width', 'fup')."</option>\n"
               ."<option value='height'";
          if(get_option(FUP_RESIZE_SIDE_OPTION) == 'height')
              echo" selected='selected'";
          echo ">".__('height', 'fup')."</option>\n"
               ."<option value='smallest_side'";
          if(get_option(FUP_RESIZE_SIDE_OPTION) == 'smallest_side')
              echo " selected='selected'";
          echo ">".__('smallest side (crop)', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Specify against which side you want to resize your pictures.  You will be able to change this for each picture.  Choosing "smallest side" allows cropping your pictures and create square images.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DEFAULT_THUMB_OPTION ?>"><?php
                     _e('Default maximum for thumbnail:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<input type='text' size='8' ";
          echo "name='".FUP_DEFAULT_THUMB_OPTION."' ";
          echo "id='".FUP_DEFAULT_THUMB_OPTION."' ";
          echo "value='".get_option(FUP_DEFAULT_THUMB_OPTION)."' />";
          _e(' (px)', 'fup'); echo "\n";
          ?><br />
          <?php _e('Specify the default thumbnail size (leave blank if you don\'t want to generate a thumbnail).  You will be able to change this for each picture.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_THUMB_SIDE_OPTION ?>"><?php
                     _e('Resize against:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_THUMB_SIDE_OPTION
               ."' id='".FUP_THUMB_SIDE_OPTION."'>\n"
               ."<option value='largest_side'";
          if(get_option(FUP_THUMB_SIDE_OPTION) == 'largest_side')
              echo " selected='selected'";
          echo ">".__('largest side', 'fup')."</option>\n"
               ."<option value='width'";
          if(get_option(FUP_THUMB_SIDE_OPTION) == 'width')
              echo" selected='selected'";
          echo ">".__('width', 'fup')."</option>\n"
               ."<option value='height'";
          if(get_option(FUP_THUMB_SIDE_OPTION) == 'height')
              echo" selected='selected'";
          echo ">".__('height', 'fup')."</option>\n"
               ."<option value='smallest_side'";
          if(get_option(FUP_THUMB_SIDE_OPTION) == 'smallest_side')
              echo " selected='selected'";
          echo ">".__('smallest side (crop)', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Specify against which side you want to create your thumbnails.  You will be able to change this for each picture.  Choosing "smallest side" allows cropping your pictures and create square thumbnails.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_ALIGNMENT_MODE_OPTION ?>"><?php
                     _e('Alignment mode:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_ALIGNMENT_MODE_OPTION
               ."' id='".FUP_ALIGNMENT_MODE_OPTION."'>\n"
               ."<option value='css'";
          if (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'css')
              echo" selected='selected'";
          echo ">".__('CSS', 'fup')."</option>\n"
               ."<option value='img'";
          if (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'img')
              echo" selected='selected'";
          echo ">".__('&lt;img&gt; property', 'fup')."</option>\n"
               ."<option value='none'";
          if (get_option(FUP_ALIGNMENT_MODE_OPTION) == 'none')
              echo " selected='selected'";
          echo ">".__('disabled', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Specify how you want to specify the alignment when inserting pictures.  You can choose:<br />1. <strong>CSS</strong>: alignment (left/right/center) will be specified through different CSS classes which you should add to your theme\'s stylesheet.<br />2. <strong>"img" property</strong>: the alignment (left/right) will be specified through the "align" property of the &lt;img&gt; html tag.<br />3. Specify no alignment at all.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DEFAULT_ALIGN_OPTION ?>"><?php
                     _e('Default alignment:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_DEFAULT_ALIGN_OPTION
               ."' id='".FUP_DEFAULT_ALIGN_OPTION."'>\n"
               ."<option value='none'";
          if (get_option(FUP_DEFAULT_ALIGN_OPTION) == 'none')
              echo " selected='selected'";
          echo ">".__('none', 'fup')."</option>\n"
               ."<option value='left'";
          if (get_option(FUP_DEFAULT_ALIGN_OPTION) == 'left')
              echo" selected='selected'";
          echo ">".__('left', 'fup')."</option>\n"
               ."<option value='right'";
          if (get_option(FUP_DEFAULT_ALIGN_OPTION) == 'right')
              echo" selected='selected'";
          echo ">".__('right', 'fup')."</option>\n"
               ."<option value='center'";
          if (get_option(FUP_DEFAULT_ALIGN_OPTION) == 'center')
              echo" selected='selected'";
          echo ">".__('center', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Choose the default alignment when inserting a picture into your posts.  You will be able to change this for each picture.<br />Note:<br />- this setting will be ignored if alignment mode is "disabled".<br />- center alignment is not supported with "&lt;img&gt; property" alignment mode.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_PIC_TARGET_OPTION ?>"><?php
                     _e('Link plugin or target:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_PIC_TARGET_OPTION
               ."' id='".FUP_PIC_TARGET_OPTION."'>\n"
               ."<option value='none'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'none')
              echo " selected='selected'";
          echo ">".__('none', 'fup')."</option>\n"
               ."<option value='lightbox'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'lightbox')
              echo" selected='selected'";
          echo ">".__('Lightbox', 'fup')."</option>\n"
               ."<option value='greybox'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'greybox')
              echo" selected='selected'";
          echo ">".__('Greybox', 'fup')."</option>\n"
               ."<option value='gb-plugin'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'gb-plugin')
              echo" selected='selected'";
          echo ">".__('Greybox plugin', 'fup')."</option>\n"
               ."<option value='thickbox'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'thickbox')
              echo" selected='selected'";
          echo ">".__('Thickbox', 'fup')."</option>\n"
               ."<option value='highslide'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'highslide')
              echo" selected='selected'";
          echo ">".__('HighslideJS', 'fup')."</option>\n"
               ."<option value='lightwindow'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'lightwindow')
              echo" selected='selected'";
          echo ">".__('LightWindow', 'fup')."</option>\n"
               ."<option value='blank'";
          if(get_option(FUP_PIC_TARGET_OPTION) == 'blank')
              echo" selected='selected'";
          echo ">".__('new window', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Choose where/how you want your pictures to be displayed.  If choosing Lightbox, Greybox, Thickbox, HighslideJS or LightWindow make sure the corresponding plugin is installed.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_WATERMARK_PIC_OPTION ?>"><?php
                     _e('Signature image (for watermark):', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<input type='text' size='50' ";
          echo "name='".FUP_WATERMARK_PIC_OPTION."' ";
          echo "id='".FUP_WATERMARK_PIC_OPTION."' ";
          echo "value='".get_option(FUP_WATERMARK_PIC_OPTION)."' />\n";
          ?><br />
          <?php
          if (get_option(FUP_WATERMARK_PIC_OPTION) == '') {
              _e('Watermarking functionality is currently <strong>disabled</strong>.  To enable it, you must:<br />1. Make sure that GD is installed on the server<br />2. Prepare a signature image and provide its path, relative to WP root directory: e.g. "wp-content/uploads/my-watermark.gif"', 'fup');
          }
          else {
              if (!function_exists('imagecopymerge')) {
                  _e('Watermarking functionality is enabled but will not function correctly because the required function was not found.  Please make sure GD is installed on the server.  If you don\'t need this, you can disable this warning by specifying an empty path.', 'fup');
              }
              elseif (!file_exists(ABSPATH.get_option(FUP_WATERMARK_PIC_OPTION))) {
                  _e('Watermarking functionality is enabled but will not function correctly because the signature image path you provided below is incorrect.  The path should be relative to Wordpress root directory and refer to an existing image file.  If you don\'t need this, you can disable this warning by specifying an empty path for the watermark image.', 'fup');
              }
              else {
                  _e('Watermarking functionality is <strong>enabled</strong>.<br />If you don\'t need it, blank out the image path above.', 'fup');
              }
          }
          ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DEFAULT_WAT_LOC_OPTION ?>"><?php
                     _e('Watermark location:', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_DEFAULT_WAT_LOC_OPTION
               ."' id='".FUP_DEFAULT_WAT_LOC_OPTION."'>\n"
               ."<option value='NO'";
          if(get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'NO')
              echo " selected='selected'";
          echo ">".__('None', 'fup')."</option>\n"
               ."<option value='BR'";
          if(get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'BR')
              echo " selected='selected'";
          echo ">".__('Bottom Right', 'fup')."</option>\n"
               ."<option value='TR'";
          if(get_option(FUP_DEFAULT_WAT_LOC_OPTION) == 'TR')
              echo" selected='selected'";
          echo ">".__('Top Right', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Choose the where you want the watermark to be incrusted into your pictures.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DEFAULT_WAT_ORI_OPTION ?>"><?php
                     _e('Rotate watermark?', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<input type='checkbox' name='".FUP_DEFAULT_WAT_ORI_OPTION
               ."' id='".FUP_DEFAULT_WAT_ORI_OPTION."' value='ROT'";
          if (get_option(FUP_DEFAULT_WAT_ORI_OPTION) == 'ROT') {
              echo " checked='checked'";
          }
          echo " />\n";
          ?><br />
          <?php
              _e('Check this box if you want the watermark picture to be rotated 90 degrees counter-clockwise before incrusting it into your pictures.', 'fup');
          ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_WATERMARK_THUMB_OPTION ?>"><?php
                     _e('Watermark Thumbnails?', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<input type='checkbox' name='".FUP_WATERMARK_THUMB_OPTION
               ."' id='".FUP_WATERMARK_THUMB_OPTION."' value='true'";
          if (get_option(FUP_WATERMARK_THUMB_OPTION) == 'true') {
              echo " checked='checked'";
          }
          echo " />\n";
          ?><br />
          <?php
              _e('Check this box if you want the watermark picture to be added to thumbnails as well.', 'fup');
          ?>
        </td>
      </tr>	  
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_WAT_OPT_PER_PIC_OPTION ?>"><?php
                     _e('Specify watermark options for each picture?', 'fup') ?></label>
        </th>
        <td>
          <?php
          echo "<input type='checkbox' name='".FUP_WAT_OPT_PER_PIC_OPTION
               ."' id='".FUP_WAT_OPT_PER_PIC_OPTION."' value='true'";
          if (get_option(FUP_WAT_OPT_PER_PIC_OPTION) == 'true') {
              echo " checked='checked'";
          }
          echo " />\n";
          ?><br />
          <?php
              _e('Check this box if you want to be able to specify watermark location and orientation separately for each picture you upload.  Otherwise, the above setting will be applied to all the pictures.', 'fup');
          ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_JPEG_QUALITY_OPTION ?>"><?php _e('JPEG quality:', 'fup'); ?></label>
        </th>
        <td>
          <?php
          echo "<input type='text' size='8' ";
          echo "name='".FUP_JPEG_QUALITY_OPTION."' ";
          echo "id='".FUP_JPEG_QUALITY_OPTION."' ";
          echo "value='".get_option(FUP_JPEG_QUALITY_OPTION)."' />";
          ?><br />
          <?php _e('Specify the JPEG quality factor (from 0 to 100).  This option will be used when manipulating JPEG images.', 'fup'); ?>
        </td>
      </tr>
      <?php if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 1)) { ?>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_IMAGE_TITLE_OPTION ?>"><?php _e('Content of "title" attribute:', 'fup'); ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_IMAGE_TITLE_OPTION
               ."' id='".FUP_IMAGE_TITLE_OPTION."'>\n"
               ."<option value='title'";
          if(get_option(FUP_IMAGE_TITLE_OPTION) == 'title')
              echo " selected='selected'";
          echo ">".__('Image title (name)', 'fup')."</option>\n"
               ."<option value='description'";
          if(get_option(FUP_IMAGE_TITLE_OPTION) == 'description')
              echo " selected='selected'";
          echo ">".__('Image description', 'fup')."</option>\n";
          if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 5)) {
              echo "<option value='longdesc'";
              if(get_option(FUP_IMAGE_TITLE_OPTION) == 'longdesc')
                  echo " selected='selected'";
              echo ">".__('Long description', 'fup')."</option>\n";
          }
          echo "<option value='none'";
          if(get_option(FUP_IMAGE_TITLE_OPTION) == 'none')
              echo" selected='selected'";
          echo ">".__('Nothing', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Select what you want to see in the "title" attribute of the image.  Some plugins display this title as a legend below the image.', 'fup'); ?>
        </td>
      </tr>
      <?php } ?>
      <?php if (($fup_wpver[0] == 2) && ($fup_wpver[1] >= 5)) { ?>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_IMAGE_ALT_OPTION ?>"><?php _e('Content of "alt" attribute:', 'fup'); ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_IMAGE_ALT_OPTION
               ."' id='".FUP_IMAGE_ALT_OPTION."'>\n"
               ."<option value='title'";
          if(get_option(FUP_IMAGE_ALT_OPTION) == 'title')
              echo " selected='selected'";
          echo ">".__('Image title (name)', 'fup')."</option>\n"
               ."<option value='description'";
          if(get_option(FUP_IMAGE_ALT_OPTION) == 'description')
              echo " selected='selected'";
          echo ">".__('Image description', 'fup')."</option>\n"
               ."<option value='longdesc'";
          if(get_option(FUP_IMAGE_ALT_OPTION) == 'longdesc')
              echo " selected='selected'";
          echo ">".__('Long description', 'fup')."</option>\n"
               ."<option value='none'";
          if(get_option(FUP_IMAGE_ALT_OPTION) == 'none')
              echo" selected='selected'";
          echo ">".__('Nothing', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('Select what you want to see in the "alt" attribute of the image.', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DEFAULT_CAPTION_OPTION ?>"><?php _e('Image caption:', 'fup'); ?></label>
        </th>
        <td>
          <?php
          echo "<select name='".FUP_DEFAULT_CAPTION_OPTION
               ."' id='".FUP_DEFAULT_CAPTION_OPTION."'>\n"
               ."<option value='title'";
          if(get_option(FUP_DEFAULT_CAPTION_OPTION) == 'title')
              echo " selected='selected'";
          echo ">".__('Image title (name)', 'fup')."</option>\n"
               ."<option value='description'";
          if(get_option(FUP_DEFAULT_CAPTION_OPTION) == 'description')
              echo " selected='selected'";
          echo ">".__('Image description', 'fup')."</option>\n"
               ."<option value='longdesc'";
          if(get_option(FUP_DEFAULT_CAPTION_OPTION) == 'longdesc')
              echo " selected='selected'";
          echo ">".__('Long description', 'fup')."</option>\n"
               ."<option value='none'";
          if(get_option(FUP_DEFAULT_CAPTION_OPTION) == 'none')
              echo" selected='selected'";
          echo ">".__('Nothing', 'fup')."</option>\n"
               ."</select>";
          ?><br />
          <?php _e('If you want a caption, select the text to be displayed below your images. Otherwise, select "Nothing".  Note:<ul><li>For the caption feature to work properly, you need to use CSS alignment, and to either disable the editor cleanup (see below) or use a visual editor that does not mess up the div\'s.</li><li>You can customize the style of your captions using the "imagecaption" CSS class</li></ul>', 'fup'); ?>
        </td>
      </tr>
      <tr>
        <th width="30%" valign="top" style="padding-top: 10px;">
          <label for="<?php echo FUP_DISABLE_WPAUTOP_OPTION ?>"><?php _e('Disable editor cleanup (wpautop)?', 'fup'); ?></label>
        </th>
        <td>
          <?php
          echo "<input type='checkbox' name='".FUP_DISABLE_WPAUTOP_OPTION
               ."' id='".FUP_DISABLE_WPAUTOP_OPTION."' value='true'";
          if (get_option(FUP_DISABLE_WPAUTOP_OPTION) == 'true') {
              echo " checked='checked'";
          }
          echo " />\n";
          ?><br />
          <?php
              _e('Check this box if you want to disable WP editor "cleanup" feature.  If you don\'t disable it, you won\'t be able to add captions to your images (the div\'s will get screwed up by wpautop).<br />Note: if you do this, the Advanced Editor will not do any cleanup. If you make errors in your HTML that break the theme, you\'re on your own!', 'fup');
          ?>
        </td>
      </tr>
      <?php } ?>
    </table>

  </fieldset>
  
  <input type="hidden" name="action" value="update" />
  <p><input type="submit" name="Submit" value="<?php _e('Update Options', 'fup'); ?>" /></p>

</div>
</form>
<?php
}

?>
