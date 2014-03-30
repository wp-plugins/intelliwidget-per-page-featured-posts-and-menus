<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-widget-intelliwidget.php - IntelliWidget Widget Class
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
class IntelliWidget_Widget extends WP_Widget {

    var $admin;
    /**
     * Constructor
     */
    function __construct() {
        global $intelliwidget;
        $widget_ops          = array('description' => __('Menus, Featured Posts, HTML and more, customized per page or site-wide.', 'intelliwidget'));
        $control_ops         = array('width' => 400, 'height' => 350);
        if (is_admin()):
            include_once('class-intelliwidget-widget-admin.php');
            $this->admin = new IntelliWidget_WidgetAdmin();
        else:
            add_action('intelliwidget_action_post_list',    array(&$this, 'action_post_list'),      10, 3);
            add_action('intelliwidget_action_nav_menu',     array(&$this, 'action_nav_menu'),       10, 3);
            add_filter('intelliwidget_before_widget',       array(&$this, 'filter_before_widget'),  10, 3);
            add_filter('intelliwidget_title',               array(&$this, 'filter_title'),          10, 3);
            add_filter('intelliwidget_custom_text',         array(&$this, 'filter_custom_text'),    10, 3);
            add_filter('intelliwidget_classes',             array(&$this, 'filter_classes'),        10, 3);
            add_filter('intelliwidget_menu_classes',        array(&$this, 'filter_menu_classes'),   10, 3);
            add_filter('intelliwidget_trim_excerpt',        array(&$this, 'filter_trim_excerpt'),   10, 3);
            // default content actions
            add_action('intelliwidget_above_content',       array(&$this, 'action_addltext_above'), 10, 3);
            add_action('intelliwidget_below_content',       array(&$this, 'action_addltext_below'), 10, 3);
            add_action( 'wp_enqueue_scripts',               array(&$this, 'enqueue_styles'));
            add_shortcode('intelliwidget',                  array(&$this, 'intelliwidget_shortcode'));
        endif;  
        $this->WP_Widget('intelliwidget', $intelliwidget->pluginName, $widget_ops, $control_ops);
    }
    
    /**
     * intelliwidget_extension_settings filter allows widget instance to be replaced by extensions
     *
     * @param <array> $args
     * @param <array> $instance
     * @return false
     */
    function widget($args, $instance) {
        $instance = apply_filters('intelliwidget_extension_settings', $instance, $args);
        // no page-specific settings, should we hide?
        if (!empty($instance['hide_if_empty']))
            return;
        global $intelliwidget;
        $intelliwidget->build_widget($args, $instance);
    }
    
    /**
     * Widget Update method
     * @param <array> $new_instance
     * @param <array> $old_instance
     * @return <array>
     */
    function update($new_instance, $old_instance) {
        return $this->admin->update($new_instance, $old_instance);
    }
    /**
     * Output Widget form
     *
     * @param <array> $instance
     */
    function form($instance) {
        $this->admin->render_form($this, $instance);
    }
        
    /**
     * Front-end css
     */
    function enqueue_styles() {
        wp_enqueue_style('intelliwidget', $this->get_stylesheet(false), array(), INTELLIWIDGET_VERSION);
        if ($override = $this->get_stylesheet(true)):
            wp_enqueue_style('intelliwidget-custom', $override);
        endif;
    }
    
    function get_stylesheet($override = false) {
        global $intelliwidget;
        if ($override):
            $file   = '/intelliwidget/intelliwidget.css';
            if (file_exists(get_stylesheet_directory() . $file)):
                return get_stylesheet_directory_uri() . $file;
            elseif (file_exists(get_template_directory() . $file)):
                return get_template_directory_uri() . $file;
            else:
                return false;
            endif;
        else:
            return $intelliwidget->templatesURL . 'intelliwidget.css';
        endif;
        return false;
    }
    
    function get_query(&$instance) {
        // add query object to instance
        $instance['query'] = new IntelliWidget_Query();
    }
    
    function filter_custom_text($custom_text, $instance = array(), $args = array()) {
        $custom_text = apply_filters( 'widget_text', $custom_text, $instance );
        if ( !empty( $instance['filter'] ))
            $custom_text = wpautop( $custom_text );
        return '<div class="textwidget">' . $custom_text . '</div>';
    }
    
    function filter_before_widget($before_widget, $instance = array(), $args = array()) {
        if (!empty($instance['container_id'])):
            $before_widget = preg_replace('/id=".+?"/', 'id="' . $instance['container_id'] . '"', $before_widget);
        endif;
        // do not apply classes to widget wrapper, but rather to wordpress menu
        if (!empty($instance['content']) && $instance['content'] != 'nav_menu'):
            $before_widget = preg_replace('/class="/', 'class="' . apply_filters('intelliwidget_classes', $instance['classes']) . ' ', $before_widget);
        endif;
        return $before_widget;
    }
    
    function filter_classes($classes) {
        return preg_replace("/[, ;]+/", ' ', $classes);
    }
        
    function filter_title($title, $instance = array(), $args = array()) {
        if ( !empty( $title ) ) {
            if ( !empty($instance['link_title'])) {
                if (!isset($instance['query'])) $this->get_query($instance);
                return get_the_intelliwidget_taxonomy_link($title, $instance);
            } else {
                return apply_filters( 'widget_title', $title );
            }
        }
        return $title;
    }
        
