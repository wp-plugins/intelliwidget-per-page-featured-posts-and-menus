<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * page-form.php - Outputs IntelliWidget Meta Box
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */

?>
<?php echo $this->docsLink; ?>
<p>
  <label for="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <?php _e('Use settings from:', 'intelliwidget'); ?>
  </label>
  <select style="width:100%" name="<?php echo 'intelliwidget_widget_page_id'; ?>" id="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <option value="">This Page</option>
    <?php echo $this->get_pages(array('post_types' => array('page'), 'page' => array($widget_page_id))); ?>
  </select>
</p>
<div class="iw-copy-container">
  <input name="save" class="iw-copy button button-primary button-large" id="iw_copy" value="Save" type="button" style="float:right" />
  <span class="spinner" id="intelliwidget_spinner"></span> </div>
<a style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwadd=1&post=' . $post->ID), 'iwadd'); ?>" id="iw_add" class="iw-add">Add New Section</a>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
