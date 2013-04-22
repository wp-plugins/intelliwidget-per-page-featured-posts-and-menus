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
  <select style="max-width:100%" name="<?php echo 'intelliwidget_widget_page_id'; ?>" id="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <option value="">This Page</option>
    <?php echo $this->get_pages(array('post_types' => array('page'), 'page' => array($widget_page_id))); ?>
  </select>
</p>
<div id="publishing-action"> <span style="display: none;" class="spinner"></span>
  <input name="save" class="button button-primary button-large" id="publish" accesskey="p" value="Save" type="submit">
</div>
<p><a style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwadd=1&post=' . $post->ID), 'iwadd'); ?>">Add New Section</a></p>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
