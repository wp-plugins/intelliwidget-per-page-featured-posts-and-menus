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
require_once( 'class-intelliwidget-query.php' );
require_once( 'class-walker-intelliwidget.php' );
class IntelliWidget {

    var $version     = '1.3.4';
    var $pluginName;
    var $pluginPath;
    var $pluginURL;
    var $templatesPath;
    var $templatesURL;
    var $dir;
    var $docsLink;
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
        $this->pluginURL     = plugin_dir_url($file) . '/';
        $this->templatesPath = $this->pluginPath . 'templates/';
        $this->templatesURL  = $this->pluginURL . 'templates/';        

        $this->docsLink      = '<a href="http://www.lilaeamedia.com/plugins/intelliwidget/" target="_blank" title="Help" style="float:right">Help</a>';
        $this->load_settings();
        add_shortcode('intelliwidget', array(&$this, 'intelliwidget_shortcode'));
        register_activation_hook($file, array(&$this, 'intelliwidget_activate'));
        // these actions only apply to admin users
        if (is_admin()):
            $this->menus = get_terms( 'nav_menu', array( 'hide_empty' => false ) );
            $this->templates  = $this->get_widget_templates();
            add_action('admin_init',          array(&$this, 'admin_scripts'));
            add_action('add_meta_boxes',      array(&$this, 'main_meta_box') );
            add_action('add_meta_boxes',      array(&$this, 'section_meta_box') );
            add_action('add_meta_boxes',      array(&$this, 'post_meta_box') );
            add_action('save_post',           array(&$this, 'save_postdata'), 1, 2 );
            add_action('wp_ajax_iw_cdfsave',  array(&$this, 'ajax_save_cdfdata' ));
            add_action('wp_ajax_iw_save',     array(&$this, 'ajax_save_postdata' ));
            add_action('wp_ajax_iw_copy',     array(&$this, 'ajax_copy_page' ));
            add_action('wp_ajax_iw_delete',   array(&$this, 'ajax_delete_meta_box' ));
            add_action('wp_ajax_iw_add',      array(&$this, 'ajax_add_meta_box' ));
            //add_action('admin_print_styles', array(&$this, 'admin_styles'));
            //add_action('admin_print_scripts', array(&$this, 'admin_scripts'));
        endif;
        // thanks to woothemes for this
        add_action( 'after_setup_theme', array( &$this, 'ensure_post_thumbnails_support' ) );
    }
    /**
     * Stub for loading settings in future release.
     */
    function load_settings() {
    }
    
    /**
     * Stub for registering scripts in future release.
     */
    function admin_init() {
        // we only use session for persisting notices across redirects FIXME: need better way
        //if (!session_id()) session_start();
        //wp_register_style('intelliwidget-admin-css', $this->pluginURL . 'includes/intelliwidget-admin.css');
        //wp_register_script('intelliwidget-js', $this->pluginURL . 'js/intelliwidget.js', array('jquery'), '1.2.0', false);
    }
    
    /**
     * Stub for printing the administration styles.
     */
    function admin_styles() {
        //wp_enqueue_style('intelliwidget-admin-css');
    }

    /**
     * Stub for printing the scripts needed for the admin.
     */
    function admin_scripts() {
        wp_enqueue_script('intelliwidget-js', $this->pluginURL . 'js/intelliwidget.js', array('jquery'), '1.2.0', false);
        wp_localize_script( 'intelliwidget-js', 'IWAjax', array(
            'ajaxurl' => admin_url( 'admin-ajax.php' )
        ));
    }
    
    /**
     * Display message in admin after create/update/delete
     * FIXME: future release
     * @return  void
     */
    function display_notice() {
        if(!empty($_SESSION['message']) && $msg = $_SESSION['intelliwidget_message']):
            unset($_SESSION['intelliwidget_message']);
    ?>

<div class="updated">
  <p><?php echo $msg; ?></p>
</div>
<?php
        endif;
    }
    /**
     * Generate input form on page edit
     * @return  void
     */
    function section_meta_box() {
        global $post;
        // box_map contains map of meta boxes to their related widgets
        $box_map = $this->get_box_map($post->ID);
        if (is_array($box_map)):
            if (array_key_exists('_wpnonce', $_GET)):
                $nonce = $_GET['_wpnonce'];
                // add or delete boxes from previous page
                if (array_key_exists('iwdelete', $_GET)):
                    $iwdelete = intval($_GET['iwdelete']);
                    // delete box
                    $this->delete_meta_box($post->ID, $iwdelete, $nonce, $box_map);
                elseif (array_key_exists('iwadd', $_GET)):
                    // add box
                    $this->add_meta_box($post->ID, $nonce, $box_map);
                endif;
                // redirect so post data is not cached
                wp_redirect(admin_url('post.php?action=edit&post=' . $post->ID));
                die();
            else:
                // display notice from previous update (if exists)
                add_action('admin_notices', array(&$this, 'display_notice'));
            endif;
            // refresh the meta box forms on the page
            $count = 1;
            foreach($box_map as $box_id => $sidebar_widget_id):
                add_meta_box( 
                    'intelliwidget_section_meta_box_' . $box_id,
                    __( 'IntelliWidget Section ', 'intelliwidget') . $count,
                    array( &$this, 'section_meta_box_form' ),
                    'page',
                    'side',
                    'low',
                    array('pagesection' => $box_id)
                );
                $count++;
            endforeach;
        endif;
    }

    /**
     * Generate input form that applies to entire page (add new, copy settings)
     * @return  void
     */
    function main_meta_box() {
        global $post;
        add_meta_box( 
            'intelliwidget_main_meta_box',
            __( 'IntelliWidget', 'intelliwidget'),
            array( &$this, 'main_meta_box_form' ),
            'page',
            'side',
            'low'
        );
    }
    
    /**
     * Generate input form that applies to posts
     * @return  void
     */
    function post_meta_box() {
        global $post;
        foreach ($this->get_eligible_post_types() as $type):
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
     * Output the form in the section meta box(es). Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function section_meta_box_form($post, $metabox) {
        $pagesection = $metabox['args']['pagesection'];
        $meta_name = '_intelliwidget_data_' . $pagesection;
        $intelliwidget_data = $this->defaults(unserialize(get_post_meta( $post->ID, $meta_name, true ) ));
        $intelliwidget_data['custom_text'] = stripslashes(base64_decode($intelliwidget_data['custom_text']));
        if (!is_array($intelliwidget_data['page'])) 
            $intelliwidget_data['page'] = array($intelliwidget_data['page']);
        if (!is_array($intelliwidget_data['post_types'])) 
            $intelliwidget_data['post_types'] = array($intelliwidget_data['post_types']);
        $widgets_array = wp_get_sidebars_widgets();
        $post_ID = $post->ID;
        include( $this->pluginPath . 'includes/section-form.php');
    }
    /**
     * Output the form in the page-wide meta box. Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function main_meta_box_form($post, $metabox) {
        $widget_page_id = get_post_meta($post->ID, '_intelliwidget_widget_page_id', true);
        include( $this->pluginPath . 'includes/page-form.php');
    }
    
    /**
     * Output the form in the post meta box. Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function post_meta_box_form($post, $metabox) {
        include( $this->pluginPath . 'includes/post-form.php');
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
        if (empty($_POST['iwpage']) || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( !empty($post) && 'revision' == $post->post_type )) return false;
        
        $post_ID   = intval($_POST['post_ID']);
        // security checkpoint
        if ( empty($post_ID) || !current_user_can('edit_post', $post_ID) || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_ID) ) return false;
        // reset the data array
        $post_data = array();
        $prefix    = 'intelliwidget_';
        // since we can now save a single meta box via ajax post, 
        // we need to manipulate the existing boxmap
        $box_map = $this->get_box_map($post_ID);
        
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
            $post_data[$box_id][$iw_field] = $this->sanitize($_POST[$iw_key]);
        endforeach;
        // track meta boxes updated
        $boxcounter = 0;
        // additional processing for each box data segment
        foreach (array_keys($post_data) as $box_id):
            // special handling for checkboxes:
            foreach(array('skip_expired', 'skip_post', 'link_title', 'hide_if_empty', 'filter', 'future_only', 'active_only', 'nocopy') as $cb):
                $post_data[$box_id][$cb] = isset($_POST[$prefix . $box_id . '_' . $cb]);
            endforeach;
            $post_data[$box_id]['post_types'] = empty($_POST[$prefix . $box_id . '_post_types']) ? 
                array() : $_POST[$prefix . $box_id . '_post_types'];
            // handle custom text
            if ( !current_user_can('unfiltered_html') ):
                // raw html parser/cleaner-upper: see WP docs re: KSES
                $post_data[$box_id]['custom_text'] = stripslashes( 
                    wp_filter_post_kses( addslashes($post_data[$box_id]['custom_text']) ) ); 
            endif;
            $post_data[$box_id]['custom_text'] = base64_encode($post_data[$box_id]['custom_text']);
            // update map
            $box_map[$box_id] = empty($_POST[$prefix . $box_id . '_replace_widget']) ? NULL : $_POST[$prefix . $box_id . '_replace_widget'];
            // serialize and save new data
            $savedata = serialize($post_data[$box_id]);
            update_post_meta($post_ID, '_intelliwidget_data_' . $box_id, $savedata);
            // increment box counter
            $boxcounter++;
        endforeach;
        if ($boxcounter)
            // if we have updates, serialize and save new map
            update_post_meta($post_ID, '_intelliwidget_map', serialize($box_map));
        // save custom post data if it exists
        $this->save_cdfdata();
        // save copy page id (i.e., "use settings from ..." ) if it exists
        $this->save_copy_page($post_ID);
        return true;
    }

    function ajax_save_postdata() {
        if (false === $this->save_postdata()) die('fail');
        // get pages to refresh page menu
        $post_type_key = current(preg_grep("/_post_types$/", array_keys($_POST)));
        $page_key = str_replace('_post_types', '_page', $post_type_key);
        $instance = array(
            'page'      => $_POST[$page_key],
            'post_types'=> $_POST[$post_type_key],
        );
        $response = $this->get_pages($instance);
        die($response);
    }
    function ajax_save_cdfdata() {
        if (false === $this->save_cdfdata()) die('fail');
        die('success');
    }
    function save_cdfdata() {
        if (empty($_POST['iwpage']) ) return false;
        
        $post_ID   = intval($_POST['post_ID']);
        // security checkpoint
        if ( empty($post_ID) || !current_user_can('edit_post', $post_ID) || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_ID) ) return false;
        // reset the data array
        $prefix    = 'intelliwidget_';
        foreach (array(
            'event_date',
            'expire_date',
            'alt_title',
            'external_url',
            'link_classes',
            'link_target',
            ) as $cptfield):
            if (array_key_exists($prefix.$cptfield, $_POST)):
                if (empty($_POST[$prefix.$cptfield])):
                    delete_post_meta($post_ID, $prefix.$cptfield);
                else:
                    update_post_meta($post_ID, $prefix.$cptfield, $_POST[$prefix.$cptfield]);
                endif;
            endif;
        endforeach;
    }
    
    function ajax_delete_meta_box() {
        if (!array_key_exists('post', $_POST) || !array_key_exists('iwdelete', $_POST) || 
            !array_key_exists('_wpnonce', $_POST)) die('fail');
        // note that the query string version uses "post" instead of "post_ID"
        $post_ID = intval($_POST['post']);
        $box_id = intval($_POST['iwdelete']);
        $nonce = $_POST['_wpnonce'];
        if ($this->delete_meta_box($post_ID, $box_id, $nonce, 
            $this->get_box_map($post_ID)) === false) die('fail');
        die('success');
    }

    function ajax_add_meta_box() {
        if (!array_key_exists('post', $_POST) || 
            !array_key_exists('_wpnonce', $_POST)) die('fail');
        // note that the query string version uses "post" instead of "post_ID"
        $post_ID = intval($_POST['post']);
        $nonce = $_POST['_wpnonce'];
        $pagesection = $this->add_meta_box($post_ID, $nonce, 
            $this->get_box_map($post_ID));
        if (false === $pagesection) die('fail');
        $intelliwidget_data = $this->defaults();
        $widgets_array = wp_get_sidebars_widgets();

        ob_start();
        include( $this->pluginPath . 'includes/section-form.php');
        $form = ob_get_contents();
        ob_end_clean();
        $form = '<div id="intelliwidget_section_meta_box_' . $pagesection . '" class="postbox iw_new_box">
<div class="handlediv" title="Click to toggle"></div><h3 class="hndle"><span>IntelliWidget Section (New)</span></h3>
<div class="inside">
' . $form . '
</div>
</div>
';
        die($form);
    }
    
    function get_box_map($post_ID = NULL) {
        if (empty($post_ID) || (! $box_map = unserialize(get_post_meta($post_ID, '_intelliwidget_map', true)))) 
            $box_map = array();
        return $box_map;
    }
    
    function ajax_copy_page() {
        if (!array_key_exists('post_ID', $_POST) || !array_key_exists('iwpage', $_POST))
            die('fail');
        $post_ID   = intval($_POST['post_ID']);
        if (!current_user_can('edit_post', $post_ID) || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_ID)) die('fail');
        if (false === $this->save_copy_page($_POST['post_ID'])) die('fail');
        die('success');
    }
    
    function save_copy_page($post_ID = NULL) {
        if (empty($post_ID) || !array_key_exists('intelliwidget_widget_page_id', $_POST)) return false;
        // save copy page id (i.e., "use settings from ..." ) if it exists
        $copy_page_id = intval($_POST['intelliwidget_widget_page_id']);
        update_post_meta($post_ID, '_intelliwidget_widget_page_id', $copy_page_id);
    }
    
    function delete_meta_box($post_ID = NULL, $box_id = NULL, $nonce = NULL, $box_map = array()) {
        if (empty($post_ID) || empty($nonce) || empty($box_id) || !current_user_can('edit_post', $post_ID)) return false;
        if (!wp_verify_nonce( $nonce, 'iwdelete' ) || !array_key_exists($box_id, $box_map) ) return false; 
        delete_post_meta($post_ID, '_intelliwidget_data_' . $box_id);
        unset($box_map[$box_id]);
        update_post_meta($post_ID, '_intelliwidget_map', serialize($box_map));
    }
    
    function add_meta_box($post_ID = NULL, $nonce = NULL, $box_map = array()) {
        if (empty($post_ID) || empty($nonce)  || !current_user_can('edit_post', $post_ID)) return false;
        if (! wp_verify_nonce( $nonce, 'iwadd' )) return false;
        if (count($box_map)): 
            $newkey = max(array_keys($box_map)) + 1;
        else: 
            $newkey = 1;
        endif;
        $box_map[$newkey] = '';
        update_post_meta($post_ID, '_intelliwidget_map', serialize($box_map));
        return $newkey;
    }
    
    /**
     * Get list of posts as select options. Selects all posts of the type(s) specified in the instance data
     * and returns them as a multi-select menu
     *
     * @param <array> $instance
     * @return <string> 
     */
    function get_pages($instance = NULL) {
        if ( empty($instance['page']) ):
            $instance['page'] = array();
        elseif (!is_array($instance['page'])):
            $instance['page'] = array($instance['page']);
        endif;
        $pages = get_posts(
            array(
                'post_type'      => $instance['post_types'], 
                'posts_per_page' => -1, 
                'showposts'      => -1,
                'orderby'        => 'menu_order,title',
            )
        );
    	$output = '';
	    if ( ! empty($pages) ) {
            $args = array($pages, 0, $instance);
            $walker = new Walker_IntelliWidget(); 
	        $output .= call_user_func_array(array($walker, 'walk'), $args);
	    }

	    return $output;
    }

    
    /**
     * Return a list of template files in the theme folder and plugin folder.
     * Templates actually render the output to the widget based on instance settings
     *
     * @return <array>
     */
    function get_widget_templates() {
        $templates = array();
        // check theme folder if intelliwidget folder exists and grab custom templates
        if ( $handle = @opendir(get_stylesheet_directory() . '/intelliwidget') ) {
            while (false !== ($file = readdir($handle))) {
                if ( ! preg_match("/^\./", $file) && preg_match('/\.php$/', $file) ) {
                    $file = str_replace('.php', '', $file);
                    $name = str_replace('-', ' ', $file);
                    $templates[$file] = ucfirst($name) . "<br>";
                }
            }
            closedir($handle);
        }
        // grab pre-configured plugin templates
        if ( $handle = opendir($this->pluginPath . '/templates') ) {
            while (false !== ($file = readdir($handle))) {
                if ( ! preg_match("/^\./", $file) && preg_match('/\.php$/', $file) ) {
                    $file = str_replace('.php', '', $file);
                    $name = str_replace('-', ' ', $file);
                    $templates[$file] = ucfirst($name) . "<br>";
                }
            }
            closedir($handle);
        }
        asort($templates);
        return $templates;
    }
    
    /**
     * Retrieve a template file from either the theme or the plugin directory.
     *
     * @param <string> $template    The name of the template.
     * @param <string> $ext            The template file extension
     * @param <string> $type        Retrieve from path or url
     * @return <string>                The full path to the template file.
     */
    function get_template($template = NULL, $ext = '.php', $type = 'path') {
        if ( NULL == $template ) {
            return false;
        }
        $themeFile = get_stylesheet_directory() . '/intelliwidget/' . $template . $ext;
        if ( file_exists($themeFile) ) {
            if ( 'url' == $type ) {
                return get_bloginfo('template_url') . '/intelliwidget/' . $template . $ext;
            } else {
                $file = get_stylesheet_directory() . '/intelliwidget/' . $template . $ext;
            }
        } elseif ( 'url' == $type ) {
            return $this->templatesURL . $template . $ext;
        } else {
            $file = $this->templatesPath . $template . $ext;
        }
        if (file_exists($file)) return $file;
        return false;
    }

    /**
     * Ensure that "post-thumbnails" support is available for those themes that don't register it.
     * @return  void
     */
    public function ensure_post_thumbnails_support () {
        if ( ! current_theme_supports( 'post-thumbnails' ) ) { add_theme_support( 'post-thumbnails' ); }
    } // End ensure_post_thumbnails_support()

    /**
     * Stub for data validation
     * @param <string> $unclean - data to parse
     * @param <array> $rules
     * @return <string> $clean - sanitized data
     */
    function sanitize($unclean = NULL, $rules = array()) {
        $clean = $unclean;
        return $clean;
    }
    /**
     * Widget Defaults
     * This will utilize an options form in a future release for customization
     * @param <array> $instance
     * @return <array> -- merged data
     */
    public function defaults($instance = array()) {
        //if (empty($instance)) $instance = array();
        $defaults = array(
            'template'       => 'menu',
            'page'           => array(),
            'category'       => -1,
            'items'          => 5,
            'length'         => 15,
            'link_text'      => __('Read More', 'intelliwidget'),
            'post_types'     => array('page', 'post'),
            'sortby'         => 'title',
            'sortorder'      => 'ASC',
            'replace_widget' => 'none',
            'imagealign'     => 'none',
            'image_size'     => 'none',
            'nav_menu'       => '',
            'title'          => '',
            'custom_text'    => '',
            'text_position'  => '',
            'classes'        => '',
            'container_id'   => '',
            'skip_expired'   => 0,
            'link_title'     => 0,
            'skip_post'      => 0,
            'hide_if_empty'  => 0,
            'filter'         => 0,
            'future_only'    => 0,
            'active_only'    => 0,
            'nocopy'         => 0,
        );
        // standard WP function for merging argument lists
        $merged = wp_parse_args($instance, $defaults);
        return $merged;
    }
    
    function get_eligible_post_types() {
        $eligible = array();
        if ( function_exists('get_post_types') ):
            $types = get_post_types(array('public' => true));
        else:
            $types = array('post', 'page');
        endif;
        foreach($types as $type):
            if (post_type_supports($type, 'custom-fields'))
                $eligible[] = $type;
        endforeach;
        return $eligible;
    }
    
    /**
     * Output the widget using selected template
     *
     * @param <array> $args
     * @param <array> $instance
     * @return void
     */
    function build_widget($args, $instance, $post_ID = null) {
        global $this_instance;
        $instance = $this_instance = $this->defaults($instance);
        if (!is_array($instance['page'])) $instance['page'] = array($instance['page']);
        if (!is_array($instance['post_types'])) $instance['post_types'] = array($instance['post_types']);
        extract($args, EXTR_SKIP);
        /* if this is a nav menu get menu object and skip query */
        $nav_menu = false;
        if (! empty( $instance['nav_menu'] )) :
            $nav_menu =  wp_get_nav_menu_object( $instance['nav_menu'] );
        else:
            $selected = new IntelliWidget_Query($instance);
        endif;
        
        // use widget CSS if present
        $classes = array();
        if (!empty($instance['classes'])) :
            $classes = preg_split("/[, ;]+/", $instance['classes']);
        endif;
        if (!empty($instance['container_id'])):
            $before_widget = preg_replace('/id=".+?"/', 'id="' . $instance['container_id'] . '"', $before_widget);
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
            if ( $instance['link_title'] && $selected->post_count) {
                // @params $post_ID, $text, $category_ID
                the_intelliwidget_link($selected->posts[0]->ID, $title, $instance['category']);
            } else {
                echo $title;
            }
            echo $after_title;
        }
        // handle custom text
        $custom_text = apply_filters( 'widget_text', $instance['custom_text'], $instance );
        if (('above' == $instance['text_position'] || 'only' == $instance['text_position'] )):
            echo '<div class="textwidget">' . ( !empty( $instance['filter'] ) ? 
                wpautop( $custom_text ) : $custom_text ) . '</div>';
        endif;
        if ('only' == $instance['text_position']):
            echo $after_widget;
            return;
        endif;
        // if this is a nav menu, use default WP menu output
        if (!empty($instance['nav_menu'])):
            if ('-1' == $instance['nav_menu'] ):
                wp_page_menu( array( 'show_home' => true, 'menu_class' => 'iw-menu' . (empty($instance['classes'])?'':' ' . $instance['classes']) ));
            else:
                wp_nav_menu( array( 'fallback_cb' => '', 'menu' => $nav_menu, 'menu_class' => 'iw-menu' . (empty($instance['classes'])?'':' ' . $instance['classes'])));
            endif;
        // otherwise load IW template
        else:
            if ($template = $this->get_template($instance['template'])):
                include ($template);
            endif;
        endif;
        if ('below' == $instance['text_position']):
            echo "<div class=\"textwidget\">\n" . ( !empty( $instance['filter'] ) ? 
                wpautop( $custom_text ) : $custom_text ) . "\n</div>\n";
        endif;
        echo $after_widget;
    }
    
    function get_page_data($post_id, $box_id) {
        // are there settings for this widget?
        if ($page_data = unserialize(get_post_meta($post_id, '_intelliwidget_data_' . $box_id, true))):
            if (!empty($page_data['custom_text'])):
                // base64 encoding saves us from markup serialization heartburn
                $page_data['custom_text'] = stripslashes(base64_decode($page_data['custom_text']));
            endif;
            return $page_data;
        endif;
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
        $old_post = $post;
        // section parameter lets us use page-specific IntelliWidgets in shortcode without all the params
        if (is_object($post) && !empty($atts['section'])):
//            if (!($atts = $this->get_page_data($post->ID, intval($atts['section'])))): return; endif;
             $atts = $this->get_page_data($post->ID, intval($atts['section']));
             if (empty($atts)): return; endif;
       else:
            if (!empty($atts['pages'])) $atts['pages'] = preg_split("/, */", $atts['pages']);
            if (!empty($atts['post_types'])) $atts['post_types'] = preg_split("/, */", $atts['post_types']);
            if (!empty($atts['custom_text'])) unset($atts['custom_text']);
            if (!empty($atts['text_position'])) unset($atts['text_position']);
            if (!empty($atts['title'])) $atts['title'] = strip_tags($atts['title']);
            if (!empty($atts['link_text'])) $atts['link_text'] = strip_tags($atts['link_text']);
        endif;
        $atts = $this->defaults($atts);
        $args = array(
            'before_title'  => '',
            'after_title'   => '',
            'before_widget' => empty($atts['nav_menu'])?'<div class="widget_intelliwidget">':'',
            'after_widget'  => empty($atts['nav_menu'])?'</div>':'',
        );
        // buffer standard output
        ob_start();
        // generate widget from arguments
        $this->build_widget($args, $atts, is_object($post) ? $post->ID : null);
        // retrieve widget content from buffer
        $content = ob_get_contents();
        ob_end_clean();
        $post = $old_post;
        // return widget content
        return $content;
    }
       
    /**
     * Stub for plugin activation
     */
    function intelliwidget_activate() {
    }
}


