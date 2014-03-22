<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-metabox.php - Outputs meta box form
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */
 
class IntelliWidgetMetaBox {
    function __construct() {
        add_action('intelliwidget_metabox_nav_menu',    array($this, 'nav_menu'), 1, 3);
        add_action('intelliwidget_metabox_post_list',   array($this, 'post_selection_settings'), 10, 3);
        add_action('intelliwidget_metabox_post_list',   array($this, 'appearance_settings'), 5, 3);
        add_action('intelliwidget_metabox_all_after',   array($this, 'addl_text_settings'), 5, 3);
        add_action('intelliwidget_metabox_all_after',   array($this, 'general_settings'), 10, 3);
    }

    function copy_form($id, $id_list) {
        
        global $intelliwidget_admin;
        echo $intelliwidget_admin->docsLink; ?>

<p>
  <label title="<?php echo $intelliwidget_admin->get_tip('widget_page_id');?>" for="<?php echo 'intelliwidget_widget_page_id'; ?>">
    <?php echo $intelliwidget_admin->get_label('widget_page_id'); ?>
    : </label>
  <input name="save" class="iw-copy button button-large" id="iw_copy" value="<?php _e('Save', 'intelliwidget'); ?>" type="button" style="max-width:24%;float:right;clear:both;margin-top:4px" />
  <?php echo $id_list; ?>
</p>
<?php
    }
    function add_form($id) {
        global $intelliwidget_admin;
?>
<div class="iw-copy-container"> 
<span class="spinner" id="intelliwidget_spinner"></span> </div>
<a title="<?php echo $intelliwidget_admin->get_tip('iw_add'); ?>" style="float:left;" href="<?php echo $intelliwidget_admin->get_nonce_url($id, 'add'); ?>" id="iw_add" class="iw-add">
<?php echo $intelliwidget_admin->get_label('iw_add'); ?>
</a>
<?php wp_nonce_field('iwpage_' . $id,'iwpage'); ?>
<div style="clear:both"></div>
<?php
    }
    function metabox($id, $box_id, $instance) {
        global $intelliwidget_admin;
        ?>
        <p>
  <input type="hidden" id="<?php echo 'intelliwidget_' . $box_id . '_category'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_category'; ?>" value="-1" /><?php /* Original Categories: <?php echo implode(',', $intelliwidget_admin->val2array($instance['category'])); */ ?>
        </p>
<p>
  <input type="hidden" id="<?php echo 'intelliwidget_' . $box_id . '_box_id'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_box_id'; ?>" value="<?php echo $box_id; ?>" />
  <label title="<?php echo $intelliwidget_admin->get_tip('replace_widget'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>">
    <?php echo $intelliwidget_admin->get_label('replace_widget'); ?>
    : </label>
  <select name="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_replace_widget'; ?>">
    <?php foreach ($intelliwidget_admin->intelliwidgets as $value => $label): ?>
    <option value="<?php echo $value; ?>" <?php selected($instance['replace_widget'], $value); ?>><?php echo $label; ?></option>
    <?php endforeach; ?>
  </select>
</p>
<?php
    // execute custom action hook for content value if it exists
    do_action('intelliwidget_metabox_all_before', $id, $box_id, $instance);
    do_action('intelliwidget_metabox_' . $instance['content'], $id, $box_id, $instance);
    do_action('intelliwidget_metabox_all_after', $id, $box_id, $instance); 
?>
<div class="iw-save-container">
  <input name="save" class="button button-large iw-save" id="<?php echo 'intelliwidget_' . $box_id . '_save'; ?>" value="<?php _e('Save Settings', 'intelliwidget'); ?>" type="button" style="float:right" autocomplete="off" />
  <span class="spinner <?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span> </div>
<a style="float:left;" href="<?php echo $intelliwidget_admin->get_nonce_url($id, 'delete', $box_id); ?>" id="iw_delete_<?php echo $box_id; ?>" class="iw-delete">
<?php _e('Delete', 'intelliwidget'); ?>
</a>
<div style="clear:both"></div>
<?php
    }
    
