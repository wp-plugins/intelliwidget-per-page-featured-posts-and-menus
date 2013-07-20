<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * section-form.php - Outputs Widget Section Meta Box on Page Edit Form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
global $_wp_additional_image_sizes, $wp_registered_sidebars;
?>

<p>
    <label>
        <input name="<?php echo 'intelliwidget_' . $pagesection . '_nocopy'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_nocopy'; ?>" type="checkbox" <?php checked($intelliwidget_data['nocopy']); ?> />
        <?php _e('Override Copied Settings', 'intelliwidget'); ?>
    </label>
</p>
<p>
    <label for="<?php echo 'intelliwidget_' . $pagesection . '_replace_widget'; ?>">
        <?php _e( 'Replaces', 'intelliwidget'); ?>: </label>
    <select name="<?php echo 'intelliwidget_' . $pagesection . '_replace_widget'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_replace_widget'; ?>">
        <option value="none"<?php selected( $intelliwidget_data['replace_widget'], 'none' ); ?>>
        <?php _e('No Widget Selected', 'intelliwidget');?>
        </option>
        <?php foreach($widgets_array as $sidebar_id => $sidebar_widgets): 
             if (false === strpos($sidebar_id, 'wp_inactive') ):
            $count = 1;
              foreach ($sidebar_widgets as $sidebar_widget_id):
                 if (false !== strpos($sidebar_widget_id, 'intelliwidget') ):
  ?>
        <option value="<?php echo $sidebar_widget_id; ?>"<?php selected( $intelliwidget_data['replace_widget'], $sidebar_widget_id ); ?>> <?php echo $wp_registered_sidebars[$sidebar_id]['name'] . ' [' . $count . ']'; ?> </option>
        <?php $count++; endif; endforeach; endif; endforeach?>
    </select>
</p>
<p>
    <label for="<?php echo 'intelliwidget_' . $pagesection . '_title'; ?>"> <?php echo __('Section', 'intelliwidget') . ' ' . __('Title', 'intelliwidget') . ' ' . __('(Leave blank to omit)', 'intelliwidget'); ?> </label>
    <input id="<?php echo 'intelliwidget_' . $pagesection . '_title'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_title'; ?>" type="text" value="<?php echo esc_attr($intelliwidget_data['title']); ?>" />
    <label>
        <input name="<?php echo 'intelliwidget_' . $pagesection . '_link_title'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_link_title'; ?>" type="checkbox" <?php checked($intelliwidget_data['link_title']); ?> />
        <?php _e('Link to Archive', 'intelliwidget'); ?>
    </label>
</p>
<p>
    <label for="<?php echo 'intelliwidget_' . $pagesection . '_template'; ?>">
        <?php _e('Template', 'intelliwidget'); ?>:</label>
    <select name="<?php echo 'intelliwidget_' . $pagesection . '_template'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_template'; ?>">
        <?php foreach ( $this->templates as $template => $name ) : ?>
        <option value="<?php echo $template; ?>" <?php selected($intelliwidget_data['template'], $template); ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
    </select>
</p>
<p>
    <label for="<?php echo 'intelliwidget_' . $pagesection . '_category'; ?>">
        <?php _e('Category', 'intelliwidget'); ?>:</label>
    <?php wp_dropdown_categories(array('name' => 'intelliwidget_' . $pagesection . '_category', 'id' => 'intelliwidget_' . $pagesection . '_category', 'show_option_none' => __('None', 'intelliwidget'), 'hide_empty' => false, 'selected' => $intelliwidget_data['category'] )); ?>
</p>
<div id="iw-specificposts" class="postbox closed">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h3 class='hndle'><span>
        <?php _e('Specific Posts', 'intelliwidget'); ?>
        </span></h3>
    <div class="inside">
        <select class="widefat" name="<?php echo 'intelliwidget_' . $pagesection . '_page'; ?>[]" size="1" style="height:100px;" multiple="multiple" id="<?php echo 'intelliwidget_' . $pagesection . '_page'; ?>">
            <?php echo $this->get_pages($intelliwidget_data); ?>
        </select>
    </div>
