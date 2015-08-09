<?php
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * albums.php - Template for album covers
 *
 * This can be copied to a folder named 'intelliwidget' in your theme
 * to customize the output.
 *
 * @package IntelliWidget
 * @subpackage templates
 * @author Jason C Fleming
 * @copyright 2014-2015 Lilaea Media LLC
 * @access public
 */
?>
<?php
if ( $selected->have_posts() ) : while ( $selected->have_posts() ) : $selected->the_post();
?>

<div id="intelliwidget_<?php the_intelliwidget_ID(); ?>"  class="rel clearfix <?php the_intelliwidget_post_classes($selected, 3, array('stagger'));?>">
  <?php if ( $posterimg = get_the_intelliwidget_postmeta( 'poster_image' ) ) : ?>
  <div class="poster" style="background-image: url(<?php echo $posterimg; ?>)" >
  <img src="/web/wpc/themes/baxter-johnson/images/pixel.gif" height="600" width="600" class="spacer-square" />
  <?php elseif ( has_intelliwidget_image() ) : ?>
  <div class="intelliwidget-album-container-<?php echo $instance[ 'image_size' ];?> intelliwidget-align-<?php echo $instance[ 'imagealign' ]; ?>">
    <?php the_intelliwidget_image(); ?>
  </div>
  <?php endif; ?>
  <div id="post-entry" class="hoverlay hoverlay-collapse width-full">
  <h3 id="intelliwidget_title_<?php the_intelliwidget_ID(); ?>" class="post-title">
    <?php the_intelliwidget_link(); ?>
  </h3><p class="hoverlay-excerpt"><?php the_intelliwidget_excerpt(); ?> <span id="intelliwidget_more_link_<?php the_intelliwidget_ID(); ?>" class="intelliwidget-more-link">
    <?php the_intelliwidget_link( get_the_intelliwidget_ID(), $instance[ 'link_text' ] ); ?>
    </span></p>
  </div>
  <?php if ( $posterimg ): ?></div><?php endif; ?>
</div>
<?php endwhile; endif; ?>
