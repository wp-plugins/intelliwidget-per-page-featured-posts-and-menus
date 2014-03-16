<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-metabox.php - Outputs meta box form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
class IntelliWidgetMetaBox {
    function __construct() {
        add_action('intelliwidget_metabox_nav_menu',    array($this, 'nav_menu'), 1, 3);
        add_action('intelliwidget_metabox_all_before',  array($this, 'general_settings'), 2, 3);
        add_action('intelliwidget_metabox_post_list',   array($this, 'post_selection_settings'), 10, 3);
        add_action('intelliwidget_metabox_post_list',   array($this, 'appearance_settings'), 5, 3);
        add_action('intelliwidget_metabox_all_after',   array($this, 'addl_text_settings'), 10, 3);
    }

    function page_form($post) {
        
        global $intelliwidget;
        $widget_page_id = get_post_meta($post->ID, '_intelliwidget_widget_page_id', true);
        echo $intelliwidget->docsLink; ?>

<p>
  <label title="<?php echo $intelliwidget->get_tip('widget_page_id');?>" for="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <?php echo $intelliwidget->get_label('widget_page_id'); ?>
    : </label>
  <input name="save" class="iw-copy button button-large" id="iw_copy" value="<?php _e('Save', 'intelliwidget'); ?>" type="button" style="max-width:24%;float:right;clear:both;margin-top:4px" />
  <select style="width:75%" name="<?php echo 'intelliwidget_widget_page_id'; ?>" id="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <option value=""> <?php printf(__('This %s', 'intelliwidget'), ucfirst($post->post_type)); ?> </option>
    <?php echo $intelliwidget->get_posts_list(array('post_types' => array($post->post_type), 'page' => array($widget_page_id))); ?>
  </select>
</p>
<div class="iw-copy-container"> <span class="spinner" id="intelliwidget_spinner"></span> </div>
<a title="<?php echo $intelliwidget->get_tip('iw_add'); ?>" style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwadd=1&post=' . $post->ID), 'iwadd'); ?>" id="iw_add" class="iw-add">
<?php echo $intelliwidget->get_label('iw_add'); ?>
</a>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
<?php
    }
    
