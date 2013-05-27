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
<?php global $iwgt_post;
if ( !empty($selected)) : foreach($selected as $iwgt_post) : ?>
  <li id="intelliwidget_<?php the_intelliwidget_ID(); ?>" class="slide">
    <?php the_intelliwidget_content(); ?>
  </li>
  <?php endforeach; endif; ?>
</ul>
