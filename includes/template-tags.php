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
    /**
     * Return the post ID
     *
     * @return <integer> post ID
     */
    function get_the_intelliwidget_ID() {
        global $post;
        return $post->ID;
    }
}
if ( !function_exists('the_intelliwidget_ID') ) {
    /**
     * Display the post ID
     */
    function the_intelliwidget_ID() {
        echo get_the_intelliwidget_ID();
    }
}
if ( !function_exists('get_the_intelliwidget_image') ) {
    /**
     * Return the featured post image with link to the full image.
     *
     * @global <array> $this_instance
     * @global <object> $post
     * @return <string> image link if exists, <boolean> false if none
     */
    function get_the_intelliwidget_image() {
        global $this_instance, $post;
        if ($this_instance['image_size'] != 'none' && has_intelliwidget_image() ) :
            return '<a title="' . strip_tags(get_the_intelliwidget_title()) . '" href="' . get_the_intelliwidget_url() . '">'
                . get_the_post_thumbnail(
                    $post->ID, 
                    $this_instance['image_size'], 
                    array(
                        'title' => strip_tags(get_the_intelliwidget_title()), 
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
     * @global <object> $post
     * @return <boolean>
     */
    function has_intelliwidget_image() {
        global $this_instance, $post;
        return !($this_instance['image_size'] == 'none' || empty($post->thumbnail_id));
    }
}

if ( !function_exists('the_intelliwidget_image') ) {
    /**
     * Display the featured post image with link to the full image.
     */
    function the_intelliwidget_image() {
        echo get_the_intelliwidget_image();
    }
}

if ( !function_exists('get_the_intelliwidget_author') ) {
    /**
     * Return meta data from the post author
     *
     * @param <string> $meta - field to retrieve
     * @global <object> $post
     * @return <string>
     */
    function get_the_intelliwidget_author_meta($meta) {
        global $post;
        if ($value = get_the_author_meta($meta, $post->post_author)) return $value;
        return false;
    }
}
if ( !function_exists('the_intelliwidget_author_meta') ) {
    /**
     * Display meta data from the post author
     *
     * @param <integer> $meta - field to retrieve
     * @return void
     */
    function the_intelliwidget_author_meta($meta = 'display_name') {
        if ($value = get_the_intelliwidget_author_meta($meta)) echo $value;
    }
}

if ( !function_exists('get_the_intelliwidget_excerpt') ) {
    /**
     * Return the excerpt to display with the current post.
     *
     * @global <array> $this_instance
     * @global <object> $post
     * @return <string>
     */
    function get_the_intelliwidget_excerpt() {
        global $intelliwidget, $this_instance, $post;
        // use excerpt text if it exists otherwise parse the main content
        $excerpt = empty($post->post_excerpt) ?
            get_the_intelliwidget_content() : $post->post_excerpt;
        return $intelliwidget->trim_excerpt($excerpt, $this_instance);
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
     * @global <object> $post
     * @return <string>
     */
    function get_the_intelliwidget_content() {
        global $post;
        $content = $post->post_content;
        if ( strpos( $content, '<!--nextpage-->' ) ) {
            $content = preg_replace("#\s*<!\-\-nextpage\-\->.*#s", '', $content);
        }
        // remove intelliwidget shortcode to stop endless recursion
        if ( strpos( $content, '[intelliwidget' )) {
            $content = preg_replace("#\[intelliwidget.*?\]#s", '', $content);
        }
        // otherwise, parse shortcodes
        return do_shortcode($content);
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
     * @global <object> $post
     * @param <integer> $post_id (optional)
     * @param <string> $link_text (optional) - text inside area tag
     * @param <integer> $category_id (optional) - return category permalink
     * @return <string>
     */
    function get_the_intelliwidget_link($post_id = NULL, $link_text = NULL, $category_id = NULL) {
        global $post;
        $post_id =  intval($post_id) ? $post_id : (is_object($post) ? $post->ID : NULL);
        if (isset($category_id) && -1 != $category_id) $url = get_category_link($category_id);
        $url     = isset($url) ? $url : get_the_intelliwidget_url($post_id);
        if (empty( $link_text )):
            $link_text = get_the_intelliwidget_title($post_id);
        endif;
        $title_text = esc_attr(strip_tags($link_text));
        $classes = empty($post->link_classes) ? '' :  ' class="' . $post->link_classes . '"';
        $target  = empty($post->link_target) ? '' : ' target="' . $post->link_target . '"';
        $content = '<a title="' . $title_text . '" href="' . $url . '"' . $classes . $target . '>' . $link_text .  '</a>';
        return $content;
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
    function the_intelliwidget_link($post_id = NULL, $title = NULL) {
        echo get_the_intelliwidget_link($post_id, $title);
    }
}

if ( !function_exists('get_the_intelliwidget_url')) {
    /**
     * Return a url for a post based on parameters
     *
     * @global <object> $post
     * @param <integer> $post_id (optional)
     * @param <integer> $category_id (optional) - return category url
     * @return <string>
     */
    function get_the_intelliwidget_url($post_id = NULL) {
        global $post;
        $post_id = intval($post_id) ? $post_id : $post->ID;
        return empty($post->external_url) ? get_permalink($post_id) : $post->external_url;
    }
}
if ( !function_exists('get_the_intelliwidget_taxonomy_link')) {

    function get_the_intelliwidget_taxonomy_link($title, $instance) {
        if (isset($instance['terms']) && '-1' != $instance['terms']):
            $term = $instance['query']->terms_query($instance['terms']);
            if ($term):
                $url = get_term_link($term);
                $title_text = esc_attr(strip_tags($title));
                return '<a title="' . $title_text . '" href="' . $url . '">' . apply_filters( 'widget_title', $title ) .  '</a>';
            endif;
        endif;
        $post_id = NULL;
        if (count($instance['query']->posts))
            $post_id = $instance['query']->posts[0]->ID;
        return get_the_intelliwidget_link($post_id, $title, (isset($instance['category']) ? $instance['category'] : NULL));
    }
}

if ( !function_exists('the_intelliwidget_url')) {
    /**
     * Display a url for a post based on parameters
     *
     * @param <integer> $post_id (optional)
     * @param <integer> $category_id (optional) - return category url
     * @return <string>
     */
    function the_intelliwidget_url($post_id = NULL, $category_id = NULL) {
        echo get_the_intelliwidget_url($post_id, $category_id);
    }
}

if ( !function_exists('get_the_intelliwidget_title') ) {
    /**
     * Get the title for the current featured post, use alt title if it exists.
     *
     * @global <object> $post
     * @return <string>
     */
    function get_the_intelliwidget_title() {
        global $post;
        $title = empty($post->alt_title) ? $post->post_title : $post->alt_title;
        return $title; //esc_attr($title);
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
     * @global <object> $post
     * @param <string> $format
     * @return <string>
     */
    function get_the_intelliwidget_date($format = 'j') {
        global $post;
        $date = empty($post->event_date) ? $post->post_date : $post->event_date;
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

if ( !function_exists('get_the_intelliwidget_exp_date') ) {
    /**
     * Get the event date for the post if it exists, otherwise return the post date.
     *
     * @global <object> $post
     * @param <string> $format
     * @return <string>
     */
    function get_the_intelliwidget_exp_date($format = 'j') {
        global $post;
        if (empty($post->expire_date) || 
            (date('j', strtotime($post->event_date)) == date('j', strtotime($post->expire_date)) 
                && date('m', strtotime($post->event_date)) == date('m', strtotime($post->expire_date)))):
            return false;
        else:
            return date($format, strtotime($post->expire_date));
        endif;
    }
}
    /**
     * Display the event date if it exists otherwise display post date.
     */

if ( !function_exists('the_intelliwidget_exp_date') ) {
    function the_intelliwidget_exp_date($format = 'j') {
        if ($exp = get_the_intelliwidget_exp_date($format)) echo $exp;
    }
}

