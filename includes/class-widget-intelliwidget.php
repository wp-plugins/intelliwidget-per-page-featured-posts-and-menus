<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-widget-intelliwidget.php - IntelliWidget Widget Class
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
class IntelliWidget_Widget extends WP_Widget {

    var $version     = '1.3.2';

    /**
     * Constructor
     */
    function __construct() {
        global $intelliwidget;
        $widget_ops          = array('description' => __('Menus, Featured Posts, HTML and more, customized per page or site-wide.', 'intelliwidget'));
        $control_ops         = array('width' => 400, 'height' => 350);
        add_action('wp_print_styles', array(&$this, 'wp_print_styles'));
        $this->WP_Widget('intelliwidget', $intelliwidget->pluginName, $widget_ops, $control_ops);
    }
    /**
     * Stub for front-end css
     */
    function wp_print_styles() {
        global $intelliwidget;
        wp_register_style('intelliwidget', $intelliwidget->get_template('intelliwidget', '.css', 'url'));
        wp_enqueue_style('intelliwidget');
    }
    
    /**
     * Main widget logic - determine if this is a customized page, copied page or global widget
     *
     * @param <array> $args
     * @param <array> $instance
     * @return false
     */
    function widget($args, $instance) {
        global $post, $intelliwidget;
        // save global post object for later
        $old_post = $post;
        $post_id = is_object($post) ? $post->ID : null;
        if ($post_id):            
            // if there are page-specific settings for this widget, use them
            $page_data = $this->get_page_data($post_id, $args['widget_id']);
            // check for no-copy override
            if (empty($page_data['nocopy'])):
                // if this page is using another page's settings and they exist for this widget, use them
                if ($other_page_id = get_post_meta($post_id, '_intelliwidget_widget_page_id', true)) :
                    $page_data = $this->get_page_data($other_page_id, $args['widget_id']);
                endif;
            endif;
            if (!empty($page_data)):
                $intelliwidget->build_widget($args, $page_data, $post_id);
                // done -- restore original post object and return
                $post = $old_post;
                return;
            endif;
            // no page-specific settings, should we hide?
            if ($instance['hide_if_empty']):
                // done -- restore original post object and return
                $post = $old_post;
                return;
            endif;
            // if we get here, there are no page settings and no hide setting, so use the primary widget settings
            $intelliwidget->build_widget($args, $instance, $post_id);
        // done -- restore original post object and return
        endif;
        $post = $old_post;
    }
    
    /**
     * For customized pages, retrieve the page-specific instance settings for the particular widget
     * being replaced
     *
     * @param <integer> $post_id
     * @param <string> $widget_id
     * @return <array> if exists or false if empty
     */
    function get_page_data($post_id, $widget_id) {
        // the box map stores meta box => widget id relations in page meta data
        $box_map = unserialize(get_post_meta($post_id, '_intelliwidget_map', true));
        if (is_array($box_map)):
            $widget_map = array_flip($box_map);
            // if two boxes point to the same widget, the second gets clobbered here
            if (array_key_exists($widget_id, $widget_map)):
                $box_id = $widget_map[$widget_id];
                // are there settings for this widget?
                if ($page_data = unserialize(get_post_meta($post_id, '_intelliwidget_data_' . $box_id, true))):
                    if (!empty($page_data['custom_text'])):
                        // base64 encoding saves us from markup serialization heartburn
                        $page_data['custom_text'] = stripslashes(base64_decode($page_data['custom_text']));
                    endif;
                    return $page_data;
                endif;
            endif;
        endif;
        // all failures fall through gracefully
        return false;
    }
    
    /**
     * Widget Update method
     * @param <array> $new_instance
     * @param <array> $old_instance
     * @return <array>
     */
    function update($new_instance, $old_instance) {
        $instance = $new_instance;
        // handle custom text
        if ( current_user_can('unfiltered_html') ):
            $instance['custom_text'] =  $new_instance['custom_text'];
        else:
            $instance['custom_text'] = stripslashes( wp_filter_post_kses( addslashes($new_instance['custom_text']) ) ); 
        endif;
        // special handling for checkboxes: //'replace_widget', 
        foreach(array('skip_expired', 'skip_post', 'link_title', 'hide_if_empty', 'filter', 'future_only', 'active_only') as $cb):
            $instance[$cb] = isset($new_instance[$cb]);
        endforeach;
        return $instance;
    }
    /**
     * Output Widget form
     *
     * @param <array> $instance
     */
    function form($instance) {
        global $intelliwidget;
        // fill in any missing fields from unserialize or unsaved instance
        $instance = $intelliwidget->defaults($instance);
        // normalize page and post_types into array datatype
        if (!is_array($instance['post_types'])) $instance['post_types'] = array($instance['post_types']);
        include( $intelliwidget->pluginPath . 'includes/widget-form.php');
    }
    

}

// initialize the widget
add_action('widgets_init', create_function('', 'return register_widget("IntelliWidget_Widget");'));

