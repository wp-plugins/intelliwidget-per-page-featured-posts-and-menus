<?php
if ( !defined('ABSPATH')) exit;
/**
 * multi-date.php - Template for showing multi-date events
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
if ( $selected->have_posts() ) : while ($selected->have_posts()) : $selected->the_post();
?>

<div id="intelliwidget_<?php the_intelliwidget_ID(); ?>" class="intelliwidget-multi-date clearfix">
<div class="intelliwidget-date-container">
    <div class="intelliwidget-date"><span class="intelliwidget-month-start">
        <?php the_intelliwidget_date('M'); ?>
        </span> <span class="intelliwidget-day-start">
        <?php the_intelliwidget_date('j'); ?>
        </span></div><?php if ($expm = get_the_intelliwidget_exp_date('M')): ?>
        <strong><sup> &mdash; </sup></strong><div class="intelliwidget-date"><span class="intelliwidget-month-end">
        <?php echo $expm; ?>
        </span> <span class="intelliwidget-day-end">
        <?php the_intelliwidget_exp_date('j'); ?>
        </span></div><?php endif; ?></div>
    <div class="intelliwidget-item">
        <?php if ( has_intelliwidget_image() ) : ?>
        <div class="intelliwidget-image-container-<?php echo $instance['image_size'];?> intelliwidget-align-<?php echo $instance['imagealign']; ?>">
            <?php the_intelliwidget_image(); ?>
        </div>
        <?php endif; ?>
        <h3 id="intelliwidget_title_<?php the_intelliwidget_ID(); ?>" class="intelliwidget-title">
            <?php the_intelliwidget_link(); ?>
        </h3>
        <div id="intelliwidget_excerpt_<?php the_intelliwidget_ID(); ?>" class="intelliwidget-excerpt">
            <?php the_intelliwidget_excerpt(); ?>
            <span id="intelliwidget_more_link_<?php the_intelliwidget_ID(); ?>" class="intelliwidget-more-link">
            <?php the_intelliwidget_link(get_the_intelliwidget_id(), $instance['link_text']); ?>
            </span></div>
    </div>
</div>
<?php endwhile; endif; ?>
