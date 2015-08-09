<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * class-intelliwidget-form.php - Outputs widget form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014-2015 Lilaea Media LLC
 * @access public
 */
echo $obj->docsLink;
?>
<p>
  <label title="<?php echo $obj->get_tip( 'widget_page_id' );?>" for="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <?php echo $obj->get_label( 'widget_page_id' ); ?>
    : </label>  <?php echo $id_list; ?>
  <input name="save" class="iw-copy button button-large" id="iw_copy" value="<?php _e( 'Use', 'intelliwidget' ); ?>" type="button" style="max-width:24%;margin-top:4px" />
</p>




