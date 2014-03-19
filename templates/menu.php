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
global $intelliwidget_post_id;
 ?>

<ul class="intelliwidget-menu">
    <?php if ( $selected->have_posts() ) : while ($selected->have_posts()) : $selected->the_post(); ?>
    <li id="intelliwidget_<?php the_intelliwidget_ID(); ?>" class="intelliwidget-menu-item <?php echo $intelliwidget_post_id == get_the_intelliwidget_ID() ? 'intelliwidget-current-menu-item' : ''; ?>">
        <?php if ( has_intelliwidget_image() ) : ?>
        <div class="intelliwidget-image-container-<?php echo $instance['image_size'];?> intelliwidget-align-<?php echo $instance['imagealign']; ?>">
            <?php the_intelliwidget_image(); ?>
        </div>
        <?php endif; ?>
        <?php the_intelliwidget_link(); ?>
    </li>
    <?php endwhile; endif; ?>
</ul>