    function post_form($post) {
        global $intelliwidget;
        $keys = $intelliwidget->get_custom_fields();
        $custom_data = get_post_custom($post->ID);
        $fields = array();
        foreach ($keys as $field):
            $key = 'intelliwidget_' . $field;
            $fields[$key] = empty($custom_data[$key]) ? '' : $custom_data[$key][0];
        endforeach;
?>
<p>
  <label title="<?php echo $intelliwidget->get_tip('event_date'); ?>" for="intelliwidget_event_date">
    <?php echo $intelliwidget->get_label('event_date');?>
    : <a href="#edit_timestamp" id="intelliwidget_event_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js">
    <?php _e('Edit', 'intelliwidget') ?>
    </a> <span id="intelliwidget_event_date_timestamp" class="intelliwidget-timestamp"> <?php echo $fields['intelliwidget_event_date'] ?></span></label>
  <input type="hidden" class="intelliwidget-input" id="intelliwidget_event_date" name="intelliwidget_event_date" value="<?php echo $fields['intelliwidget_event_date'] ?>" />
<div id="intelliwidget_event_date_div" class="intelliwidget-timestamp-div hide-if-js">
  <?php $this->timestamp('intelliwidget_event_date', $fields['intelliwidget_event_date']); ?>
</div>
</p>
<p>
  <label title="<?php echo $intelliwidget->get_tip('expire_date'); ?>" for="intelliwidget_expire_date">
    <?php echo $intelliwidget->get_label('expire_date');?>
    : <a href="#edit_timestamp" id="intelliwidget_expire_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js">
    <?php _e('Edit', 'intelliwidget') ?>
    </a> <span id="intelliwidget_expire_date_timestamp" class="intelliwidget-timestamp"> <?php echo $fields['intelliwidget_expire_date']; ?></span></label>
  <input type="hidden" class="intelliwidget-input" id="intelliwidget_expire_date" name="intelliwidget_expire_date" value="<?php echo $fields['intelliwidget_expire_date'] ?>" />
<div id="intelliwidget_expire_date_div" class="intelliwidget-timestamp-div hide-if-js">
  <?php $this->timestamp('intelliwidget_expire_date', $fields['intelliwidget_expire_date']); ?>
</div>
</p>
<p>
  <label title="<?php echo $intelliwidget->get_tip('alt_title');?>" for="intelliwidget_alt_title">
    <?php echo $intelliwidget->get_label('alt_title');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_alt_title" name="intelliwidget_alt_title" value="<?php echo $fields['intelliwidget_alt_title'] ?>" />
</p>
<p>
  <label title="<?php echo $intelliwidget->get_tip('external_url');?>" for="intelliwidget_external_url">
    <?php echo $intelliwidget->get_label('external_url');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_external_url" name="intelliwidget_external_url" value="<?php echo $fields['intelliwidget_external_url'] ?>" />
</p>
<p>
  <label title="<?php echo $intelliwidget->get_tip('link_classes');?>" for="intelliwidget_link_classes">
    <?php echo $intelliwidget->get_label('link_classes');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_link_classes" name="intelliwidget_link_classes" value="<?php echo $fields['intelliwidget_link_classes'] ?>" />
</p>
<p>
  <label title="<?php echo $intelliwidget->get_tip('link_target');?>" for="intelliwidget_link_target">
    <?php echo $intelliwidget->get_label('link_target');?>
    :</label>
  <select class="intelliwidget-input" id="intelliwidget_link_target" name="intelliwidget_link_target">
    <?php foreach ($intelliwidget->get_link_target_menu() as $value => $label): ?>
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

    function metabox($box_id, $post_id, $instance) {
        global $intelliwidget;
        ?>
        <p>
  <input type="hidden" id="<?php echo 'intelliwidget_' . $box_id . '_category'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_category'; ?>" value="-1" />Original Categories: <?php echo implode(',', $intelliwidget->val2array($instance['category'])); ?>
        </p>
<p>
  <input type="hidden" id="<?php echo 'intelliwidget_' . $box_id . '_box_id'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_box_id'; ?>" value="<?php echo $box_id; ?>" />
  <label title="<?php echo $intelliwidget->get_tip('replace_widget'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>">
    <?php echo $intelliwidget->get_label('replace_widget'); ?>
    : </label>
  <select name="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>">
    <?php foreach ($intelliwidget->intelliwidgets as $value => $label): ?>
    <option value="<?php echo $value; ?>" <?php selected($instance['replace_widget'], $value); ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
  </select>
</p>
<?php
    // execute custom action hook for content value if it exists
    do_action('intelliwidget_metabox_all_before', $box_id, $post_id, $instance);
    do_action('intelliwidget_metabox_' . $instance['content'], $box_id, $post_id, $instance);
    do_action('intelliwidget_metabox_all_after', $box_id, $post_id, $instance); 
?>
<div class="iw-save-container">
  <input name="save" class="button button-large iw-save" id="<?php echo 'intelliwidget_' . $box_id . '_save'; ?>" value="<?php _e('Save Settings', 'intelliwidget'); ?>" type="button" style="float:right" autocomplete="off" />
  <span class="spinner <?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span> </div>
<a style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwdelete='.$box_id.'&post=' . $post_id), 'iwdelete'); ?>" id="iw_delete_<?php echo $box_id; ?>" class="iw-delete">
<?php _e('Delete', 'intelliwidget'); ?>
</a>
<div style="clear:both"></div>
<?php
    }
    
    function general_settings($box_id, $post_id, $instance) {
        global $intelliwidget;
        ?>
<div id="iw-generalsettings-<?php echo $box_id; ?>" class="postbox closed panel-general">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget->get_tip('generalsettings'); ?>"><span>
    <?php echo $intelliwidget->get_label('generalsettings'); ?>
    </span></h3>
  <div  id="iw-generalsettings-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label title="<?php echo $intelliwidget->get_tip('content'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>">
        <?php echo $intelliwidget->get_label('content'); ?>
        : </label>
      <select class="iw-control" id="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>" autocomplete="off">
        <?php foreach ($intelliwidget->get_content_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['content'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select> <span class="spinner <?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('title'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>"> <?php echo $intelliwidget->get_label('title'); ?> </label>
      <label title="<?php echo $intelliwidget->get_tip('link_title'); ?>">
        <input name="<?php echo 'intelliwidget_' . $box_id . '_link_title'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_link_title'; ?>" type="checkbox" <?php checked($instance['link_title'], 1); ?> value="1" />
        <?php echo $intelliwidget->get_label('link_title'); ?>
      </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_label('container_id'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" style="display:block">
        <?php echo $intelliwidget->get_label('container_id'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" type="text" value="<?php echo esc_attr($instance['container_id']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('classes'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" style="display:block">
        <?php echo $intelliwidget->get_label('classes'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" type="text" value="<?php echo esc_attr($instance['classes']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('nocopy'); ?>">
        <input name="<?php echo 'intelliwidget_' . $box_id . '_nocopy'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_nocopy'; ?>" type="checkbox" <?php checked($instance['nocopy'], 1); ?> value="1"/>
        <?php echo $intelliwidget->get_label('nocopy'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }
    
    function addl_text_settings($box_id, $post_id, $instance) {
        global $intelliwidget;
        ?>
<div id="iw-addltext-<?php echo $box_id; ?>" class="postbox closed panel-addltext">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget->get_tip('addltext'); ?>"><span>
    <?php echo $intelliwidget->get_label('addltext'); ?>
    </span></h3>
  <div  id="iw-addltext-<?php echo $box_id; ?>-inside" class="inside">
    <label title="<?php echo $intelliwidget->get_tip('text_position'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>">
      <?php echo $intelliwidget->get_label('text_position'); ?>
      : </label>
    <select name="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>">
      <?php foreach ($intelliwidget->get_text_position_menu() as $value => $label): ?>
      <option value="<?php echo $value; ?>" <?php selected($instance['text_position'], $value); ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
    </select>
    <textarea class="widefat" rows="3" cols="20" id="<?php echo 'intelliwidget_' . $box_id . '_custom_text'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_custom_text'; ?>">
<?php echo esc_textarea($instance['custom_text']); ?></textarea>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('filter'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>" type="checkbox" <?php checked($instance['filter'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget->get_label('filter'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }
    
    function appearance_settings($box_id, $post_id, $instance) {
        global $intelliwidget, $_wp_additional_image_sizes;
        ?>
<div id="iw-appearance-<?php echo $box_id; ?>" class="postbox closed panel-appearance">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget->get_tip('appearance'); ?>"><span>
    <?php echo $intelliwidget->get_label('appearance'); ?>
    </span></h3>
  <div id="iw-appearance-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label title="<?php echo $intelliwidget->get_tip('template'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>">
        <?php echo $intelliwidget->get_label('template'); ?>
        :</label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>">
        <?php foreach ( $intelliwidget->templates as $template => $name ) : ?>
        <option value="<?php echo $template; ?>" <?php selected($instance['template'], $template); ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('sortby'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>">
        <?php echo $intelliwidget->get_label('sortby'); ?>
        : </label>
      <br/>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>">
        <?php foreach ($intelliwidget->get_sortby_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['sortby'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_sortorder'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_sortorder'; ?>">
        <option value="ASC"<?php selected( $instance['sortorder'], 'ASC' ); ?>>
        <?php _e('ASC', 'intelliwidget'); ?>
        </option>
        <option value="DESC"<?php selected( $instance['sortorder'], 'DESC' ); ?>>
        <?php _e('DESC', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('items'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>">
        <?php echo $intelliwidget->get_label('items'); ?>
        : </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>" size="3" type="text" value="<?php echo esc_attr($instance['items']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('length'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>">
        <?php echo $intelliwidget->get_label('length'); ?>
        : </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('allowed_tags'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>">
        <?php echo $intelliwidget->get_label('allowed_tags'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>" type="text" value="<?php echo esc_attr($instance['allowed_tags']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('link_text'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>">
        <?php echo $intelliwidget->get_label('link_text'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('imagealign'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>">
        <?php echo $intelliwidget->get_label('imagealign'); ?>
      </label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>">
        <?php foreach ($intelliwidget->get_imagealign_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['imagealign'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('image_size'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_image_size'; ?>">
        <?php echo $intelliwidget->get_label('image_size'); ?>
        : </label>
      <select id="<?php echo 'intelliwidget_' . $box_id . '_image_size'; ?>" name="<?php  echo 'intelliwidget_' . $box_id . '_image_size'; ?>">
        <?php foreach ($intelliwidget->get_image_size_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['image_size'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
        <?php if (is_array($_wp_additional_image_sizes)): foreach ( $_wp_additional_image_sizes as $name => $size ) : ?>
        <option value="<?php echo $name; ?>" <?php selected($instance['image_size'], $name); ?> ><?php echo $name; ?> (<?php echo $size['width']; ?>x<?php echo $size['height']; ?>px)</option>
        <?php endforeach; endif; ?>
      </select>
    </p>
  </div>
</div>
<?php
    }
    
    function post_selection_settings($box_id, $post_id, $instance) {
        global $intelliwidget;
        ?>
<div id="iw-selection-<?php echo $box_id; ?>" class="postbox closed panel-selection">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget->get_tip('selection'); ?>"><span>
    <?php echo $intelliwidget->get_label('selection'); ?>
    </span></h3>
  <div id="iw-selection-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label title="<?php echo $intelliwidget->get_tip('post_types'); ?>" style="display:block;margin-bottom:10px"><span class="spinner <?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span>
        <?php echo $intelliwidget->get_label('post_types'); ?>
        :</label>
      <?php foreach ( $intelliwidget->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo 'intelliwidget_' . $box_id . '_post_types_' . $type; ?>">
        <input class="iw-control" id="<?php echo 'intelliwidget_' . $box_id . '_post_types_' . $type; ?>" type="checkbox" name="<?php echo 'intelliwidget_' . $box_id . '_post_types[]'; ?>" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
    <div id="<?php echo 'intelliwidget_' . $box_id . '_menus'; ?>">
<?php  
        /*
         * this has been moved to its own method: post_selection_menus()
         */
         
         do_action('intelliwidget_post_selection_menus', $box_id, $post_id, $instance);
?>
    </div>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('skip_post'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>" type="checkbox" <?php checked($instance['skip_post'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget->get_label('skip_post'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('future_only'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>" type="checkbox" <?php checked($instance['future_only'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget->get_label('future_only'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('active_only'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>" type="checkbox" <?php checked($instance['active_only'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget->get_label('active_only'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('skip_expired'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>" type="checkbox" <?php checked($instance['skip_expired'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget->get_label('skip_expired'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }

    function post_selection_menus($box_id, $post_id, $instance) {
        global $intelliwidget;
?>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('taxonomies'); ?>">
        <?php echo $intelliwidget->get_label('taxonomies'); ?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo 'intelliwidget_' . $box_id . '_taxonomies'; ?>[]" size="1" multiple="multiple" id="<?php echo 'intelliwidget_' . $box_id . '_taxonomies'; ?>">
        <?php echo $intelliwidget->get_relevant_terms($instance); ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget->get_tip('page'); ?>">
        <?php echo $intelliwidget->get_label('page'); ?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo 'intelliwidget_' . $box_id . '_page'; ?>[]" size="1" multiple="multiple" id="<?php echo 'intelliwidget_' . $box_id . '_page'; ?>">
        <?php echo $intelliwidget->get_posts_list($instance); ?>
      </select>
    </p>

<?php
    }
    
    function nav_menu($box_id, $post_id, $instance){
        global $intelliwidget;
        ?>
<p>
  <label title="<?php echo $intelliwidget->get_tip('nav_menu'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>">
    <?php echo $intelliwidget->get_label('nav_menu'); ?>
    : </label>
  <select id="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>">
        <?php   // Get menus
            foreach ( $intelliwidget->get_nav_menu() as $value => $label ): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['nav_menu'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
  </select>
</p>
<?php
    }
}

