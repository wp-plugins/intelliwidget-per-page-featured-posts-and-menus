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
        global $intelliwidget, $this_instance;
        $this_instance = $instance = $intelliwidget->defaults($instance);
        if (!is_array($instance['page'])) $instance['page'] = array($instance['page']);
        if (!is_array($instance['post_types'])) $instance['post_types'] = array($instance['post_types']);
        extract($args, EXTR_SKIP);
		/* if this is a nav menu get menu object and skip query */
		$nav_menu = false;
		if ($instance['template'] == 'WP_NAV_MENU' && ! empty( $instance['nav_menu'] )) :
			$nav_menu =  wp_get_nav_menu_object( $instance['nav_menu'] );
		else:
            $selected = $this->iw_query($instance);
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
                the_iwgt_link($selected[0]->ID, $title, $instance['category']);
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
	    	    include ($template);
			endif;
		endif;
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
        if (!is_array($instance['post_types'])) $instance['post_types'] = array($instance['post_types']);
        include( $intelliwidget->pluginPath . 'includes/widget-form.php');
    }
    
    /* Intelliwidget has a lot of internal logic that can't be done efficiently using the standard
     * WP_Query parameters. This function dyanamically builds a custom query so that the majority of the 
     * post and postmeta data can be retrieved in a single db query.
     */
    function iw_query($instance) {
        global $wpdb;
        $select = "
SELECT 
	p1.ID,
	p1.post_content, 
	p1.post_excerpt, 
	p1.post_title,
    p1.post_date,
    p1.post_author,
	pm2.meta_value AS event_date, 
	pm3.meta_value AS classes,
	pm4.meta_value AS alt_title,
	pm5.meta_value AS target,
	pm6.meta_value AS external_url,
	pm7.meta_value AS thumbnail_id
 FROM {$wpdb->posts} p1
";
         $joins = array("
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = 'intelliwidget_event_date'
) pm2 ON pm2.post_id = p1.ID
            ", "
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = 'intelliwidget_classes'
) pm3 ON pm3.post_id = p1.ID
            ", "
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = 'intelliwidget_alt_title'
) pm4 ON pm4.post_id = p1.ID
            ", "
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = 'intelliwidget_target'
) pm5 ON pm5.post_id = p1.ID
            ", "
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = 'intelliwidget_external_url'
) pm6 ON pm6.post_id = p1.ID
            ", "
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = '_thumbnail_id'
) pm7 ON pm7.post_id = p1.ID
");
        $clauses = array(
            "(p1.post_status = 'publish')",
            "(p1.post_password = '' OR p1.post_password IS NULL)",
        );
        if ( $instance['category'] != -1 ):
            $clauses[] = '( tx1.term_taxonomy_id IN (' . $instance['category'] . ') )';
            $joins[] = "INNER JOIN {$wpdb->term_relationships} tx1 ON p1.ID = tx1.object_id"; 
        endif;
        if (!empty($instance['page'])):
            $pages = is_array($instance['page']) ? implode(',', $instance['page']) : $instance['page'];
            $clauses[] = '(p1.ID IN (' . $pages . ') )'; 
        endif;
        /* Remove current page from list of pages if set */
        if ( $instance['skip_post'] && !empty($post)):
            $clauses[] = "(p1.ID != '" . $post->ID . "' )";
        endif;
        $post_types = empty($instance['post_types']) ? "'post'" : "'" . implode("','", $instance['post_types']) . "'";
        $clauses[] = '(p1.post_type IN (' . $post_types . ') )';

        $time_adj = gmdate('Y-m-d H:i', current_time('timestamp') );

        // skip expired posts
        if ($instance['skip_expired']):
            $joins[] = "
LEFT OUTER JOIN (
	SELECT post_id, meta_value
	FROM {$wpdb->postmeta}
	WHERE meta_key = 'intelliwidget_expire_date'
) pm1 ON pm1.post_id = p1.ID
            ";
            $clauses[] = "(  pm1.meta_value IS NULL  OR CAST( pm1.meta_value AS CHAR ) > '" . $time_adj . "' )";
        endif;
        // use future events only
		// note postmeta intelliwidget_event_date date format 
		// MUST be YYYY-MM-DD HH:MM for this to work correctly!
        if ($instance['future_only']):
			$clauses[] = "(CAST( pm2.meta_value AS CHAR ) > '" . $time_adj . "')";
        endif;

        $order = $instance['sortorder'] == 'ASC' ? 'ASC' : 'DESC';
        switch ($instance['sortby']):
            case 'meta_value':
                $orderby = 'pm2.meta_value ' . $order;
                break;
            case 'random':
                $orderby = 'RAND()';
                break;
            case 'menu_order':
                $orderby = 'p1.menu_order ' . $order;
                break;
            case 'date':
                $orderby = 'p1.post_date ' . $order;
                break;
            case 'title':
            default:
                $orderby = 'p1.post_title ' . $order;
                break;
            
        endswitch;
        $orderby = ' ORDER BY ' . $orderby;
        $items = intval($instance['items']);
        $limit = ' LIMIT 0, ' . (empty($items) ? '5' : $items);
        $querystr = $select . implode(' ', $joins) . ' WHERE ' . implode(" AND ", $clauses) . $orderby . $limit;

        return $wpdb->get_results($querystr, OBJECT);
    }

}

// initialize the widget
add_action('widgets_init', create_function('', 'return register_widget("IntelliWidget_Widget");'));

