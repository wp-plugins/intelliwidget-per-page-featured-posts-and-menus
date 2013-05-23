<?php
if ( !defined('ABSPATH')) exit;
/**
 * big-date.php - Template for showing big date next to excerpt (calendar)
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
global $iwgt_post;
if ( !empty($selected)) : foreach($selected as $iwgt_post) : 
?>

<div id="intelliwidget_<?php the_iwgt_ID(); ?>" class="intelliwidget-big-date clearfix">
  <div class="intelliwidget-date"><span class="intelliwidget-month"> <?php the_iwgt_date('M'); ?> </span> <span class="intelliwidget-day"> <?php the_iwgt_date('j'); ?> </span></div>
  <div class="intelliwidget-item">
    <?php if ( has_iwgt_image() ) : ?>
    <div class="intelliwidget-image-container-<?php echo $instance['image_size'];?> intelliwidget-align-<?php echo $instance['imagealign']; ?>">
      <?php the_iwgt_image(); ?>
    </div>
    <?php endif; ?>
    <h3 id="intelliwidget_title_<?php the_iwgt_ID(); ?>" class="intelliwidget-title">
      <?php the_iwgt_link(); ?>
    </h3>
    <div id="intelliwidget_excerpt_<?php the_iwgt_ID(); ?>" class="intelliwidget-excerpt">
      <?php the_iwgt_excerpt(); ?>
      <span id="intelliwidget_more_link_<?php the_iwgt_ID(); ?>" class="intelliwidget-more-link">
      <?php the_iwgt_link(get_the_iwgt_id(), $instance['link_text']); ?>
      </span></div>
  </div>
</div>
<?php endforeach; endif; ?>
