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
if ( !function_exists('get_the_intelliwidget_ID') ) {
    function get_the_intelliwidget_ID() {
        global $iwgt_post;
        return $iwgt_post->ID;
    }
}
if ( !function_exists('the_intelliwidget_ID') ) {
    function the_intelliwidget_ID() {
        echo get_the_intelliwidget_ID();
    }
}
if ( !function_exists('get_the_intelliwidget_image') ) {
    /**
     * Return the featured image if it exists and process based on settings
     *
     * @global <array> $this_instance
     * @global <object> $iwgt_post
     * @return <string> if exists, <boolean> false if none
     */
    function get_the_intelliwidget_image() {
        global $this_instance, $iwgt_post;
        if ($this_instance['image_size'] != 'none' && has_intelliwidget_image() ) :
            return '<a title="' . get_the_intelliwidget_title() . '" href="' . get_the_intelliwidget_url() . '">'
                . get_the_post_thumbnail(
                    $iwgt_post->ID, 
                    $this_instance['image_size'], 
                    array(
                        'title' => get_the_intelliwidget_title(), 
                        'class' =>'intelliwidget-image-'. $this_instance['image_size'],
                    )
                )
                . '</a>';
        endif;
        return false;
    }
}

if ( !function_exists('has_intelliwidget_image') ) {
    /**
     * Check if the post has a featured image.
     * 
     * @global <object> $iwgt_post
     * @return <boolean>
     */
    function has_intelliwidget_image() {
        global $iwgt_post;
        return !empty($iwgt_post->thumbnail_id);
    }
}

if ( !function_exists('the_intelliwidget_image') ) {
    /**
     * Display the featured post image.
     */
    function the_intelliwidget_image() {
        echo get_the_intelliwidget_image();
    }
}

if ( !function_exists('get_the_intelliwidget_excerpt') ) {
    /**
     * Return the excerpt to display with the current post.
     *
     * @global <array> $this_instance
     * @global <object> $iwgt_post
     * @return <string>
     */
    function get_the_intelliwidget_excerpt() {
        global $this_instance, $iwgt_post;
        // use excerpt text if it exists otherwise parse the main content
        $excerpt = empty($iwgt_post->post_excerpt) ?
            get_the_intelliwidget_content() : $iwgt_post->post_excerpt;
        return _intelliwidget_trim_excerpt($excerpt, $this_instance['length']);
    }
}

if ( !function_exists('the_intelliwidget_excerpt') ) {
    /**
     * Display the excerpt for the featured post.
     */
    function the_intelliwidget_excerpt() {
        echo get_the_intelliwidget_excerpt();
    }
}

if ( !function_exists('get_the_intelliwidget_content') ) {
    /**
     * Return the excerpt to display with the current post.
     *
     * @global <object> $iwgt_post
     * @return <string>
     */
    function get_the_intelliwidget_content() {
        global $iwgt_post;
        $content = $iwgt_post->post_content;
	    if ( strpos( $content, '<!--nextpage-->' ) ) {
    	    $content = preg_replace("#\s*<!\-\-nextpage\-\->.*#s", '', $content);
        }
        return $content;
    }
}

if ( !function_exists('the_intelliwidget_content') ) {
    /**
     * Display the excerpt for the featured post.
     */
    function the_intelliwidget_content() {
        echo get_the_intelliwidget_content();
    }
}

if ( !function_exists('get_the_intelliwidget_link') ) {
    /**
     * Return a link for a post based on parameters
     *
     * @global <object> $iwgt_post
     * @param <integer> $post_id (optional)
     * @param <string> $link_text (optional) - text inside area tag
     * @param <integer> $category_id (optional) - return category permalink
     * @return <string>
     */
    function get_the_intelliwidget_link($post_id = NULL, $link_text = NULL, $category_id = NULL) {
        global $iwgt_post;
        $post_id =  intval($post_id) ? $post_id : $iwgt_post->ID;
        if (empty( $link_text )):
            $link_text = get_the_intelliwidget_title($post_id);
        endif;
        $url = get_the_intelliwidget_url($post_id, $category_id);
        $classes = empty($iwgt_post->link_classes) ? '' :  ' class="' . $iwgt_post->link_classes . '"';
        $target = empty($iwgt_post->link_target) ? '' : ' target="' . $iwgt_post->link_target . '"';
        $content = '<a title="' . $link_text . '" href="' . $url . '"' . $classes . $target . '>' . $link_text .  '</a>';
        return $content;
    }
}

if ( !function_exists('get_the_intelliwidget_url')) {
    /**
     * Return a url for a post based on parameters
     *
     * @global <object> $iwgt_post
     * @param <integer> $post_id (optional)
     * @param <integer> $category_id (optional) - return category url
     * @return <string>
     */
    function get_the_intelliwidget_url($post_id = NULL, $category_id = NULL) {
        global $iwgt_post;
        $post_id = intval($post_id) ? $post_id : $iwgt_post->ID;
        if (intval($category_id) && $category_id != -1):
            return get_category_link($category_id);
        else:
            return empty($iwgt_post->external_url) ? get_permalink($post_id) : $iwgt_post->external_url;
        endif;
    }
}

if ( !function_exists('the_intelliwidget_link') ) {
    /**
     * Display a link for a post based on parameters
     *
     * @param <integer> $post_id (optional)
     * @param <strong> $link_text (optional) - text inside area tag
     * @param <integer> $category_id (optional) - return category permalink
     */
    function the_intelliwidget_link($post_id = NULL, $title = NULL, $category_id = NULL) {
        echo get_the_intelliwidget_link($post_id, $title, $category_id);
    }
}

if ( !function_exists('get_the_intelliwidget_title') ) {
    /**
     * Get the title for the current featured post, use alt title if it exists.
     *
     * @global <object> $iwgt_post
     * @return <string>
     */
    function get_the_intelliwidget_title() {
        global $iwgt_post;
        return empty($iwgt->alt_title) ? $iwgt_post->post_title : $iwgt_post->alt_title;
    }
}
    /**
     * Display the title for the current featured post, use alt title if it exists.
     */

if ( !function_exists('the_intelliwidget_title') ) {
    function the_intelliwidget_title() {
        echo get_the_intelliwidget_title();
    }
}

if ( !function_exists('get_the_intelliwidget_date') ) {
    /**
     * Get the event date for the post if it exists, otherwise return the post date.
     *
     * @global <object> $iwgt_post
     * @param <string> $format
     * @return <string>
     */
    function get_the_intelliwidget_date($format = 'j') {
        global $iwgt_post;
        $date = empty($iwgt_post->event_date) ? $iwgt_post->post_date : $iwgt_post->event_date;
        return date($format, strtotime($date));
    }
}
    /**
     * Display the event date if it exists otherwise display post date.
     */

if ( !function_exists('the_intelliwidget_date') ) {
    function the_intelliwidget_date($format = 'j') {
        echo get_the_intelliwidget_date($format);
    }
}

if ( !function_exists('_intelliwidget_trim_excerpt') ) {
    /**
     * Trim the content to a set number of words.
     *
     * @param <string> $text
     * @param <integer> $length
     * @return <string>
     */
    function _intelliwidget_trim_excerpt($text, $length = 15) {
        $text = apply_filters('the_content', $text);
        $text = str_replace(']]>', ']]&gt;', $text);
        $text = preg_replace('@<script[^>]*?>.*?</script>@si', '', $text);
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
     * @global <object> $intelliwidget
     * @global <object> $post
     * @global <array> $this_instance
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