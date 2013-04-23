<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * template-tags.php - Global functions for the IntelliWidget plugin.
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
if ( !function_exists('get_the_intelliwidget_image') ) {
    /**
     * Return the featured image if it exists and process based on settings
     * 
     * @param int $post_id (optional)
     * @return <string> if exists, <boolean> false if none
     */
    function get_the_intelliwidget_image($post_id = NULL) {
        global $this_instance;
        $post_id = ( NULL === $post_id ) ? get_the_ID() : $post_id;
        if ($this_instance['image_size'] != 'none' && function_exists('has_post_thumbnail') && has_post_thumbnail() ) :
            return '<a title="' . get_the_intelliwidget_title() . '" href="' . get_the_intelliwidget_url() . '">'
                . get_the_post_thumbnail($post_id, $this_instance['image_size'], array('title' => get_the_intelliwidget_title(), 'class'=>'intelliwidget-image-'. $this_instance['image_size']))
                . '</a>';
        else:
            return false;
        endif;
    }
}

if ( !function_exists('has_intelliwidget_image') ) {
    /**
     * Check if the post has a featured image.
     * 
     * @param <integer> $post_id
     * @return <boolean>
     */
    function has_intelliwidget_image($post_id = NULL) {
        $image = get_the_intelliwidget_image($post_id);
		return !empty($image);
    }
}

if ( !function_exists('the_intelliwidget_image') ) {
    /**
     * Display the featured post image.
     *
     * @param int $post_id (optional)
     */
    function the_intelliwidget_image($post_id = NULL) {
        echo get_the_intelliwidget_image($post_id);
    }
}

if ( !function_exists('get_the_intelliwidget_excerpt') ) {
    /**
     * Return the excerpt to display with the current post.
     *
     * @global <array> $this_instance
     * @param  <integer> $post_id (optional)
     * @return <string>
     */
    function get_the_intelliwidget_excerpt($post_id = NULL) {
        global $this_instance;
        $post_id = ( NULL === $post_id ) ? get_the_ID() : $post_id;
        // use excerpt text if it exists otherwise parse the main content
        $content = preg_replace("#\[\.\.\.\]#", '', get_the_excerpt()); //"[...]"
        if ( empty($content)):
            $content = get_the_content();
        endif;
        $content = _intelliwidget_trim_excerpt($content, $this_instance['length']);
        return $content;
    }
}

if ( !function_exists('the_intelliwidget_excerpt') ) {
    /**
     * Display the excerpt for the featured post.
     *
     * @param <integer> $post_id (optional)
     */
    function the_intelliwidget_excerpt($post_id = NULL) {
        echo get_the_intelliwidget_excerpt($post_id);
    }
}

if ( !function_exists('get_the_intelliwidget_link') ) {
    /**
     * Return a link for a post based on parameters
     *
     * @param <integer> $post_id (optional)
     * @param <string> $link_text (optional) - text inside area tag
     * @param <integer> $category_ID (optional) - return category permalink
     * @return <string>
     */
    function get_the_intelliwidget_link($post_ID = NULL, $link_text = NULL, $category_ID = NULL) {
        global $this_instance;
        $post_ID = intval($post_ID) ? $post_ID : get_the_ID();
        if (empty( $link_text )):
            $link_text = get_the_intelliwidget_title($post_ID);
        endif;
		$url = get_the_intelliwidget_url($post_ID, $category_ID);
        if (! $classes = get_post_meta($post_ID, 'intelliwidget_classes', true) ) $classes = '';
        $classes = ' class="' . $classes . '"';
        if (! $target = get_post_meta($post_ID, 'intelliwidget_target', true) ) $target = '';
        $target = empty($target) ? '' : ' target="' . $target . '"';
        $content = '<a title="' . $link_text . '" href="' . $url . '"' . $classes . $target . '>' . $link_text .  '</a>';
        return $content;
    }
}

