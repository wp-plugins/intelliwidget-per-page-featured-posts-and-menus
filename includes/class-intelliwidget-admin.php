<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-admin.php - Administration class
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
class IntelliWidgetAdmin {

    var $docsLink;
    var $metabox;
    var $menus;
    var $posts;
    var $terms;
    var $post_types;
    var $intelliwidgets;
    var $lists;
    var $objecttype;
    var $tax_menu;
    /**
     * Object constructor
     * @param <string> $file
     * @return void
     */
    function __construct() {
    }

    /**
     * Configures the admin object for this request
     */
    function admin_init($objecttype = '', $idfield = '') {
            include_once( 'class-intelliwidget-strings.php' );
            include_once( 'class-intelliwidget-walker.php' );
            // this property tells IW how to set/get options (post uses post_meta, others use options table)
            $this->objecttype       = $objecttype;
            // cache post types
            $this->post_types       = $this->get_eligible_post_types();
            // cache menus
            $this->menus            = $this->get_nav_menus();
            // cache templates
            $this->templates        = $this->get_widget_templates();
            // cache intelliwidgets
            $this->intelliwidgets   = $this->get_intelliwidgets();
            // enqueue JS and CSS
            $this->admin_scripts($idfield);
            // FIXME: should this go here???
            $this->docsLink         = '<a href="http://www.lilaeamedia.com/plugins/intelliwidget/" target="_blank" title="' . __('Hover labels for more info or click here to view documentation.', 'intelliwidget') . '" style="float:right">' . __('Help', 'intelliwidget') . '</a>';
        
    }

    /**
     * Output scripts to the admin. 
     * @param <string> $idfield - the input field name to use as the object id
     */
    function admin_scripts($idfield) {
        if (!wp_script_is('intelliwidget-js', 'enqueued')): // prevent multiple initialization by other plugins
            global $intelliwidget;
            wp_enqueue_style('intelliwidget-js', $intelliwidget->pluginURL . 'templates/intelliwidget-admin.css', array(), INTELLIWIDGET_VERSION);
            wp_enqueue_script('jquery-ui-tabs');
            wp_enqueue_script('intelliwidget-js', $intelliwidget->pluginURL . 'js/intelliwidget.min.js', array('jquery'), INTELLIWIDGET_VERSION, false);
            wp_localize_script( 'intelliwidget-js', 'IWAjax', array(
                'ajaxurl'   => admin_url( 'admin-ajax.php' ),
                'objtype'   => $this->objecttype,
                'idfield'   => $idfield,
            ));
        endif;
    }
    
    function add_options_page() {
        global $intelliwidget;
        if (empty($intelliwidget->admin_hook)):
            $hook = add_theme_page(
                $intelliwidget->pluginName, 
                $intelliwidget->shortName, 
                'edit_theme_options', 
                $intelliwidget->menuName, 
                array(&$this, 'options_page') 
            );
            $intelliwidget->set_admin_hook($hook);
            // only load plugin-specific data 
            // when options page is loaded
            if (has_action('intelliwidget_options_init'))
                add_action( 'load-' . $hook, array(&$this, 'options_init') );
        endif;
    }

    function options_init() {
        do_action('intelliwidget_options_init');
    }

    function options_page() {
        global $intelliwidget;
        $active_tab = isset( $_GET[ 'tab' ] ) ? sanitize_text_field($_GET[ 'tab' ]) : '';
?>
<div class="wrap">
  <div id="icon-appearance" class="icon32"></div>
  <h2><?php echo $intelliwidget->pluginName . ' ' . __('Extended Settings', 'intelliwidget'); ?></h2>
  <div id="intelliwidget_error_notice">
    <?php echo apply_filters('intelliwidget_options_errors', ''); ?>
  </div>
  <h2 class="nav-tab-wrapper"><?php do_action('intelliwidget_options_tab', $active_tab); ?></h2>
  <div class="intelliwidget-option-panel-container"><?php do_action('intelliwidget_options_panel'); ?></div>
</div>
<?php

    }
    
    function metabox_init() {
        include_once('class-intelliwidget-metabox.php');
        $this->metabox = new IntelliWidgetMetaBox();
    }
    
