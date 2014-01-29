<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * post-form.php - Outputs Custom Data Fields form and admin styles
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
?>
<style>
.iw-save-container, .iw-copy-container, .iw-cdf-container {
    position: relative;
    float: right;
}
.iw-save-container.success:before, .iw-copy-container.success:before, .iw-cdf-container.success:before {
    content: "";
    display: block;
    position: absolute;
    height: 16px;
    width: 16px;
    top: 8px;
    left: -26px;
    background:url(<?php echo admin_url( 'images/yes.png' ); ?>) no-repeat;
}
input.iw-save.failure:before, input.iw-copy.failure:before {
    content: "";
    display: block;
    position: absolute;
    height: 16px;
    width: 16px;
    top: 8px;
    left: -26px;
    background:url(<?php echo admin_url( 'images/no.png') ; ?>) no-repeat;
}
.intelliwidget-timestamp-div select {
    height: 20px;
    line-height: 14px;
    padding: 0;
    vertical-align: top
}
.intelliwidget-aa, .intelliwidget-jj, .intelliwidget-hh, .intelliwidget-mn {
    padding: 1px;
    font-size: 12px
}
.intelliwidget-jj, .intelliwidget-hh, .intelliwidget-mn {
    width: 2em
}
.intelliwidget-aa {
    width: 3.4em
}
.intelliwidget-timestamp-div {
    position:relative;
    padding-top: 5px;
    line-height: 23px
}
.intelliwidget-timestamp-div p {
    margin: 8px 0 6px
}
.intelliwidget-timestamp-div input {
    border-width: 1px;
    border-style: solid
}
input.intelliwidget-input, select.intelliwidget-input {
    float:right;
    width:65%;
}
.intelliwidget-edit-timestamp, .intelliwidget-timestamp {
    float:right;
    margin-left:5%;
}
    
</style>
<?php 
        $keys = array(
            'intelliwidget_event_date',
            'intelliwidget_expire_date',
            'intelliwidget_link_classes',
            'intelliwidget_link_target',
            'intelliwidget_alt_title',
            'intelliwidget_external_url',
        );
        $custom_data = get_post_custom($post->ID);
        $fields = array();
        foreach ($keys as $key):
            $fields[$key] = '';
            if (!empty($custom_data[$key]))
                $fields[$key] = $custom_data[$key][0];
        endforeach;
?>
<p>    <label for="intelliwidget_event_date"><?php _e('Start Date', 'intelliwidget');?>:
    <a href="#edit_timestamp" id="intelliwidget_event_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js"><?php _e('Edit', 'intelliwidget') ?></a>
<span id="intelliwidget_event_date_timestamp" class="intelliwidget-timestamp">
    <?php echo $fields['intelliwidget_event_date'] ?></span></label>
    <input type="hidden" class="intelliwidget-input" id="intelliwidget_event_date" name="intelliwidget_event_date" value="<?php echo $fields['intelliwidget_event_date'] ?>" />
    <div id="intelliwidget_event_date_div" class="intelliwidget-timestamp-div hide-if-js"><?php intelliwidget_timestamp('intelliwidget_event_date', $fields['intelliwidget_event_date']); ?></div></p>
<p>    <label for="intelliwidget_expire_date"><?php _e('Expire Date', 'intelliwidget');?>:
    <a href="#edit_timestamp" id="intelliwidget_expire_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js"><?php _e('Edit', 'intelliwidget') ?></a>
<span id="intelliwidget_expire_date_timestamp" class="intelliwidget-timestamp">
    <?php echo $fields['intelliwidget_expire_date']; ?></span></label>
    <input type="hidden" class="intelliwidget-input" id="intelliwidget_expire_date" name="intelliwidget_expire_date" value="<?php echo $fields['intelliwidget_expire_date'] ?>" />
    <div id="intelliwidget_expire_date_div" class="intelliwidget-timestamp-div hide-if-js"><?php intelliwidget_timestamp('intelliwidget_expire_date', $fields['intelliwidget_expire_date']); ?></div>
</p>
<p>    <label for="intelliwidget_alt_title"><?php _e('Alt Title', 'intelliwidget');?>:</label>
    <input class="intelliwidget-input" type="text" id="intelliwidget_alt_title" name="intelliwidget_alt_title" value="<?php echo $fields['intelliwidget_alt_title'] ?>" />
</p>
<p>    <label for="intelliwidget_external_url"><?php _e('External URL', 'intelliwidget');?>:</label>
    <input class="intelliwidget-input" type="text" id="intelliwidget_external_url" name="intelliwidget_external_url" value="<?php echo $fields['intelliwidget_external_url'] ?>" />
</p>
<p>    <label for="intelliwidget_link_classes"><?php _e('Link Classes', 'intelliwidget');?>:</label>
    <input class="intelliwidget-input" type="text" id="intelliwidget_link_classes" name="intelliwidget_link_classes" value="<?php echo $fields['intelliwidget_link_classes'] ?>" />
</p>
<p>    <label for="intelliwidget_link_target"><?php _e('Link Target', 'intelliwidget');?>:</label>
    <select class="intelliwidget-input" id="intelliwidget_link_target" name="intelliwidget_link_target">
      <option value=""<?php selected( $fields['intelliwidget_link_target'], '' ); ?>>None</option>
      <option value="_new"<?php selected( $fields['intelliwidget_link_target'], '_new' ); ?>>_new</option>
      <option value="_blank"<?php selected( $fields['intelliwidget_link_target'], '_blank' ); ?>>_blank</option>
      <option value="_self"<?php selected( $fields['intelliwidget_link_target'], '_self' ); ?>>_self</option>
      <option value="_top"<?php selected( $fields['intelliwidget_link_target'], '_top' ); ?>>_top</option>
    </select>
</p>
<div class="iw-cdf-container">
  <input name="save" class="iw-cdfsave button button-primary button-large" id="iw_cdfsave" value="<?php _e('Save', 'intelliwidget');?>" type="button" style="float:right" />
  <span class="spinner" id="intelliwidget_cpt_spinner"></span> </div>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
<?php

/**
 * Display timestamp edit fields for IntelliWidget
 *
 * @param <string> $field
 * @param <string> $post_date
 */
function intelliwidget_timestamp($field = 'intelliwidget_event_date', $post_date = null) {
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

<p>
<a href="#edit_timestamp" id="<?php echo $field; ?>-save" class="intelliwidget-save-timestamp hide-if-no-js button"><?php _e('OK', 'intelliwidget'); ?></a>
<a href="#edit_timestamp" id="<?php echo $field; ?>-clear" class="intelliwidget-clear-timestamp hide-if-no-js button"><?php _e('Clear', 'intelliwidget'); ?></a>
<a href="#edit_timestamp" id="<?php echo $field; ?>-cancel" class="intelliwidget-cancel-timestamp hide-if-no-js"><?php _e('Cancel', 'intelliwidget'); ?></a>
</p>
<?php
}
?>
