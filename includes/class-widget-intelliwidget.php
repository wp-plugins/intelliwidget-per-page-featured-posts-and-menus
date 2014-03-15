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

    var $version     = '1.5.0';
    var $form;
    /**
     * Constructor
     */
    function __construct() {
        global $intelliwidget;
        $widget_ops          = array('description' => __('Menus, Featured Posts, HTML and more, customized per page or site-wide.', 'intelliwidget'));
        $control_ops         = array('width' => 400, 'height' => 350);
        add_action('wp_enqueue_scripts', array(&$this, 'enqueue_styles'));
        if (is_admin()):
            add_action('load-widgets.php', array(&$intelliwidget, 'admin_init') );
            // lazy load UI
            include_once('class-intelliwidget-form.php');
            $this->form = new IntelliWidgetForm();
        endif;
        $this->WP_Widget('intelliwidget', $intelliwidget->pluginName, $widget_ops, $control_ops);
    }
    /**
     * Stub for front-end css
     */
    function enqueue_styles() {
        global $intelliwidget;
        wp_enqueue_style('intelliwidget', $intelliwidget->get_stylesheet(false));
        if ($override = $intelliwidget->get_stylesheet(true)):
            wp_enqueue_style('intelliwidget-custom', $override);
        endif;
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
            if ($page_data = $this->get_page_data($args['widget_id'], $post_id)):
                // check for no-copy override
                if (empty($page_data['nocopy'])):
                    // if this page is using another page's settings and they exist for this widget, use them
                    if ($other_page_id = get_post_meta($post_id, '_intelliwidget_widget_page_id', true)) :
                        $page_data = $this->get_page_data($args['widget_id'], $other_page_id);
                    endif;
                endif;
                if (is_singular() && !empty($page_data)):
                    $intelliwidget->build_widget($args, $page_data, $post_id);
                    // done -- restore original post object and return
                    $post = $old_post;
                    return;
                endif;
            endif;
            // no page-specific settings, should we hide?
            if (!empty($instance['hide_if_empty'])):
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
    function get_page_data($widget_id, $post_id) {
        global $intelliwidget;
        // the box map stores meta box => widget id relations in page meta data
        $box_map = unserialize(get_post_meta($post_id, '_intelliwidget_map', true));
        if (is_array($box_map)):
            $widget_map = array_flip($box_map);
            // if two boxes point to the same widget, the second gets clobbered here
            if (array_key_exists($widget_id, $widget_map)):
                $box_id = $widget_map[$widget_id];
                // are there settings for this widget?
                if ($page_data = $intelliwidget->get_page_data($box_id, $post_id)):
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
        global $intelliwidget;
        foreach($intelliwidget->get_text_fields() as $field):
            // handle custom text
            if ( current_user_can('unfiltered_html') ):
                $instance[$field] =  $new_instance[$field];
            else:
                // raw html parser/cleaner-upper: see WP docs re: KSES
                $instance[$field] = stripslashes( 
                    wp_filter_post_kses( addslashes($new_instance[$field]) ) ); 
            endif;
        endforeach;
        // special handling for checkboxes:  
        foreach($intelliwidget->get_checkbox_fields() as $cb):
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
        //echo 'BEFORE defaults: ' . "\n" . print_r($instance, true) . "\n\n";
        global $intelliwidget;
        $instance = $intelliwidget->defaults($instance);
        //echo 'AFTER defaults: ' . "\n" . print_r($instance, true) . "\n\n";
        $this->form->form($instance, $this);
    }
    

}

// initialize the widget
add_action('widgets_init', create_function('', 'return register_widget("IntelliWidget_Widget");'));