    function begin_tab_container() {
        echo apply_filters('intelliwidget_start_tab_container', 
            '<div class="iw-tabbed-sections"><a class="iw-larr">&#171</a><a class="iw-rarr">&#187;</a><ul class="iw-tabs">');
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
    
    function begin_section($id, $box_id) {
        return apply_filters('intelliwidget_begin_section', '<div id="iw_tabbed_section_' . $id . '_' . $box_id . '" class="iw-tabbed-section">');
    }
    
    function end_section() {
        return apply_filters('intelliwidget_end_section', '</div>');
    }
    
    function render_tabbed_sections($id) {
        global $intelliwidget;
        $this->metabox->add_form($this, $id);
        // box_map contains map of meta boxes to their related widgets
        $box_map = $intelliwidget->get_box_map($id, $this->objecttype);
        if (is_array($box_map)):
            ksort($box_map);
            $tabs = $section = '';
            foreach($box_map as $box_id => $sidebar_widget_id):
                list($tab, $form) = $this->get_section($id, $box_id);
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
    
    function save_data($id) {
        global $intelliwidget;
        // reset the data array
        $post_data = array();
        $prefix    = 'intelliwidget_';
        // since we can now save a single meta box via ajax post, 
        // we need to manipulate the existing boxmap
        $box_map = $intelliwidget->get_box_map($id, $this->objecttype);
        // allow customization of input fields
        $checkbox_fields = $this->get_checkbox_fields();
        $text_fields = $this->get_text_fields();
        /***
         * Here is some perlesque string handling. Using grep gives us a subset of relevant data fields
         * quickly. We then iterate through the fields, parsing out the actual data field name and the 
         * box_id from the input key.
         */
        foreach(preg_grep('#^' . $prefix . '#', array_keys($_POST)) as $field):
            // find the box id and field name in the post key with a perl regex
            preg_match('#^' . $prefix . '(\d+)_(\d+)_([\w\-]+)$#', $field, $matches);
            if (count($matches)):
                if (!($id == $matches[1]) || !($box_id = $matches[2])) continue;
                $name      = $matches[3];
            else: 
                continue;
            endif;
            // organize this into a 2-dimensional array for later
            // raw html parser/cleaner-upper: see WP docs re: KSES
            if (in_array($name, $text_fields)):
                if ( !current_user_can('unfiltered_html') ):
                    $post_data[$box_id][$name] = stripslashes( wp_filter_post_kses( addslashes($_POST[$field]) ) );
                else:
                    $post_data[$box_id][$name] = $_POST[$field];
                endif;
            else:
                $post_data[$box_id][$name] = $this->filter_sanitize_input($_POST[$field]);
            endif;
        endforeach;
        // track meta boxes updated
        $boxcounter = 0;
        // additional processing for each box data segment
        foreach ($post_data as $box_id => $new_instance):
            // get current values
            $old_instance = $intelliwidget->get_meta($id, '_intelliwidget_data_', $this->objecttype, $box_id);
            foreach ($new_instance as $name => $value):
                $old_instance[$name] = $value;
                // make sure at least one post type exists
                if ('post_types' == $name && empty($new_instance[$name]))
                    $old_instance['post_types'] = array('post');
                if ('replace_widget' == $name) $box_map[$box_id] = $new_instance['replace_widget'];
                // handle multi selects that may not be passed or may just be empty
                if ('page_multi' == $name && empty($new_instance['page']))
                    $old_instance['page'] = array();
                if ('terms_multi' == $name && empty($new_instance['terms']))
                    $old_instance['terms'] = array();
            endforeach;
            // special handling for checkboxes:
            foreach($checkbox_fields as $name)
                $old_instance[$name] = isset($new_instance[$name]);
            if (isset($old_instance['custom_text'])) $old_instance['custom_text'] = base64_encode($old_instance['custom_text']);
            // save new data
            $this->update_meta($id, '_intelliwidget_data_', $old_instance, $box_id);
            // increment box counter
            $boxcounter++;
        endforeach;
        if ($boxcounter)
            // if we have updates, save new map
            $this->update_meta($id, '_intelliwidget_', $box_map, 'map');
    }

    function save_copy_id($id) {
        $copy_id = isset($_POST['intelliwidget_widget_page_id']) ? intval($_POST['intelliwidget_widget_page_id']) : NULL;
        if (isset($copy_id))
            $this->update_meta($id, '_intelliwidget_', $copy_id, 'widget_page_id');
    }
    
    function validate_post($action, $noncefield, $capability, $is_ajax = false, $post_id = NULL, $get = false) {
        
        return (($get ? 'GET' : 'POST') == $_SERVER['REQUEST_METHOD'] 
            && ($is_ajax ? check_ajax_referer( $action, $noncefield, false ) : check_admin_referer($action, $noncefield, false ))
            && current_user_can($capability, $post_id)
            );
    }
    
    function delete_tabbed_section($id, $box_id) {
        global $intelliwidget;
        $box_map = $intelliwidget->get_box_map($id, $this->objecttype);
        $this->delete_meta($id, '_intelliwidget_data_', $box_id);
        unset($box_map[NULL == $box_id ? '' : $box_id]);
        $this->update_meta($id, '_intelliwidget_', $box_map, 'map');
    }

    function add_tabbed_section($id) {
        global $intelliwidget;
        $box_map = $intelliwidget->get_box_map($id, $this->objecttype);

        if (count($box_map)): 
            $newkey = max(array_keys($box_map)) + 1;
        else: 
            $newkey = 1;
        endif;
        $box_map[$newkey] = '';
        $this->update_meta($id, '_intelliwidget_', $box_map, 'map');
        return $newkey;
        //return false;
    }
    
    // use this for all saves
    function ajax_save_data($id, $box_id) {
        if (false === $this->save_data($id)) die('fail'); 
        global $intelliwidget;
        $this->metabox_init();
        add_action('intelliwidget_post_selection_menus', array($this->metabox, 'post_selection_menus'), 10, 4);
        $instance = $intelliwidget->defaults($intelliwidget->get_meta($id, '_intelliwidget_data_', $this->objecttype, $box_id));
        die(json_encode(array(
            'tab'   => $this->get_tab($id, $box_id, $instance['replace_widget']),
            'form'  => $this->get_metabox($id, $box_id, $instance),
        )));
    }
    
    // use this for all adds
    function ajax_add_tabbed_section($id) {
        if (!($box_id = $this->add_tabbed_section($id))) die('fail');
        global $intelliwidget;
        $this->metabox_init();
        $instance = $intelliwidget->defaults();
        $response = array(
                'tab'   => $this->get_tab($id, $box_id, $instance['replace_widget']),
                'form'  => $this->begin_section($id, $box_id) . $this->get_metabox($id, $box_id, $instance) . $this->end_section(),
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

    // use this for all gets
    function ajax_get_post_select_menus($id, $box_id) {
        global $intelliwidget;
        $this->metabox_init();
        $instance = $intelliwidget->defaults($intelliwidget->get_meta($id, '_intelliwidget_data_', $this->objecttype, $box_id));
        ob_start();
        $this->metabox->post_selection_menus($this, $id, $box_id, $instance);
        $form = ob_get_contents();
        ob_end_clean();
        die($form);
    }
    
    function get_nonce_url($id, $action, $box_id = NULL) {
        global $pagenow; 
        $val = 'delete' == $action ? $box_id : 1;
        return wp_nonce_url(admin_url($pagenow . '?iw' . $action . '=' . $val . '&objid=' . $id), 'iw' . $action);
    }
    
    function get_intelliwidgets() {
        global $wp_registered_sidebars;
        $widgets = array();
        foreach ($this->get_replaces_menu() as $value => $label):
            $widgets[$value] = $label;
        endforeach;
        foreach(wp_get_sidebars_widgets() as $sidebar_id => $sidebar_widgets): 
            if (false === strpos($sidebar_id, 'wp_inactive') 
                && false === strpos($sidebar_id, 'orphaned')
                && is_array($sidebar_widgets)):
                $count = 0;
                foreach ($sidebar_widgets as $sidebar_widget_id):
                    if (false !== strpos($sidebar_widget_id, 'intelliwidget') && isset($wp_registered_sidebars[$sidebar_id])):
                        $widgets[$sidebar_widget_id] = $wp_registered_sidebars[$sidebar_id]['name'] . ' [' . ++$count . ']';
                    endif; 
                endforeach; 
            endif; 
        endforeach;
        return $widgets;
    }

    function get_tab($id, $box_id, $replace_widget = '') {
        $title = (empty($this->intelliwidgets[$replace_widget]) ? $this->intelliwidgets['none'] : $this->intelliwidgets[$replace_widget]);
        return apply_filters('intelliwidget_tab', '<li id="iw_tab_' . $id . '_' . $box_id . '" class="iw-tab">
        <a href="#iw_tabbed_section_' . $id . '_' . $box_id . '" title="' . $title . '">' . $box_id . '</a></li>', $id, $box_id);
    }
    
    function get_section($id, $box_id) {
        global $intelliwidget;
        $instance   = $intelliwidget->defaults($intelliwidget->get_meta($id, '_intelliwidget_data_', $this->objecttype, $box_id));
        $tab        = $this->get_tab($id, $box_id, $instance['replace_widget']);
        $section    = $this->begin_section($id, $box_id) . $this->get_metabox($id, $box_id, $instance) . $this->end_section();
        return array($tab, $section);
    }

    function get_metabox($id, $box_id, $instance) {
        ob_start();
        $this->metabox->metabox($this, $id, $box_id, $instance);
        $form = ob_get_contents();
        ob_end_clean();
        return $form;
    }
    
    /**
     * Get list of posts as select options. Selects all posts of the type(s) specified in the instance data
     * and returns them as a multi-select menu
     *
     * @param <array> $instance
     * @return <string> 
     */
    function get_posts_list($instance = NULL, $profiles = false) {
        $instance['page'] = $this->val2array(isset($instance['page']) ? $instance['page'] : '');
        $instance['profiles_only'] = $profiles;
        $posts = array();
        if (!isset($this->posts)) $this->load_posts();
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
    
    function get_terms_list($instance = NULL) {
        if (!isset($this->terms)) $this->load_terms();
    	$output = '';
        $post_types = $this->val2array(isset($instance['post_types']) ? $instance['post_types'] : '');
        $instance['terms']   = $this->val2array(isset($instance['terms']) ? $instance['terms'] : '');
        $terms = array();
        foreach ($this->val2array(preg_grep('/post_format/', get_object_taxonomies($post_types), PREG_GREP_INVERT)) as $tax):
            if (isset($this->terms[$tax]))
                $terms = array_merge($terms, $this->terms[$tax]);
        endforeach;
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
        global $intelliwidget;
        $templates  = array();
        $paths      = array();
        $parentPath = get_template_directory() . '/intelliwidget';
        $themePath  = get_stylesheet_directory() . '/intelliwidget';
        $paths[]    = $intelliwidget->templatesPath;
        $paths[]    = $parentPath;
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
    

    function get_eligible_post_types() {
        $eligible = array();
        if ( function_exists('get_post_types') ):
            $args   = array('public' => true);
            $types  = get_post_types($args);
        else:
            $types  = array('post', 'page');
        endif;
        foreach($types as $type):
            if (post_type_supports($type, 'custom-fields')):
                $eligible[] = $type;
            endif;
        endforeach;
        return apply_filters('intelliwidget_post_types', $eligible);
    }
    
    function delete_meta($id, $optionname, $index = NULL) {
        if (!empty($id) && !empty($optionname)):
            switch($this->objecttype):
                case 'post':
                    if (isset($index)) $optionname .= $index;
                    return delete_post_meta($id, $optionname);
                default:
                    $optionname = 'intelliwidget_data_' . $this->objecttype . '_' . $id;
                    if (isset($index) && ($data = get_option($optionname))):
                        unset($data[$index]);
                        update_option($optionname, $data);
                    endif;
            endswitch;
        endif;
    }
    
    function update_meta($id, $optionname, $data, $index = NULL) {
        if (empty($id) || empty($optionname)) return false;
        switch($this->objecttype):
            case 'post':
                if (isset($index)) $optionname .= $index;
                $serialized = maybe_serialize($data);
                
                update_post_meta($id, $optionname, $serialized);
                break;
            default:
                $optionname = 'intelliwidget_data_' . $this->objecttype . '_' . $id;
                if (isset($index)):
                    if (!($option = get_option($optionname)))
                        $option = array();
                    $option[$index] = $data;
                    update_option($optionname, $option);
                endif;
        endswitch;
    }
    
    
    function get_content_menu() {
        return IntelliWidgetStrings::get_menu('content');
    }
    
    function get_replaces_menu() {
        return IntelliWidgetStrings::get_menu('replaces');
    }
    
    function get_text_position_menu() {
        return IntelliWidgetStrings::get_menu('text_position');
    }

    function get_sortby_menu() {
        return IntelliWidgetStrings::get_menu('sortby');
    }

    function get_image_size_menu() {
        return IntelliWidgetStrings::get_menu('image_size');
    }

    function get_imagealign_menu() {
        return IntelliWidgetStrings::get_menu('imagealign');
    }

    function get_link_target_menu() {
        return IntelliWidgetStrings::get_menu('link_target');
    }

    function get_checkbox_fields() {
        return IntelliWidgetStrings::get_fields('checkbox');
    }
    
    function get_text_fields() {
        return IntelliWidgetStrings::get_fields('text');
    }
    
    function get_custom_fields() {
        return IntelliWidgetStrings::get_fields('custom');
    }
    
    function get_nav_menu() {
        $defaults = IntelliWidgetStrings::get_menu('default_nav');
        return $defaults + $this->menus;
    }
    
    function get_nav_menus() {
        $nav_menus = array();
        $menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
        foreach ($menus as $menu)
            $nav_menus[$menu->term_id] = $menu->name;
        return $nav_menus;
    }

    function get_tax_menu() {
        if (isset($this->tax_menu)) return $this->tax_menu;
        if (!isset($this->terms)) $this->load_terms();
        $menu = array('' => __('None', 'intelliwidget'));
        foreach (array_keys($this->terms) as $name):
            $taxonomy = get_taxonomy($name);
            $menu[$name] = $taxonomy->label;
        endforeach;
        $this->tax_menu = $menu;
        return $menu;
    }

    function get_tax_sortby_menu() {
        return IntelliWidgetStrings::get_menu('tax_sortby');
    }

    function get_label($key = '') {
        return IntelliWidgetStrings::get_label($key);
    }

    function get_tip($key = '') {
        return IntelliWidgetStrings::get_tip($key);
    }
    /**
     * Stub for data validation
     * @param <string> $unclean - data to parse
     * @return <string> - sanitized data
     */
    function filter_sanitize_input($unclean = NULL) {
        if (is_array($unclean)):
            return array_map(array($this, __FUNCTION__), $unclean);
        else:
            return sanitize_text_field($unclean);
        endif;
    }
    
    // Backwards compatability: replaces original category value with new term taxonomy id value
    function map_category_to_tax($category) {
        $catarr = $this->val2array($category);
        $tax = array('category');
        if (!isset($this->terms)) $this->load_terms();
        return array_map(array($this, 'lookup_term'), $catarr, $tax);
    }
    
    function lookup_term($id, $tax) {
        foreach($this->terms[$tax] as $term):
            if ($term->term_id == $id) return $term->term_taxonomy_id;
        endforeach;
        return -1;
    }
    

    function load_posts() {
        // cache all available posts (Lightweight IW objects)
        $iwq = new IntelliWidget_Query();
        $this->index_query_objects('posts', 'post_type', $iwq->post_list_query($this->post_types));
    }
    
    function load_terms() {
        // cache all available posts (Lightweight IW objects)
        $iwq = new IntelliWidget_Query();
        $this->index_query_objects('terms', 'taxonomy', get_terms(array_intersect(
            preg_grep('/post_format/', get_object_taxonomies($this->post_types), PREG_GREP_INVERT), 
                get_taxonomies(array('public' => TRUE, 'query_var' => TRUE))
            ), array('hide_empty' => FALSE)));
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
    
}
