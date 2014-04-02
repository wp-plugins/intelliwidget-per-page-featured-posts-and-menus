<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-post.php - Edit Post Settings
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */

class IntelliWidgetPostAdmin extends IntelliWidgetAdmin {

    /**
     * Object constructor
     * @param <string> $file
     * @return void
     */
    function __construct() {
            // these actions only apply to admin users
            add_action('load-post.php',                     array(&$this, 'post_metabox_actions') );
            add_action('save_post',                         array(&$this, 'post_save_data'), 1, 2 );
            add_action('wp_ajax_iw_post_cdfsave',           array(&$this, 'ajax_post_save_cdf_data' ));
            add_action('wp_ajax_iw_post_save',              array(&$this, 'ajax_post_save_data' ));
            add_action('wp_ajax_iw_post_copy',              array(&$this, 'ajax_post_copy_data' ));
            add_action('wp_ajax_iw_post_delete',            array(&$this, 'ajax_post_delete_tabbed_section' ));
            add_action('wp_ajax_iw_post_add',               array(&$this, 'ajax_post_add_tabbed_section' ));
            add_action('wp_ajax_iw_post_menus',             array(&$this, 'ajax_post_get_select_menus' ));
    }

    function post_metabox_actions() {
        if (!isset($this->objecttype)) $this->admin_init('post', 'post_ID');
        add_action('add_meta_boxes',            array(&$this, 'post_main_meta_box'));
        add_action('add_meta_boxes',            array(&$this, 'post_cdf_meta_box'));
        wp_enqueue_style('wp-jquery-ui-dialog');
    }
    /**
     * Generate input form that applies to entire page (add new, copy settings)
     * @return  void
     */
    function post_main_meta_box() {
        // set up meta boxes
        $this->metabox_init();
        foreach (array('post', 'page') as $type):
            add_meta_box( 
                'intelliwidget_main_meta_box',
                $this->get_label('metabox_title'),
                array( &$this, 'post_meta_box_form' ),
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
    function post_cdf_meta_box() {
        global $post;
        foreach (array('post', 'page') as $type):
            add_meta_box( 
                'intelliwidget_post_meta_box',
                $this->get_label('cdf_title'),
                array( &$this, 'post_cdf_meta_box_form' ),
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
    function post_meta_box_form($post, $metabox) {
        $this->metabox->copy_form($this, $post->ID, $this->get_id_list($post));
        $this->render_tabbed_sections($post->ID);
    }
    
    function get_id_list($post) {
        global $intelliwidget;
        $copy_id = $intelliwidget->get_meta($post->ID, '_intelliwidget_', 'post', 'widget_page_id');
        return '
  <select style="width:75%" name="intelliwidget_widget_page_id" id="intelliwidget_widget_page_id">
    <option value="">' . __('This form', 'intelliwidget') . '</option>
      ' . $this->get_posts_list(array('post_types' => array('page', 'post'), 'page' => $copy_id)) . '
  </select>';
    }
    
    /**
     * Output the form in the post meta box. Params are passed by add_meta_box() function
     * @param <object> $post
     * @param <array>  $metabox
     * @return  void
     */
    function post_cdf_meta_box_form($post, $metabox) {
        $this->post_cdf_form($post);
    }
    
    /**
     * Parse POST data and update page-specific data using custom fields
     * @param <integer> $id -- revision id
     * @param <object>  $post -- revision post data
     * @return  void
     */
     
    function post_save_data($id, $post) {
        /***
         * Skip auto-save and revisions. wordpress saves each post twice, once for the revision and once to update
         * the actual post record. The parameters passed by the 'save_post' action are for the revision, so 
         * we must use the post_ID passed in the form data, and skip the revision. 
         */
        if ((defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) 
            || ( !empty($post) && !in_array($post->post_type, array('post','page')) )) return false;

        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        if (empty($post_id)
            // skip nonce test on non-ajax post
            //|| !$this->validate_post('iwpage_' . $post_id, 'iwpage', 'edit_post', false, $post_id)
         ) return false;

        $this->admin_init('post', 'post_ID');

        $this->save_data($post_id);
        // save custom post data if it exists
        $this->post_save_cdf_data($post_id);
        // save copy page id (i.e., "use settings from ..." ) if it exists
        $this->save_copy_id($post_id);
    }

    function post_save_cdf_data($post_id) {
        // reset the data array
        $prefix    = 'intelliwidget_';
        foreach ($this->get_custom_fields() as $cfield):
            $cdfield = $prefix . $cfield;
            if (array_key_exists($cdfield, $_POST)):
                if (empty($_POST[$cdfield]) || '' == $_POST[$cdfield]):
                    $this->delete_meta($post_id, $cdfield);
                else:
                    $newdata = $_POST[$cdfield];
                    if ( !current_user_can('unfiltered_html') ):
                        $newdata = stripslashes( 
                        wp_filter_post_kses( addslashes($newdata) ) ); 
                    endif;
                    $this->update_meta($post_id, $cdfield, $newdata);
                endif;
            endif;
        endforeach;
    }
    
    // ajax save for posts only - duplicate this for other types
    function ajax_post_save_data() {
        $this->admin_init('post', 'post_ID');
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        $box_id_key = current(preg_grep("/_box_id$/", array_keys($_POST)));
        $box_id = isset($_POST[$box_id_key]) ? intval($_POST[$box_id_key]) : NULL;
        if (empty($post_id) || empty($box_id) || 
            !$this->validate_post('iwpage_' . $post_id, 'iwpage', 'edit_post', true, $post_id)) die('fail');
        $this->ajax_save_data($post_id, $box_id);
    }
    
    // ajax copy for posts only - duplicate this for other types
    function ajax_post_copy_data() {
        $this->admin_init('post', 'post_ID');
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        if (empty($post_id) ||  
            !$this->validate_post('iwpage_' . $post_id, 'iwpage', 'edit_post', true, $post_id)) die('fail');

        if (false === $this->save_copy_id($post_id)) die('fail');
        die('success');
    }
    
    // posts only
    function ajax_post_save_cdf_data() {
        $this->admin_init('post', 'post_ID');
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        if (empty($post_id) || 
            !$this->validate_post('iwpage_' . $post_id, 'iwpage', 'edit_post', true, $post_id)) die('fail');
        if (false === $this->post_save_cdf_data($post_id)) die('fail');
        die('success');
    }
    
    // ajax delete for posts only - duplicate this for other types
    function ajax_post_delete_tabbed_section() {
        $this->admin_init('post', 'post_ID');
        // note that the query string version uses "post" instead of "post_ID"
        $post_id = isset($_POST['objid']) ? intval($_POST['objid']) : NULL;
        $box_id = isset($_POST['iwdelete']) ? intval($_POST['iwdelete']) : NULL;
        if (empty($post_id) || //empty($box_id) || 
            !$this->validate_post('iwdelete', '_wpnonce', 'edit_post', true, $post_id)) die('fail');
        if (false === $this->delete_tabbed_section($post_id, $box_id)) die('fail');
        die('success');
    }

    // ajax add for posts only - duplicate this for other types
    function ajax_post_add_tabbed_section() {
        $this->admin_init('post', 'post_ID');
        // note that the query string version uses "post" instead of "post_ID"
        $post_id = isset($_POST['objid']) ? intval($_POST['objid']) : NULL;
        if (empty($post_id) 
            || !$this->validate_post('iwadd', '_wpnonce', 'edit_post', true, $post_id)) die('fail');
        // note that the query string version uses "post" instead of "post_ID"
        $this->ajax_add_tabbed_section($post_id);
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
        $this->admin_init('post', 'post_ID');
        $post_id = isset($_POST['post_ID']) ? intval($_POST['post_ID']) : NULL;
        $box_id_key = current(preg_grep("/_box_id$/", array_keys($_POST)));
        $box_id = isset($_POST[$box_id_key]) ? intval($_POST[$box_id_key]) : NULL;
        if (empty($post_id) || empty($box_id) || 
            !$this->validate_post('iwpage_' . $post_id, 'iwpage', 'edit_post', true, $post_id)) die('fail');
        $this->ajax_get_post_select_menus($post_id, $box_id);
    }
    
    function post_cdf_form($post) {
        $keys = $this->get_custom_fields();
        $custom_data = get_post_custom($post->ID);
        $fields = array();
        foreach ($keys as $field):
            $key = 'intelliwidget_' . $field;
            $fields[$key] = empty($custom_data[$key]) ? '' : $custom_data[$key][0];
        endforeach;
?>
<p>
  <label title="<?php echo $this->get_tip('event_date'); ?>" for="intelliwidget_event_date">
    <?php echo $this->get_label('event_date');?>
    : <a href="#edit_timestamp" id="intelliwidget_event_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js">
    <?php _e('Edit', 'intelliwidget') ?>
    </a> <span id="intelliwidget_event_date_timestamp" class="intelliwidget-timestamp"> <?php echo $fields['intelliwidget_event_date'] ?></span></label>
  <input type="hidden" class="intelliwidget-input" id="intelliwidget_event_date" name="intelliwidget_event_date" value="<?php echo $fields['intelliwidget_event_date'] ?>" autocomplete="off" />
<div id="intelliwidget_event_date_div" class="intelliwidget-timestamp-div hide-if-js">
  <?php $this->timestamp('intelliwidget_event_date', $fields['intelliwidget_event_date']); ?>
</div>
</p>
<p>
  <label title="<?php echo $this->get_tip('expire_date'); ?>" for="intelliwidget_expire_date">
    <?php echo $this->get_label('expire_date');?>
    : <a href="#edit_timestamp" id="intelliwidget_expire_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js">
    <?php _e('Edit', 'intelliwidget') ?>
    </a> <span id="intelliwidget_expire_date_timestamp" class="intelliwidget-timestamp"> <?php echo $fields['intelliwidget_expire_date']; ?></span></label>
  <input type="hidden" class="intelliwidget-input" id="intelliwidget_expire_date" name="intelliwidget_expire_date" value="<?php echo $fields['intelliwidget_expire_date'] ?>" autocomplete="off" />
<div id="intelliwidget_expire_date_div" class="intelliwidget-timestamp-div hide-if-js">
  <?php $this->timestamp('intelliwidget_expire_date', $fields['intelliwidget_expire_date']); ?>
</div>
</p>
<p>
  <label title="<?php echo $this->get_tip('alt_title');?>" for="intelliwidget_alt_title">
    <?php echo $this->get_label('alt_title');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_alt_title" name="intelliwidget_alt_title" value="<?php echo $fields['intelliwidget_alt_title'] ?>" autocomplete="off" />
</p>
<p>
  <label title="<?php echo $this->get_tip('external_url');?>" for="intelliwidget_external_url">
    <?php echo $this->get_label('external_url');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_external_url" name="intelliwidget_external_url" value="<?php echo $fields['intelliwidget_external_url'] ?>" autocomplete="off" />
</p>
<p>
  <label title="<?php echo $this->get_tip('link_classes');?>" for="intelliwidget_link_classes">
    <?php echo $this->get_label('link_classes');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_link_classes" name="intelliwidget_link_classes" value="<?php echo $fields['intelliwidget_link_classes'] ?>" autocomplete="off" />
</p>
<p>
  <label title="<?php echo $this->get_tip('link_target');?>" for="intelliwidget_link_target">
    <?php echo $this->get_label('link_target');?>
    :</label>
  <select class="intelliwidget-input" id="intelliwidget_link_target" name="intelliwidget_link_target" autocomplete="off" >
    <?php foreach ($this->get_link_target_menu() as $value => $label): ?>
    <option value="<?php echo $value; ?>" <?php selected($fields['intelliwidget_link_target'], $value); ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
  </select>
</p>
<div class="iw-cdf-container">
  <input name="save" class="iw-cdfsave button button-large" id="iw_cdfsave" value="<?php _e('Save Custom Fields', 'intelliwidget');?>" type="button" style="float:right" />
  <span class="spinner" id="intelliwidget_cpt_spinner"></span> </div>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
<?php
    }
    /**
     * Display timestamp edit fields for IntelliWidget
     *
     * @param <string> $field
     * @param <string> $post_date
     */
    function timestamp($field = 'intelliwidget_event_date', $post_date = null) {
        global $wp_locale;

        $time_adj = current_time('timestamp');
        $jj = ($post_date) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
        $mm = ($post_date) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
        $aa = ($post_date) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
        $hh = ($post_date) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
        $mn = ($post_date) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
        $ss = ($post_date) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

        $cur_jj = gmdate( 'd', $time_adj );
        $cur_mm = gmdate( 'm', $time_adj );
        $cur_aa = gmdate( 'Y', $time_adj );
        $cur_hh = gmdate( 'H', $time_adj );
        $cur_mn = gmdate( 'i', $time_adj );

        $month = '<select id="'.$field.'_mm" name="'.$field.'_mm" class="intelliwidget-mm">' ."\n";
        for ( $i = 1; $i < 13; $i = $i +1 ) {
            $monthnum = zeroise($i, 2);
            $month .= "            " . '<option value="' . $monthnum . '"';
            if ( $i == $mm )
                $month .= ' selected="selected"';
                /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
            $month .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
        }
        $month .= '</select>';

        $day = '<input type="text" id="'.$field.'_jj" class="intelliwidget-jj" name="'.$field.'_jj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
        $year = '<input type="text" id="'.$field.'_aa" class="intelliwidget-aa" name="'.$field.'_aa" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';
        $hour = '<input type="text" id="'.$field.'_hh" class="intelliwidget-hh" name="'.$field.'_hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';
        $minute = '<input type="text" id="'.$field.'_mn" class="intelliwidget-mn" name="'.$field.'_mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

        echo '<div class="timestamp-wrap">';
        /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
        printf(__('%1$s%2$s, %3$s @ %4$s : %5$s', 'intelliwidget'), $month, $day, $year, $hour, $minute);

        echo '</div><input type="hidden" id="'.$field.'_ss" name="'.$field.'_ss" value="' . $ss . '" />';

        echo "\n\n";
        foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) {
            echo '<input type="hidden" id="'.$field.'_hidden_' . $timeunit . '" name="'.$field.'_hidden_' . $timeunit . '" value="' . (($post_date) ? $$timeunit : '') . '" />' . "\n";
            $cur_timeunit = 'cur_' . $timeunit;
            echo '<input type="hidden" id="'. $field . '_' . $cur_timeunit . '" name="'. $field . '_' . $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
        }
?>
<p> <a href="#edit_timestamp" id="<?php echo $field; ?>-save" class="intelliwidget-save-timestamp hide-if-no-js button">
  <?php _e('OK', 'intelliwidget'); ?>
  </a> <a href="#edit_timestamp" id="<?php echo $field; ?>-clear" class="intelliwidget-clear-timestamp hide-if-no-js button">
  <?php _e('Clear', 'intelliwidget'); ?>
  </a> <a href="#edit_timestamp" id="<?php echo $field; ?>-cancel" class="intelliwidget-cancel-timestamp hide-if-no-js">
  <?php _e('Cancel', 'intelliwidget'); ?>
  </a> </p>
<?php
    }
    
}
