<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * widget-form.php - Outputs widget form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
global $_wp_additional_image_sizes;
?>
<?php echo $intelliwidget->docsLink; ?>

<p>
  <label>
    <input name="<?php echo $this->get_field_name('hide_if_empty'); ?>" id="<?php echo $this->get_field_id('hide_if_empty'); ?>" type="checkbox" <?php checked($instance['hide_if_empty']); ?> />
    <?php _e('Hide if no Page Data is available', 'intelliwidget'); ?>
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('title'); ?>"> <?php echo __('Widget', 'intelliwidget') . ' ' . __('Title', 'intelliwidget') . ' ' . __('(Leave blank to omit)', 'intelliwidget'); ?>: </label>
  <br/>
  <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
  <label>
    <input name="<?php echo $this->get_field_name('link_title'); ?>" id="<?php echo $this->get_field_id('link_title'); ?>" type="checkbox" <?php checked($instance['link_title']); ?> />
    <?php _e('Link to Archive', 'intelliwidget'); ?>
  </label>
</p>
<p>
  <label for="<?php echo $this->get_field_id('template'); ?>">
    <?php _e('Template', 'intelliwidget'); ?>
    :</label>
  <select name="<?php echo $this->get_field_name('template'); ?>" id="<?php echo $this->get_field_id('template'); ?>">
    <option value="WP_NAV_MENU" <?php selected($instance['template'], 'WP_NAV_MENU'); ?>>[NAV MENU]</option>
    <?php foreach ( $intelliwidget->templates as $template => $name ) : ?>
    <option value="<?php echo $template; ?>" <?php selected($instance['template'], $template); ?>><?php echo $name; ?></option>
    <?php endforeach; ?>
  </select>
  <label for="<?php echo $this->get_field_id('category'); ?>">
    <?php _e('Category', 'intelliwidget'); ?>
    :</label>
  <?php wp_dropdown_categories(array('name' => $this->get_field_name('category'), 'id' => $this->get_field_id('category'), 'show_option_none' => __('None', 'intelliwidget'), 'hide_empty' => false, 'selected' => $instance['category'] )); ?>
</p>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $this->get_field_id('specificposts'); ?>" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Specific Post(s)', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $this->get_field_id('specificposts'); ?>-inside" style="display:none;padding:8px" class="closed">
    <select  class="widefat" name="<?php echo $this->get_field_name('page'); ?>[]" style="height:100px;" multiple="multiple" id="<?php echo $this->get_field_id('page'); ?>">
      <?php echo $intelliwidget->get_pages($instance); ?>
    </select>
  </div>
