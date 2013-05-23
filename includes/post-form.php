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
<style>
.iw-save-container, .iw-copy-container, .iw-cptsave-container {
    position: relative;
    float: right;
}
.iw-save-container.success:before, .iw-copy-container.success:before, .iw-cptsave-container.success:before {
    content: "";
    display: block;
    position: absolute;
    height: 16px;
    width: 16px;
    top: 8px;
    left: -26px;
	background:url(<?php echo admin_url( 'images/yes.png' ); ?>) no-repeat;
}
input.iw-save-container.failure:before, input.iw-copy-container.failure:before, .iw-cptsave-container.failure:before {
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
.intelliwidget-timestamp {
    background-repeat: no-repeat;
    background-position: left center;
    padding: 2px 0 1px 20px
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
input[type=text].iw-cpt {
	width:65%;
	float:right;
}
</style>
<p>	<label style="width:34%" for="intelliwidget_event_date"><?php echo __('Event Date', 'intelliwidget') . ': '; ?><span id="intelliwidget_event_date_timestamp" class="intelliwidget-timestamp">
	<?php echo $fields['intelliwidget_event_date'] ?></span></label>
    <input class="iw-cpt" type="hidden" id="intelliwidget_event_date" name="intelliwidget_event_date" value="<?php echo $fields['intelliwidget_event_date'] ?>" />
	<a href="#edit_timestamp" id="intelliwidget_event_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js"><?php _e('Edit') ?></a>
	<div id="intelliwidget_event_date_div" class="intelliwidget-timestamp-div hide-if-js"><?php intelliwidget_timestamp('intelliwidget_event_date', $fields['intelliwidget_event_date']); ?></div></p>
<p>	<label style="width:34%"  for="intelliwidget_expire_date"><?php echo __('Expire Date', 'intelliwidget') . ': '; ?><span id="intelliwidget_expire_date_timestamp" class="intelliwidget-timestamp">
	<?php echo $fields['intelliwidget_expire_date']; ?></span></label>
    <input class="iw-cpt" type="hidden" id="intelliwidget_expire_date" name="intelliwidget_expire_date" value="<?php echo $fields['intelliwidget_expire_date'] ?>" />
	<a href="#edit_timestamp" id="intelliwidget_expire_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js"><?php _e('Edit') ?></a>
	<div id="intelliwidget_expire_date_div" class="intelliwidget-timestamp-div hide-if-js"><?php intelliwidget_timestamp('intelliwidget_expire_date', $fields['intelliwidget_expire_date']); ?></div>
</p>
<p>	<label for="intelliwidget_alt_title"><?php echo __('Alt Title', 'intelliwidget') . ': '; ?></label>
    <input class="iw-cpt" type="text" id="intelliwidget_alt_title" name="intelliwidget_alt_title" value="<?php echo $fields['intelliwidget_alt_title'] ?>" />
</p>
<p>	<label for="intelliwidget_external_url"><?php echo __('External URL', 'intelliwidget') . ': '; ?></label>
    <input class="iw-cpt" type="text" id="intelliwidget_external_url" name="intelliwidget_external_url" value="<?php echo $fields['intelliwidget_external_url'] ?>" />
</p>
<p>	<label for="intelliwidget_link_classes"><?php echo __('Link Classes', 'intelliwidget') . ': '; ?></label>
    <input class="iw-cpt" type="text" id="intelliwidget_link_classes" name="intelliwidget_link_classes" value="<?php echo $fields['intelliwidget_link_classes'] ?>" />
</p>
<p>	<label for="intelliwidget_link_target"><?php echo __('Link Target', 'intelliwidget') . ': '; ?></label>
    <input class="iw-cpt" type="text" id="intelliwidget_link_target" name="intelliwidget_link_target" value="<?php echo $fields['intelliwidget_link_target'] ?>" />
</p>
<div class="iw-cptsave-container">
  <input name="save" class="button button-primary button-large iw-cptsave" id="iw_cptsave" value="Save" type="button" style="float:right">
  <span class="spinner" id="<?php echo 'intelliwidget_cpt_spinner'; ?>"></span> </div>
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
	$cur_jj = gmdate( 'd', $time_adj );
	$cur_mm = gmdate( 'm', $time_adj );
	$cur_aa = gmdate( 'Y', $time_adj );
	$cur_hh = gmdate( 'H', $time_adj );
	$cur_mn = gmdate( 'i', $time_adj );
	$cur_ss = gmdate( 's', $time_adj );
	$prev_jj = null;
	$prev_mm = null;
	$prev_aa = null;
	$prev_hh = null;
	$prev_mn = null;
	$prev_ss = null;

	if ($post_date):
		$jj = $prev_jj = mysql2date( 'd', $post_date, false );
		$mm = $prev_mm = mysql2date( 'm', $post_date, false );
		$aa = $prev_aa = mysql2date( 'Y', $post_date, false );
		$hh = $prev_hh = mysql2date( 'H', $post_date, false );
		$mn = $prev_mn = mysql2date( 'i', $post_date, false );
		$ss = $prev_ss = mysql2date( 's', $post_date, false );
	else:
		$jj = $cur_jj;
		$mm = $cur_mm;
		$aa = $cur_aa;
		$hh = $cur_hh;
		$mn = $cur_mn; 
		$ss = $cur_ss;
	endif;
	$month = '<select id="'.$field.'_mm" name="'.$field.'_mm" class="intelliwidget-mm">' ."\n";
	for ( $i = 1; $i < 13; $i = $i +1 ) {
		$monthnum = zeroise($i, 2);
		$month .= "\t\t\t" . '<option value="' . $monthnum . '"';
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
	printf(__('%1$s%2$s, %3$s @ %4$s : %5$s'), $month, $day, $year, $hour, $minute);

	echo '</div><input type="hidden" id="'.$field.'_ss" name="'.$field.'_ss" value="' . (empty($ss) ? $curr_ss : $ss) . '" />';

	echo "\n\n";
	foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) {
		$cur_timeunit = 'cur_' . $timeunit;
		$prev_timeunit = 'prev_' . $timeunit;
		echo '<input type="hidden" id="'.$field.'_hidden_' . $prev_timeunit . '" name="'.$field.'_hidden_' . $prev_timeunit . '" value="' . $$prev_timeunit . '" />' . "\n";
		echo '<input type="hidden" id="'. $field . '_' . $cur_timeunit . '" name="'. $field . '_' . $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
	}
?>

<p>
<a href="#edit_timestamp" id="<?php echo $field; ?>-save" class="intelliwidget-save-timestamp hide-if-no-js button"><?php _e('OK'); ?></a>
<a href="#edit_timestamp" id="<?php echo $field; ?>-clear" class="intelliwidget-clear-timestamp hide-if-no-js button"><?php _e('Clear'); ?></a>
<a href="#edit_timestamp" id="<?php echo $field; ?>-cancel" class="intelliwidget-cancel-timestamp hide-if-no-js"><?php _e('Cancel'); ?></a>
</p>
<?php
}
?>
