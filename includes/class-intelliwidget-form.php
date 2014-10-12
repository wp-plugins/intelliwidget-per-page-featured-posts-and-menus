<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-form.php - Outputs widget form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
class IntelliWidgetForm {
    
    function __construct() {
        add_action('intelliwidget_form_all_before', array($this, 'general_settings'), 10, 4);
        add_action('intelliwidget_form_post_list',  array($this, 'post_selection_settings'), 5, 4);
        add_action('intelliwidget_form_post_list',  array($this, 'appearance_settings'), 10, 4);
        add_action('intelliwidget_form_nav_menu',   array($this, 'nav_menu'), 10, 4);
        add_action('intelliwidget_form_tax_menu',   array($this, 'tax_menu'), 10, 4);
        add_action('intelliwidget_form_all_after',  array($this, 'addl_text_settings'), 10, 4);
        if (isset($_POST['widget-id'])) add_action('intelliwidget_post_selection_menus', array($this, 'post_selection_menus'), 10, 4);
    }

    function render_form($adminobj, $widgetobj, $instance) {
        ?>
<p>  <input type="hidden" id="<?php echo $widgetobj->get_field_id('category'); ?>" name="<?php echo $widgetobj->get_field_name('category'); ?>" value="-1" /><?php /* Original Categories: <?php echo implode(',', $adminobj->val2array($instance['category'])); */ ?>
</p>
<p> <?php echo $adminobj->docsLink; ?>
  <label title="<?php echo $adminobj->get_tip('hide_if_empty'); ?>">
    <input class="iw-widget-control" name="<?php echo $widgetobj->get_field_name('hide_if_empty'); ?>" id="<?php echo $widgetobj->get_field_id('hide_if_empty'); ?>" type="checkbox" <?php checked($instance['hide_if_empty'], 1); ?> value="1"/><?php echo $adminobj->get_label('hide_if_empty'); ?>  </label>
</p>
<?php // execute custom action hook for content value if it exists
        if (empty($instance['hide_if_empty'])):
            do_action('intelliwidget_form_all_before', $adminobj, $widgetobj, $instance);
            do_action('intelliwidget_form_' . $instance['content'], $adminobj, $widgetobj, $instance);
            do_action('intelliwidget_form_all_after', $adminobj, $widgetobj, $instance);
        endif;
    }