if ( !function_exists('get_the_intelliwidget_url')) {
    /**
     * Return a url for a post based on parameters
     *
     * @param <integer> $post_id (optional)
     * @param <integer> $category_ID (optional) - return category url
     * @return <string>
     */
	function get_the_intelliwidget_url($post_ID = NULL, $category_ID = NULL) {
        global $this_instance;
        $post_ID = intval($post_ID) ? $post_ID : get_the_ID();
        if (intval($category_ID) && $category_ID != -1):
            $url = get_category_link($category_ID);
        else:
            if (! $url = get_post_meta($post_ID, 'intelliwidget_external_url', true) ) $url = get_permalink($post_ID);
        endif;
		return $url;
	}
}

if ( !function_exists('the_intelliwidget_link') ) {
    /**
     * Display a link for a post based on parameters
     *
     * @param <integer> $post_id (optional)
     * @param <strong> $link_text (optional) - text inside area tag
     * @param <integer> $category_ID (optional) - return category permalink
     * @return <string>
     */
    function the_intelliwidget_link($post_ID = NULL, $title = NULL, $category_ID = NULL) {
        echo get_the_intelliwidget_link($post_ID, $title, $category_ID);
    }
}

if ( !function_exists('get_the_intelliwidget_title') ) {
    /**
     * Get the title for the current featured post, use alt title if it exists.
     *
     * @global <array> $this_instance
     * @param <integer> $post_id
     * @return <string>
     */
    function get_the_intelliwidget_title($post_id = NULL) {
        global $this_instance;
        $post_id = ( NULL === $post_id ) ? get_the_ID() : $post_id;
        if ( $alt_title = get_post_meta($post_id, 'alt_title', true) ):
            return $alt_title;
        else:
            return get_the_title();
        endif;
    }
}

if ( !function_exists('the_intelliwidget_title') ) {
    function the_intelliwidget_title($post_id = NULL) {
        echo get_the_intelliwidget_title($post_id);
    }
}

if ( !function_exists('_intelliwidget_trim_excerpt') ) {
    /**
     * Trim the content to a set number of words.
     *
     * @global <object> $post
     * @param <string> $text
     * @param <integer> $length
     * @return <string>
     */
    function _intelliwidget_trim_excerpt($text, $length = 15) {
        global $post;
        $text = apply_filters('the_content', $text);
        $text = str_replace(']]>', ']]&gt;', $text);
        $text = preg_replace('@<script[^>]*?>
.*?</script>@si', '', $text);
        $text = strip_tags($text);
        $words = preg_split("#\s+#s", $text, $length + 1);
        if ( count($words) > $length ) {
            array_pop($words);
            array_push($words, '...');
            $text = implode(' ', $words);
        }
        return $text;
    }
}

if ( !function_exists('intelliwidget_shortcode') ) {

    /**
     * Shortcode handler
     *
     * @global <object> $post
     * @param <array> $atts
     * @return <string>
     */

    function intelliwidget_shortcode($atts) {
        global $intelliwidget, $post, $this_instance;
        $old_post = $post;
        $instance = $this_instance = $atts = $intelliwidget->defaults($atts);
        // Get the list of pages
        $args = array(
            'post__in'                => (is_array($atts['pages'])) ? $atts['pages'] : explode(',', $atts['pages']),
            'posts_per_page'       => $atts['items'],
            'orderby'              => $atts['sortby'],
            'order'                  => $atts['sortorder'],
            'post_type'              => $atts['post_types'],
            'ignore_sticky_posts' => $atts['ignore_sticky'],
        );
        $selected = new WP_Query($args);
        // create and return the content
        ob_start();
		// temporarily disable wpautop if it is on
		if ($has_content_filter = has_filter('the_content', 'wpautop'))
			remove_filter( 'the_content', 'wpautop' );
        include ($intelliwidget->get_template($atts['template']));
		// restore wpautop if it was on
        if ($has_content_filter)
			add_filter( 'the_content', 'wpautop' );
        $content = ob_get_contents();
        ob_end_clean();
        $post = $old_post;
        return $content;
    }
       
}
add_shortcode('intelliwidget', 'intelliwidget_shortcode'); 