<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: IntelliWidget Per Page Featured Posts and Menus
    Plugin URI: http://www.lilaeamedia.com/plugins/intelliwidget
    Description: Display featured posts, custom menus, html content and more within a single dynamic sidebar that can be customized on a per-page or site-wide basis.
    Version: 1.5.0
    Author: Jason C Fleming
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
    var $pluginURL;
    var $templatesPath;
    var $templatesURL;
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
        $this->pluginName = __('IntelliWidget', 'intelliwidget');
        $this->pluginPath    = $this->dir . '/';
        /* get url to this directory 
         * Thanks to Spokesrider for finding this bug! 
         */
        $this->pluginURL     = plugin_dir_url($file);// . '/';
        $this->templatesPath = $this->pluginPath . 'templates/';
        $this->templatesURL  = $this->pluginURL . 'templates/';        
        register_activation_hook($file,     array(&$this, 'intelliwidget_activate'));
    }

    /**
     * Stub for plugin activation
     */
    function intelliwidget_activate() {
        
    }

    function get_meta($id, $box_id, $optiontype) {
        // are there settings for this widget?
        if (!empty($id) && !empty($optiontype)):
            switch($optiontype):
                case 'post':
                    if ($instance = unserialize(get_post_meta($id, '_intelliwidget_data_' . $box_id, true))):
                        if (!empty($instance['custom_text'])):
                            // base64 encoding saves us from markup serialization heartburn
                            $instance['custom_text'] = stripslashes(base64_decode($instance['custom_text']));
                        endif;
                        return $instance;
                    endif;
                    break;
                default:
                    $optionname = 'intelliwidget_data_' . $optiontype . '_' . $id;
                    if ($instance = unserialize(get_option($optionname)))
                        return $instance;
            endswitch;
        endif;
        return false;
    }

    function get_box_map($id, $optiontype) {
        if (!empty($id) && !empty($optiontype)):
            switch($optiontype):
                case 'post':
                    if ($box_map = unserialize(get_post_meta($id, '_intelliwidget_map', true))) 
                        return $box_map;
                    break;
                default:
                    $optionname = 'intelliwidget_map_' . $optiontype . '_' . $id;
                    if ($box_map = unserialize(get_option($optionname)))
                        return $box_map;
            endswitch;
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
    function get_settings_data($id, $widget_id, $optiontype) {
        // the box map stores meta box => widget id relations in page meta data
        $box_map = $this->get_box_map($id, $optiontype);
        if (is_array($box_map)):
            $widget_map = array_flip($box_map);
            // if two boxes point to the same widget, the second gets clobbered here
            if (array_key_exists($widget_id, $widget_map)):
                $box_id = $widget_map[$widget_id];
                // are there settings for this widget?
                if ($instance = $this->get_meta($id, $box_id, $optiontype)):
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
            $atts = $this->get_meta($post->ID, intval($atts['section']), 'post');
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
        $post = $old_post;
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
        // skip to after widget content if this is custom text only
        if ('only' != $instance['text_position']):
            // use action hook to render content
            if ( has_action('intelliwidget_action_' . $instance['content']))
                do_action('intelliwidget_action_' . $instance['content'], $instance, $args, $post_id);
        endif;
        // handle custom text below content
        do_action('intelliwidget_below_content', $instance, $args);
        // render after widget argument
        echo apply_filters('intelliwidget_after_widget', $after_widget, $instance, $args);
    }
    
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
        // convert legacy values
        if (empty($instance['content']) && !empty($instance['nav_menu']) && '' != ($instance['nav_menu'])) 
            $instance['content'] = 'nav_menu';
        // standard WP function for merging argument lists
        $merged = wp_parse_args($instance, $defaults);
        // backwards compatibility: add content=nav_menu if nav_menu param set
        return $merged;
    }
}

define('INTELLIWIDGET_VERSION', '1.5.0');

//require_once( 'includes/class-intelliwidget.php' );
include_once( 'includes/class-intelliwidget-widget.php' );
include_once( 'includes/class-intelliwidget-post.php' );
include_once( 'includes/class-intelliwidget-query.php'  );
if (is_admin())
    include_once( 'includes/class-intelliwidget-admin.php'  );
else
    include_once( 'includes/template-tags.php' );
    
global $intelliwidget;
$intelliwidget = new IntelliWidget( __FILE__ );