    function general_settings($adminobj, $widgetobj, $instance) { 
        ?>
<div class="postbox iw-collapsible closed panel-general" id="<?php echo $widgetobj->get_field_id('generalsettings'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 title="<?php echo $adminobj->get_tip('generalsettings'); ?>">
      <?php echo $adminobj->get_label('generalsettings'); ?>
    </h4>
  <div id="<?php echo $widgetobj->get_field_id('generalsettings'); ?>-panel-inside" class="inside">
    <p>
      <label title="<?php echo $adminobj->get_tip('content');?>" for="<?php echo $widgetobj->get_field_id('content'); ?>">
        <?php echo $adminobj->get_label('content') ?>
        : </label>
      <select class="iw-widget-control" id="<?php echo $widgetobj->get_field_id('content'); ?>" name="<?php echo $widgetobj->get_field_name('content'); ?>" autocomplete="off">
        <?php foreach ($adminobj->get_content_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['content'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('title');?>" for="<?php echo $widgetobj->get_field_id('title'); ?>"> <?php echo $adminobj->get_label('title'); ?>: </label>
      <label title="<?php echo $adminobj->get_tip('link_title');?>">
        <input name="<?php echo $widgetobj->get_field_name('link_title'); ?>" id="<?php echo $widgetobj->get_field_id('link_title'); ?>" type="checkbox" <?php checked($instance['link_title'], 1); ?> value="1" />
        <?php echo $adminobj->get_label('link_title'); ?>
      </label>
      <label title="<?php echo $adminobj->get_tip('hide_title');?>">
        <input name="<?php echo $widgetobj->get_field_name('hide_title'); ?>" id="<?php echo $widgetobj->get_field_id('hide_title'); ?>" type="checkbox" <?php checked($instance['hide_title'], 1); ?> value="1" />
        <?php echo $adminobj->get_label('hide_title'); ?>
      </label>
      <br/>
      <input id="<?php echo $widgetobj->get_field_id('title'); ?>" name="<?php echo $widgetobj->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('container_id');?>" for="<?php echo $widgetobj->get_field_id('container_id'); ?>">
        <?php echo $adminobj->get_label('container_id'); ?>
        : </label>
      <input name="<?php echo $widgetobj->get_field_name('container_id'); ?>" id="<?php echo $widgetobj->get_field_id('container_id'); ?>" type="text" value="<?php echo esc_attr($instance['container_id']); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('classes');?>" for="<?php echo $widgetobj->get_field_id('classes'); ?>">
        <?php echo $adminobj->get_label('classes'); ?>
        : </label>
      <input name="<?php echo $widgetobj->get_field_name('classes'); ?>" id="<?php echo $widgetobj->get_field_id('classes'); ?>" type="text" value="<?php echo esc_attr($instance['classes']); ?>" />
    </p>
  </div>
</div>
<?php
    }

    function addl_text_settings($adminobj, $widgetobj, $instance) { 
         ?>
<div class="postbox iw-collapsible closed panel-addltext" id="<?php echo $widgetobj->get_field_id('addltext'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 title="<?php echo $adminobj->get_tip('addltext');?>">
      <?php echo $adminobj->get_label('addltext'); ?>
    </h4>
  <div id="<?php echo $widgetobj->get_field_id('addltext'); ?>-panel-inside" class="inside">
    <p>
      <label title="<?php echo $adminobj->get_tip('text_position');?>" for="<?php echo $widgetobj->get_field_id('text_position'); ?>">
        <?php echo $adminobj->get_label('text_position'); ?>
        : </label>
      <select name="<?php echo $widgetobj->get_field_name('text_position'); ?>" id="<?php echo $widgetobj->get_field_id('text_position'); ?>">
        <?php foreach ($adminobj->get_text_position_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['text_position'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <textarea class="widefat" rows="3" cols="20" id="<?php echo $widgetobj->get_field_id('custom_text'); ?>" 
name="<?php echo $widgetobj->get_field_name('custom_text'); ?>">
<?php echo esc_textarea($instance['custom_text']); ?></textarea>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('filter');?>">
        <input id="<?php echo $widgetobj->get_field_id('filter'); ?>" name="<?php echo $widgetobj->get_field_name('filter'); ?>" type="checkbox" <?php checked($instance['filter'], 1); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label('filter'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }

    function appearance_settings($adminobj, $widgetobj, $instance) { 
        global $_wp_additional_image_sizes;
        ?>
<div class="postbox iw-collapsible closed panel-appearance" id="<?php echo $widgetobj->get_field_id('appearance'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 title="<?php echo $adminobj->get_tip('appearance');?>">
      <?php echo $adminobj->get_label('appearance'); ?>
    </h4>
  <div id="<?php echo $widgetobj->get_field_id('appearance'); ?>-panel-inside" class="inside">
    <p>
      <label title="<?php echo $adminobj->get_tip('sortby');?>" for="<?php echo $widgetobj->get_field_id('sortby'); ?>">
        <?php echo $adminobj->get_label('sortby'); ?>
        : </label>
      <select name="<?php echo $widgetobj->get_field_name('sortby'); ?>" id="<?php echo $widgetobj->get_field_id('sortby'); ?>">
        <?php foreach ($adminobj->get_sortby_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['sortby'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
      <select name="<?php echo $widgetobj->get_field_name('sortorder'); ?>" id="<?php echo $widgetobj->get_field_id('sortorder'); ?>">
        <option value="ASC"<?php selected( $instance['sortorder'], 'ASC' ); ?>>
        <?php _e('ASC', 'intelliwidget'); ?>
        </option>
        <option value="DESC"<?php selected( $instance['sortorder'], 'DESC' ); ?>>
        <?php _e('DESC', 'intelliwidget'); ?>
        </option>
      </select>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('items');?>" for="<?php echo $widgetobj->get_field_id('items'); ?>">
        <?php echo $adminobj->get_label('items'); ?>
        : </label>
      <input id="<?php echo $widgetobj->get_field_id('items'); ?>" name="<?php echo $widgetobj->get_field_name('items'); ?>" size="3" type="text" value="<?php echo esc_attr($instance['items']); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('length');?>" for="<?php echo $widgetobj->get_field_id('length'); ?>">
        <?php echo $adminobj->get_label('length'); ?>
        : </label>
      <input id="<?php echo $widgetobj->get_field_id('length'); ?>" name="<?php echo $widgetobj->get_field_name('length'); ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('allowed_tags');?>" for="<?php echo $widgetobj->get_field_id('allowed_tags'); ?>">
        <?php echo $adminobj->get_label('allowed_tags'); ?>
        : </label>
      <input name="<?php echo $widgetobj->get_field_name('allowed_tags'); ?>" id="<?php echo $widgetobj->get_field_id('allowed_tags'); ?>" type="text" value="<?php echo esc_attr($instance['allowed_tags']); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('link_text');?>" for="<?php echo $widgetobj->get_field_id('link_text'); ?>">
        <?php echo $adminobj->get_label('link_text'); ?>
        : </label>
      <input name="<?php echo $widgetobj->get_field_name('link_text'); ?>" id="<?php echo $widgetobj->get_field_id('link_text'); ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('imagealign');?>" for="<?php print $widgetobj->get_field_id('imagealign'); ?>">
        <?php echo $adminobj->get_label('imagealign'); ?>
        : </label>
      <select name="<?php print $widgetobj->get_field_name('imagealign'); ?>" id="<?php print $widgetobj->get_field_id('imagealign'); ?>">
        <?php foreach ($adminobj->get_imagealign_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['imagealign'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('image_size');?>" for="<?php print $widgetobj->get_field_id('image_size'); ?>">
        <?php echo $adminobj->get_label('image_size'); ?>
        : </label>
      <select id="<?php echo $widgetobj->get_field_id('image_size'); ?>" name="<?php echo $widgetobj->get_field_name('image_size'); ?>">
        <?php foreach ($adminobj->get_image_size_menu() as $value => $label): ?>
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

    function post_selection_settings($adminobj, $widgetobj, $instance) { 
         
    ?>
<div class="postbox iw-collapsible closed panel-selection" id="<?php echo $widgetobj->get_field_id('selection'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
    <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
    <h4 title="<?php echo $adminobj->get_tip('selection');?>">
      <?php echo $adminobj->get_label('selection'); ?>
    </h4>
  <div id="<?php echo $widgetobj->get_field_id('selection'); ?>-panel-inside" class="inside">
    <p>
      <label title="<?php echo $adminobj->get_tip('template');?>" for="<?php echo $widgetobj->get_field_id('template'); ?>">
        <?php echo $adminobj->get_label('template'); ?>
        :</label>
      <select name="<?php echo $widgetobj->get_field_name('template'); ?>" id="<?php echo $widgetobj->get_field_id('template'); ?>">
        <?php foreach ( $adminobj->templates as $template => $name ) : ?>
        <option value="<?php echo $template; ?>" <?php selected($instance['template'], $template); ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('post_types');?>" style="display:block">
        <?php echo $adminobj->get_label('post_types'); ?>
        :</label>
      <?php foreach ( $adminobj->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo $widgetobj->get_field_id('post_types_' . $type); ?>">
        <input class="iw-widget-control"  type="checkbox" id="<?php echo $widgetobj->get_field_id('post_types_' . $type); ?>" name="<?php echo $widgetobj->get_field_name('post_types'); ?>[]" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
    <div id="<?php echo $widgetobj->get_field_id('menus'); ?>">
<?php  /*
        * this has been moved to its own method: post_selection_menus()
        */
         do_action('intelliwidget_post_selection_menus', $adminobj, $widgetobj, $instance);
        
?>
    </div>
    <p>
      <label title="<?php echo $adminobj->get_tip('skip_post');?>">
        <input name="<?php echo $widgetobj->get_field_name('skip_post'); ?>" id="<?php echo $widgetobj->get_field_id('skip_post'); ?>" type="checkbox" <?php checked($instance['skip_post'], 1); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label('skip_post'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('future_only');?>">
        <input name="<?php echo $widgetobj->get_field_name('future_only'); ?>" id="<?php echo $widgetobj->get_field_id('future_only'); ?>" type="checkbox" <?php checked($instance['future_only'], 1); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label('future_only'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('active_only');?>">
        <input name="<?php echo $widgetobj->get_field_name('active_only'); ?>" id="<?php echo $widgetobj->get_field_id('active_only'); ?>" type="checkbox" <?php checked($instance['active_only'], 1); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label('active_only'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('skip_expired');?>">
        <input name="<?php echo $widgetobj->get_field_name('skip_expired'); ?>" id="<?php echo $widgetobj->get_field_id('skip_expired'); ?>" type="checkbox" <?php checked($instance['skip_expired'], 1); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label('skip_expired'); ?>
      </label>
    </p>
<?php if (current_user_can('read_private_posts')): ?>
    <p>
      <label title="<?php echo $adminobj->get_tip('include_private');?>">
        <input name="<?php echo $widgetobj->get_field_name('include_private'); ?>" id="<?php echo $widgetobj->get_field_id('include_private'); ?>" type="checkbox" <?php checked($instance['include_private'], 1); ?> value="1" />
        &nbsp;
        <?php echo $adminobj->get_label('include_private'); ?>
      </label>
    </p>   
<?php endif; ?> 
  </div>
</div>
<?php
    }

    function post_selection_menus($adminobj, $widgetobj, $instance) {
        // convert legacy category to taxonomies
        if (empty($instance['terms']) && isset($instance['category']) && '-1' != $instance['category'])
            $instance['terms'] = $adminobj->map_category_to_tax($instance['category']);
       
?>
<input type="hidden" name="<?php echo $widgetobj->get_field_name('page_multi'); ?>" id="<?php echo $widgetobj->get_field_name('page_multi'); ?>" value="1" />
<input type="hidden" name="<?php echo $widgetobj->get_field_name('terms_multi'); ?>" id="<?php echo $widgetobj->get_field_name('terms_multi'); ?>" value="1" />
    <p>
      <label title="<?php echo $adminobj->get_tip('page');?>">
        <?php echo $adminobj->get_label('page');?>
        :</label>
      <select  class="widefat intelliwidget-multiselect" name="<?php echo $widgetobj->get_field_name('page'); ?>[]"  multiple="multiple" id="<?php echo $widgetobj->get_field_id('page'); ?>">
        <?php echo $adminobj->get_posts_list($instance); ?>
      </select>
    </p> 
    <p>
      <label title="<?php echo $adminobj->get_tip('terms');?>">
        <?php echo $adminobj->get_label('terms');?>
      </label>
      <select name="<?php echo $widgetobj->get_field_name('allterms'); ?>" id="<?php echo $widgetobj->get_field_id('allterms'); ?>">
        <option value="0"<?php if (isset($instance['allterms'])) selected( $instance['allterms'], 0 ); ?>>
        <?php _e('any', 'intelliwidget'); ?>
        </option>
        <option value="1"<?php if (isset($instance['allterms'])) selected( $instance['allterms'], 1 ); ?>>
        <?php _e('all', 'intelliwidget'); ?>
        </option>
      </select>
      <label title="<?php echo $adminobj->get_tip('allterms'); ?>">
        <?php echo $adminobj->get_label('allterms'); ?>
        :</label>
      <select class="widefat intelliwidget-multiselect" name="<?php echo $widgetobj->get_field_name('terms'); ?>[]" size="1" multiple="multiple" id="<?php echo $widgetobj->get_field_id('terms'); ?>">
        <?php echo $adminobj->get_terms_list($instance); ?>
      </select>
    </p>
<?php
    }
    function nav_menu($adminobj, $widgetobj, $instance) { 
        ?>
<p>
  <label title="<?php echo $adminobj->get_tip('nav_menu');?>" for="<?php echo $widgetobj->get_field_id('nav_menu'); ?>">
    <?php echo $adminobj->get_label('nav_menu'); ?>
    : </label>
  <select id="<?php echo $widgetobj->get_field_id('nav_menu'); ?>" name="<?php echo $widgetobj->get_field_name('nav_menu'); ?>">
            <?php
            // Get menus
            foreach ( $adminobj->get_nav_menu() as $value => $label ): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['nav_menu'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach;?>
  </select>
</p>
<?php 
    }

    function tax_menu($adminobj, $widgetobj, $instance) { 
        ?>
<div class="postbox iw-collapsible closed panel-addltext" id="<?php echo $widgetobj->get_field_id('taxmenusettings'); ?>-panel" title="<?php _e('Click to toggle', 'intelliwidget'); ?>">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h4 title="<?php echo $adminobj->get_tip('taxmenusettings');?>"> <?php echo $adminobj->get_label('taxmenusettings'); ?> </h4>
  <div id="<?php echo $widgetobj->get_field_id('taxmenusettings'); ?>-panel-inside" class="inside">
    <p>
      <label title="<?php echo $adminobj->get_tip('taxonomy');?>" for="<?php echo $widgetobj->get_field_id('taxonomy'); ?>"> <?php echo $adminobj->get_label('taxonomy'); ?> : </label>
      <select id="<?php echo $widgetobj->get_field_id('taxonomy'); ?>" name="<?php echo $widgetobj->get_field_name('taxonomy'); ?>">
        <?php
            // Get menus
            foreach ( $adminobj->get_tax_menu() as $value => $label ): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['taxonomy'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach;?>
      </select>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('sortby_terms'); ?>"> <?php echo $adminobj->get_label('sortby_terms'); ?> : </label>
      <br/>
      <select name="<?php echo $widgetobj->get_field_name('sortby'); ?>" id="<?php echo $widgetobj->get_field_id('sortby'); ?>">
        <?php foreach ($adminobj->get_tax_sortby_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['sortby'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('show_count');?>">
        <input name="<?php echo $widgetobj->get_field_name('show_count'); ?>" id="<?php echo $widgetobj->get_field_id('show_count'); ?>" type="checkbox" <?php checked($instance['show_count'], 1); ?> value="1" />
        &nbsp; <?php echo $adminobj->get_label('show_count'); ?> </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('hierarchical');?>">
        <input name="<?php echo $widgetobj->get_field_name('hierarchical'); ?>" id="<?php echo $widgetobj->get_field_id('hierarchical'); ?>" type="checkbox" <?php checked($instance['hierarchical'], 1); ?> value="1" />
        &nbsp; <?php echo $adminobj->get_label('hierarchical'); ?> </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('current_only_all');?>">
        <input name="<?php echo $widgetobj->get_field_name('current_only'); ?>" id="<?php echo $widgetobj->get_field_id('current_only_all'); ?>" type="radio" <?php checked($instance['current_only'], 0); ?> value="0" />
        &nbsp; <?php echo $adminobj->get_label('current_only_all'); ?> </label><br/>
      <label title="<?php echo $adminobj->get_tip('current_only_cur');?>">
        <input name="<?php echo $widgetobj->get_field_name('current_only'); ?>" id="<?php echo $widgetobj->get_field_id('current_only_cur'); ?>" type="radio" <?php checked($instance['current_only'], 1); ?> value="1" />
        &nbsp; <?php echo $adminobj->get_label('current_only_cur'); ?> </label><br/>
      <label title="<?php echo $adminobj->get_tip('current_only_sub');?>">
        <input name="<?php echo $widgetobj->get_field_name('current_only'); ?>" id="<?php echo $widgetobj->get_field_id('current_only_sub'); ?>" type="radio" <?php checked($instance['current_only'], 2); ?> value="2" />
        &nbsp; <?php echo $adminobj->get_label('current_only_sub'); ?> </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('show_descr');?>">
        <input name="<?php echo $widgetobj->get_field_name('show_descr'); ?>" id="<?php echo $widgetobj->get_field_id('show_descr'); ?>" type="checkbox" <?php checked($instance['show_descr'], 1); ?> value="1" />
        &nbsp; <?php echo $adminobj->get_label('show_descr'); ?> </label>
    </p>
    <p>
      <label title="<?php echo $adminobj->get_tip('hide_empty');?>">
        <input name="<?php echo $widgetobj->get_field_name('hide_empty'); ?>" id="<?php echo $widgetobj->get_field_id('hide_empty'); ?>" type="checkbox" <?php checked($instance['hide_empty'], 1); ?> value="1" />
        &nbsp; <?php echo $adminobj->get_label('hide_empty'); ?> </label>
    </p>
  </div>
</div>
<?php 
    }

}




