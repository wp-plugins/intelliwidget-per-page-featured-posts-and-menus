<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-post.php - Edit Post
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
class IntelliWidgetPost {

    /**
     * Object constructor
     * @param <string> $file
     * @return void
     */
    function __construct() {
        global $intelliwidget;
        if (is_admin()):
            global $intelliwidget_admin;
            // these actions only apply to admin users
            add_action('load-post.php',                    array(&$intelliwidget_admin, 'admin_init') );
            add_action('load-post.php',                    array(&$this, 'add_metabox_actions') );
            add_action('save_post',                     array(&$this, 'save_postdata'), 1, 2 );
            add_action('wp_ajax_iw_post_cdfsave',            array(&$this, 'ajax_save_cdfdata' ));
            add_action('wp_ajax_iw_post_save',               array(&$this, 'ajax_save_data' ));
            add_action('wp_ajax_iw_post_copy',               array(&$this, 'ajax_copy_post' ));
            add_action('wp_ajax_iw_post_delete',             array(&$this, 'ajax_delete_tabbed_section' ));
            add_action('wp_ajax_iw_post_add',                array(&$this, 'ajax_add_tabbed_section' ));
            add_action('wp_ajax_iw_post_get',                array(&$this, 'ajax_get_select_menus' ));
        else:
            add_filter('intelliwidget_extension_settings',  array(&$this, 'get_post_settings'), 10, 3);
        endif;
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
        $box_map = $intelliwidget->get_box_map($post->ID, 'post_meta');
        if (is_array($box_map)):
            $tabs = $section = '';
            foreach($box_map as $box_id => $sidebar_widget_id):
                list($tab, $form) = $intelliwidget_admin->get_section($post->ID, $box_id, 'post_meta');
                $tabs .= $tab . "\n";
                $section .= $form . "\n";
            endforeach;
            $intelliwidget_admin->begin_tab_container();
            echo $tabs;
            $intelliwidget_admin->end_tab_container();
            $intelliwidget_admin->begin_section_container();
            echo $section;
            $intelliwidget_admin->end_section_container();
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
    
    function add_metabox_actions() {
        add_action('add_meta_boxes',                array(&$this, 'main_meta_box'));
        add_action('add_meta_boxes',                array(&$this, 'post_meta_box'));
    }
    /**
     * Parse POST data and update page-specific data using custom fields
     * @param <integer> $id -- revision id
     * @param <object>  $post -- revision post data
     * @return  void
     */
     
    function save_postdata($id, $post) {
        global $intelliwidget_admin;
        /***
         * Skip auto-save and revisions. wordpress saves each post twice, once for the revision and once to update
         * the actual post record. The parameters passed by the 'save_post' action are for the revision, so 
         * we must use the post_ID passed in the form data, and skip the revision. 
         */
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            || ( !empty($post) && 'revision' == $post->post_type )) return false;

        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        if (empty($post_id) || 
            !$intelliwidget_admin->validate_post('iwpage', 'iwpage_' . $post_id, 'edit_post', $post_id)) die('fail');

        $intelliwidget_admin->save_data($post_id, 'post_meta');
        // save custom post data if it exists
        $this->save_cdfdata();
        // save copy page id (i.e., "use settings from ..." ) if it exists
        $intelliwidget_admin->save_copy_post($post_id, 'post_meta');
    }

    function save_cdfdata() {
        global $intelliwidget_admin;
        // reset the data array
        $prefix    = 'intelliwidget_';
        foreach ($this->get_custom_fields() as $cfield):
            $cdfield = $prefix . $cfield;
            if (array_key_exists($cdfield, $_POST)):
                if (empty($_POST[$cdfield])):
                    $intelliwidget_admin->delete_meta($post_id, $cdfield, 'post_meta');
                else:
                    $newdata = $_POST[$cdfield];
                    if ( !current_user_can('unfiltered_html') ):
                        $newdata = stripslashes( 
                        wp_filter_post_kses( addslashes($newdata) ) ); 
                    endif;
                    $intelliwidget_admin->update_meta($post_id, $cdfield, 'post_meta', $newdata);
                endif;
            endif;
        endforeach;
    }
    
