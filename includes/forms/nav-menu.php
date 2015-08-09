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
?>
<p>
  <label title="<?php echo $adminobj->get_tip( 'nav_menu' );?>" for="<?php echo $widgetobj->get_field_id( 'nav_menu' ); ?>">
    <?php echo $adminobj->get_label( 'nav_menu' ); ?>
    : </label>
  <select id="<?php echo $widgetobj->get_field_id( 'nav_menu' ); ?>" name="<?php echo $widgetobj->get_field_name( 'nav_menu' ); ?>">
            <?php
            // Get menus
            foreach ( $adminobj->get_nav_menu() as $value => $label ): ?>
        <option value="<?php echo $value; ?>" <?php selected( $instance[ 'nav_menu' ], $value ); ?>><?php echo $label; ?></option>
        <?php endforeach;?>
  </select>
</p>
