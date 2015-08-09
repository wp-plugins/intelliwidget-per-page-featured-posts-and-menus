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
 */
$this->section_header( $adminobj, $widgetobj, 'generalsettings', $is_widget );
?>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'content' );?>" for="<?php echo $widgetobj->get_field_id( 'content' ); ?>">
        <?php echo $adminobj->get_label( 'content' ) ?>
        : </label>
      <select class="iw<?php echo $is_widget? '-widget' : ''; ?>-control" id="<?php echo $widgetobj->get_field_id( 'content' ); ?>" name="<?php echo $widgetobj->get_field_name( 'content' ); ?>" autocomplete="off">
        <?php foreach ( $adminobj->get_content_menu() as $value => $label ): ?>
        <option value="<?php echo $value; ?>" <?php selected( $instance[ 'content' ], $value ); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select><?php if ( !$is_widget ): ?><span class="spinner <?php echo $widgetobj->get_field_id( 'spinner' ); ?>"></span><?php endif; ?>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'title' );?>" for="<?php echo $widgetobj->get_field_id( 'title' ); ?>"> <?php echo $adminobj->get_label( 'title' ); ?>: </label>
      <label title="<?php echo $adminobj->get_tip( 'link_title' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'link_title' ); ?>" id="<?php echo $widgetobj->get_field_id( 'link_title' ); ?>" type="checkbox" <?php checked( $instance[ 'link_title' ], 1 ); ?> value="1" />
        <?php echo $adminobj->get_label( 'link_title' ); ?>
      </label>
      <label title="<?php echo $adminobj->get_tip( 'hide_title' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'hide_title' ); ?>" id="<?php echo $widgetobj->get_field_id( 'hide_title' ); ?>" type="checkbox" <?php checked( $instance[ 'hide_title' ], 1 ); ?> value="1" />
        <?php echo $adminobj->get_label( 'hide_title' ); ?>
      </label>
      <br/>
      <input id="<?php echo $widgetobj->get_field_id( 'title' ); ?>" name="<?php echo $widgetobj->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $instance[ 'title' ] ); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'container_id' );?>" for="<?php echo $widgetobj->get_field_id( 'container_id' ); ?>">
        <?php echo $adminobj->get_label( 'container_id' ); ?>
        : </label>
      <input name="<?php echo $widgetobj->get_field_name( 'container_id' ); ?>" id="<?php echo $widgetobj->get_field_id( 'container_id' ); ?>" type="text" value="<?php echo esc_attr( $instance[ 'container_id' ] ); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'classes' );?>" for="<?php echo $widgetobj->get_field_id( 'classes' ); ?>">
        <?php echo $adminobj->get_label( 'classes' ); ?>
        : </label>
      <input name="<?php echo $widgetobj->get_field_name( 'classes' ); ?>" id="<?php echo $widgetobj->get_field_id( 'classes' ); ?>" type="text" value="<?php echo esc_attr( $instance[ 'classes' ] ); ?>" />
    </p>
<?php $this->section_footer();
