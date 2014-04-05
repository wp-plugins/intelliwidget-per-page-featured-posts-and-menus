<?php 
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-list.php - arrays for UI output
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
class IntelliWidgetList {
    var $labels;
    var $tips;
    var $menus;
    var $fields;
    
    function __construct() {
        $this->labels = array(
            'metabox_title'     => __('IntelliWidget Profiles', 'intelliwidget'),
            
            'cdf_title'         => __('IntelliWidget Custom Fields', 'intelliwidget'),
            
            'hide_if_empty'     => __('Placeholder Only (do not display)', 'intelliwidget'),

            'generalsettings'   => __('General Settings', 'intelliwidget'),

            'content'           => __('IntelliWidget Type', 'intelliwidget'),

            'title'             => __('Section Title (Optional)', 'intelliwidget'),

            'link_title'        => __('Link', 'intelliwidget'),

            'container_id'      => __('Unique ID', 'intelliwidget'),

            'classes'           => __('Classes', 'intelliwidget'),

            'addltext'          => __('Additional Text/HTML', 'intelliwidget'),

            'text_position'     => __('Display', 'intelliwidget'),

            'filter'            => __('Automatically add paragraphs', 'intelliwidget'),

            'appearance'        => __('Appearance', 'intelliwidget'),

            'template'          => __('Template', 'intelliwidget'),

            'sortby'            => __('Sort posts by', 'intelliwidget'),

            'items'             => __('Max posts', 'intelliwidget'),

            'length'            => __('Max words per post', 'intelliwidget'),

            'allowed_tags'      => __('Allowed HTML Tags', 'intelliwidget') . '<br/>' . __('(p, br, em, strong, etc.)', 'intelliwidget'),
            
            'link_text'         => __('"Read More" Text', 'intelliwidget'),

            'imagealign'        => __('Image Align', 'intelliwidget'),

            'image_size'        => __('Image Size', 'intelliwidget'),

            'selection'         => __('Post Selection', 'intelliwidget'),

            'post_types'        => __('Select from these Post Types', 'intelliwidget'),

            'terms'             => __('Terms: Select by Category, Tag, etc.', 'intelliwidget'),

            'page'              => __('Select specific posts', 'intelliwidget'),

            'skip_post'         => __('Exclude current post', 'intelliwidget'),

            'future_only'       => __('Include only future posts', 'intelliwidget'),

            'active_only'       => __('Exclude future posts', 'intelliwidget'),

            'skip_expired'      => __('Exclude expired posts', 'intelliwidget'),

            'include_private'   => __('Include private posts', 'intelliwidget'),

            'nav_menu'          => __('Menu to display', 'intelliwidget'),

            'widget_page_id'    => __('Use Profiles from', 'intelliwidget'),

            'iw_add'            => __('+ Add New Profile', 'intelliwidget'),
            
            'event_date'        => __('Start Date', 'intelliwidget'),
            
            'expire_date'       => __('Expire Date', 'intelliwidget'),
            
            'alt_title'         => __('Alt Title', 'intelliwidget'),
            
            'external_url'      => __('External URL', 'intelliwidget'),
            
            'link_classes'      => __('Link Classes', 'intelliwidget'),
            
            'link_target'       => __('Link Target', 'intelliwidget'),
            
            'replace_widget'    => __('Parent Profile to replace', 'intelliwidget'),
            
            'nocopy'            => __('Override profiles selected above with this Profile', 'intelliwidget'),
);

        $this->tips = array(
            'hide_if_empty'     => __('Check this box to restrict this IntelliWidget to pages/posts with custom settings. If the page or post being viewed has not been configured with its own Intelliwidget settings, this section will be hidden.', 'intelliwidget'),

            'generalsettings'   => __('These settings apply to all IntelliWidgets, including the type of IntelliWidget, the Section Title, HTML container id and CSS classes.', 'intelliwidget'),

            'content'           => __('This menu controls the type of IntelliWidget to display and the other settings available. If you are using IntelliWidget extensions, they will appear as options here as well.', 'intelliwidget'),

            'title'             => __('Enter a title here if you want a heading above this IntelliWidget section, otherwise, leave it blank.', 'intelliwidget'),

            'link_title'        => __('Check this box to automatically link the title to another page. If you are using categories, tags or other taxonomies, the link will point to that archive, otherwise it will point to the first post that appears in the list.', 'intelliwidget'),

            'container_id'      => __('Enter a unique value if you wish to customize the IntelliWidget div container id attribute.', 'intelliwidget'),

            'classes'           => __("Enter additional CSS class names if you wish to customize this section's styles.", 'intelliwidget'),

            'addltext'          => __('These settings allow you to add additional text to display above or below the IntelliWidget output. If your theme supports shortcodes in text widgets, you can use them here. If your user account has HTML editing capabilities, you can enter HTML as well.', 'intelliwidget'),

            'text_position'     => __('This menu controls the position of the additional text. You can also choose to display only the text, skipping the post selection entirely.', 'intelliwidget'),

            'filter'            => __('Check this box to insert paragraph breaks wherever blank lines appear in the text you enter.', 'intelliwidget'),

            'appearance'        => __('Control the number of posts displayed, excerpt length, featured image and other settings.', 'intelliwidget'),

            'template'          => __('This menu controls the IntelliWidget template used to display the output. If you are using custom templates, they will appear here as well.', 'intelliwidget'),

            'sortby'            => __('This menu controls the post attribute used to sort the posts that are selected. Select ascending or descending order with the second menu (does not apply to random). Start Date is set for each post with IntelliWidget Custom Fields (see).', 'intelliwidget'),

            'items'             => __('This setting controls the number of posts that are selected to appear in this IntelliWidget section.', 'intelliwidget'),

            'length'            => __('This setting controls the number of words to display for each post selected to appear in this IntelliWidget section.', 'intelliwidget'),

            'allowed_tags'      => __('By default, HTML is stripped from the post content. Enter any HTML tags that you do not not wish to remove. Do not include &gt; or &lt; characters.', 'intelliwidget'),

            'link_text'         => __('Enter a value if you wish to customize the text that appears in the link to each post.', 'intelliwidget'),

            'imagealign'        => __('If you are using a Template that includes the featured image, this menu controls how it is aligned relative to the post content.', 'intelliwidget'),

            'image_size'        => __('If you are using a Template that includes the featured image, this menu controls the display size of the image.', 'intelliwidget'),

            'selection'         => __('These settings control the template used and the posts that are displayed. Select post type, taxonomy terms and date conditions. You can also restrict selection to specific posts.', 'intelliwidget'),

            'post_types'        => __('These checkboxes restrict the selection to specific Post Types, post and page by default. At least one must be checked.', 'intelliwidget'),

            'terms'             => __('Restrict the output to specific categories, tags or other taxonomies by selecting them from the menu below. Only taxonomies related to the selected post types will appear here as options. Hold down the CTRL key (command on Mac) to select multiple options.', 'intelliwidget'),

            'page'              => __('Restrict the output to specific posts by selecting them from the menu below. Only posts of the types selected above will appear as options. The specific posts must also meet any other selection you choose here. Hold down the CTRL key (command on Mac) to select multiple options.', 'intelliwidget'),

            'skip_post'         => __('Check this box if you wish to exclude the post currently being viewed in the main content from the selection list.', 'intelliwidget'),

            'future_only'       => __('Check this box if you wish to restrict the selection list to posts with a future start date. Start dates are set for each individual post using IntelliWidget Custom Fields (see).', 'intelliwidget'),

            'active_only'       => __('Check this box if you wish to exclude posts with a future start date from the selection list. Start dates are set for each individual post using IntelliWidget Custom Fields (see).', 'intelliwidget'),

            'skip_expired'      => __('Check this box if you wish to exclude posts with a past expire date from the selection list. Expire dates are set for each individual post using IntelliWidget Custom Fields (see).', 'intelliwidget'),

            'nav_menu'          => __("This menu controls the Navigation Menu to be displayed in this IntelliWidget section. To show all pages (including a home page), use the 'Automatic Page Menu' option. Nav Menus are customized from Appearance > Menus in the WordPress admin.", 'intelliwidget'),
            
            'widget_page_id'    => __("Instead entering new settings below, you can reuse all the settings from another IntelliWidget Profile by selecting it from this menu.", 'intelliwidget'),
            
            'iw_add'            => __('Click to add a new IntelliWidget section tab.', 'intelliwidget'),
            
            'event_date'        => __("This value represents the post's starting date. It is used in date-based templates, and to include or exclude posts by date in the 'Post Selection Settings.'", 'intelliwidget'),

            'expire_date'       => __("This value represents the post's ending date. It is used in date-based templates, and to exclude posts that have expired in the 'Post Selection Settings.'", 'intelliwidget'),
            'alt_title'         => __("Enter the value to be used as the title for the post in the IntelliWidget output. If no value is entered, the entire post title will be used.", 'intelliwidget'),
            
            'external_url'      => __("Enter an external URL if you wish for the title to link somewhere other than the post.", 'intelliwidget'),
            
            'link_classes'      => __("Enter additional CSS class names if you wish to customize the title's link styles. This is often used for menu icons.", 'intelliwidget'),
            
            'link_target'       => __("Select a target attribute if you wish for the title link to open in a new window or tab.", 'intelliwidget'),
            
            'replace_widget'    => __("This menu determines the IntelliWidget instance to replace with these settings. Options are labeled by Sidebar Name followed by the nth IntelliWidget in that sidebar. Even if there are other Widgets in the Sidebar, the number represents only the IntelliWidgets in the Sidebar. If you reorder the Widgets in the Sidebar, the number will reflect the change. To use these settings for a shortcode on the post, select 'Shortcode' and use the format [intelliwidget section=tab#], where 'tab#' corresponds to the number of the tab (above) containing the settings you wish to use.", 'intelliwidget'),

            'nocopy'            => __("Check this box to keep these settings even when using another profile from the menu above.", 'intelliwidget'),
            
);

        $this->menus = array(
            'content' => apply_filters('intelliwidget_content_menu', 
                array(
                    'post_list' => __('Posts (default)', 'intelliwidget'),
                    'nav_menu'  => __('Nav Menu', 'intelliwidget'),
                    )
                ),
            'replaces' => apply_filters('intelliwidget_replaces_menu', 
                array(
                    'none'      => __('Unassigned', 'intelliwidget'),
                    'content'   => __('Shortcode in Post Content', 'intelliwidget'),
                    )
                ),
            'text_position' => apply_filters('intelliwidget_text_position_menu', 
                array(
                    ''          => __('None', 'intelliwidget'),
                    'above'     => __('Above Posts', 'intelliwidget'),
                    'below'     => __('Below Posts', 'intelliwidget'),
                    'only'      => __('This text only (no posts)', 'intelliwidget'),
                    )
                ),
            'sortby' => apply_filters('intelliwidget_sortby_menu', 
                array(
                    'date'      => __('Post Date', 'intelliwidget'),
                    'event_date'=> __('Start Date', 'intelliwidget'),
                    'menu_order'=> __('Menu Order', 'intelliwidget'),
                    'title'     => __('Title', 'intelliwidget'),
                    'rand'      => __('Random', 'intelliwidget'),
                    )
                ),
            'image_size' => apply_filters('intelliwidget_image_size_menu', 
                array(
                    'none'      => __('No Image', 'intelliwidget'),
                    'thumbnail' => __('Thumbnail', 'intelliwidget'),
                    'medium'    => __('Medium', 'intelliwidget'),
                    'large'     => __('Large', 'intelliwidget'),
                    'full'      => __('Full', 'intelliwidget'),
                    )
                ),
            'imagealign' => apply_filters('intelliwidget_imagealign_menu', array(
                    'none'      => __('Auto', 'intelliwidget'),
                    'left'      => __('Left', 'intelliwidget'),
                    'center'    => __('Center', 'intelliwidget'),
                    'right'     => __('Right', 'intelliwidget'),
                    )
                ),
            'link_target' => apply_filters('intelliwidget_link_target_menu', 
                array(
                    ''          => __('None', 'intelliwidget'),
                    '_new'      => '_new',
                    '_blank'    => '_blank',
                    '_self'     => '_self',
                    '_top'      => '_top',
                    )
                ),
            'default_nav' => apply_filters('intelliwiget_default_nav_menu', 
                array(
                    ''          => __('None', 'intelliwidget'),
                    '-1'        => __('Automatic Page Menu', 'intelliwidget'),
                    )
                ),
            );
            
        $this->fields = array(
            'checkbox' => apply_filters('intelliwidget_checkbox_fields', 
                array(
                    'skip_expired', 
                    'skip_post', 
                    'link_title', 
                    'hide_if_empty', 
                    'filter', 
                    'future_only', 
                    'active_only', 
                    'include_private',
                    'nocopy',
                )
            ),
            'text' => apply_filters('intelliwidget_text_fields', 
                array(
                    'custom_text', 
                    'title', 
                    'link_text',
                )
            ),
            'custom' => apply_filters('intelliwidget_custom_fields', 
                array(
                    'event_date',
                    'expire_date',
                    'alt_title',
                    'external_url',
                    'link_classes',
                    'link_target',
                    )
                ),
            );
    }
    function get_label($key = '') {
        return isset($this->labels[$key]) ? $this->labels[$key] : '';
    }
    function get_tip($key = '') {
        return isset($this->tips[$key]) ? $this->tips[$key] : '';
    }
    function get_menu($key = '') {
        return isset($this->menus[$key]) ? $this->menus[$key] : array();
    }
    function get_fields($key = '') {
        return isset($this->fields[$key]) ? $this->fields[$key] : array();
    }
}