    // ajax save for posts only - duplicate this for other types
    function ajax_post_save_data() {
        global $intelliwidget_admin;
        $this->is_ajax = true;
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        $box_id_key = current(preg_grep("/_box_id$/", array_keys($_POST)));
        $box_id = isset($_POST[$box_id_key]) ? intval($_POST[$box_id_key]) : NULL;
        if (empty($post_id) || empty($box_id) || 
            !$intelliwidget_admin->validate_post('iwpage', 'iwpage_' . $post_id, 'edit_post', $post_id)) die('fail');
        $intelliwidget_admin->ajax_save_data($post_id, $box_id, 'post_meta');
    }
    
    // ajax copy for posts only - duplicate this for other types
    function ajax_copy_post() {
        global $intelliwidget_admin;
        $this->is_ajax = true;
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        if (empty($post_id) ||  
            !$intelliwidget_admin->validate_post('iwpage', 'iwpage_' . $post_id, 'edit_post', $post_id)) die('fail');

        if (false === $intelliwidget_admin->save_copy_post($post_id, 'post_meta')) die('fail');
        die('success');
    }
    
    // posts only
    function ajax_save_cdfdata() {
        global $intelliwidget_admin;
        $this->is_ajax = true;
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        if (empty($post_id) || 
            !$intelliwidget_admin->validate_post('iwpage', 'iwpage_' . $post_id, 'edit_post', $post_id)) die('fail');
        if (false === $this->save_cdfdata()) die('fail');
        die('success');
    }
    
    // ajax delete for posts only - duplicate this for other types
    function ajax_post_delete_tabbed_section() {
        global $intelliwidget_admin;
        $this->is_ajax = true;
        // note that the query string version uses "post" instead of "post_ID"
        $post_id = isset($_POST['post']) ? intval($_POST['post']) : NULL;
        $box_id = isset($_POST['iwdelete']) ? intval($_POST['iwdelete']) : NULL;
        if (empty($post_id) || empty($box_id) || 
            !$intelliwidget_admin->validate_post('iwdelete', '_wpnonce', 'edit_post', $post_id)) die('fail');
        if (false === $intelliwidget_admin->delete_tabbed_section($id, $box_id, 'post_meta')) die('fail');
        die('success');
    }

    // ajax add for posts only - duplicate this for other types
    function ajax_post_add_tabbed_section() {
        global $intelliwidget_admin;
        $this->is_ajax = true;
        // note that the query string version uses "post" instead of "post_ID"
        $post_id = isset($_POST['post']) ? intval($_POST['post']) : NULL;
        if (empty($post_id) 
            || !$intelliwidget_admin->validate_post('iwpage_' . $post_id, 'iwpage', 'edit_post', $post_id)) die('fail');
        // note that the query string version uses "post" instead of "post_ID"
        $intelliwidget_admin->ajax_add_tabbed_section($post_id, 'post_meta');
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
    // ajax get for posts only - duplicate this for other types
    function ajax_post_get_select_menus() {
        global $intelliwidget_admin;
        $this->is_ajax = true;
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        $box_id_key = current(preg_grep("/_box_id$/", array_keys($_POST)));
        $box_id = isset($_POST[$box_id_key]) ? intval($_POST[$box_id_key]) : NULL;
        if (empty($post_id) || empty($box_id) || 
            !$intelliwidget_admin->validate_post('iwpage', 'iwpage_' . $post_id, 'edit_post', $post_id)) die('fail');
        $intelliwidget_admin->ajax_get_post_select_menus($post_id, $box_id, 'post_meta');
    }

}
global $intelliwidget_post;
$intelliwidget_post = new IntelliWidgetPost();