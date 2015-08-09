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
// convert legacy category to taxonomies
if ( empty( $instance[ 'terms' ] ) && isset( $instance[ 'category' ] ) && '-1' != $instance[ 'category' ] )
    $instance[ 'terms' ] = $adminobj->map_category_to_tax( $instance[ 'category' ] );
       
?>
<input type="hidden" name="<?php echo $widgetobj->get_field_name( 'page_multi' ); ?>" id="<?php echo $widgetobj->get_field_id( 'page_multi' ); ?>" value="1" />
<input type="hidden" name="<?php echo $widgetobj->get_field_name( 'terms_multi' ); ?>" id="<?php echo $widgetobj->get_field_id( 'terms_multi' ); ?>" value="1" />
    <p>
      <label title="<?php echo $adminobj->get_tip( 'page' );?>">
        <?php echo $adminobj->get_label( 'page' );?>
        :</label>
      <select  class="widefat intelliwidget-multiselect" name="<?php echo $widgetobj->get_field_name( 'page' ); ?>[]"  multiple="multiple" id="<?php echo $widgetobj->get_field_id( 'page' ); ?>">
        <?php echo $adminobj->get_menu_list( $instance, 'posts', FALSE, 0, 200 ); ?>
      </select>
    </p> 
    <p>
      <label title="<?php echo $adminobj->get_tip( 'terms' );?>">
        <?php echo $adminobj->get_label( 'terms' );?>
      </label>
      <select name="<?php echo $widgetobj->get_field_name( 'allterms' ); ?>" id="<?php echo $widgetobj->get_field_id( 'allterms' ); ?>">
        <option value="0"<?php if ( isset( $instance[ 'allterms' ] ) ) selected( $instance[ 'allterms' ], 0 ); ?>>
        <?php _e( 'any', 'intelliwidget' ); ?>
        </option>
        <option value="1"<?php if ( isset( $instance[ 'allterms' ] ) ) selected( $instance[ 'allterms' ], 1 ); ?>>
        <?php _e( 'all', 'intelliwidget' ); ?>
        </option>
      </select>
      <label title="<?php echo $adminobj->get_tip( 'allterms' ); ?>">
        <?php echo $adminobj->get_label( 'allterms' ); ?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo $widgetobj->get_field_name( 'terms' ); ?>[]" size="1" multiple="multiple" id="<?php echo $widgetobj->get_field_id( 'terms' ); ?>">
        <?php echo $adminobj->get_menu_list( $instance, 'terms', FALSE, 0, 200 ); ?>
      </select>
    </p>