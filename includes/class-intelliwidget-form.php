<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-form.php - Outputs widget form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
class IntelliWidgetForm {
    function __construct() {
//        print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS));
        add_action('intelliwidget_form_nav_menu',   array($this, 'widget_form_nav_menu'), 1, 3);
        add_action('intelliwidget_form_all_before', array($this, 'widget_form_general_settings'), 2, 3);
        add_action('intelliwidget_form_post_list',  array($this, 'widget_form_post_selection_settings'), 5, 3);
        add_action('intelliwidget_form_post_list',  array($this, 'widget_form_appearance_settings'), 10, 3);
        add_action('intelliwidget_form_all_after',  array($this, 'widget_form_addl_text_settings'), 3, 3);
    }

    function intelliwidget_form($instance, $widget) {
        global $intelliwidget;
        ?>

<p> <?php echo $intelliwidget->docsLink; ?>
  <label>
    <input class="iw-widget-control" name="<?php echo $widget->get_field_name('hide_if_empty'); ?>" id="<?php echo $widget->get_field_id('hide_if_empty'); ?>" type="checkbox" <?php checked($instance['hide_if_empty'], 1); ?> value="1"/>
    <?php _e('Placeholder Only (do not display)', 'intelliwidget'); ?>
  </label>
</p>
<?php // execute custom action hook for content value if it exists
        if (empty($instance['hide_if_empty'])):
            do_action('intelliwidget_form_all_before', $instance, $widget);
            do_action('intelliwidget_form_' . $instance['content'], $instance, $widget);
            do_action('intelliwidget_form_all_after', $instance, $widget);
        endif;
    }

