<?php
if ( !defined( 'ABSPATH' ) ) exit;
/**
 * excerpts-author.php - Template for basic featured post list
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
if ( $selected->have_posts() ) : while ( $selected->have_posts() ) : $selected->the_post();
?>
        <div class="intelliwidget-excerpt-container">
        <?php if ($av = get_avatar( get_the_intelliwidget_author_meta( 'ID' ), 32 ) ): ?>
            <div class="intelliwidget-image-container intelliwidget-align-<?php echo $instance[ 'imagealign' ]; ?>">
            <?php echo $av; ?>
            </div><?php endif; ?>
            <div lass="intelliwidget-excerpt">
                <?php echo '<a href="' . get_author_posts_url( get_the_intelliwidget_author_meta( 'ID' ) ) . '">' . get_the_intelliwidget_author_meta( 'display_name' ) . '</a>'; ?>
                <?php echo apply_filters('the_content', get_the_intelliwidget_author_meta( 'description' ) ); ?>
            </div>
  <div style="clear:both"></div>
        </div>
<?php endwhile; endif; ?>
