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
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
 ?>

<ul class="intelliwidget-menu">
  <?php if ( $selected->have_posts() ) : while ($selected->have_posts()) : $selected->the_post();?>
  <li id="intelliwidget_<?php the_id(); ?>" class="intelliwidget-menu-item">
    <?php if ( has_intelliwidget_image() ) : ?>
    <div class="intelliwidget-image-container-<?php echo $instance['image_size'];?> intelliwidget-align-<?php echo $instance['imagealign']; ?>">
      <?php the_intelliwidget_image(); ?>
    </div>
    <?php endif; ?>
    <?php the_intelliwidget_link(); ?>
  </li>
  <?php endwhile; endif; ?>
</ul>