</div>
<p>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $this->get_field_id('customtext'); ?>" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Custom Text/HTML', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $this->get_field_id('customtext'); ?>-inside" style="display:none;padding:8px" class="closed">
    <p>
      <label for="<?php echo $this->get_field_id('text_position'); ?>">
        <?php _e( 'Display', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo $this->get_field_name('text_position'); ?>" id="<?php echo $this->get_field_id('text_position'); ?>">
        <option value="">
        <?php _e('None', 'intelliwidget'); ?>
        </option>
        <option value="above"<?php selected( $instance['text_position'], 'above' ); ?>>
        <?php _e('Above Posts', 'intelliwidget'); ?>
        </option>
        <option value="below"<?php selected( $instance['text_position'], 'below' ); ?>>
        <?php _e('Below Posts', 'intelliwidget'); ?>
        </option>
        <option value="only"<?php selected( $instance['text_position'], 'only' ); ?>>
        <?php _e('Text Only-No Posts', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <textarea class="widefat" rows="3" cols="20" id="<?php echo $this->get_field_id('custom_text'); ?>" 
name="<?php echo $this->get_field_name('custom_text'); ?>">
<?php echo esc_textarea($instance['custom_text']); ?></textarea>
    </p>
  </div>
</div>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $this->get_field_id('advancedoptions'); ?>" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Advanced Options', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $this->get_field_id('advancedoptions'); ?>-inside" style="display:none;padding:8px" class="closed">
    <p>
      <label for="<?php echo $this->get_field_id('nav_menu'); ?>">
        <?php _e('WP Nav Menu:'); ?>
      </label>
      <select id="<?php echo $this->get_field_id('nav_menu'); ?>" name="<?php echo $this->get_field_name('nav_menu'); ?>">
        <option value="" <?php selected($instance['nav_menu'], ""); ?>>None</option>
        <?php
			// Get menus
			foreach ( $intelliwidget->menus as $menu ):
				echo '<option value="' . $menu->term_id . '"'
					. selected( $instance['nav_menu'], $menu->term_id, false )
					. '>'. $menu->name . '</option>';
			endforeach;

		?>
      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('sortby'); ?>">
        <?php _e( 'Sort by:', 'intelliwidget'); ?>
      </label>
      <select name="<?php echo $this->get_field_name('sortby'); ?>" id="<?php echo $this->get_field_id('sortby'); ?>">
        <option value="date"<?php selected( $instance['sortby'], 'date' ); ?>>
        <?php _e('Post Date', 'intelliwidget'); ?>
        </option>
        <option value="meta_value"<?php selected( $instance['sortby'], 'meta_value' ); ?>>
        <?php _e('Event Date', 'intelliwidget'); ?>
        </option>
        <option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>>
        <?php _e('Menu Order', 'intelliwidget'); ?>
        </option>
        <option value="title"<?php selected( $instance['sortby'], 'title' ); ?>>
        <?php _e('Title', 'intelliwidget'); ?>
        </option>
        <option value="rand"<?php selected( $instance['sortby'], 'rand' ); ?>>
        <?php _e( 'Random', 'intelliwidget'); ?>
        </option>
      </select>
      <select name="<?php echo $this->get_field_name('sortorder'); ?>" id="<?php echo $this->get_field_id('sortorder'); ?>">
        <option value="ASC"<?php selected( $instance['sortorder'], 'ASC' ); ?>>
        <?php _e('ASC', 'intelliwidget'); ?>
        </option>
        <option value="DESC"<?php selected( $instance['sortorder'], 'DESC' ); ?>>
        <?php _e('DESC', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('items'); ?>">
        <?php _e('Posts per section', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo $this->get_field_name('items'); ?>" id="<?php echo $this->get_field_id('items'); ?>">
        <option value="all" <?php selected($instance['items'], 'all'); ?>>
        <?php _e('Show All', 'intelliwidget'); ?>
        </option>
        <?php for ( $ictr = 1; $ictr <= 10; ++$ictr ) : ?>
        <option value="<?php echo $ictr; ?>" <?php selected($instance['items'], $ictr); ?>><?php echo $ictr; ?></option>
        <?php endfor; ?>
      </select>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('length'); ?>">
        <?php _e('Words per post', 'intelliwidget'); ?>
        : </label>
      <input id="<?php echo $this->get_field_id('length'); ?>" name="<?php echo $this->get_field_name('length'); ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label>
        <input name="<?php echo $this->get_field_name('skip_post'); ?>" id="<?php echo $this->get_field_id('skip_post'); ?>" type="checkbox" <?php checked($instance['skip_post'], 1); ?> />
        &nbsp;
        <?php _e('Exclude current post', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input name="<?php echo $this->get_field_name('future_only'); ?>" id="<?php echo $this->get_field_id('future_only'); ?>" type="checkbox" <?php checked($instance['future_only'], 1); ?> />
        &nbsp;
        <?php _e('Only future events', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input name="<?php echo $this->get_field_name('active_only'); ?>" id="<?php echo $this->get_field_id('active_only'); ?>" type="checkbox" <?php checked($instance['active_only'], 1); ?> />
        &nbsp;
        <?php _e('Only active events', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input name="<?php echo $this->get_field_name('skip_expired'); ?>" id="<?php echo $this->get_field_id('skip_expired'); ?>" type="checkbox" <?php checked($instance['skip_expired'], 1); ?> />
        &nbsp;
        <?php _e('Exclude expired posts', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input id="<?php echo $this->get_field_id('filter'); ?>" name="<?php echo $this->get_field_name('filter'); ?>" type="checkbox" <?php checked(isset($instance['filter']) ? $instance['filter'] : 0); ?> />
        &nbsp;
        <?php _e('Auto-format Custom Text', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('link_text'); ?>">
        <?php _e('Link Text', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $this->get_field_name('link_text'); ?>" id="<?php echo $this->get_field_id('link_text'); ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label for="<?php echo $this->get_field_id('classes'); ?>">
        <?php _e('Classes', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $this->get_field_name('classes'); ?>" id="<?php echo $this->get_field_id('classes'); ?>" type="text" value="<?php echo esc_attr($instance['classes']); ?>" />
    </p>
    <p>
      <label for="<?php print $this->get_field_id('imagealign'); ?>">
        <?php _e('Image Align:', 'intelliwidget'); ?>
      </label>
      <select name="<?php print $this->get_field_name('imagealign'); ?>" id="<?php print $this->get_field_id('imagealign'); ?>">
        <option value="none" <?php selected($instance['imagealign'], 'none'); ?> >
        <?php _e('Auto', 'intelliwidget'); ?>
        </option>
        <option value="left" <?php selected($instance['imagealign'], 'left'); ?> >
        <?php _e('Left', 'intelliwidget'); ?>
        </option>
        <option value="center" <?php selected($instance['imagealign'], 'center'); ?> >
        <?php _e('Center', 'intelliwidget'); ?>
        </option>
        <option value="right" <?php selected($instance['imagealign'], 'right'); ?> >
        <?php _e('Right', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <label for="<?php print $this->get_field_id('image_size'); ?>">
        <?php _e('Image Size:', 'intelliwidget'); ?>
      </label>
      <select id="<?php echo $this->get_field_id('image_size'); ?>" name="<?php echo $this->get_field_name('image_size'); ?>">
        <option value="none" <?php selected($instance['image_size'], 'none'); ?> >
        <?php _e('No Image', 'intelliwidget'); ?>
        </option>
        <option value="thumbnail" <?php selected($instance['image_size'], 'thumbnail'); ?> >
        <?php _e('Thumbnail', 'intelliwidget'); ?>
        </option>
        <option value="medium" <?php selected($instance['image_size'], 'medium'); ?> >
        <?php _e('Medium', 'intelliwidget'); ?>
        </option>
        <option value="large" <?php selected($instance['image_size'], 'large'); ?> >
        <?php _e('Large', 'intelliwidget'); ?>
        </option>
        <option value="full" <?php selected($instance['image_size'], 'full'); ?> >
        <?php _e('Full', 'intelliwidget'); ?>
        </option>
        <?php if (is_array($_wp_additional_image_sizes)): foreach ( $_wp_additional_image_sizes as $name => $size ) : ?>
        <option value="<?php echo $name; ?>" <?php selected($instance['image_size'], $name); ?> ><?php echo $name; ?> (<?php echo $size['width']; ?>x<?php echo $size['height']; ?>px)</option>
        <?php endforeach; endif;?>
      </select>
    </p>
    <p>Post Types:<br/>
      <?php foreach ( $intelliwidget->get_eligible_post_types() as $type ) : ?>
      <label for="<?php echo $this->get_field_id('post_types'); ?>">
        <input type="checkbox" id="<?php echo $this->get_field_id('post_types'); ?>" name="<?php echo $this->get_field_name('post_types'); ?>[]" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
  </div>
</div>
