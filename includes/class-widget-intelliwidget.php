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

    var $version     = '1.0';

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
        global $post;
        	// save global post object for later
        $old_post = $post;
		if (is_object($post)):
        	// if there are page-specific settings for this widget, use them
        	if ($page_data = $this->get_page_data($post->ID, $args['widget_id'])):
        	    $this->build_widget($args, $page_data);
        	    // done -- restore original post object and return
        	    $post = $old_post;
        	    return;
        	// if this page is using another page's settings and they exist for this widget, use them
        	elseif (($other_page_id = get_post_meta($post->ID, '_intelliwidget_widget_page_id', true))) :
        	    if ($page_data = $this->get_page_data($other_page_id, $args['widget_id'])):
        	        $this->build_widget($args, $page_data);
        	        // done -- restore original post object and return
        	        $post = $old_post;
        	        return;
        	    endif;
        	endif;
		endif;
        // no page-specific settings, should we hide?
        if ($instance['hide_if_empty']):
            // done -- restore original post object and return
            $post = $old_post;
            return;
        endif;
        // if we get here, there are no page settings and no hide setting, so use the widget settings
        $this->build_widget($args, $instance);
        // done -- restore original post object and return
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
     * Output the widget using selected template
     *
     * @param <array> $args
     * @param <array> $instance
     * @return void
     */
    function build_widget($args, $instance) {
        global $intelliwidget, $post, $this_instance;
        $this_instance = $instance = $intelliwidget->defaults($instance);
        if (!is_array($instance['page'])) $instance['page'] = array($instance['page']);
        if (!is_array($instance['post_types'])) $instance['post_types'] = array($instance['post_types']);
        extract($args, EXTR_SKIP);
        /* Remove current page from list of pages if set */
        if ( $instance['skip_post'] and in_array($post->ID, $instance['page']) ) {
            $pages = array_flip($instance['page']);
            unset($pages[$post->ID]);
            $instance['page'] = array_flip($pages);
        }
        $args = array(
            'posts_per_page'      => intval($instance['items']),
            'orderby'             => $instance['sortby'],
            'order'               => $instance['sortorder'],
            'post_status'         => 'publish',
            'post_type'           => $instance['post_types'],
            'ignore_sticky_posts' => true,
        );
        
        /* Get the list of pages */
        if ( $instance['category'] != -1 ) {
            // get future only if this is event list
            if ($instance['future_only']):
                $args['post_status'] = 'future';        
            endif;
            $pages = array_flip($instance['post_types']);
            unset($pages['page']);
            $instance['post_types'] = array_flip($pages);
            $args['category__in'] = $instance['category'];
        } else {
            $args['post__in'] = $instance['page'];
        }
        $selected = new WP_Query($args);
        
        
        /* Output the widget */
        $classes = array();
        if (!empty($instance['classes'])) :
            $classes = explode("[, ;]", $instance['classes']);
        endif;
        if (!empty($classes)):
            $before_widget = preg_replace('/class="/', 'class="' . implode(" ", $classes) . ' ', $before_widget);
        endif;
        echo $before_widget;
        // handle title
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );
        if ( !empty( $title ) ) {
            echo $before_title;
            if ( $instance['link_title'] ) {
                // @params $post_ID, $text, $category_ID
                the_intelliwidget_link($selected->posts[0]->ID, $title, $instance['category']);
            } else {
                echo $title;
            }
            echo $after_title;
        }
        // handle custom text
        $custom_text = apply_filters( 'widget_text', $instance['custom_text'], $instance );
        if (($instance['text_position'] == 'above' || $instance['text_position'] == 'only')):
            echo '<div class="textwidget">' . ( !empty( $instance['filter'] ) ? 
                wpautop( $custom_text ) : $custom_text ) . '</div>';
        endif;
        if ($instance['text_position'] == 'only'):
            echo $after_widget;
            return;
        endif;
		// temporarily disable wpautop if it is on
		if ($has_content_filter = has_filter('the_content', 'wpautop'))
			remove_filter( 'the_content', 'wpautop' );
        include ($intelliwidget->get_template($instance['template']));
		// restore wpautop if it was on
        if ($has_content_filter)
			add_filter( 'the_content', 'wpautop' );
        if ($instance['text_position'] == 'below'):
            echo "<div class=\"textwidget\">\n" . ( !empty( $instance['filter'] ) ? 
                wpautop( $custom_text ) : $custom_text ) . "\n</div>\n";
        endif;
        echo $after_widget;
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
        foreach(array('skip_post', 'link_title', 'hide_if_empty', 'filter', 'future_only') as $cb):
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
        if (!is_array($instance['page'])) $instance['page'] = array($instance['page']);
        if (!is_array($instance['post_types'])) $instance['post_types'] = array($instance['post_types']);
        include( $intelliwidget->pluginPath . 'includes/widget-form.php');
    }
    
        
}
// initialize the widget
add_action('widgets_init', create_function('', 'return register_widget("IntelliWidget_Widget");'));

