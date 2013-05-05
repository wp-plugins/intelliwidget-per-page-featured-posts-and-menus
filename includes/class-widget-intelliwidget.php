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
		/* if this is a nav menu get menu object and skip query */
		$nav_menu = false;
		if ($instance['template'] == 'WP_NAV_MENU' && ! empty( $instance['nav_menu'] )) :
			$nav_menu =  wp_get_nav_menu_object( $instance['nav_menu'] );
		else:
			// we need custom SQL filters to make the expiration dates work
		    add_filter('posts_join', array(&$this, 'intelliwidget_query_posts_join'), 10, 2);
			// join returns multiple meta rows for each post, so we need a groupby clause
		    add_filter('posts_groupby', array(&$this, 'intelliwidget_query_posts_groupby'), 10, 2);

        	/* Remove current page from list of pages if set */
        	if ( $instance['skip_post'] && !empty($post) && in_array($post->ID, $instance['page']) ) {
        	    $pages = array_flip($instance['page']);
        	    unset($pages[$post->ID]);
        	    $instance['page'] = array_flip($pages);
        	}
        	$args = array(
        	    'posts_per_page'      => $instance['items'],
        	    'orderby'             => $instance['sortby'],
        	    'order'               => $instance['sortorder'],
        	    'post_status'         => 'publish',
        	    'post_type'           => $instance['post_types'],
        	    'ignore_sticky_posts' => true,
        	);
        	if ($instance['sortby'] == 'meta_value')
				$args['meta_key'] = 'intelliwidget_event_date';
				
        	// use future events only
			// note postmeta intelliwidget_event_date date format 
			// MUST be YYYY-MM-DD HH:MM for this to work correctly!
        	if ($instance['future_only']):
				$args['meta_query'] = array(
					array(
                    	'key'     => 'intelliwidget_event_date',
                    	'value'   => date('Y-m-d H:i'),
                    	'compare' => '>',
					)
				);
        	endif;
        	// skip expired posts
        	if ($instance['skip_expired']):
			    // turn off supression if it is on
				$args['suppress_filters'] = false;
				// tell filter to do its thing
				$args['iw_skip_expired'] = true;
        	endif;
        	/* Get the list of pages */
        	if ( $instance['category'] != -1 ):
        	    $pages = array_flip($instance['post_types']);
        	    unset($pages['page']);
        	    $instance['post_types'] = array_flip($pages);
        	    $args['category__in'] = $instance['category'];
        	elseif (!empty($instance['page'])):
        	    $args['post__in'] = $instance['page'];
            endif;
        	$selected = new WP_Query($args);
			// clean up the custom SQL filters
			remove_filter('posts_join', array(&$this, 'intelliwidget_query_posts_join'));
			remove_filter('posts_groupby', array(&$this, 'intelliwidget_query_posts_groupby'));
        endif;
        
        // use widget CSS if present
        $classes = array();
        if (!empty($instance['classes'])) :
            $classes = explode("[, ;]", $instance['classes']);
        endif;
        if (!empty($classes)):
            $before_widget = preg_replace('/class="/', 'class="' . implode(" ", $classes) . ' ', $before_widget);
        endif;
		// use before widget argument
        echo $before_widget;
        // handle title
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance );
        if ( !empty( $title ) ) {
            echo $before_title;
            if ( $instance['link_title'] && !empty($selected)) {
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
		// if this is a nav menu, use default WP menu output
		if ($instance['template'] == 'WP_NAV_MENU' && !empty($nav_menu)):
		    wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu, 'menu_class' => 'iw-menu'));
		// otherwise load IW template
		else:
			if ($template = $intelliwidget->get_template($instance['template'])):
	    	    // temporarily disable wpautop if it is on
	    	    if ($has_content_filter = has_filter('the_content', 'wpautop'))
	    	        remove_filter( 'the_content', 'wpautop' );	
	    	    include ($template);
	    	    // restore wpautop if it was on
	    	    if ($has_content_filter)
	    	        add_filter( 'the_content', 'wpautop' );
			endif;
		endif;
        if ($instance['text_position'] == 'below'):
            echo "<div class=\"textwidget\">\n" . ( !empty( $instance['filter'] ) ? 
                wpautop( $custom_text ) : $custom_text ) . "\n</div>\n";
        endif;
        echo $after_widget;
    }
	
    /**
     * Filter JOIN clause for Expired Posts
     * @param <string> $join
     * @param <object> $query
     * @return <string>
     */
    function intelliwidget_query_posts_join ($join, $query) {
		// ignore this filter unless skip expired option is set
        if ( empty( $query->query_vars['iw_skip_expired'] ) )
            return $join;
        global $wpdb;
		// adds join clause to select posts with no expiration
		// and posts with expiration that are still active
		// note postmeta intelliwidget_expire_date date format 
		// MUST be YYYY-MM-DD HH:MM for this to work correctly!
		// Normally I avoid hard-coded SQL but WP can't currently 
		// do this using meta_query parameters.
        $new_join = "
INNER JOIN {$wpdb->postmeta} pm1 ON (
    pm1.post_id = {$wpdb->posts}.ID
        AND pm1.meta_key = 'intelliwidget_expire_date'
        AND CAST( pm1.meta_value AS CHAR ) > '" . date('Y-m-d H:i') . "'
    )
    OR (
        pm1.post_id = {$wpdb->posts}.ID
        AND pm1.post_id NOT IN (
            SELECT pm2.post_id
            FROM {$wpdb->postmeta} pm2
            WHERE pm2.meta_key = 'intelliwidget_expire_date'
        )
    )
";
        return $join . ' ' . $new_join;
    }
	
    /**
     * Filter GROUP BY clause for Expired Posts
     * @param <string> $groupby
     * @param <object> $query
     * @return <string>
     */
    function intelliwidget_query_posts_groupby ($groupby, $query) {
		// ignore this filter unless skip expired option is set
        if ( empty( $query->query_vars['iw_skip_expired'] ) )
            return $groupby;
        global $wpdb;
        $new_groupby = $wpdb->posts . ".ID";
		// check if we are already grouping by id
		if (strpos($groupby, $new_groupby) === false) $groupby = $new_groupby;
        return $groupby;
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
        foreach(array('skip_expired', 'skip_post', 'link_title', 'hide_if_empty', 'filter', 'future_only') as $cb):
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