    function widget_form_general_settings($instance, $widget) { 
        global $intelliwidget; 
        ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('generalsettings'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('General Settings', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('generalsettings'); ?>-panel-inside" style="display:none;padding:8px" class="iw-section-inside closed">
    <p>
      <label for="<?php echo $widget->get_field_id('content'); ?>">
        <?php _e('IntelliWidget Type', 'intelliwidget'); ?>
        : </label>
      <select class="iw-widget-control" id="<?php echo $widget->get_field_id('content'); ?>" name="<?php echo $widget->get_field_name('content'); ?>" autocomplete="off">
        <?php foreach ($intelliwidget->get_content_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['content'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('title'); ?>"> <?php echo __('Section', 'intelliwidget') . ' ' . __('Title', 'intelliwidget') . ' ' . __('(Optional)', 'intelliwidget'); ?>: </label>
      <label>
        <input name="<?php echo $widget->get_field_name('link_title'); ?>" id="<?php echo $widget->get_field_id('link_title'); ?>" type="checkbox" <?php checked($instance['link_title'], 1); ?> value="1" />
        <?php _e('Link', 'intelliwidget'); ?>
      </label>
      <br/>
      <input id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('container_id'); ?>">
        <?php _e('Container ID', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('container_id'); ?>" id="<?php echo $widget->get_field_id('container_id'); ?>" type="text" value="<?php echo esc_attr($instance['container_id']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('classes'); ?>">
        <?php _e('Style Classes', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('classes'); ?>" id="<?php echo $widget->get_field_id('classes'); ?>" type="text" value="<?php echo esc_attr($instance['classes']); ?>" />
    </p>
  </div>
</div>
<?
    }

    function widget_form_nav_menu($instance, $widget) { 
        global $intelliwidget;
        ?>
<p>
  <label for="<?php echo $widget->get_field_id('nav_menu'); ?>">
    <?php _e('Menu to display', 'intelliwidget'); ?>
    : </label>
  <select id="<?php echo $widget->get_field_id('nav_menu'); ?>" name="<?php echo $widget->get_field_name('nav_menu'); ?>">
    <option value="" <?php selected($instance['nav_menu'], ""); ?>>
    <?php _e('None', 'intelliwidget'); ?>
    </option>
    <option value="-1" <?php selected($instance['nav_menu'], "-1"); ?>>
    <?php _e('Automatic Page Menu', 'intelliwidget'); ?>
    </option>
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
<?php 
    }

    function widget_form_addl_text_settings($instance, $widget) { 
        global $intelliwidget; ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('customtext'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Additional Text/HTML', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('customtext'); ?>-panel-inside" style="display:none;padding:8px" class="iw-section-inside closed">
    <p>
      <label for="<?php echo $widget->get_field_id('text_position'); ?>">
        <?php _e( 'Display', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo $widget->get_field_name('text_position'); ?>" id="<?php echo $widget->get_field_id('text_position'); ?>">
        <?php foreach ($intelliwidget->get_text_position_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['text_position'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <textarea class="widefat" rows="3" cols="20" id="<?php echo $widget->get_field_id('custom_text'); ?>" 
name="<?php echo $widget->get_field_name('custom_text'); ?>">
<?php echo esc_textarea($instance['custom_text']); ?></textarea>
    </p>
    <p>
      <label>
        <input id="<?php echo $widget->get_field_id('filter'); ?>" name="<?php echo $widget->get_field_name('filter'); ?>" type="checkbox" <?php checked($instance['filter'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Automatically add paragraphs', 'intelliwidget'); ?>
      </label>
    </p>
  </div>
</div>
<?
    }

    function widget_form_appearance_settings($instance, $widget) { 
        global $intelliwidget, $_wp_additional_image_sizes;
        ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('appearance'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Appearance Settings', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('appearance'); ?>-panel-inside" style="display:none;padding:8px" class="iw-section-inside closed">
    <p>
      <label for="<?php echo $widget->get_field_id('template'); ?>">
        <?php _e('Template', 'intelliwidget'); ?>
        :</label>
      <select name="<?php echo $widget->get_field_name('template'); ?>" id="<?php echo $widget->get_field_id('template'); ?>">
        <?php foreach ( $intelliwidget->templates as $template => $name ) : ?>
        <option value="<?php echo $template; ?>" <?php selected($instance['template'], $template); ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('sortby'); ?>">
        <?php _e( 'Sort posts by', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo $widget->get_field_name('sortby'); ?>" id="<?php echo $widget->get_field_id('sortby'); ?>">
        <?php foreach ($intelliwidget->get_sortby_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['sortby'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <select name="<?php echo $widget->get_field_name('sortorder'); ?>" id="<?php echo $widget->get_field_id('sortorder'); ?>">
        <option value="ASC"<?php selected( $instance['sortorder'], 'ASC' ); ?>>
        <?php _e('ASC', 'intelliwidget'); ?>
        </option>
        <option value="DESC"<?php selected( $instance['sortorder'], 'DESC' ); ?>>
        <?php _e('DESC', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('items'); ?>">
        <?php _e('Max posts', 'intelliwidget'); ?>
        : </label>
      <input id="<?php echo $widget->get_field_id('items'); ?>" name="<?php echo $widget->get_field_name('items'); ?>" size="3" type="text" value="<?php echo esc_attr($instance['items']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('length'); ?>">
        <?php _e('Max words per post', 'intelliwidget'); ?>
        : </label>
      <input id="<?php echo $widget->get_field_id('length'); ?>" name="<?php echo $widget->get_field_name('length'); ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('allowed_tags'); ?>">
        <?php _e('Allowed HTML Elements', 'intelliwidget'); ?>
        <br/>
        <?php _e('(p, br, em, strong, etc.)', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('allowed_tags'); ?>" id="<?php echo $widget->get_field_id('allowed_tags'); ?>" type="text" value="<?php echo esc_attr($instance['allowed_tags']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('link_text'); ?>">
        <?php _e('"Read More" Text', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('link_text'); ?>" id="<?php echo $widget->get_field_id('link_text'); ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label for="<?php print $widget->get_field_id('imagealign'); ?>">
        <?php _e('Image Align', 'intelliwidget'); ?>
        : </label>
      <select name="<?php print $widget->get_field_name('imagealign'); ?>" id="<?php print $widget->get_field_id('imagealign'); ?>">
        <?php foreach ($intelliwidget->get_imagealign_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['imagealign'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="<?php print $widget->get_field_id('image_size'); ?>">
        <?php _e('Image Size', 'intelliwidget'); ?>
        : </label>
      <select id="<?php echo $widget->get_field_id('image_size'); ?>" name="<?php echo $widget->get_field_name('image_size'); ?>">
        <?php foreach ($intelliwidget->get_image_size_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['image_size'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
        <?php if (is_array($_wp_additional_image_sizes)): foreach ( $_wp_additional_image_sizes as $name => $size ) : ?>
        <option value="<?php echo $name; ?>" <?php selected($instance['image_size'], $name); ?> ><?php echo $name; ?> (<?php echo $size['width']; ?>x<?php echo $size['height']; ?>px)</option>
        <?php endforeach; endif;?>
      </select>
    </p>
  </div>
</div>
<?php
    }

    function widget_form_post_selection_settings($instance, $widget) { 
        global $intelliwidget; 
    ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('selection'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Post Selection Settings', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('selection'); ?>-panel-inside" style="display:none;padding:8px" class="iw-section-inside closed">
    <p>
      <label>
        <?php _e('Select from these Post Types', 'intelliwidget'); ?>
        :</label>
      <?php foreach ( $intelliwidget->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo $widget->get_field_id('post_types_' . $type); ?>">
        <input class="iw-widget-control"  type="checkbox" id="<?php echo $widget->get_field_id('post_types_' . $type); ?>" name="<?php echo $widget->get_field_name('post_types'); ?>[]" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
    <p>
      <label>
        <?php _e('Select by Category, Tag, etc...', 'intelliwidget');?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo $widget->get_field_name('category'); ?>[]" size="1" multiple="multiple" id="<?php echo $widget->get_field_id('category'); ?>">
        <?php echo $intelliwidget->get_relevant_terms($instance); ?>
      </select>
    </p>
    <p>
      <label>
        <?php _e('... -OR- select specific posts', 'intelliwidget');?>
        :</label>
      <select  class="widefat intelliwidget-multiselect" name="<?php echo $widget->get_field_name('page'); ?>[]"  multiple="multiple" id="<?php echo $widget->get_field_id('page'); ?>">
        <?php echo $intelliwidget->get_posts_list($instance); ?>
      </select>
    </p>
    <p>
      <label>
        <input name="<?php echo $widget->get_field_name('skip_post'); ?>" id="<?php echo $widget->get_field_id('skip_post'); ?>" type="checkbox" <?php checked($instance['skip_post'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Exclude current post', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input name="<?php echo $widget->get_field_name('future_only'); ?>" id="<?php echo $widget->get_field_id('future_only'); ?>" type="checkbox" <?php checked($instance['future_only'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Only future posts', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input name="<?php echo $widget->get_field_name('active_only'); ?>" id="<?php echo $widget->get_field_id('active_only'); ?>" type="checkbox" <?php checked($instance['active_only'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Exclude future posts', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label>
        <input name="<?php echo $widget->get_field_name('skip_expired'); ?>" id="<?php echo $widget->get_field_id('skip_expired'); ?>" type="checkbox" <?php checked($instance['skip_expired'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Exclude expired posts', 'intelliwidget'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }

}




