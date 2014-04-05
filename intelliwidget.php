<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: IntelliWidget Per Page Featured Posts and Menus
    Plugin URI: http://www.lilaeamedia.com/plugins/intelliwidget
    Description: Combine custom page menus, featured posts, sliders and other content into any widget area that can be customized on a per-page or site-wide basis.
    Version: 2.0.4
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: intelliwidget
    Domain Path: /lang
    License: GPLv2
    * *************************************************************************
    Copyright (C) 2014 Lilaea Media LLC
    Portions adapted from Featured Page Widget 
    Copyright (C) 2009-2011 GrandSlambert http://grandslambert.com/
*/
class IntelliWidget {

    var $pluginName;
    var $pluginPath;
    var $shortName;
    var $menuName;
    var $pluginURL;
    var $templatesPath;
    var $templatesURL;
    var $admin_hook;
    var $dir;
    /**
     * Object constructor
     * @param <string> $file
     * @return void
     */
    function __construct($file) {
        $this->dir = dirname( $file );
        /* Load the language support */
        $lang_dir             = $this->dir . '/lang';
        // sorry, only english for now (stub)
        load_plugin_textdomain('intelliwidget', false, $lang_dir, $lang_dir);
        /* Plugin Details */
        $this->pluginName   = __('IntelliWidget', 'intelliwidget');
        $this->pluginPath   = $this->dir . '/';
        $this->shortName    = __('IntelliWidget', 'intelliwidget');
        $this->menuName     = 'intelliwidget';
        $this->pluginURL     = plugin_dir_url($file);// . '/';
        $this->templatesPath = $this->pluginPath . 'templates/';
        $this->templatesURL  = $this->pluginURL . 'templates/';        
        add_shortcode('intelliwidget',  array(&$this, 'intelliwidget_shortcode'));
        register_activation_hook($file, array(&$this, 'intelliwidget_activate'));
        add_action('after_setup_theme', array(&$this, 'ensure_post_thumbnails_support' ) );
    }

    /**
     * Stub for plugin activation
     */
    function intelliwidget_activate() {
        
    }

    // semaphore to create options page once
    function set_admin_hook($hook) {
        $this->admin_hook = $hook;
    }
    
    function get_meta($id, $optionname, $objecttype, $index = NULL) {
        // are there settings for this widget?
        if (!empty($id) && !empty($objecttype)):
            switch($objecttype):
                case 'post':               
                    if (isset($index)) $optionname .= $index;
                    $instance = maybe_unserialize(get_post_meta($id, $optionname, true));
                    break;
                default:
                    $optionname = 'intelliwidget_data_' . $objecttype . '_' . $id;
                    if ($data = get_option($optionname)):
                        if (isset($index) && isset($data[$index])):
                            $instance = $data[$index];
                        endif;
                    endif;
            endswitch;
            if (isset($instance)):
                if (is_array($instance) && isset($instance['custom_text']))
                    // base64 encoding saves us from markup serialization heartburn
                    $instance['custom_text'] = stripslashes(base64_decode($instance['custom_text']));
                return $instance;
            endif;
        endif;
        return false;
    }

    function get_box_map($id, $objecttype) {
        if (!empty($id) && !empty($objecttype)):
            if ($data = $this->get_meta($id, '_intelliwidget_', $objecttype, 'map')) 
                return $data;
        endif;
        return array();
    }
    
