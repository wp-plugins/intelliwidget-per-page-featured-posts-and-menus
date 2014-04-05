<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-widget-intelliwidget.php - IntelliWidget Widget Class
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Jason C Fleming
 * @copyright 2014 Lilaea Media LLC
 * @access public
 */

include_once('class-intelliwidget-form.php');
class IntelliWidget_WidgetAdmin extends IntelliWidgetAdmin {

    var $widget_form;
    /**
     * Constructor
     */
    function __construct() {
        add_action('load-widgets.php',                  array(&$this, 'load_widget_form') );
        add_action('wp_ajax_iw_widget_menus',           array(&$this, 'ajax_get_widget_post_select_menus'));
        $this->widget_form = new IntelliWidgetForm();
    }
    
    
    /**
     * Widget Update method
     * @param <array> $new_instance
     * @param <array> $old_instance
     * @return <array>
     */
    function update($new_instance, $old_instance) {
        $textfields = $this->get_text_fields();
        foreach ($new_instance as $name => $value):
            // special handling for text inputs
                if (in_array($name, $textfields)):
                    if ( current_user_can('unfiltered_html') ):
                        $old_instance[$name] =  $new_instance[$name];
                    else:
                        // raw html parser/cleaner-upper: see WP docs re: KSES
                        $old_instance[$name] = stripslashes( 
                            wp_filter_post_kses( addslashes($new_instance[$name]) ) ); 
                    endif;
                else:
                    $old_instance[$name] = $this->filter_sanitize_input($new_instance[$name]);
                endif;
                if ('post_types' == $name && empty($value))
                    $old_instance[$name] = array('post');
        endforeach;
        // special handling for checkboxes:
        foreach (  $this->get_checkbox_fields() as $name) :
            $old_instance[$name] = isset($new_instance[$name]);
        endforeach;
        return $old_instance;
    }
    /**
     * Output Widget form
     *
     * @param <array> $instance
     */
    function render_form($obj, $instance) {
        global $intelliwidget;
        $instance = $intelliwidget->defaults($instance);
        $this->widget_form->render_form($this, $obj, $instance);
    }
    
    function load_widget_form(){
        $this->admin_init();
    }
    
    // widgets only
    function ajax_get_widget_post_select_menus() {
        global $wp_registered_widgets;
        $widget_id = sanitize_text_field($_POST['widget-id']);
        if ( empty($widget_id) || 
            !$this->validate_post('save-sidebar-widgets', '_wpnonce_widgets', 'edit_theme_options', true) 
            ) return false;
        $this->admin_init();
        // getting to the widget info is a complicated task ...
        if (isset($wp_registered_widgets[$widget_id])):
            if (isset($wp_registered_widgets[$widget_id]['callback']) && isset($wp_registered_widgets[$widget_id]['params'])
                && count($wp_registered_widgets[$widget_id]['callback']) && count($wp_registered_widgets[$widget_id]['params'])):
                    $widget = $wp_registered_widgets[$widget_id]['callback'][0];
                    $params = $wp_registered_widgets[$widget_id]['params'][0];
                    $settings = $widget->get_settings($widget_id);
                    $instance = $settings[$params['number']];
                    include_once('class-intelliwidget-form.php');
                    $this->widget_form = new IntelliWidgetForm();
                    ob_start();
                    $this->widget_form->post_selection_menus($this, $widget, $instance);
                    $form = ob_get_contents();
                    ob_end_clean();
                    die($form);
            endif;
        endif;
        die('fail');
    }
  
}
