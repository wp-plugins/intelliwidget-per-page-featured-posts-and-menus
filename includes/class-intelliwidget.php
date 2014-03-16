<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget.php - Main Plugin class
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
require_once( 'class-intelliwidget-query.php'  );
require_once( 'class-walker-intelliwidget.php' );
class IntelliWidget {

    var $version     = '1.5.0';
    var $pluginName;
    var $pluginPath;
    var $pluginURL;
    var $templatesPath;
    var $templatesURL;
    var $dir;
    var $docsLink;
    var $metabox;
    var $menus;
    var $posts;
    var $terms;
    var $post_types;
    var $intelliwidgets;
    var $lists;
    var $is_ajax;
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

        $this->is_ajax = false;
        $this->docsLink      = '<a href="http://www.lilaeamedia.com/plugins/intelliwidget/" target="_blank" title="' . __('Hover labels for more info or click here to view documentation.', 'intelliwidget') . '" style="float:right">' . __('Help', 'intelliwidget') . '</a>';
        
        add_shortcode('intelliwidget',                  array(&$this, 'intelliwidget_shortcode'));
        register_activation_hook($file,                 array(&$this, 'intelliwidget_activate'));
        if (is_admin()):
            // these actions only apply to admin users
            add_action('load-post.php',                 array(&$this, 'admin_init') );
            add_action('load-post.php',                 array(&$this, 'add_metabox_actions') );
            add_action('save_post',                     array(&$this, 'save_postdata'), 1, 2 );
            add_action('wp_ajax_iw_cdfsave',            array(&$this, 'ajax_save_cdfdata' ));
            add_action('wp_ajax_iw_save',               array(&$this, 'ajax_save_postdata' ));
            add_action('wp_ajax_iw_copy',               array(&$this, 'ajax_copy_page' ));
            add_action('wp_ajax_iw_delete',             array(&$this, 'ajax_delete_tabbed_section' ));
            add_action('wp_ajax_iw_add',                array(&$this, 'ajax_add_tabbed_section' ));
            add_action('wp_ajax_iw_get',                array(&$this, 'ajax_get_post_select_menus' ));
            add_action('wp_ajax_iw_widget_get',         array(&$this, 'ajax_get_widget_post_select_menus' ));
            add_filter('intelliwidget_sanitize_input',  array(&$this, 'filter_sanitize_input'));
        else:
            // default content filters
            add_filter('intelliwidget_before_widget',   array(&$this, 'filter_before_widget'),  10, 2);
            add_filter('intelliwidget_title',           array(&$this, 'filter_title'),          10, 2);
            add_filter('intelliwidget_custom_text',     array(&$this, 'filter_custom_text'),    10, 2);
            add_filter('intelliwidget_classes',         array(&$this, 'filter_classes'),        10, 2);
            add_filter('intelliwidget_menu_classes',    array(&$this, 'filter_menu_classes'),   10, 2);
            // default content actions
            add_action('intelliwidget_post_list',       array(&$this, 'action_post_list'),      10, 3);
            add_action('intelliwidget_nav_menu',        array(&$this, 'action_nav_menu'),       10, 2);
        endif;
        // thanks to woothemes for this
        add_action( 'after_setup_theme',                array(&$this, 'ensure_post_thumbnails_support' ) );
    }

    /**
     * Stub for registering scripts in future release.
     */
    function admin_init() {
            // cache lists and labels
            include_once( 'class-intelliwidget-list.php' );
            $this->lists = new IntelliWidgetList();
            // cache post types
            $this->post_types = $this->get_eligible_post_types();
            // cache menus
            $this->menus = $this->get_nav_menus();
            // cache templates
            $this->templates  = $this->get_widget_templates();
            // cache all available posts (Lightweight IW objects)
            $iwq = new IntelliWidget_Query();
            $this->index_query_objects('posts', 'post_type', $iwq->post_list_query($this->post_types));
            // cache all available taxonomy terms (WP Term objects)
            $this->index_query_objects('terms', 'taxonomy', get_terms(array_intersect(
                preg_grep('/post_format/', get_object_taxonomies($this->post_types), PREG_GREP_INVERT), 
                get_taxonomies(array('public' => TRUE, 'query_var' => TRUE))
            ), array('hide_empty' => FALSE)));
            // cache intelliwidgets
            $this->intelliwidgets = $this->get_intelliwidgets();
            $this->admin_scripts();
    }
    
    /**
     * Stub for printing the scripts needed for the admin.
     */
    function admin_scripts() {
        wp_enqueue_style('intelliwidget-js', $this->pluginURL . 'templates/intelliwidget-admin.css');
        wp_enqueue_script('jquery-ui-tabs');
        wp_enqueue_script('intelliwidget-js', $this->pluginURL . 'js/intelliwidget.js', array('jquery'), '1.5.0', false);
        wp_localize_script( 'intelliwidget-js', 'IWAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ));
    }
        
    /**
     * Stub for plugin activation
     */
    function intelliwidget_activate() {
        
    }
    
    /**
     * Generate input form that applies to entire page (add new, copy settings)
     * @return  void
     */
    function main_meta_box() {
        // set up meta boxes
        $this->init_metabox();
        global $post;
        foreach ($this->post_types as $type):
            add_meta_box( 
                'intelliwidget_main_meta_box',
                __( 'IntelliWidget', 'intelliwidget'),
                array( &$this, 'main_meta_box_form' ),
                $type,
                'side',
                'low'
            );
        endforeach;
    }
    
    /**
     * Generate input form that applies to posts
     * @return  void
     */
    function post_meta_box() {
        global $post;
        foreach ($this->post_types as $type):
            add_meta_box( 
                'intelliwidget_post_meta_box',
                __( 'IntelliWidget Custom Fields', 'intelliwidget'),
                array( &$this, 'post_meta_box_form' ),
                $type,
                'side',
                'low'
            );
        endforeach;
        add_filter('default_hidden_meta_boxes', array(&$this, 'hide_post_meta_box') );
    }
    
    /**
     * Hide Custom Post Fields Meta Box by default
     */
    function hide_post_meta_box( $hidden ) {
        $hidden[] = 'intelliwidget_post_meta_box';
        return $hidden;
    }
    
    /**
     * Output the form in the page-wide meta box. Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function main_meta_box_form($post, $metabox) {
        $this->metabox->page_form($post);

        // box_map contains map of meta boxes to their related widgets
        $box_map = $this->get_box_map($post->ID);
        if (is_array($box_map)):
            $tabs = $section = '';
            foreach($box_map as $box_id => $sidebar_widget_id):
                list($tab, $form) = $this->get_section($box_id, $post->ID);
                $tabs .= $tab . "\n";
                $section .= $form . "\n";
            endforeach;
            $this->begin_tab_container();
            echo $tabs;
            $this->end_tab_container();
            $this->begin_section_container();
            echo $section;
            $this->end_section_container();
        endif;
    }
    
    /**
     * Output the form in the post meta box. Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function post_meta_box_form($post, $metabox) {
        $this->metabox->post_form($post);
    }
    
    function init_metabox() {
        include_once('class-intelliwidget-metabox.php');
        $this->metabox = new IntelliWidgetMetaBox();
    }
    
    function add_metabox_actions() {
        add_action('add_meta_boxes',                array(&$this, 'main_meta_box'));
        add_action('add_meta_boxes',                array(&$this, 'post_meta_box'));
    }
    function begin_tab_container() {
        echo apply_filters('intelliwidget_start_tab_container', 
            '<div id="iw_tabbed_sections"><a id="iw_larr">&#171</a><a id="iw_rarr">&#187;</a><ul id="iw_tabs" class="">');
    }
    
    function end_tab_container() {
        echo apply_filters('intelliwidget_end_tab_container', '</ul>');
    }
    function begin_section_container() {
        echo apply_filters('intelliwidget_start_section_container', '');
    }
    
    function end_section_container() {
        echo apply_filters('intelliwidget_end_section_container', '</div>');
    }
    
    function begin_section($box_id) {
        return apply_filters('intelliwidget_begin_section', '<div id="iw_tabbed_section_' . $box_id . '" class="iw-tabbed-section">');
    }
    
    function end_section() {
        return apply_filters('intelliwidget_end_section', '</div>');
    }
    
    /**
     * Parse POST data and update page-specific data using custom fields
     * @param <integer> $id -- revision id
     * @param <object>  $post -- revision post data
     * @return  void
     */
    function save_postdata($id = NULL, $post = NULL) {
        /***
         * Skip auto-save and revisions. wordpress saves each post twice, once for the revision and once to update
         * the actual post record. The parameters passed by the 'save_post' action are for the revision, so 
         * we must use the post_ID passed in the form data, and skip the revision. 
         */
        if (empty($_POST['iwpage']) || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            || ( !empty($post) && 'revision' == $post->post_type )) return false;
        
        $post_id   = intval($_POST['post_ID']);
        // security checkpoint
        if ( empty($post_id) || !current_user_can('edit_post', $post_id) 
            || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_id) ) return false;
        // reset the data array
        if (!isset($this->lists)) $this->admin_init();
        $post_data = array();
        $prefix    = 'intelliwidget_';
        // since we can now save a single meta box via ajax post, 
        // we need to manipulate the existing boxmap
        $box_map = $this->get_box_map($post_id);
        // allow customization of input fields
        $checkbox_fields = $this->get_checkbox_fields();
        $text_fields = $this->get_text_fields();
        /***
         * Here is some perlesque string handling. Using grep gives us a subset of relevant data fields
         * quickly. We then iterate through the fields, parsing out the actual data field name and the 
         * box_id from the input key.
         */
        foreach(preg_grep('#^' . $prefix . '#', array_keys($_POST)) as $iw_key):
            // find the box id and field name in the post key with a perl regex
            preg_match('#^' . $prefix . '(\d+)_([\w\-]+)$#', $iw_key, $matches);
            if (count($matches)):
                if (!$box_id = $matches[1]) continue;
                $iw_field      = $matches[2];
            else: 
                continue;
            endif;
            // organize this into a 2-dimensional array for later
            // raw html parser/cleaner-upper: see WP docs re: KSES
            if (in_array($iw_field, $text_fields)):
                if ( !current_user_can('unfiltered_html') ):
                    $post_data[$box_id][$iw_field] = stripslashes( wp_filter_post_kses( addslashes($_POST[$iw_key]) ) );
                else:
                    $post_data[$box_id][$iw_field] = $_POST[$iw_key];
                endif;
            else:
                $post_data[$box_id][$iw_field] = apply_filters('intelliwidget_sanitize_input', $_POST[$iw_key]);
            endif;
        endforeach;
        // track meta boxes updated
        $boxcounter = 0;
        // additional processing for each box data segment
        foreach (array_keys($post_data) as $box_id):
            // special handling for checkboxes:
            foreach($checkbox_fields as $cb):
                $post_data[$box_id][$cb] = isset($_POST[$prefix . $box_id . '_' . $cb]);
            endforeach;
            // make sure at least one post type exists
            $post_data[$box_id]['post_types'] = empty($_POST[$prefix . $box_id . '_post_types']) ? 
                array('post') : $_POST[$prefix . $box_id . '_post_types'];
            // handle custom text
            $post_data[$box_id]['custom_text'] = base64_encode($post_data[$box_id]['custom_text']);
            // update map
            $box_map[$box_id] = empty($_POST[$prefix . $box_id . '_replace_widget']) ? NULL : $_POST[$prefix . $box_id . '_replace_widget'];
            // serialize and save new data
            $savedata = serialize($post_data[$box_id]);
            update_post_meta($post_id, '_intelliwidget_data_' . $box_id, $savedata);
            // increment box counter
            $boxcounter++;
        endforeach;
        if ($boxcounter)
            // if we have updates, serialize and save new map
            update_post_meta($post_id, '_intelliwidget_map', serialize($box_map));
        // save custom post data if it exists
        $this->save_cdfdata();
        // save copy page id (i.e., "use settings from ..." ) if it exists
        $this->save_copy_page($post_id);
    }

    function save_cdfdata() {
        if (empty($_POST['iwpage']) ) return false;
        $post_id   = intval($_POST['post_ID']);
        // security checkpoint
        if ( empty($post_id) || !current_user_can('edit_post', $post_id) || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_id) ) return false;
        if (!isset($this->lists)) $this->admin_init();
        // reset the data array
        $prefix    = 'intelliwidget_';
        foreach ($this->get_custom_fields() as $cfield):
            $cdfield = $prefix . $cfield;
            if (array_key_exists($cdfield, $_POST)):
                if (empty($_POST[$cdfield])):
                    delete_post_meta($post_id, $cdfield);
                else:
                    $newdata = $_POST[$cdfield];
                    if ( !current_user_can('unfiltered_html') ):
                        $newdata = stripslashes( 
                        wp_filter_post_kses( addslashes($newdata) ) ); 
                    endif;
                    update_post_meta($post_id, $cdfield, $newdata);
                endif;
            endif;
        endforeach;
    }
    
    function save_copy_page($post_id = NULL) {
        if (empty($post_id) || !array_key_exists('intelliwidget_widget_page_id', $_POST)) return false;
        // save copy page id (i.e., "use settings from ..." ) if it exists
        $copy_page_id = intval($_POST['intelliwidget_widget_page_id']);
        update_post_meta($post_id, '_intelliwidget_widget_page_id', $copy_page_id);
    }
    
    function delete_tabbed_section($box_id = NULL, $post_id = NULL, $nonce = NULL, $box_map = array()) {
        if (empty($post_id) || empty($nonce) || empty($box_id) || !wp_verify_nonce( $nonce, 'iwdelete' ) 
            || !array_key_exists($box_id, $box_map) || !current_user_can('edit_post', $post_id)) return false;
        delete_post_meta($post_id, '_intelliwidget_data_' . $box_id);
        unset($box_map[$box_id]);
        update_post_meta($post_id, '_intelliwidget_map', serialize($box_map));
    }
    
    function add_tabbed_section($post_id = NULL, $nonce = NULL, $box_map = array()) {
        if (empty($post_id) || empty($nonce)  || ! wp_verify_nonce( $nonce, 'iwadd' ) || !current_user_can('edit_post', $post_id)) return false;
        if (count($box_map)): 
            $newkey = max(array_keys($box_map)) + 1;
        else: 
            $newkey = 1;
        endif;
        $box_map[$newkey] = '';
        update_post_meta($post_id, '_intelliwidget_map', serialize($box_map));
        return $newkey;
    }
    
    function ajax_save_postdata() {
        if (false === $this->save_postdata()) die('fail');
        $this->is_ajax = true;
        $this->init_metabox();
        add_action('intelliwidget_post_selection_menus', array($this->metabox, 'post_selection_menus'), 10, 4);
        $post_id = intval($_POST['post_ID']);
        $box_id_key = current(preg_grep("/_box_id$/", array_keys($_POST)));
        $box_id = intval($_POST[$box_id_key]);
        $instance = $this->defaults($this->get_page_data($box_id, $post_id));
        
        $response = array(
            'tab'   => $this->get_tab($box_id, $instance['replace_widget']),
            'form'  => $this->get_metabox($box_id, $post_id, $instance),
        );
        die(json_encode($response));
    }
    
    function ajax_save_cdfdata() {
        $this->is_ajax = true;
        if (false === $this->save_cdfdata()) die('fail');
        die('success');
    }
    
    function ajax_delete_tabbed_section() {
        if (!array_key_exists('post', $_POST) || !array_key_exists('iwdelete', $_POST) || 
            !array_key_exists('_wpnonce', $_POST)) die('fail');
        // note that the query string version uses "post" instead of "post_ID"
        $this->is_ajax = true;
        $post_id = intval($_POST['post']);
        $box_id = intval($_POST['iwdelete']);
        $nonce = $_POST['_wpnonce'];
        if (false === $this->delete_tabbed_section($box_id, $post_id, $nonce, 
            $this->get_box_map($post_id))) die('failure');
        die('success');
    }

    function ajax_add_tabbed_section() {
        if (!array_key_exists('post', $_POST) 
            || !array_key_exists('_wpnonce', $_POST) 
            || !current_user_can('edit_post', $_POST['post']) 
            || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $_POST['post'])            
            ) die('fail');
        // note that the query string version uses "post" instead of "post_ID"
        $this->is_ajax = true;
        $post_id = intval($_POST['post']);
        $nonce = $_POST['_wpnonce'];
        $box_id = $this->add_tabbed_section($post_id, $nonce, 
            $this->get_box_map($post_id));
        if (false === $box_id) die('fail');
        if (!isset($this->lists)) $this->admin_init();
        $this->init_metabox();
        $instance = $this->defaults();

        $response = array(
            'tab'   => $this->get_tab($box_id, $instance['replace_widget']),
            'form'  => $this->begin_section($box_id) . $this->get_metabox($box_id, $post_id, $instance) . $this->end_section(),
        );
        die(json_encode($response));
    }
    
    /*
     * ajax_get_hierarchical_menus
     * This is an important improvement to the application for performance.
     * We now dynamically load all walker-generated menus when the panel is opened
     * and reuse the same DOM element to render them on the page. Since only one panel
     * is ever in use at a time, we remove them from any panels not currently in use
     * and reload them when they are focus()ed again. The reused DOM element also prevents
     * memory leakage from multiple xhr refreshes of multiple copies of the same huge lists.
     */
    function ajax_get_post_select_menus() {
        $post_id = intval($_POST['post_ID']);
        $box_id_key = current(preg_grep("/_box_id$/", array_keys($_POST)));
        $box_id = intval($_POST[$box_id_key]);
        if ( empty($post_id) || empty($box_id) || !current_user_can('edit_post', $post_id) 
            || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_id) ) return false;
        $this->is_ajax = true;
        if (!isset($this->lists)) $this->admin_init();
        $this->init_metabox();
        $instance = $this->defaults($this->get_page_data($box_id, $post_id));
        ob_start();
        $this->metabox->post_selection_menus($box_id, $post_id, $instance);
        $form = ob_get_contents();
        ob_end_clean();
        die($form);
    }
  
    function ajax_get_widget_post_select_menus() {
        $widget_id = sanitize_text_field($_POST['widget-id']);
        $nonce = sanitize_text_field($_POST['_wpnonce_widgets']);
        if ( empty($widget_id) || empty($nonce) 
            || !current_user_can('edit_theme_options') 
            || !wp_verify_nonce($nonce, 'save-sidebar-widgets' ) 
            ) return false;
        $this->is_ajax = true;
        global $wp_registered_widgets;
        if (isset($wp_registered_widgets[$widget_id])):
            if (isset($wp_registered_widgets[$widget_id]['callback'])):
                if (count($wp_registered_widgets[$widget_id]['callback'])):
                    $widget = $wp_registered_widgets[$widget_id]['callback'][0];
                    $settings = $widget->get_settings($widget_id);
                    $instance = $settings[$widget->number];
                    if (!isset($this->lists)) $this->admin_init();
                    include_once('class-intelliwidget-form.php');
                    $form = new IntelliWidgetForm();
                    ob_start();
                    $form->post_selection_menus($instance, $widget);
                    $form = ob_get_contents();
                    ob_end_clean();
                    die($form);
                endif;
            endif;
        endif;
        die('fail');
    }
  
    function ajax_copy_page() {
        if (!array_key_exists('post_ID', $_POST) || !array_key_exists('iwpage', $_POST))
            die('fail');
        $this->is_ajax = true;
        $post_id   = intval($_POST['post_ID']);
        if (!current_user_can('edit_post', $post_id) || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_id)) die('fail');
        if (false === $this->save_copy_page($_POST['post_ID'])) die('fail');
        die('success');
    }
    
    function get_intelliwidgets() {
        global $wp_registered_sidebars;
        $widgets = array();
        foreach ($this->get_replaces_menu() as $value => $label):
            $widgets[$value] = $label;
        endforeach;
        foreach(wp_get_sidebars_widgets() as $sidebar_id => $sidebar_widgets): 
            if (false === strpos($sidebar_id, 'wp_inactive') && false === strpos($sidebar_id, 'orphaned')):
                $count = 0;
                foreach ($sidebar_widgets as $sidebar_widget_id):
                    if (false !== strpos($sidebar_widget_id, 'intelliwidget') ):
                        $widgets[$sidebar_widget_id] = $wp_registered_sidebars[$sidebar_id]['name'] . ' [' . ++$count . ']';
                    endif; 
                endforeach; 
            endif; 
        endforeach;
        return $widgets;
    }

    function get_tab($box_id, $replace_widget = '') {
        $title = (empty($this->intelliwidgets[$replace_widget]) ? $this->intelliwidgets['none'] : $this->intelliwidgets[$replace_widget]);
        return apply_filters('intelliwidget_tab', '<li id="iw_tab_' . $box_id . '" class="iw-tab">
        <a href="#iw_tabbed_section_' . $box_id . '" title="' . $title . '">' . $box_id . '</a></li>', $box_id);
    }
    
    function get_section($box_id, $post_id) {
        $instance   = $this->defaults($this->get_page_data($box_id, $post_id));
        $tab        = $this->get_tab($box_id, $instance['replace_widget']);
        $section    = $this->begin_section($box_id) . $this->get_metabox($box_id, $post_id, $instance) . $this->end_section();
        return array($tab, $section);
    }

    function get_metabox($box_id = NULL, $post_id = NULL, $instance = array()) {
        ob_start();
        $this->metabox->metabox($box_id, $post_id, $instance);
        $form = ob_get_contents();
        ob_end_clean();
        return $form;
    }
    
    function get_box_map($post_id = NULL) {
        if (empty($post_id) || (! $box_map = unserialize(get_post_meta($post_id, '_intelliwidget_map', true)))) 
            $box_map = array();
        return $box_map;
    }
    
    /**
     * Get list of posts as select options. Selects all posts of the type(s) specified in the instance data
     * and returns them as a multi-select menu
     *
     * @param <array> $instance
     * @return <string> 
     */
    function get_posts_list($instance = NULL) {
        $instance['page'] = $this->val2array($instance['page']);
        $posts = array();
        foreach ($this->val2array($instance['post_types']) as $post_type):
            if (isset($this->posts[$post_type]))
                $posts = array_merge($posts, $this->posts[$post_type]);
        endforeach;
    	$output = '';
	    if ( ! empty($posts) ) {
            $args = array($posts, 0, $instance);
            $walker = new Walker_IntelliWidget(); 
	        $output .= call_user_func_array(array($walker, 'walk'), $args);
	    }

	    return $output;
    }
    
    function get_relevant_terms($instance = NULL) {
    	$output = '';
        $post_types = $this->val2array($instance['post_types']);
        $instance['taxonomies']   = $this->val2array($instance['taxonomies']);
        $terms = array();
        foreach ($this->val2array(preg_grep('/post_format/', get_object_taxonomies($post_types), PREG_GREP_INVERT)) as $tax):
            if (isset($this->terms[$tax]))
                $terms = array_merge($terms, $this->terms[$tax]);
        endforeach;
        //print_r($terms);
	    if ( ! empty($terms) ) {
            $args = array($terms, 0, $instance);
            $walker = new Walker_IntelliWidget_Terms(); 
            
	        $output .= call_user_func_array(array($walker, 'walk'), $args);
	    }
	    return $output;
    }
    
    /**
     * Return a list of template files in the theme folder(s) and plugin folder.
     * Templates actually render the output to the widget based on instance settings
     *
     * @return <array>
     */
    function get_widget_templates() {
        $templates  = array();
        $paths      = array();
        $parentPath = get_template_directory() . '/intelliwidget';
        $themePath  = get_stylesheet_directory() . '/intelliwidget';
        $paths[] = $this->templatesPath;
        $paths[] = $parentPath;
        if ($parentPath != $themePath) $paths[] = $themePath;
        foreach ($paths as $path):
            if (file_exists($path) && ($handle = opendir($path)) ):
                while (false !== ($file = readdir($handle))):
                    if ( ! preg_match("/^\./", $file) && preg_match('/\.php$/', $file) ):
                        $file = str_replace('.php', '', $file);
                        $name = str_replace('-', ' ', $file);
                        $templates[$file] = ucfirst($name);
                    endif;
                endwhile;
                closedir($handle);
            endif;
        endforeach;
        asort($templates);
        // hook custom actions into templates menu
        return apply_filters('intelliwidget_templates', $templates);
    }
    
    /**
     * Load either IW stylesheet or override stylesheet.
     *
     * @param <string> $override    The name of the template.
     * @param <string> $ext         The template file extension
     * @param <string> $type        Retrieve from path or url
     * @return <string>             The url to the stylesheet file or false if none exist.
     */

    function get_stylesheet($override = false) {
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
            return $this->templatesURL . 'intelliwidget.css';
        endif;
        return false;
    }
    
    /**
     * Retrieve a template file from either the theme or the plugin directory.
     * First, check if an action hook exists for this template value and execute
     * Second check if file exists. If no file exists, return false
     * @param <string> $template    The name of the template.
     * @return <string>             The full path to the template file or false if no template exists
     */
    function get_template($template = NULL) {
        if ( NULL == $template ) return false;
            $themeFile  = get_stylesheet_directory() . '/intelliwidget/' . $template . '.php';
            $parentFile = get_template_directory() . '/intelliwidget/' . $template . '.php';
            $pluginFile = $this->templatesPath . $template . '.php';
            if ( file_exists($themeFile ) ) return $themeFile;
            if ( file_exists($parentFile) ) return $parentFile;
            if ( file_exists($pluginFile) ) return $pluginFile;
        return false;
    }

    function get_eligible_post_types() {
        $eligible = array();
        if ( function_exists('get_post_types') ):
            $args = array('public' => true);
            $types = get_post_types($args);
        else:
            $types = array('post', 'page');
        endif;
        foreach($types as $type):
            if (post_type_supports($type, 'custom-fields')):
                $eligible[] = $type;
            endif;
        endforeach;
        return apply_filters('intelliwidget_post_types', $eligible);
    }
    
    function get_page_data($box_id, $post_id) {
        // are there settings for this widget?
        if ($instance = unserialize(get_post_meta($post_id, '_intelliwidget_data_' . $box_id, true))):
            if (!empty($instance['custom_text'])):
                // base64 encoding saves us from markup serialization heartburn
                $instance['custom_text'] = stripslashes(base64_decode($instance['custom_text']));
            endif;
            return $instance;
        endif;
        return false;
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
    
    function get_content_menu() {
        return $this->lists->get_menu('content');
    }
    
    function get_replaces_menu() {
        return $this->lists->get_menu('replaces');
    }
    
    function get_text_position_menu() {
        return $this->lists->get_menu('text_position');
    }

    function get_sortby_menu() {
        return $this->lists->get_menu('sortby');
    }

    function get_image_size_menu() {
        return $this->lists->get_menu('image_size');
    }

    function get_imagealign_menu() {
        return $this->lists->get_menu('imagealign');
    }

    function get_link_target_menu() {
        return $this->lists->get_menu('link_target');
    }

    function get_checkbox_fields() {
        return $this->lists->get_fields('checkbox');
    }
    
    function get_text_fields() {
        return $this->lists->get_fields('text');
    }
    
    function get_custom_fields() {
        return $this->lists->get_fields('custom');
    }
    
    function get_nav_menu() {
        $defaults = $this->lists->get_menu('default_nav');
        return array_merge($this->lists->get_menu('default_nav'), $this->menus);
    }
    
    function get_nav_menus() {
        $nav_menus = array();
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
        foreach ($menus as $menu)
            $nav_menus[$menu->term_id] = $menu->name;
        return $nav_menus;
    }
    
    function get_label($key = '') {
        return $this->lists->get_label($key);
    }
    function get_tip($key = '') {
        return $this->lists->get_tip($key);
    }
    /**
     * Stub for data validation
     * @param <string> $unclean - data to parse
     * @param <array> $rules
     * @return <string> $clean - sanitized data
     */
    function filter_sanitize_input($unclean = NULL) {
        if (is_array($unclean)):
            return array_map(array($this, __FUNCTION__), $unclean);
        else:
            return sanitize_text_field($unclean);
        endif;
    }
    
    function filter_custom_text($custom_text, $instance = array()) {
        if ( !empty( $instance['filter'] ))
            $custom_text = wpautop( $custom_text );
        return '<div class="textwidget">' . $custom_text . '</div>';
    }
    
    function filter_before_widget($before_widget, $instance = array()) {
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
        
    function filter_title($title, $instance = array()) {
        if ( !empty( $title ) ) {
            if ( !empty($instance['link_title']) && 
                !empty($instance['query']) && 
                is_object($instance['query']) && 
                !empty($instance['query']->post_count)) {
                // @params $post_id, $text, $category_ID
                return get_the_intelliwidget_link($instance['query']->posts[0]->ID, apply_filters( 'widget_title', $title), $instance['category']);
            } else {
                return apply_filters( 'widget_title', $title );
            }
        }
        return $title;
    }
        
    function filter_menu_classes($classes, $instance = array()) {
        return $classes . (empty($instance['classes']) ? '' : ' ' . $instance['classes']);
    }
        
    function action_post_list($instance = array(), $post_id = NULL) {
        if (!empty($instance['template'])):
            if (has_action('intelliwidget_action_' . $instance['template'])):
                do_action('intelliwidget_action_' . $instance['template'], $instance);
            elseif ($template = $this->get_template($instance['template'])):
                $selected = is_object($instance['query']) ? $instance['query'] : new IntelliWidget_Query($instance);
                include ($template);
            endif;
        endif;
    }
    
    function action_nav_menu($instance = array()) {
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

    function map_category_to_tax($category) {
        $catarr = $this->val2array($category);
        $tax = array('category');
        return array_map(array($this, 'lookup_term'), $catarr, $tax);
    }
    
    function lookup_term($id, $tax) {
        foreach($this->terms[$tax] as $term):
            if ($term->term_id == $id) return $term->term_taxonomy_id;
        endforeach;
        return -1;
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
        $old_post = $post;
        // section parameter lets us use page-specific IntelliWidgets in shortcode without all the params
        if (is_object($post) && !empty($atts['section'])):
            $atts = $this->get_page_data(intval($atts['section']), $post->ID);
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

        if ('post_list' == $instance['content']):
            // query database and add to instance
            $instance['query'] = new IntelliWidget_Query();
            $instance['query']->iw_query($instance);
        endif;
        // render before widget argument
        echo apply_filters('intelliwidget_before_widget', $before_widget, $instance);
        // handle title
        if (!empty($instance['title'])):
            echo apply_filters('intelliwidget_before_title', $before_title, $instance);
            echo apply_filters('intelliwidget_title', $instance['title'], $instance);
            echo apply_filters('intelliwidget_after_title', $after_title, $instance);
        endif;
        // handle custom text above content
        if (('above' == $instance['text_position'] || 'only' == $instance['text_position'] )):
            echo apply_filters('intelliwidget_custom_text', $instance['custom_text'], $instance);
        endif;
        // skip to after widget content if this is custom text only
        if ('only' == $instance['text_position']):
            echo apply_filters('intelliwidget_after_widget', $after_widget, $instance);
            return;
        endif;
        
        // use action hook to render content
        if ( has_action('intelliwidget_' . $instance['content']))
            do_action('intelliwidget_' . $instance['content'], $instance, $post_id);
        // handle custom text below content
        if ('below' == $instance['text_position']):
            echo apply_filters('intelliwidget_custom_text', $instance['custom_text'], $instance);
        endif;
        // render after widget argument
        echo apply_filters('intelliwidget_after_widget', $after_widget, $instance);
    }
    
    /**
     * Trim the content to a set number of words.
     *
     * @param <string> $text
     * @param <integer> $length
     * @return <string>
     */
    function trim_excerpt($text, $this_instance) {
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

    function index_query_objects($property, $keyfield, $data) {
        $indexarray = array();
        if (isset($data) && count($data)):
            foreach ($data as $object):
                $indexarray[$object->{$keyfield}][] = $object;
            endforeach;
        endif;
        $this->{$property} = $indexarray;
    }
    
    function val2array($value) {
        $value = empty($value) ? 
            array() : (is_array($value) ?
                $value : explode(',', $value));
        sort($value);
        return $value;
    }
    
    function sort_terms($a, $b) {
        $c = strcmp($a->taxonomy, $b->taxonomy);
        if($c != 0) {
            return $c;
        }
        return strcmp($a->name, $b->name);
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
            'content'        => 'post_list', // this is the main control, determines hook to use
            'nav_menu'       => '', // built-in extension, uses wordpress menu instead of post_list
            'title'          => '',
            'link_title'     => 0,
            'classes'        => '',
            'container_id'   => '',
            'custom_text'    => '',
            'text_position'  => '',
            'filter'         => 0,
            'hide_if_empty'  => 0,      // applies to site-wide intelliwidgets
            'replace_widget' => 'none', // applies to post-specific intelliwidgets
            'nocopy'         => 0,      // applies to post-specific intelliwidgets
            // these apply to post_list intelliwidgets
            'post_types'     => array('page', 'post'),
            'template'       => 'menu',
            'page'           => array(), // stores any post_type, not just pages
            'category'       => -1, // legacy value, convert to tax_id
            'taxonomies'     => -1,
            'items'          => 5,
            'sortby'         => 'title',
            'sortorder'      => 'ASC',
            'skip_expired'   => 0,
            'skip_post'      => 0,
            'future_only'    => 0,
            'active_only'    => 0,
            // these apply to post_list items
            'length'         => 15,
            'link_text'      => __('Read More', 'intelliwidget'),
            'allowed_tags'   => '',
            'imagealign'     => 'none',
            'image_size'     => 'none',
        ));
        // convert legacy values
        if (empty($instance['content']) && !empty($instance['nav_menu']) && '' != ($instance['nav_menu'])) 
            $instance['content'] = 'nav_menu';
        if (empty($instance['taxonomies']) && isset($instance['category']) && '-1' != $instance['category'])
            $instance['taxonomies'] = $this->map_category_to_tax($instance['category']);
        // standard WP function for merging argument lists
        $merged = wp_parse_args($instance, $defaults);
        // backwards compatibility: add content=nav_menu if nav_menu param set
        return $merged;
    }
    
}
