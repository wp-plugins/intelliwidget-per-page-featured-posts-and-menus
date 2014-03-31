<?php
if ( !defined('ABSPATH')) exit;
/**
 * menu.php - Template for Custom Page Menus
 *
 * This can be copied to a folder named 'intelliwidget' in your theme
 * to customize the output.
 *
 * @package IntelliWidget
 * @subpackage templates
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
$post_id    = have_posts() ? get_the_ID() : NULL;
$ancestors  = isset($post_id) ? get_post_ancestors($post_id) : array();
$parent     = current($ancestors);
 ?>

<ul class="intelliwidget-menu">
  <?php if ( $selected->have_posts() ) : while ($selected->have_posts()) : $selected->the_post(); 
    $intelliwidget_post_id    = get_the_intelliwidget_ID();
    ?>
  <li id="intelliwidget_<?php $intelliwidget_post_id; ?>" class="intelliwidget-menu-item<?php echo ($post_id == $intelliwidget_post_id ? ' intelliwidget-current-menu-item' : '') . (in_array( $intelliwidget_post_id, $ancestors) ? ' intelliwidget-current-menu-ancestor' : '') . ($intelliwidget_post_id == $parent ? ' intelliwidget-current-menu-parent' : ''); ?>">
    <?php if ( has_intelliwidget_image() ) : ?>
    <div class="intelliwidget-image-container-<?php echo $instance['image_size'];?> intelliwidget-align-<?php echo $instance['imagealign']; ?>">
      <?php the_intelliwidget_image(); ?>
    </div>
    <?php endif; ?>
    <?php the_intelliwidget_link(); ?>
    <div style="clear:both"></div>
  </li>
  <?php endwhile; endif; ?>
</ul>
