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
        add_action('intelliwidget_form_nav_menu',       array($this, 'widget_form_nav_menu'), 1, 3);
        add_action('intelliwidget_form_general',        array($this, 'widget_form_general_settings'), 2, 3);
        add_action('intelliwidget_form_general',        array($this, 'widget_form_custom_text_settings'), 3, 3);

        add_action('intelliwidget_form_post_list',      array($this, 'widget_form_post_list_settings'), 5, 3);
        add_action('intelliwidget_form_post_list',      array($this, 'widget_form_post_taxonomies_settings'), 10, 3);
        add_action('intelliwidget_form_post_list',      array($this, 'widget_form_specific_posts_settings'), 15, 3);
        add_action('intelliwidget_section_nav_menu',    array($this, 'section_form_nav_menu'), 1, 3);
        add_action('intelliwidget_section_all_before',     array($this, 'section_form_general_settings'), 2, 3);
        add_action('intelliwidget_section_post_list',   array($this, 'section_form_post_selection_settings'), 3, 3);
        add_action('intelliwidget_section_post_list',   array($this, 'section_form_appearance_settings'), 5, 3);
        add_action('intelliwidget_section_all_after',     array($this, 'section_form_addl_text_settings'), 10, 3);
    }

    function intelliwidget_form($instance, $widget) {
        global $intelliwidget;
        ?>

<p> <?php echo $intelliwidget->docsLink; ?>
  <label>
    <input name="<?php echo $widget->get_field_name('hide_if_empty'); ?>" id="<?php echo $widget->get_field_id('hide_if_empty'); ?>" type="checkbox" <?php checked($instance['hide_if_empty'], 1); ?> value="1"/>
    <?php _e('Placeholder Only (do not display)', 'intelliwidget'); ?>
  </label>
</p>
<p>
  <label for="<?php echo $widget->get_field_id('content'); ?>">
    <?php _e('Content', 'intelliwidget'); ?>
    : </label>
  <select class="iw-widget-control" id="<?php echo $widget->get_field_id('content'); ?>" name="<?php echo $widget->get_field_name('content'); ?>" autocomplete="off">
    <?php foreach ($intelliwidget->get_content_menu() as $value => $label): ?>
    <option value="<?php echo $value; ?>" <?php selected($instance['content'], $value); ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
  </select>
</p>
<?php // execute custom action hook for content value if it exists
            do_action('intelliwidget_form_all_before', $instance, $widget);
            do_action('intelliwidget_form_' . $instance['content'], $instance, $widget);
            do_action('intelliwidget_form_all_after', $instance, $widget);
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
  <div id="<?php echo $widget->get_field_id('generalsettings'); ?>-panel-inside" style="display:none;padding:8px" class="closed">
    <p>
      <label for="<?php echo $widget->get_field_id('title'); ?>"> <?php echo __('Widget', 'intelliwidget') . ' ' . __('Title', 'intelliwidget') . ' ' . __('(Optional)', 'intelliwidget'); ?>: </label>
      <label>
        <input name="<?php echo $widget->get_field_name('link_title'); ?>" id="<?php echo $widget->get_field_id('link_title'); ?>" type="checkbox" <?php checked($instance['link_title'], 1); ?> value="1" />
        <?php _e('Link', 'intelliwidget'); ?>
      </label>
      <br/>
      <input id="<?php echo $widget->get_field_id('title'); ?>" name="<?php echo $widget->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('container_id'); ?>">
        <?php _e('ID', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('container_id'); ?>" id="<?php echo $widget->get_field_id('container_id'); ?>" type="text" value="<?php echo esc_attr($instance['container_id']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('classes'); ?>">
        <?php _e('Classes', 'intelliwidget'); ?>
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
    <?php _e('WordPress Menu', 'intelliwidget'); ?>
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

    function widget_form_custom_text_settings($instance, $widget) { 
        global $intelliwidget; ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('customtext'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Additional Text/HTML', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('customtext'); ?>-panel-inside" style="display:none;padding:8px" class="closed">
    <p>
      <label for="<?php echo $widget->get_field_id('text_position'); ?>">
        <?php _e( 'Display', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo $widget->get_field_name('text_position'); ?>" id="<?php echo $widget->get_field_id('text_position'); ?>">
        <option value="">
        <?php _e('None', 'intelliwidget'); ?>
        </option>
        <option value="above"<?php selected( $instance['text_position'], 'above' ); ?>>
        <?php _e('Above Content', 'intelliwidget'); ?>
        </option>
        <option value="below"<?php selected( $instance['text_position'], 'below' ); ?>>
        <?php _e('Below Content', 'intelliwidget'); ?>
        </option>
        <option value="only"<?php selected( $instance['text_position'], 'only' ); ?>>
        <?php _e('Custom Text Only (No Content)', 'intelliwidget'); ?>
        </option>
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
        <?php _e('Auto-format Custom Text', 'intelliwidget'); ?>
      </label>
    </p>
  </div>
</div>
<?
    }

    function widget_form_post_list_settings($instance, $widget) { 
        global $intelliwidget, $_wp_additional_image_sizes;
        ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('listsettings'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Display Settings', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('listsettings'); ?>-panel-inside" style="display:none;padding:8px" class="closed">
    <p>
      <label>
        <?php _e('Post Types', 'intelliwidget'); ?>
        :</label>
      <?php foreach ( $intelliwidget->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo $widget->get_field_id('post_types_' . $type); ?>">
        <input type="checkbox" id="<?php echo $widget->get_field_id('post_types_' . $type); ?>" name="<?php echo $widget->get_field_name('post_types'); ?>[]" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
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
        <?php _e( 'Sort by', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo $widget->get_field_name('sortby'); ?>" id="<?php echo $widget->get_field_id('sortby'); ?>">
        <option value="date"<?php selected( $instance['sortby'], 'date' ); ?>>
        <?php _e('Post Date', 'intelliwidget'); ?>
        </option>
        <option value="event_date"<?php selected( $instance['sortby'], 'event_date' ); ?>>
        <?php _e('Start Date', 'intelliwidget'); ?>
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
    <p>
      <label for="<?php echo $widget->get_field_id('length'); ?>">
        <?php _e('Max words per post', 'intelliwidget'); ?>
        : </label>
      <input id="<?php echo $widget->get_field_id('length'); ?>" name="<?php echo $widget->get_field_name('length'); ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('allowed_tags'); ?>">
        <?php _e('Allowed HTML Elements', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('allowed_tags'); ?>" id="<?php echo $widget->get_field_id('allowed_tags'); ?>" type="text" value="<?php echo esc_attr($instance['allowed_tags']); ?>" />
    </p>
    <p>
      <label for="<?php echo $widget->get_field_id('link_text'); ?>">
        <?php _e('Link Text', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo $widget->get_field_name('link_text'); ?>" id="<?php echo $widget->get_field_id('link_text'); ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label for="<?php print $widget->get_field_id('imagealign'); ?>">
        <?php _e('Image Align', 'intelliwidget'); ?>
        : </label>
      <select name="<?php print $widget->get_field_name('imagealign'); ?>" id="<?php print $widget->get_field_id('imagealign'); ?>">
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
      <label for="<?php print $widget->get_field_id('image_size'); ?>">
        <?php _e('Image Size', 'intelliwidget'); ?>
        : </label>
      <select id="<?php echo $widget->get_field_id('image_size'); ?>" name="<?php echo $widget->get_field_name('image_size'); ?>">
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
  </div>
</div>
<?php
    }

    function widget_form_post_taxonomies_settings($instance, $widget) { 
        global $intelliwidget; 
    ?>
<div class="postbox">
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('taxonomies'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Post Taxonomies', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('taxonomies'); ?>-panel-inside" style="display:none;padding:8px" class="closed">
    <select class="widefat intelliwidget-multiselect" name="<?php echo $widget->get_field_name('category'); ?>[]" size="1" style="font-size:smaller;height:100px;" multiple="multiple" id="<?php echo $widget->get_field_id('category'); ?>">
      <?php echo $intelliwidget->get_relevant_terms($instance); ?>
    </select>
  </div>
</div>
<?php
    }

    function widget_form_specific_posts_settings($instance, $widget) { 
        global $intelliwidget; 
        ?>
<div class="postbox" >
  <div class="iw-collapsible" id="<?php echo $widget->get_field_id('specificposts'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 style="margin:0;padding:8px">
      <?php _e('Specific Posts', 'intelliwidget'); ?>
    </h4>
  </div>
  <div id="<?php echo $widget->get_field_id('specificposts'); ?>-panel-inside" style="display:none;padding:8px" class="closed">
    <select  class="widefat intelliwidget-multiselect" name="<?php echo $widget->get_field_name('page'); ?>[]" style="font-size:smaller;height:100px;" multiple="multiple" id="<?php echo $widget->get_field_id('page'); ?>">
      <?php echo $intelliwidget->get_posts($instance); ?>
    </select>
  </div>
</div>
<?php 
    }

    function page_form($post) {
        
        global $intelliwidget;
        $widget_page_id = get_post_meta($post->ID, '_intelliwidget_widget_page_id', true);
        echo $intelliwidget->docsLink; ?>
<p>
  <label for="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <?php _e('Use settings from', 'intelliwidget'); ?>
    : </label>
  <input name="save" class="iw-copy button button-large" id="iw_copy" value="<?php _e('Save', 'intelliwidget'); ?>" type="button" style="max-width:24%;float:right;clear:both;margin-top:4px" />
  <select style="width:75%" name="<?php echo 'intelliwidget_widget_page_id'; ?>" id="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <option value=""> <?php printf(__('This %s', 'intelliwidget'), ucfirst($post->post_type)); ?> </option>
    <?php echo $intelliwidget->get_posts(array('post_types' => array($post->post_type), 'page' => array($widget_page_id))); ?>
  </select>
</p>
<div class="iw-copy-container"> <span class="spinner" id="intelliwidget_spinner"></span> </div>
<a style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwadd=1&post=' . $post->ID), 'iwadd'); ?>" id="iw_add" class="iw-add">
<?php _e('+ Add New Section', 'intelliwidget'); ?>
</a>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
<?php
    }
    
    function post_form($post) {
        global $intelliwidget;
        $fields = $intelliwidget->get_custom_fields();
        $custom_data = get_post_custom($post->ID);
        $keys = array();
        foreach ($fields as $field):
            $key = 'intelliwidget_' . $field;
            $fields[$key] = empty($custom_data[$key]) ? '' : $custom_data[$key][0];
        endforeach;
?>
<p>
  <label for="intelliwidget_event_date">
    <?php _e('Start Date', 'intelliwidget');?>
    : <a href="#edit_timestamp" id="intelliwidget_event_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js">
    <?php _e('Edit', 'intelliwidget') ?>
    </a> <span id="intelliwidget_event_date_timestamp" class="intelliwidget-timestamp"> <?php echo $fields['intelliwidget_event_date'] ?></span></label>
  <input type="hidden" class="intelliwidget-input" id="intelliwidget_event_date" name="intelliwidget_event_date" value="<?php echo $fields['intelliwidget_event_date'] ?>" />
<div id="intelliwidget_event_date_div" class="intelliwidget-timestamp-div hide-if-js">
  <?php $this->intelliwidget_timestamp('intelliwidget_event_date', $fields['intelliwidget_event_date']); ?>
</div>
</p>
<p>
  <label for="intelliwidget_expire_date">
    <?php _e('Expire Date', 'intelliwidget');?>
    : <a href="#edit_timestamp" id="intelliwidget_expire_date-edit" class="intelliwidget-edit-timestamp hide-if-no-js">
    <?php _e('Edit', 'intelliwidget') ?>
    </a> <span id="intelliwidget_expire_date_timestamp" class="intelliwidget-timestamp"> <?php echo $fields['intelliwidget_expire_date']; ?></span></label>
  <input type="hidden" class="intelliwidget-input" id="intelliwidget_expire_date" name="intelliwidget_expire_date" value="<?php echo $fields['intelliwidget_expire_date'] ?>" />
<div id="intelliwidget_expire_date_div" class="intelliwidget-timestamp-div hide-if-js">
  <?php $this->intelliwidget_timestamp('intelliwidget_expire_date', $fields['intelliwidget_expire_date']); ?>
</div>
</p>
<p>
  <label for="intelliwidget_alt_title">
    <?php _e('Alt Title', 'intelliwidget');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_alt_title" name="intelliwidget_alt_title" value="<?php echo $fields['intelliwidget_alt_title'] ?>" />
</p>
<p>
  <label for="intelliwidget_external_url">
    <?php _e('External URL', 'intelliwidget');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_external_url" name="intelliwidget_external_url" value="<?php echo $fields['intelliwidget_external_url'] ?>" />
</p>
<p>
  <label for="intelliwidget_link_classes">
    <?php _e('Link Classes', 'intelliwidget');?>
    :</label>
  <input class="intelliwidget-input" type="text" id="intelliwidget_link_classes" name="intelliwidget_link_classes" value="<?php echo $fields['intelliwidget_link_classes'] ?>" />
</p>
<p>
  <label for="intelliwidget_link_target">
    <?php _e('Link Target', 'intelliwidget');?>
    :</label>
  <select class="intelliwidget-input" id="intelliwidget_link_target" name="intelliwidget_link_target">
    <option value=""<?php selected( $fields['intelliwidget_link_target'], '' ); ?>>None</option>
    <option value="_new"<?php selected( $fields['intelliwidget_link_target'], '_new' ); ?>>_new</option>
    <option value="_blank"<?php selected( $fields['intelliwidget_link_target'], '_blank' ); ?>>_blank</option>
    <option value="_self"<?php selected( $fields['intelliwidget_link_target'], '_self' ); ?>>_self</option>
    <option value="_top"<?php selected( $fields['intelliwidget_link_target'], '_top' ); ?>>_top</option>
  </select>
</p>
<div class="iw-cdf-container">
  <input name="save" class="iw-cdfsave button button-large" id="iw_cdfsave" value="<?php _e('Save Custom Fields', 'intelliwidget');?>" type="button" style="float:right" />
  <span class="spinner" id="intelliwidget_cpt_spinner"></span> </div>
<?php wp_nonce_field('iwpage_' . $post->ID,'iwpage'); ?>
<div style="clear:both"></div>
<?php
    }
    /**
     * Display timestamp edit fields for IntelliWidget
     *
     * @param <string> $field
     * @param <string> $post_date
     */
    function intelliwidget_timestamp($field = 'intelliwidget_event_date', $post_date = null) {
        global $wp_locale;

        $time_adj = current_time('timestamp');
        $jj = ($post_date) ? mysql2date( 'd', $post_date, false ) : gmdate( 'd', $time_adj );
        $mm = ($post_date) ? mysql2date( 'm', $post_date, false ) : gmdate( 'm', $time_adj );
        $aa = ($post_date) ? mysql2date( 'Y', $post_date, false ) : gmdate( 'Y', $time_adj );
        $hh = ($post_date) ? mysql2date( 'H', $post_date, false ) : gmdate( 'H', $time_adj );
        $mn = ($post_date) ? mysql2date( 'i', $post_date, false ) : gmdate( 'i', $time_adj );
        $ss = ($post_date) ? mysql2date( 's', $post_date, false ) : gmdate( 's', $time_adj );

        $cur_jj = gmdate( 'd', $time_adj );
        $cur_mm = gmdate( 'm', $time_adj );
        $cur_aa = gmdate( 'Y', $time_adj );
        $cur_hh = gmdate( 'H', $time_adj );
        $cur_mn = gmdate( 'i', $time_adj );

        $month = '<select id="'.$field.'_mm" name="'.$field.'_mm" class="intelliwidget-mm">' ."\n";
        for ( $i = 1; $i < 13; $i = $i +1 ) {
            $monthnum = zeroise($i, 2);
            $month .= "            " . '<option value="' . $monthnum . '"';
            if ( $i == $mm )
                $month .= ' selected="selected"';
                /* translators: 1: month number (01, 02, etc.), 2: month abbreviation */
            $month .= '>' . $wp_locale->get_month_abbrev( $wp_locale->get_month( $i ) ) . "</option>\n";
        }
        $month .= '</select>';

        $day = '<input type="text" id="'.$field.'_jj" class="intelliwidget-jj" name="'.$field.'_jj" value="' . $jj . '" size="2" maxlength="2" autocomplete="off" />';
        $year = '<input type="text" id="'.$field.'_aa" class="intelliwidget-aa" name="'.$field.'_aa" value="' . $aa . '" size="4" maxlength="4" autocomplete="off" />';
        $hour = '<input type="text" id="'.$field.'_hh" class="intelliwidget-hh" name="'.$field.'_hh" value="' . $hh . '" size="2" maxlength="2" autocomplete="off" />';
        $minute = '<input type="text" id="'.$field.'_mn" class="intelliwidget-mn" name="'.$field.'_mn" value="' . $mn . '" size="2" maxlength="2" autocomplete="off" />';

        echo '<div class="timestamp-wrap">';
        /* translators: 1: month input, 2: day input, 3: year input, 4: hour input, 5: minute input */
        printf(__('%1$s%2$s, %3$s @ %4$s : %5$s', 'intelliwidget'), $month, $day, $year, $hour, $minute);

        echo '</div><input type="hidden" id="'.$field.'_ss" name="'.$field.'_ss" value="' . $ss . '" />';

        echo "\n\n";
        foreach ( array('mm', 'jj', 'aa', 'hh', 'mn') as $timeunit ) {
            echo '<input type="hidden" id="'.$field.'_hidden_' . $timeunit . '" name="'.$field.'_hidden_' . $timeunit . '" value="' . (($post_date) ? $$timeunit : '') . '" />' . "\n";
            $cur_timeunit = 'cur_' . $timeunit;
            echo '<input type="hidden" id="'. $field . '_' . $cur_timeunit . '" name="'. $field . '_' . $cur_timeunit . '" value="' . $$cur_timeunit . '" />' . "\n";
        }
?>
<p> <a href="#edit_timestamp" id="<?php echo $field; ?>-save" class="intelliwidget-save-timestamp hide-if-no-js button">
  <?php _e('OK', 'intelliwidget'); ?>
  </a> <a href="#edit_timestamp" id="<?php echo $field; ?>-clear" class="intelliwidget-clear-timestamp hide-if-no-js button">
  <?php _e('Clear', 'intelliwidget'); ?>
  </a> <a href="#edit_timestamp" id="<?php echo $field; ?>-cancel" class="intelliwidget-cancel-timestamp hide-if-no-js">
  <?php _e('Cancel', 'intelliwidget'); ?>
  </a> </p>
<?php
    }

    function intelliwidget_section($post_id, $box_id, $instance) {
        global $wp_registered_sidebars;
        ?>
    <p>
      <input type="hidden" id="<?php echo 'intelliwidget_' . $box_id . '_box_id'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_box_id'; ?>" value="<?php echo $box_id; ?>" />
      <label for="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>">
        <?php _e( 'Use these settings to replace', 'intelliwidget'); ?>
        : </label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>">
        <option value="none"<?php selected( $instance['replace_widget'], 'none' ); ?>>
        <?php _e('None selected', 'intelliwidget');?>
        </option>
        <option value="content"<?php selected( $instance['replace_widget'], 'content' ); ?>>
        <?php _e('Shortcode (use tab number)', 'intelliwidget');?>
        </option>
        <?php 
    foreach(wp_get_sidebars_widgets() as $sidebar_id => $sidebar_widgets): 
        if (false === strpos($sidebar_id, 'wp_inactive') && false === strpos($sidebar_id, 'orphaned')):
            $count = 0;
            foreach ($sidebar_widgets as $sidebar_widget_id):
                if (false !== strpos($sidebar_widget_id, 'intelliwidget') ):
?>
        <option value="<?php echo $sidebar_widget_id; ?>"<?php selected( $instance['replace_widget'], $sidebar_widget_id ); 
        ?>> <?php echo $wp_registered_sidebars[$sidebar_id]['name'] . ' [' . ++$count . ']'; ?> </option>
        <?php 
                endif; 
            endforeach; 
        endif; 
    endforeach;
?>
      </select>
    </p>
<?php
    // execute custom action hook for content value if it exists
            do_action('intelliwidget_section_all_before', $post_id, $box_id, $instance);
            do_action('intelliwidget_section_' . $instance['content'], $post_id, $box_id, $instance);
            do_action('intelliwidget_section_all_after', $post_id, $box_id, $instance); ?>
<div class="iw-save-container">
  <input name="save" class="button button-large iw-save" id="<?php echo 'intelliwidget_' . $box_id . '_save'; ?>" value="<?php _e('Save Settings', 'intelliwidget'); ?>" type="button" style="float:right">
  <span class="spinner" id="<?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span> </div>
<a style="float:left;" href="<?php echo wp_nonce_url(admin_url('post.php?action=edit&iwdelete='.$box_id.'&post=' . $post_id), 'iwdelete'); ?>" id="iw_delete_<?php echo $box_id; ?>" class="iw-delete">
<?php _e('Delete', 'intelliwidget'); ?>
</a>
<div style="clear:both"></div>
<?php
    }
    
    function section_form_general_settings($post_id, $box_id, $instance) {
        global $intelliwidget;
        ?>
<div id="iw-generalsettings-<?php echo $box_id; ?>" class="postbox closed">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3><span>
    <?php _e('General Settings', 'intelliwidget'); ?>
    </span></h3>
  <div  id="iw-generalsettings-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>">
        <?php _e('IntelliWidget Type', 'intelliwidget'); ?>
        : </label>
      <select class="iw-control" id="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>" autocomplete="off">
        <?php foreach ($intelliwidget->get_content_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['content'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>"> <?php echo __('Section', 'intelliwidget') . ' ' . __('Title', 'intelliwidget') . ' ' . __('(Optional)', 'intelliwidget'); ?> </label>
      <label>
        <input name="<?php echo 'intelliwidget_' . $box_id . '_link_title'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_link_title'; ?>" type="checkbox" <?php checked($instance['link_title'], 1); ?> value="1" />
        <?php _e('Link', 'intelliwidget'); ?>
      </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" style="display:block">
        <?php _e('Container ID', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" type="text" value="<?php echo esc_attr($instance['container_id']); ?>" />
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" style="display:block">
        <?php _e('Style Classes', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" type="text" value="<?php echo esc_attr($instance['classes']); ?>" />
    </p>
    <p>
      <label>
        <input name="<?php echo 'intelliwidget_' . $box_id . '_nocopy'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_nocopy'; ?>" type="checkbox" <?php checked($instance['nocopy'], 1); ?> value="1"/>
        <?php _e('Use these settings even if using settings from another page/post', 'intelliwidget'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }
    
    function section_form_addl_text_settings($post_id, $box_id, $instance) {
        global $intelliwidget;
        ?>
<div id="iw-addltext-<?php echo $box_id; ?>" class="postbox closed">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3><span>
    <?php _e('Additional Text/HTML', 'intelliwidget'); ?>
    </span></h3>
  <div  id="iw-addltext-<?php echo $box_id; ?>-inside" class="inside">
    <select name="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>">
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
      <?php _e('This text only (no posts)', 'intelliwidget'); ?>
      </option>
    </select>
    <textarea class="widefat" rows="3" cols="20" id="<?php echo 'intelliwidget_' . $box_id . '_custom_text'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_custom_text'; ?>">
<?php echo esc_textarea($instance['custom_text']); ?></textarea>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>" type="checkbox" <?php checked($instance['filter'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Automatically add paragraphs', 'intelliwidget'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }
    
    function section_form_appearance_settings($post_id, $box_id, $instance) {
        global $intelliwidget, $_wp_additional_image_sizes;
        ?>
<div id="iw-appearance-<?php echo $box_id; ?>" class="postbox closed">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3><span>
    <?php _e('Appearance Settings', 'intelliwidget'); ?>
    </span></h3>
  <div id="iw-appearance-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>">
        <?php _e('Template', 'intelliwidget'); ?>
        :</label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>">
        <?php foreach ( $intelliwidget->templates as $template => $name ) : ?>
        <option value="<?php echo $template; ?>" <?php selected($instance['template'], $template); ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>">
        <?php _e( 'Sort Posts by', 'intelliwidget'); ?>
        : </label>
      <br/>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>">
        <option value="date"<?php selected( $instance['sortby'], 'date' ); ?>>
        <?php _e('Post Date', 'intelliwidget'); ?>
        </option>
        <option value="event_date"<?php selected( $instance['sortby'], 'event_date' ); ?>>
        <?php _e('Start Date', 'intelliwidget'); ?>
        </option>
        <option value="menu_order"<?php selected( $instance['sortby'], 'menu_order' ); ?>>
        <?php _e('Menu Order', 'intelliwidget'); ?>
        </option>
        <option value="title"<?php selected( $instance['sortby'], 'title' ); ?>>
        <?php _e('Title', 'intelliwidget'); ?>
        </option>
        <option value="rand"<?php selected( $instance['sortby'], 'rand' ); ?>>
        <?php _e( 'Random Order', 'intelliwidget'); ?>
        </option>
      </select>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_sortorder'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_sortorder'; ?>">
        <option value="ASC"<?php selected( $instance['sortorder'], 'ASC' ); ?>>
        <?php _e('ASC', 'intelliwidget'); ?>
        </option>
        <option value="DESC"<?php selected( $instance['sortorder'], 'DESC' ); ?>>
        <?php _e('DESC', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>">
        <?php _e('Max posts', 'intelliwidget'); ?>
        : </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>" size="3" type="text" value="<?php echo esc_attr($instance['items']); ?>" />
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>">
        <?php _e('Max words per post', 'intelliwidget'); ?>
        : </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>">
        <?php _e('Allowed HTML Elements', 'intelliwidget'); ?><br/><?php _e('(p, br, em, strong, etc.)', 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>" type="text" value="<?php echo esc_attr($instance['allowed_tags']); ?>" />
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>">
        <?php _e("'Read More' Text", 'intelliwidget'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>">
        <?php _e('Image Align', 'intelliwidget'); ?>
      </label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>">
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
      <label for="<?php echo 'intelliwidget_' . $box_id . '_image_size'; ?>">
        <?php _e('Image Size', 'intelliwidget'); ?>
        : </label>
      <select id="<?php echo 'intelliwidget_' . $box_id . '_image_size'; ?>" name="<?php  echo 'intelliwidget_' . $box_id . '_image_size'; ?>">
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
        <?php endforeach; endif; ?>
      </select>
    </p>
  </div>
</div>
<?php
    }
    
    function section_form_post_selection_settings($post_id, $box_id, $instance) {
        global $intelliwidget;
        ?>
<div id="iw-selection-<?php echo $box_id; ?>" class="postbox closed">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3><span>
    <?php _e('Post Selection Settings', 'intelliwidget'); ?>
    </span></h3>
  <div id="iw-selection-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label>
        <?php _e('Select from these Post Types', 'intelliwidget'); ?>
        :</label>
      <?php foreach ( $intelliwidget->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo 'intelliwidget_' . $box_id . '_post_types_' . $type; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_post_types_' . $type; ?>" type="checkbox" name="<?php echo 'intelliwidget_' . $box_id . '_post_types[]'; ?>" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
    <p>
      <label>
        <?php _e('Select by Category, Tag, etc...', 'intelliwidget');?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo 'intelliwidget_' . $box_id . '_category'; ?>[]" size="1" style="height:100px;" multiple="multiple" id="<?php echo 'intelliwidget_' . $box_id . '_category'; ?>">
        <?php echo $intelliwidget->get_relevant_terms($instance); ?>
      </select>
    </p>
    <p>
      <label>
        <?php _e('... -OR- select specific posts', 'intelliwidget');?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo 'intelliwidget_' . $box_id . '_page'; ?>[]" size="1" style="height:100px;" multiple="multiple" id="<?php echo 'intelliwidget_' . $box_id . '_page'; ?>">
        <?php echo $intelliwidget->get_posts($instance); ?>
      </select>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>" type="checkbox" <?php checked($instance['skip_post'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Exclude current post', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>" type="checkbox" <?php checked($instance['future_only'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Include only future posts', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>" type="checkbox" <?php checked($instance['active_only'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Exclude future posts', 'intelliwidget'); ?>
      </label>
    </p>
    <p>
      <label for="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>" type="checkbox" <?php checked($instance['skip_expired'], 1); ?> value="1" />
        &nbsp;
        <?php _e('Exclude expired posts', 'intelliwidget'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }

    function section_form_nav_menu($post_id, $box_id, $instance){
        global $intelliwidget;
        ?>
<p>
  <label for="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>">
    <?php _e('WordPress Menu', 'intelliwidget'); ?>
    : </label>
  <select id="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>">
    <option value="" <?php selected( $instance['nav_menu'], '' ); ?>>
    <?php _e('None', 'intelliwidget'); ?>
    </option>
    <option value="-1" <?php selected( $instance['nav_menu'], '-1' ); ?>>
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
}