    /**
     * For customized pages, retrieve the page-specific instance settings for the particular widget
     * being replaced
     *
     * @param <integer> $post_id
     * @param <string> $widget_id
     * @return <array> if exists or false if empty
     */
    function get_settings_data($id, $widget_id, $objecttype) {
        // the box map stores meta box => widget id relations in page meta data
        $box_map = $this->get_box_map($id, $objecttype);
        if (is_array($box_map)):
            $widget_map = array_flip($box_map);
            // if two boxes point to the same widget, the second gets clobbered here
            if (array_key_exists($widget_id, $widget_map)):
                $box_id = $widget_map[$widget_id];
                // are there settings for this widget?
                if ($instance = $this->get_meta($id, '_intelliwidget_data_', $objecttype, $box_id)):
                    return $instance;
                endif;
            endif;
        endif;
        // all failures fall through gracefully
        return false;
    }

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
        global $post;
        // section parameter lets us use page-specific IntelliWidgets in shortcode without all the params
        if (is_object($post) && !empty($atts['section'])):
            $atts = $this->get_meta($post->ID, '_intelliwidget_data_', 'post', intval($atts['section']));
            if (empty($atts)): 
                return;
            endif;
        else:
            if (!empty($atts['custom_text'])) unset($atts['custom_text']);
            if (!empty($atts['text_position'])) unset($atts['text_position']);
            if (!empty($atts['title'])) $atts['title'] = strip_tags($atts['title']);
            if (!empty($atts['link_text'])) $atts['link_text'] = strip_tags($atts['link_text']);
            // backwards compatability: if nav_menu has value, add attr 'content=nav_menu' 
            if (!empty($atts['nav_menu'])) $atts['content'] = 'nav_menu';
        endif;
        $atts = $this->defaults($atts);
        $args = array(
            'before_title'  => '',
            'after_title'   => '',
            'before_widget' => empty($atts['nav_menu']) ? '<div class="widget_intelliwidget">' : '',
            'after_widget'  => empty($atts['nav_menu']) ? '</div>' : '',
        );
        // buffer standard output
        ob_start();
        // generate widget from arguments
        $this->build_widget($args, $atts);
        // retrieve widget content from buffer
        $content = ob_get_contents();
        ob_end_clean();
        // return widget content
        return $content;
    }
    
    /**
     * Output the widget using selected template
     *
     * @param <array> $args
     * @param <array> $instance
     * @return void
     */
    function build_widget($args, $instance, $post_id = NULL) {
        global $this_instance;
        $instance = $this_instance = $this->defaults($instance);

        extract($args, EXTR_SKIP);

        // render before widget argument
        echo apply_filters('intelliwidget_before_widget', $before_widget, $instance, $args);
        // handle title
        if (!empty($instance['title'])):
            echo apply_filters('intelliwidget_before_title', $before_title, $instance, $args);
            echo apply_filters('intelliwidget_title', $instance['title'], $instance, $args);
            echo apply_filters('intelliwidget_after_title', $after_title, $instance, $args);
        endif;
        // handle custom text above content
        do_action('intelliwidget_above_content', $instance, $args);
        // use action hook to render content
        if ( has_action('intelliwidget_action_' . $instance['content']))
            do_action('intelliwidget_action_' . $instance['content'], $instance, $args, $post_id);
        // handle custom text below content
        do_action('intelliwidget_below_content', $instance, $args);
        // render after widget argument
        echo apply_filters('intelliwidget_after_widget', $after_widget, $instance, $args);
    }
    
    /**
     * Ensure that "post-thumbnails" support is available for those themes that don't register it.
     * @return  void
     */
    public function ensure_post_thumbnails_support () {
        if ( ! current_theme_supports( 'post-thumbnails' ) ) { add_theme_support( 'post-thumbnails' ); }
    } // End ensure_post_thumbnails_support()

    /**
     * Widget Defaults
     * This will utilize an options form in a future release for customization
     * @param <array> $instance
     * @return <array> -- merged data
     */
    public function defaults($instance = array()) {
        //if (empty($instance)) $instance = array();
        $defaults = apply_filters('intelliwidget_defaults', array(
            // these apply to all intelliwidgets
            'content'           => 'post_list', // this is the main control, determines hook to use
            'nav_menu'          => '', // built-in extension, uses wordpress menu instead of post_list
            'title'             => '',
            'link_title'        => 0,
            'classes'           => '',
            'container_id'      => '',
            'custom_text'       => '',
            'text_position'     => '',
            'filter'            => 0,
            'hide_if_empty'     => 0,      // applies to site-wide intelliwidgets
            'replace_widget'    => 'none', // applies to post-specific intelliwidgets
            'nocopy'            => 0,      // applies to post-specific intelliwidgets
            // these apply to post_list intelliwidgets
            'post_types'        => array('page', 'post'),
            'template'          => 'menu',
            'page'              => array(), // stores any post_type, not just pages
            'category'          => -1, // legacy value, convert to tax_id
            'terms'             => -1,
            'items'             => 5,
            'sortby'            => 'title',
            'sortorder'         => 'ASC',
            'skip_expired'      => 0,
            'skip_post'         => 0,
            'future_only'       => 0,
            'active_only'       => 0,
            // these apply to post_list items
            'length'            => 15,
            'link_text'         => __('Read More', 'intelliwidget'),
            'allowed_tags'      => '',
            'imagealign'        => 'none',
            'image_size'        => 'none',
        ));
        // backwards compatibility: add content=nav_menu if nav_menu param set
        if (empty($instance['content']) && !empty($instance['nav_menu']) && '' != ($instance['nav_menu'])) 
            $instance['content'] = 'nav_menu';
        // standard WP function for merging argument lists
        $merged = wp_parse_args($instance, $defaults);
        return $merged;
    }
    
}

define('INTELLIWIDGET_VERSION', '2.0.4');

if (is_admin())
    include_once( 'includes/class-intelliwidget-admin.php' );
else
    include_once( 'includes/template-tags.php' );
include_once( 'includes/class-intelliwidget-widget.php' );
include_once( 'includes/class-intelliwidget-post.php' );
include_once( 'includes/class-intelliwidget-query.php'  );
    
global $intelliwidget;
$intelliwidget = new IntelliWidget( __FILE__ );
