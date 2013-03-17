<?php
/*
Plugin Name: iosSlider Gallery
Plugin URI: http://www.patrickmjones.com
Description: Replaces the default gallery with ioslider gallery
Version: 1.0.0
Author: Patrick Jones
Author URI: http://www.patrickmjones.com
License: CC BY 3.0
Note: 
	This uses the excellent javascript gallery iosSlider which is licensed under 
	http://creativecommons.org/licenses/by-nc/3.0/.  
	For more information please visit http://www.iosscripts.com/iosslider/#licensing.  
	Many thanks to iosscripts for their hard work on this library!

	Commercial use of this plugin is allowed where appropriate commmercial license 
	for iosSlider has already been obtained.
*/

class pjones_iosslider_gallery {
	function pjones_iosslider_gallery() {
		$plugin_dir = basename(dirname(__FILE__));
		$lang_dir = realpath($plugin_dir . DIRECTORY_SEPARATOR . "lang");
		load_plugin_textdomain( 'pjones_iosslider_gallery', false, $lang_dir );

		if(!is_feed()) {
			remove_shortcode('gallery');
			add_shortcode('gallery', array(&$this, 'gallery_shortcode'));
		}
	}

	function gallery_shortcode($attr) {
		global $post;
		if (isset($attr['orderby']))
		{
			$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
			if ( !$attr['orderby'] )
				unset( $attr['orderby'] );
		}
		extract(shortcode_atts(array(
			'orderby'		=> 'menu_order ASC, ID ASC',
			'id'			=> $post->ID,
			'include'		=> '',
			'ids'			=> ''
		), $attr));

		$id = intval($id);
		$include = preg_replace( '/[^0-9,]+/', '', $include );

		$get_posts_args = array(
								'include'			=> $ids,
								'post_type'			=> 'attachment',
								'post_mime_type'	=> 'image',
								'orderby'			=> $orderby
							);
		if(!empty($ids)) {
			$ids = preg_replace( '/[^0-9,]+/', '', $ids );
			$get_posts_args['include'] = $ids;
		} else {
			$get_posts_args['post_parent'] = $id;
		}

		$_attachments = get_posts($get_posts_args);
		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}

		$output = <<<html
		<!-- slider container -->
		<div class = 'iosSlider'>
			<!-- slider -->
			<div class = 'slider'>	
html;

		foreach ( $attachments as $attachmentid => $attachment ) {
			$a_img = wp_get_attachment_url($attachmentid);
			$att_page = get_attachment_link($attachmentid);
			$img = wp_get_attachment_image_src($attachmentid, $size);
			$img = $img[0];
			$desc = $attachment -> post_content;
			$title = $attachment -> post_excerpt;
			if($title == '') $title = $attachment->post_title;
		
	
			$output .= <<<html
			<div class = 'slide'>
				<a href="{$a_img}" class="thickbox">
					<img src="{$a_img}" alt="{$title}" />
				</a>
			</div>	
html;
		}
	
	    $output .= '</div></div>';
		$output .= <<<html
		<script src="http://www.iosscripts.com/iosslider/_js/jquery.iosslider.min.js" type="text/javascript"></script>
		<script type="text/javascript">
			if(window.jQuery) {
				jQuery(document).ready(function() {
					/* basic - default settings */
					jQuery('.iosSlider').iosSlider({
						snapToChildren: true,
						desktopClickDrag: true,
						infiniteSlider: true						
					});
				});
			}
		</script>
		<style type="text/css">
			/* slider container */
			.iosSlider {
				/* required */
				position: relative;
				top: 0;
				left: 0;
				overflow: hidden;
	
				width: 600px;
				height: 400px;
				margin-bottom: 1em;
			}

			/* slider */
			.iosSlider .slider {
				/* required */
				width: 100%;
				height: 100%;
			}

			/* slide */
			.iosSlider .slider .slide {
				/* required */
				float: left;

				width: 600px;
				height: 400px;
			}
			.iosSlider .slider .slide img {
				max-width: 589px;
				max-height: 400px;
				display: block;
				margin: 0 auto;
			}

		</style>
html;
		return $output;
	}
}

add_action("init", "pjones_iosslider_gallery_init");
function pjones_iosslider_gallery_init() { 
	global $pjones_iosslider_gallery_instance; 
	$pjones_iosslider_gallery_instance = new pjones_iosslider_gallery(); 
}
?>