    function filter_menu_classes($classes, $instance = array()) {
        return $classes . (empty($instance['classes']) ? '' : ' ' . $instance['classes']);
    }
    
    /**
     * Trim the content to a set number of words.
     *
     * @param <string> $text
     * @param <integer> $length
     * @return <string>
     */
    function filter_trim_excerpt($text, $this_instance) {
        $length = intval($this_instance['length']);
        $allowed_tags = '';
        if (isset($this_instance['allowed_tags'])):
            $tags = explode(',', $this_instance['allowed_tags']);
            foreach ( $tags as $tag ):
                $allowed_tags .= '<' . trim($tag) . '>';
            endforeach;          
        endif;
        $text   = strip_shortcodes($text);
        $text   = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $text );
        $text   = apply_filters('the_content', $text);
        //$text   = str_replace(']]>', ']]&gt;', $text);
        $text   = strip_tags($text, $allowed_tags);
        if (empty($allowed_tags)):
            $words  = preg_split('/[\r\n\t ]+/', $text, $length + 1);
            if ( count($words) > $length ):
                array_pop($words);
                array_push($words, '...');
                $text = implode(' ', $words);
            endif;
        else:
            $text = $this->get_words_html($text, $length);
        endif; 
        return $text;
    }

    function action_addltext_above($instance, $args) {
        if (('above' == $instance['text_position'] || 'only' == $instance['text_position'] )):
            echo apply_filters('intelliwidget_custom_text', $instance['custom_text'], $instance, $args);
        endif;
    }

    function action_post_list($instance = array(), $args = array(), $post_id = NULL) {
        // skip to after widget content if this is custom text only
        if ('only' == $instance['text_position']) return;
        if (!empty($instance['template'])):
            if (has_action('intelliwidget_action_' . $instance['template'])):
                do_action('intelliwidget_action_' . $instance['template'], $instance);
            elseif ($template = $this->get_template($instance['template'])):
                if (!isset($instance['query'])) $this->get_query($instance);
                $instance['query']->iw_query($instance);
                $selected = $instance['query'];
                include ($template);
            endif;
        endif;
    }

    function action_addltext_below($instance, $args) {
        if ('below' == $instance['text_position']):
            echo apply_filters('intelliwidget_custom_text', $instance['custom_text'], $instance, $args);
        endif;
    }
    function action_nav_menu($instance = array(), $args = array(), $post_id = NULL) {
        // skip to after widget content if this is custom text only
        if ('only' == $instance['text_position']) return;
        if (!empty($instance['nav_menu'])):
            if ('-1' == $instance['nav_menu'] ):
                wp_page_menu( array( 
                    'show_home' => true, 
                    'menu_class' => apply_filters('intelliwidget_menu_classes', 'iw-menu', $instance),
                    )
                );
            else:
                $nav_menu =  wp_get_nav_menu_object( $instance['nav_menu'] );
                wp_nav_menu( array( 
                    'fallback_cb'   => '', 
                    'menu'          => $nav_menu, 
                    'menu_class'    => apply_filters('intelliwidget_menu_classes', 'iw-menu', $instance),
                    )
                );
            endif;
        endif;
    }

    function get_words_html($text, $length) {
        $opentags   = array();
        $excerpt    = '';
        $text       = preg_replace('/<(br|hr)[ \/]*>/', "<$1/>", $text);
        preg_match_all('/(<[^>]+?>)?([^<]*)/', $text, $elements);
        if (!empty($elements[2])):
            $count = 0;
            foreach($elements[2] as $string):
                $html = array_shift($elements[1]);
                if (preg_match('/<(\w+)[^\/]*>/', $html, $matches)):
                    $opentags[] = $matches[1];
                elseif (preg_match('/<\/(\w+)/', $html, $matches)):
                    $close = array_pop($opentags);
                endif;
                $excerpt .= $html;
                $words = preg_split('/[\r\n\t ]+/', $string);
                foreach ($words as $word):
                    if (empty($word)) continue;
                    $count++;
                    if ($count <= $length):
                        $excerpt .= $word . ' ';
                    else:
                        $excerpt .= '...';
                        break;
                    endif;
                endforeach;
                if ($count > $length) break;
            endforeach;
            while (count($opentags)):
                $close = array_pop($opentags);
                $excerpt .= '</' . $close . '>';
            endwhile;
        endif;
        return $excerpt;
    }
    
    /**
     * Retrieve a template file from either the theme or the plugin directory.
     * First, check if an action hook exists for this template value and execute
     * Second check if file exists. If no file exists, return false
     * @param <string> $template    The name of the template.
     * @return <string>             The full path to the template file or false if no template exists
     */
    function get_template($template = NULL) {
        global $intelliwidget;
        if ( NULL == $template ) return false;
            $themeFile  = get_stylesheet_directory() . '/intelliwidget/' . $template . '.php';
            $parentFile = get_template_directory() . '/intelliwidget/' . $template . '.php';
            $pluginFile = $intelliwidget->templatesPath . $template . '.php';
            if ( file_exists($themeFile ) ) return $themeFile;
            if ( file_exists($parentFile) ) return $parentFile;
            if ( file_exists($pluginFile) ) return $pluginFile;
        return false;
    }
  
}

// initialize the widget
add_action('widgets_init', create_function('', 'return register_widget("IntelliWidget_Widget");'));

