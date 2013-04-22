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
class IntelliWidget {

    var $version     = '1.0';
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
        $this->pluginURL     = esc_url( str_replace( WP_PLUGIN_DIR, WP_PLUGIN_URL, $this->pluginPath) );
        $this->templatesPath = $this->dir . '/templates/';
        $this->templatesURL  = esc_url( str_replace( WP_PLUGIN_DIR, WP_PLUGIN_URL, $this->templatesPath) );
		$this->docsLink      = '<a href="http://www.lilaeamedia.com/plugins/intelliwidget/" target="_blank" title="Help" style="float:right">Help</a>';
        $this->load_settings();
        register_activation_hook($file, array(&$this, 'intelliwidget_activate'));
        // these actions only apply to admin users
        if (is_admin()):
            add_action('admin_init', array(&$this, 'admin_init'));
            add_action('add_meta_boxes', array(&$this, 'intelliwidget_main_meta_box') );
            add_action('add_meta_boxes', array(&$this, 'intelliwidget_section_meta_box') );
            add_action('save_post', array(&$this, 'intelliwidget_save_postdata'), 1, 2 );
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
        //wp_register_script('intelliwidget-js', $this->pluginURL . 'js/intelliwidget.js');
    }
    
    /**
     * Stub for printing the administration styles.
     */
    function admin_print_styles() {
        //wp_enqueue_style('intelliwidget-admin-css');
    }

    /**
     * Stub for printing the scripts needed for the admin.
     */
    function admin_print_scripts() {
        //wp_enqueue_script('intelliwidget-js');
    }
    
    /**
     * Display message in admin after create/update/delete
	 * FIXME: future release
     * @return  void
     */
    function intelliwidget_display_notice() {
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
    function intelliwidget_section_meta_box() {
        global $post;
        // box_map contains map of meta boxes to their related widgets
        if (!($box_map = unserialize(get_post_meta($post->ID, '_intelliwidget_map', true)))):
            $box_map = array();
        endif;
        if (is_array($box_map)):
            if (array_key_exists('_wpnonce', $_GET)):
                $nonce = $_GET['_wpnonce'];
                // add or delete boxes from previous page
                if (array_key_exists('iwdelete', $_GET)):
                    $iwdelete = intval($_GET['iwdelete']);
                    // delete box
                    if ( wp_verify_nonce( $nonce, 'iwdelete' ) && array_key_exists($iwdelete, $box_map) ): 
                        delete_post_meta($post->ID, '_intelliwidget_data_' . $iwdelete);
                        unset($box_map[$iwdelete]);
                        $_SESSION['intelliwidget_message'] = 'IntelliWidget deleted.';
                        update_post_meta($post->ID, '_intelliwidget_map', serialize($box_map));
                    endif;
                elseif (array_key_exists('iwadd', $_GET)):
                    // add box
                    if ( wp_verify_nonce( $nonce, 'iwadd' )): 
                        if (count($box_map)): 
                            $newkey = max(array_keys($box_map)) + 1;
                        else: 
                            $newkey = 1;
                        endif;
                        $box_map[$newkey] = '';
                        $_SESSION['intelliwidget_message'] = 'New IntelliWidget added.';
                        update_post_meta($post->ID, '_intelliwidget_map', serialize($box_map));
                    endif;
                endif;
                // redirect so post data is not cached
                wp_redirect(admin_url('post.php?action=edit&post=' . $post->ID));
                die();
            else:
                // display notice from previous update (if exists)
                add_action('admin_notices', array(&$this, 'intelliwidget_display_notice'));
            endif;
            // refresh the meta box forms on the page
            $count = 1;
            foreach($box_map as $box_id => $sidebar_widget_id):
                add_meta_box( 
                    'intelliwidget_section_meta_box_' . $box_id,
                    __( 'IntelliWidget Section ', 'intelliwidget') . $count,
                    array( &$this, 'intelliwidget_section_meta_box_form' ),
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
    function intelliwidget_main_meta_box() {
        global $post;
        add_meta_box( 
            'intelliwidget_main_meta_box',
            __( 'IntelliWidget', 'intelliwidget'),
            array( &$this, 'intelliwidget_main_meta_box_form' ),
            'page',
            'side',
            'low'
        );
    }
    
    /**
     * Output the form in the section meta box(es). Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function intelliwidget_section_meta_box_form($post, $metabox) {
        $pagesection = $metabox['args']['pagesection'];
        $meta_name = '_intelliwidget_data_' . $pagesection;
        $intelliwidget_data = $this->defaults(unserialize(get_post_meta( $post->ID, $meta_name, true ) ));
        $intelliwidget_data['custom_text'] = stripslashes(base64_decode($intelliwidget_data['custom_text']));
        if (!is_array($intelliwidget_data['page'])) $intelliwidget_data['page'] = array($intelliwidget_data['page']);
        if (!is_array($intelliwidget_data['post_types'])) $intelliwidget_data['post_types'] = array($intelliwidget_data['post_types']);
        $widgets_array = wp_get_sidebars_widgets();
        include( $this->pluginPath . 'includes/section-form.php');
    }
    /**
     * Output the form in the page-wide meta box. Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function intelliwidget_main_meta_box_form($post, $metabox) {
        $widget_page_id = get_post_meta($post->ID, '_intelliwidget_widget_page_id', true);
        include( $this->pluginPath . 'includes/page-form.php');
    }
    
    
    /**
     * Parse POST data and update page-specific data using custom fields
     * @param <integer> $id -- revision id
     * @param <object>  $post -- revision post data
     * @return  void
     */
    function intelliwidget_save_postdata($id, $post) {
        /***
         * Skip auto-save and revisions. wordpress saves each post twice, once for the revision and once to update
         * the actual post record. The parameters passed by the 'save_post' action are for the revision, so 
         * we must use the post_ID passed in the form data, and skip the revision. 
         */
        if (empty($_POST['iwpage']) || ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) || ( $post->post_type == 'revision' )) return;
        
        $post_ID   = $_POST['post_ID'];
        if ( empty($post_ID) || !wp_verify_nonce($_POST['iwpage'],'iwpage_' . $post_ID) ) return;
        // reset the map and data arrays
        $box_map   = $post_data = array();
        $prefix    = 'intelliwidget_';
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

        // additional processing for each box data segment
        foreach (array_keys($post_data) as $box_id):
            // special handling for checkboxes:
            foreach(array('skip_post', 'link_title', 'hide_if_empty', 'filter', 'future_only') as $cb):
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
            $box_map[$box_id] = $_POST[$prefix . $box_id . '_replace_widget'];
            // serialize and save new data
            $savedata = serialize($post_data[$box_id]);
            update_post_meta($post_ID, '_intelliwidget_data_' . $box_id, $savedata);
        endforeach;
        // serialize and save new map
        update_post_meta($post_ID, '_intelliwidget_map', serialize($box_map));
        if (array_key_exists('intelliwidget_widget_page_id', $_POST)):
            $copy_page_id = intval($_POST['intelliwidget_widget_page_id']);
            update_post_meta($post_ID, '_intelliwidget_widget_page_id', $copy_page_id);
        endif;
    }

    /**
     * Get list of posts as select options. Selects all posts of the type(s) specified in the instance data
     * and returns them as a multi-select menu
     *
     * @param <array> $instance
     * @return <string> 
     */
    function get_pages($instance = NULL) {
        extract($instance);
        if ( !is_array($page) ) {
            $page = array($page);
        }
        $pages = get_posts(array(
            'post_type'      => $post_types, 
            'posts_per_page' => -1, 
            'showposts'      => -1, 
            'orderby'          => 'title', 
            'order'          => 'asc')
        );
        $output = '';
        foreach ( $pages as $thispage ) {
            $output.= '<option class="intelliwidget-option intelliwidget-' . $thispage->post_type . '" value="' . $thispage->ID . '"';
            if ( in_array($thispage->ID, $page) ) {
                $output.= ' selected';
            }
            // display the post title and post type
            $output.= '>' . substr($thispage->post_title, 0, 60) . ' (' . ucfirst($thispage->post_type) . ')' 
                . "</option>\n";
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
     * @param <string> $template	The name of the template.
     * @param <string> $ext			The template file extension
     * @param <string> $type		Retrieve from path or url
     * @return <string>				The full path to the template file.
     */
    function get_template($template = NULL, $ext = '.php', $type = 'path') {
        if ( $template == NULL ) {
            return false;
        }
        $themeFile = get_stylesheet_directory() . '/intelliwidget/' . $template . $ext;
        if ( file_exists($themeFile) ) {
            if ( $type == 'url' ) {
                $file = get_bloginfo('template_url') . '/intelliwidget/' . $template . $ext;
            } else {
                $file = get_stylesheet_directory() . '/intelliwidget/' . $template . $ext;
            }
        } elseif ( $type == 'url' ) {
            $file = $this->templatesURL . $template . $ext;
        } else {
            $file = $this->templatesPath . $template . $ext;
        }
        return $file;
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
    public function defaults($instance) {
        if (empty($instance)) $instance = array();
        $defaults = array(
            'template'        => 'menu',
            'title'            => '',
            'page'            => array(),
            'category'        => -1,
            'items'            => 5,
            'length'        => 15,
            'link_title'    => '',
            'link_text'        => __('Read More', 'intelliwidget'),
            'classes'        => '',
            'post_types'    => array('page', 'element'),
            'skip_post'        => '',
            'sortby'        => 'title',
            'sortorder'        => 'ASC',
            'custom_text'    => '',
            'replace_widget'=> '',
            'hide_if_empty'    => '',
            'text_position'    => '',
            'filter'        => '',
            'future_only'    => '',
            'imagealign'    => 'auto',
            'image_size'    => 'none'
        );
        // standard WP function for merging argument lists
        $merged = wp_parse_args($instance, $defaults);
        return $merged;
    }
    
    /**
     * Stub for plugin activation
     */
    function intelliwidget_activate() {
    }
}

