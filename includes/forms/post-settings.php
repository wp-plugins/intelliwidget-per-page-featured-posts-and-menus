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
$this->section_header( $adminobj, $widgetobj, 'selection', $is_widget );
?>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'post_types' );?>" style="display:block">
        <?php echo $adminobj->get_label( 'post_types' ); ?>
        :</label>
      <?php foreach ( $adminobj->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo $widgetobj->get_field_id( 'post_types_' . $type ); ?>">
        <input class="iw<?php echo $is_widget? '-widget' : ''; ?>-control"  type="checkbox" id="<?php echo $widgetobj->get_field_id( 'post_types_' . $type ); ?>" name="<?php echo $widgetobj->get_field_name( 'post_types' ); ?>[]" value="<?php echo $type; ?>" <?php checked( in_array( $type, $instance[ 'post_types' ] ), 1 ); ?> />
        <?php echo ucfirst( $type ); ?></label>
      <?php endforeach; ?>
    </p>
    <div id="<?php echo $widgetobj->get_field_id( 'menus' ); ?>">
<?php  /*
        * this has been moved to its own method: post_selection_menus()
        */
         do_action( 'intelliwidget_post_selection_menus', $adminobj, $widgetobj, $instance );
        
?>
    </div>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'skip_post' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'skip_post' ); ?>" id="<?php echo $widgetobj->get_field_id( 'skip_post' ); ?>" type="checkbox" <?php checked( $instance[ 'skip_post' ], 1 ); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label( 'skip_post' ); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'future_only' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'future_only' ); ?>" id="<?php echo $widgetobj->get_field_id( 'future_only' ); ?>" type="checkbox" <?php checked( $instance[ 'future_only' ], 1 ); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label( 'future_only' ); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'active_only' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'active_only' ); ?>" id="<?php echo $widgetobj->get_field_id( 'active_only' ); ?>" type="checkbox" <?php checked( $instance[ 'active_only' ], 1 ); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label( 'active_only' ); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'skip_expired' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'skip_expired' ); ?>" id="<?php echo $widgetobj->get_field_id( 'skip_expired' ); ?>" type="checkbox" <?php checked( $instance[ 'skip_expired' ], 1 ); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label( 'skip_expired' ); ?>
      </label>
    </p>
<?php if ( current_user_can( 'read_private_posts' ) ): ?>
    <p>
      <label title="<?php echo $adminobj->get_tip( 'include_private' );?>">
        <input name="<?php echo $widgetobj->get_field_name( 'include_private' ); ?>" id="<?php echo $widgetobj->get_field_id( 'include_private' ); ?>" type="checkbox" <?php checked( $instance[ 'include_private' ], 1 ); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label( 'include_private' ); ?>
      </label>
    </p>   
<?php 
    endif; 
    $this->section_footer();