    function general_settings($id, $box_id, $instance) {
        global $intelliwidget_admin;
        ?>
<div id="iw-generalsettings-<?php echo $box_id; ?>" class="postbox closed iw-collapsible panel-general">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget_admin->get_tip('generalsettings'); ?>"><span>
    <?php echo $intelliwidget_admin->get_label('generalsettings'); ?>
    </span></h3>
  <div  id="iw-generalsettings-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('content'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>">
        <?php echo $intelliwidget_admin->get_label('content'); ?>
        : </label>
      <select class="iw-control" id="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_content'; ?>" autocomplete="off">
        <?php foreach ($intelliwidget_admin->get_content_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['content'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select> <span class="spinner <?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('title'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>"> <?php echo $intelliwidget_admin->get_label('title'); ?> </label>
      <label title="<?php echo $intelliwidget_admin->get_tip('link_title'); ?>">
        <input name="<?php echo 'intelliwidget_' . $box_id . '_link_title'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_link_title'; ?>" type="checkbox" <?php checked($instance['link_title'], 1); ?> value="1" />
        <?php echo $intelliwidget_admin->get_label('link_title'); ?>
      </label><br/>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_title'; ?>" type="text" value="<?php echo esc_attr($instance['title']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_label('container_id'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" style="display:block">
        <?php echo $intelliwidget_admin->get_label('container_id'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_container_id'; ?>" type="text" value="<?php echo esc_attr($instance['container_id']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('classes'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" style="display:block">
        <?php echo $intelliwidget_admin->get_label('classes'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_classes'; ?>" type="text" value="<?php echo esc_attr($instance['classes']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('nocopy'); ?>">
        <input name="<?php echo 'intelliwidget_' . $box_id . '_nocopy'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_nocopy'; ?>" type="checkbox" <?php checked($instance['nocopy'], 1); ?> value="1"/>
        <?php echo $intelliwidget_admin->get_label('nocopy'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }
    
    function addl_text_settings($id, $box_id, $instance) {
        global $intelliwidget_admin;
        ?>
<div id="iw-addltext-<?php echo $box_id; ?>" class="postbox closed iw-collapsible panel-addltext">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget_admin->get_tip('addltext'); ?>"><span>
    <?php echo $intelliwidget_admin->get_label('addltext'); ?>
    </span></h3>
  <div  id="iw-addltext-<?php echo $box_id; ?>-inside" class="inside">
    <label title="<?php echo $intelliwidget_admin->get_tip('text_position'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>">
      <?php echo $intelliwidget_admin->get_label('text_position'); ?>
      : </label>
    <select name="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_text_position'; ?>">
      <?php foreach ($intelliwidget_admin->get_text_position_menu() as $value => $label): ?>
      <option value="<?php echo $value; ?>" <?php selected($instance['text_position'], $value); ?>><?php echo $label; ?></option>
      <?php endforeach; ?>
    </select>
    <textarea class="widefat" rows="3" cols="20" id="<?php echo 'intelliwidget_' . $box_id . '_custom_text'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_custom_text'; ?>">
<?php echo esc_textarea($instance['custom_text']); ?></textarea>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('filter'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_filter'; ?>" type="checkbox" <?php checked($instance['filter'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget_admin->get_label('filter'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }
    
    function appearance_settings($id, $box_id, $instance) {
        global $intelliwidget_admin, $_wp_additional_image_sizes;
        ?>
<div id="iw-appearance-<?php echo $box_id; ?>" class="postbox closed iw-collapsible panel-appearance">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget_admin->get_tip('appearance'); ?>"><span>
    <?php echo $intelliwidget_admin->get_label('appearance'); ?>
    </span></h3>
  <div id="iw-appearance-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('template'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>">
        <?php echo $intelliwidget_admin->get_label('template'); ?>
        :</label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_template'; ?>">
        <?php foreach ( $intelliwidget_admin->templates as $template => $name ) : ?>
        <option value="<?php echo $template; ?>" <?php selected($instance['template'], $template); ?>><?php echo $name; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('sortby'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>">
        <?php echo $intelliwidget_admin->get_label('sortby'); ?>
        : </label>
      <br/>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_sortby'; ?>">
        <?php foreach ($intelliwidget_admin->get_sortby_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['sortby'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
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
      <label title="<?php echo $intelliwidget_admin->get_tip('items'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>">
        <?php echo $intelliwidget_admin->get_label('items'); ?>
        : </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_items'; ?>" size="3" type="text" value="<?php echo esc_attr($instance['items']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('length'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>">
        <?php echo $intelliwidget_admin->get_label('length'); ?>
        : </label>
      <input id="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_length'; ?>" size="3" type="text" value="<?php echo esc_attr($instance['length']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('allowed_tags'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>">
        <?php echo $intelliwidget_admin->get_label('allowed_tags'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_allowed_tags'; ?>" type="text" value="<?php echo esc_attr($instance['allowed_tags']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('link_text'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>">
        <?php echo $intelliwidget_admin->get_label('link_text'); ?>
        : </label>
      <input name="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_link_text'; ?>" type="text" value="<?php echo esc_attr($instance['link_text']); ?>" />
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('imagealign'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>">
        <?php echo $intelliwidget_admin->get_label('imagealign'); ?>
      </label>
      <select name="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>" id="<?php echo 'intelliwidget_' . $box_id . '_imagealign'; ?>">
        <?php foreach ($intelliwidget_admin->get_imagealign_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['imagealign'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('image_size'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_image_size'; ?>">
        <?php echo $intelliwidget_admin->get_label('image_size'); ?>
        : </label>
      <select id="<?php echo 'intelliwidget_' . $box_id . '_image_size'; ?>" name="<?php  echo 'intelliwidget_' . $box_id . '_image_size'; ?>">
        <?php foreach ($intelliwidget_admin->get_image_size_menu() as $value => $label): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['image_size'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
        <?php if (is_array($_wp_additional_image_sizes)): foreach ( $_wp_additional_image_sizes as $name => $size ) : ?>
        <option value="<?php echo $name; ?>" <?php selected($instance['image_size'], $name); ?> ><?php echo $name; ?> (<?php echo $size['width']; ?>x<?php echo $size['height']; ?>px)</option>
        <?php endforeach; endif; ?>
      </select>
    </p>
  </div>
</div>
<?php
    }
    
    function post_selection_settings($id, $box_id, $instance) {
        global $intelliwidget_admin;
        ?>
<div id="iw-selection-<?php echo $box_id; ?>" class="postbox closed iw-collapsible panel-selection">
  <div class="handlediv" title="<?php _e('Click to toggle', 'intelliwidget'); ?>"></div>
  <h3 title="<?php echo $intelliwidget_admin->get_tip('selection'); ?>"><span>
    <?php echo $intelliwidget_admin->get_label('selection'); ?>
    </span></h3>
  <div id="iw-selection-<?php echo $box_id; ?>-inside" class="inside">
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('post_types'); ?>" style="display:block;margin-bottom:10px"><span class="spinner <?php echo 'intelliwidget_' . $box_id . '_spinner'; ?>"></span>
        <?php echo $intelliwidget_admin->get_label('post_types'); ?>
        :</label>
      <?php foreach ( $intelliwidget_admin->post_types as $type ) : ?>
      <label style="white-space:nowrap;margin-right:10px" for="<?php echo 'intelliwidget_' . $box_id . '_post_types_' . $type; ?>">
        <input class="iw-control" id="<?php echo 'intelliwidget_' . $box_id . '_post_types_' . $type; ?>" type="checkbox" name="<?php echo 'intelliwidget_' . $box_id . '_post_types[]'; ?>" value="<?php echo $type; ?>" <?php checked(in_array($type, $instance['post_types']), 1); ?> />
        <?php echo ucfirst($type); ?></label>
      <?php endforeach; ?>
    </p>
    <div id="<?php echo 'intelliwidget_' . $box_id . '_menus'; ?>">
<?php  
        /*
         * this has been moved to its own method: post_selection_menus()
         */
         
         do_action('intelliwidget_post_selection_menus', $id, $box_id, $instance);
?>
    </div>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('skip_post'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_skip_post'; ?>" type="checkbox" <?php checked($instance['skip_post'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget_admin->get_label('skip_post'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('future_only'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_future_only'; ?>" type="checkbox" <?php checked($instance['future_only'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget_admin->get_label('future_only'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('active_only'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_active_only'; ?>" type="checkbox" <?php checked($instance['active_only'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget_admin->get_label('active_only'); ?>
      </label>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('skip_expired'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>">
        <input id="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_skip_expired'; ?>" type="checkbox" <?php checked($instance['skip_expired'], 1); ?> value="1" />
        &nbsp;
        <?php echo $intelliwidget_admin->get_label('skip_expired'); ?>
      </label>
    </p>
  </div>
</div>
<?php
    }

    function post_selection_menus($id, $box_id, $instance) {
        global $intelliwidget_admin;
        // backwards compatibility with original category value
        if (empty($instance['terms']) && isset($instance['category']) && '-1' != $instance['category'])
            $instance['terms'] = $intelliwidget_admin->map_category_to_tax($instance['category']);
?>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('terms'); ?>">
        <?php echo $intelliwidget_admin->get_label('terms'); ?>
        :</label><br/>
      <select class="widefat intelliwidget-multiselect" name="<?php echo 'intelliwidget_' . $box_id . '_terms'; ?>[]" size="1" multiple="multiple" id="<?php echo 'intelliwidget_' . $box_id . '_terms'; ?>">
        <?php echo $intelliwidget_admin->get_terms_list($instance); ?>
      </select>
    </p>
    <p>
      <label title="<?php echo $intelliwidget_admin->get_tip('page'); ?>">
        <?php echo $intelliwidget_admin->get_label('page'); ?>
        :</label><br/>
      <select class="widefat intelliwidget-multiselect" name="<?php echo 'intelliwidget_' . $box_id . '_page'; ?>[]" size="1" multiple="multiple" id="<?php echo 'intelliwidget_' . $box_id . '_page'; ?>">
        <?php echo $intelliwidget_admin->get_posts_list($instance); ?>
      </select>
    </p>

<?php
    }
    
    function nav_menu($id, $box_id, $instance){
        global $intelliwidget_admin;
        ?>
<p>
  <label title="<?php echo $intelliwidget_admin->get_tip('nav_menu'); ?>" for="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>">
    <?php echo $intelliwidget_admin->get_label('nav_menu'); ?>
    : </label>
  <select id="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>" name="<?php echo 'intelliwidget_' . $box_id . '_nav_menu'; ?>">
        <?php   // Get menus
            foreach ( $intelliwidget_admin->get_nav_menu() as $value => $label ): ?>
        <option value="<?php echo $value; ?>" <?php selected($instance['nav_menu'], $value); ?>><?php echo $label; ?></option>
        <?php endforeach; ?>
  </select>
</p>
<?php
    }
}

