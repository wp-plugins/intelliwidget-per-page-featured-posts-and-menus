<?php
if ( !defined('ABSPATH')) exit;
/**
 * slides.php - Template to generate ul li output. Useful for jQuery sliders.
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

<ul class="slides">
  <?php if ( $selected->have_posts() ) : while ($selected->have_posts()) : $selected->the_post(); ?>
  <li id="intelliwidget_<?php the_id(); ?>" class="slide">
    <?php the_content(); ?>
  </li>
  <?php endwhile; endif; ?>
</ul>