</div>
<div id="iw-customtext" class="postbox closed">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h3 class='hndle'><span>
        <?php _e('Custom Text/HTML', 'intelliwidget'); ?>
        </span></h3>
    <div class="inside">
        <select name="<?php echo 'intelliwidget_' . $pagesection . '_text_position'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_text_position'; ?>">
            <option value="">
            <?php _e('None', 'intelliwidget'); ?>
            </option>
            <option value="above"<?php selected( $intelliwidget_data['text_position'], 'above' ); ?>>
            <?php _e('Above Posts', 'intelliwidget'); ?>
            </option>
            <option value="below"<?php selected( $intelliwidget_data['text_position'], 'below' ); ?>>
            <?php _e('Below Posts', 'intelliwidget'); ?>
            </option>
            <option value="only"<?php selected( $intelliwidget_data['text_position'], 'only' ); ?>>
            <?php _e('Text Only-No Posts', 'intelliwidget'); ?>
            </option>
        </select>
        <textarea class="widefat" rows="3" cols="20" id="<?php echo 'intelliwidget_' . $pagesection . '_custom_text'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_custom_text'; ?>">
<?php echo esc_textarea($intelliwidget_data['custom_text']); ?></textarea>
    </div>
</div>
<div id="iw-advancedsettings" class="postbox closed">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h3 class='hndle'><span>
        <?php _e('Advanced Settings', 'intelliwidget'); ?>
        </span></h3>
    <div class="inside">
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_nav_menu'; ?>">
                <?php _e('WP Nav Menu'); ?>
            </label>
            <select id="<?php echo 'intelliwidget_' . $pagesection . '_nav_menu'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_nav_menu'; ?>">
                <option value="" <?php selected( $intelliwidget_data['nav_menu'], '' ); ?>>
                <?php _e('None', 'intelliwidget'); ?>
                </option>
                <option value="-1" <?php selected( $intelliwidget_data['nav_menu'], '-1' ); ?>>
                <?php _e('Page Menu', 'intelliwidget'); ?>
                </option>
                <?php
            // Get menus
            foreach ( $this->menus as $menu ):
                echo '<option value="' . $menu->term_id . '"'
                    . selected( $intelliwidget_data['nav_menu'], $menu->term_id, false )
                    . '>'. $menu->name . '</option>';
            endforeach;

        ?>
            </select>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_sortby'; ?>">
                <?php _e( 'Sort by', 'intelliwidget'); ?>
            </label>
            <select name="<?php echo 'intelliwidget_' . $pagesection . '_sortby'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_sortby'; ?>">
                <option value="date"<?php selected( $intelliwidget_data['sortby'], 'date' ); ?>>
                <?php _e('Post Date', 'intelliwidget'); ?>
                </option>
                <option value="event_date"<?php selected( $intelliwidget_data['sortby'], 'event_date' ); ?>>
                <?php _e('Event Date', 'intelliwidget'); ?>
                </option>
                <option value="menu_order"<?php selected( $intelliwidget_data['sortby'], 'menu_order' ); ?>>
                <?php _e('Menu Order', 'intelliwidget'); ?>
                </option>
                <option value="title"<?php selected( $intelliwidget_data['sortby'], 'title' ); ?>>
                <?php _e('Title', 'intelliwidget'); ?>
                </option>
                <option value="rand"<?php selected( $intelliwidget_data['sortby'], 'rand' ); ?>>
                <?php _e( 'Random', 'intelliwidget'); ?>
                </option>
            </select>
            <select name="<?php echo 'intelliwidget_' . $pagesection . '_sortorder'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_sortorder'; ?>">
                <option value="ASC"<?php selected( $intelliwidget_data['sortorder'], 'ASC' ); ?>>
                <?php _e('ASC', 'intelliwidget'); ?>
                </option>
                <option value="DESC"<?php selected( $intelliwidget_data['sortorder'], 'DESC' ); ?>>
                <?php _e('DESC', 'intelliwidget'); ?>
                </option>
            </select>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_items'; ?>">
                <?php _e('Posts per section', 'intelliwidget'); ?>: </label>
            <select name="<?php echo 'intelliwidget_' . $pagesection . '_items'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_items'; ?>">
                <option value="all" <?php selected($intelliwidget_data['items'], 'all'); ?>>
                <?php _e('Show All', 'intelliwidget'); ?>
                </option>
                <?php for ( $ictr = 1; $ictr <= 10; ++$ictr ) : ?>
                <option value="<?php echo $ictr; ?>" <?php selected($intelliwidget_data['items'], $ictr); ?>><?php echo $ictr; ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_length'; ?>">
                <?php _e('Words per post', 'intelliwidget'); ?>: </label>
            <input id="<?php echo 'intelliwidget_' . $pagesection . '_length'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_length'; ?>" size="3" type="text" value="<?php echo esc_attr($intelliwidget_data['length']); ?>" />
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_link_text'; ?>">
                <?php _e('Link Text', 'intelliwidget'); ?>: </label>
            <input name="<?php echo 'intelliwidget_' . $pagesection . '_link_text'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_link_text'; ?>" type="text" value="<?php echo esc_attr($intelliwidget_data['link_text']); ?>" />
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_container_id'; ?>">
                <?php _e('ID', 'intelliwidget'); ?>: </label>
            <input name="<?php echo 'intelliwidget_' . $pagesection . '_container_id'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_container_id'; ?>" type="text" value="<?php echo esc_attr($intelliwidget_data['container_id']); ?>" />
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_classes'; ?>">
                <?php _e('Classes', 'intelliwidget'); ?>: </label>
            <input name="<?php echo 'intelliwidget_' . $pagesection . '_classes'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_classes'; ?>" type="text" value="<?php echo esc_attr($intelliwidget_data['classes']); ?>" />
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_skip_post'; ?>">
                <input id="<?php echo 'intelliwidget_' . $pagesection . '_skip_post'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_skip_post'; ?>" type="checkbox" <?php checked($intelliwidget_data['skip_post'], 1); ?> />
                &nbsp;
                <?php _e('Exclude current post', 'intelliwidget'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_future_only'; ?>">
                <input id="<?php echo 'intelliwidget_' . $pagesection . '_future_only'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_future_only'; ?>" type="checkbox" <?php checked($intelliwidget_data['future_only'], 1); ?> />
                &nbsp;
                <?php _e('Only future events', 'intelliwidget'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_active_only'; ?>">
                <input id="<?php echo 'intelliwidget_' . $pagesection . '_active_only'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_active_only'; ?>" type="checkbox" <?php checked($intelliwidget_data['active_only'], 1); ?> />
                &nbsp;
                <?php _e('Only active events', 'intelliwidget'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_skip_expired'; ?>">
                <input id="<?php echo 'intelliwidget_' . $pagesection . '_skip_expired'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_skip_expired'; ?>" type="checkbox" <?php checked($intelliwidget_data['skip_expired'], 1); ?> />
                &nbsp;
                <?php _e('Exclude expired posts', 'intelliwidget'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_filter'; ?>">
                <input id="<?php echo 'intelliwidget_' . $pagesection . '_filter'; ?>" name="<?php echo 'intelliwidget_' . $pagesection . '_filter'; ?>" type="checkbox" <?php checked($intelliwidget_data['filter'] ? 1 : 0); ?> />
                &nbsp;
                <?php _e('Auto-format Custom Text', 'intelliwidget'); ?>
            </label>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_imagealign'; ?>">
                <?php _e('Image Align', 'intelliwidget'); ?>
            </label>
            <select name="<?php echo 'intelliwidget_' . $pagesection . '_imagealign'; ?>" id="<?php echo 'intelliwidget_' . $pagesection . '_imagealign'; ?>">
                <option value="none" <?php selected($intelliwidget_data['imagealign'], 'none'); ?> >
                <?php _e('Auto', 'intelliwidget'); ?>
                </option>
                <option value="left" <?php selected($intelliwidget_data['imagealign'], 'left'); ?> >
                <?php _e('Left', 'intelliwidget'); ?>
                </option>
                <option value="center" <?php selected($intelliwidget_data['imagealign'], 'center'); ?> >
                <?php _e('Center', 'intelliwidget'); ?>
                </option>
                <option value="right" <?php selected($intelliwidget_data['imagealign'], 'right'); ?> >
                <?php _e('Right', 'intelliwidget'); ?>
                </option>
            </select>
        </p>
        <p>
            <label for="<?php echo 'intelliwidget_' . $pagesection . '_image_size'; ?>">
                <?php _e('Image Size', 'intelliwidget'); ?>: </label>
            <select id="<?php echo 'intelliwidget_' . $pagesection . '_image_size'; ?>" name="<?php  echo 'intelliwidget_' . $pagesection . '_image_size'; ?>">
                <option value="none" <?php selected($intelliwidget_data['image_size'], 'none'); ?> >
                <?php _e('No Image', 'intelliwidget'); ?>
                </option>
                <option value="thumbnail" <?php selected($intelliwidget_data['image_size'], 'thumbnail'); ?> >
                <?php _e('Thumbnail', 'intelliwidget'); ?>
                </option>
                <option value="medium" <?php selected($intelliwidget_data['image_size'], 'medium'); ?> >
                <?php _e('Medium', 'intelliwidget'); ?>
                </option>
                <option value="large" <?php selected($intelliwidget_data['image_size'], 'large'); ?> >
                <?php _e('Large', 'intelliwidget'); ?>
                </option>
                <option value="full" <?php selected($intelliwidget_data['image_size'], 'full'); ?> >
                <?php _e('Full', 'intelliwidget'); ?>
                </option>
                <?php if (is_array($_wp_additional_image_sizes)): foreach ( $_wp_additional_image_sizes as $name => $size ) : ?>
                <option value="<?php echo $name; ?>" <?php selected($intelliwidget_data['image_size'], $name); ?> ><?php echo $name; ?> (<?php echo $size['width']; ?>x<?php echo $size['height']; ?>px)</option>
                <?php endforeach; endif; ?>
            </select>
        </p>
        <p>
            <?php _e('Post Types', 'intelliwidget'); ?>
            <br/>
            <?php foreach ( $this->get_eligible_post_types() as $type ) : ?>
            <label>
                <input id="<?php echo 'intelliwidget_' . $pagesection . '_post_types'; ?>" type="checkbox" name="<?php echo 'intelliwidget_' . $pagesection . '_post_types[]'; ?>" value="<?php echo $type; ?>" <?php checked(in_array($type, $intelliwidget_data['post_types']), 1); ?> />
                &nbsp;<?php echo ucfirst($type); ?></label>
            <?php endforeach; ?>
        </p>
    </div>
</div>
<div class="iw-save-container">
    <input name="save" class="button button-primary button-large iw-save" id="<?php echo 'intelliwidget_' . $pagesection . '_save'; ?>" value="<?php _e('Save', 'intelliwidget'); ?>" type="button" style="float:right">
    <span class="spinner" id="<?php echo 'intelliwidget_' . $pagesection . '_spinner'; ?>"></span> </div>
<a style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwdelete='.$pagesection.'&post=' . $post_ID), 'iwdelete'); ?>" id="iw_delete_<?php echo $pagesection; ?>" class="iw-delete">
<?php _e('Delete', 'intelliwidget'); ?>
</a>
<div style="clear:both"></div>
