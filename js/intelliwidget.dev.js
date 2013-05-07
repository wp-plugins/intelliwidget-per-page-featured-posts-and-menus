/**
 * intelliwidget.js - Javascript for the Admin.
 *
 * @package IntelliWidget
 * @subpackage js
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 *
 */

jQuery(document).ready(function(e) {
    // add collapsibles to widgets admin
    jQuery('body').on('click', '.iw-collapsible', function() {
        id = jQuery(this).attr('id');
        sel = '#' + id + '-inside';
        jQuery(sel).stop().slideToggle();
    });
    // bind click events to edit page meta box buttons
    jQuery('body').on('click', '.iw-save', iw_save_postdata);    
    jQuery('body').on('click', '.iw-cptsave', iw_save_cptdata);    
    jQuery('body').on('click', '.iw-copy', iw_copy_page);    
    jQuery('body').on('click', '.iw-add', iw_add_meta_box);    
    jQuery('body').on('click', '.iw-delete', iw_delete_meta_box);    
});
var iw_save_postdata = function (){ 
        // disable the button until ajax returns
        jQuery(this).attr('disabled', 'disabled');;
        jQuery('.iw-copy-container,.iw-save-container').removeClass('success failure');
        // get id of button
        var thisID = jQuery(this).attr('id');
        // munge selector
        var sel = '#' + thisID;
        // unbind button from click event
        jQuery('body').off('click', sel, iw_save_postdata);
        // parse id to get section number
        var pre = 'intelliwidget_' + thisID.split('_')[1];
        // show spinner
        jQuery('#' + pre + '_spinner').show();
        // build post data array
        var postData = {};
        // special handling for post types (array of checkboxes)
        postData[ pre + '_post_types'] = [];
        // find inputs for this section
        jQuery('input[name=post_ID],input[name=iwpage],input[type=text][id^='+pre+'],input[type=checkbox][id^='+pre+']:checked,select[id^='+pre+'],textarea[id^='+pre+']').each(function(index, element) {
            // get field id
            fieldID = jQuery(this).attr('id');
            // special handling for post types
            if (fieldID.indexOf('_post_types') > 0) {
                postData[fieldID].push(jQuery(this).val());
            // otherwise add to post data
            } else {
                postData[fieldID] = jQuery(this).val();
            }
        });
        // add wp ajax action to array
        postData['action'] = 'iw_save';
        // send to wp
        jQuery.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function(response){
                // release button
                jQuery(sel).removeAttr('disabled');
                // hide spinner
                jQuery('#' + pre + '_spinner').hide();
                // show check mark
                jQuery(sel).parent().addClass('success');
                return false;  
            }
        );  
        return false;  
}

var iw_copy_page = function (){ 
        // disable the button until ajax returns
        jQuery(this).attr('disabled', 'disabled');
        // clear previous success/fail icons
        jQuery('.iw-copy-container,.iw-save-container').removeClass('success failure');
        // unbind button from click event
        jQuery('body').off('click', '#iw_copy', iw_copy_page);
        // show spinner
        jQuery('#intelliwidget_spinner').show();
        // build post data array
        var postData = {};
        // find inputs for this section
        jQuery('input[name=post_ID],input[name=iwpage],select[id=intelliwidget_widget_page_id]').each(function(index, element) {
            // get field id
            fieldID = jQuery(this).attr('id');
            postData[fieldID] = jQuery(this).val();
        });
        // add wp ajax action to array
        postData['action'] = 'iw_copy';
        // send to wp
        jQuery.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function(response){
                // release button
                jQuery('#iw_copy').removeAttr('disabled');
                // hide spinner
                jQuery('#intelliwidget_spinner').hide();
                // show check mark
                jQuery('.iw-copy-container').addClass('success');
                return false;  
            }
        );  
        return false;  
}

var iw_add_meta_box = function (e){ 
        // don't act like a link
        e.stopPropagation();
        // ignore click if we are in process
        if (jQuery(this).hasClass('disabled')) return false;
        // disable the button until ajax returns
        jQuery(this).addClass('disabled');
        // clear previous success/fail icons
        jQuery('.iw-copy-container,.iw-save-container').removeClass('success failure');
        // get id of button
        var thisID = jQuery(this).attr('id');
        // munge selector
        var sel = '#' + thisID;
        // get href from link
        var href = jQuery(this).attr('href');
        // build post data array from query string
        var postData = URLToArray(href);
        // show spinner
        jQuery('#intelliwidget_spinner').show();
        // add wp ajax action to array
        postData['action'] = 'iw_add';
        // send to wp
        jQuery.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function(response){
                jQuery(sel).removeClass('disabled');
                jQuery('#intelliwidget_spinner').hide();
                if (response == 'fail') {
                    jQuery('.iw-copy-container').addClass('failure');
                } else {
                    jQuery('#side-sortables').append(response);
                    // bind toggle events to new content - using native wp postboxes class
                    jQuery('body').on('click', '.iw_new_box h3, .iw_new_box .handlediv, .iw_new_box .postbox h3, .iw_new_box .postbox .handlediv', function() {
                        var p = jQuery(this).parent('.postbox'), id = p.attr('id');
                        p.toggleClass('closed');
                        if ( id ) {
                            if ( !p.hasClass('closed') && jQuery.isFunction(postboxes.pbshow) )
                                postboxes.pbshow(id);
                            else if ( p.hasClass('closed') && jQuery.isFunction(postboxes.pbhide) )
                                postboxes.pbhide(id);
                        }
                    });
                    // prevent link action on h3 
                    jQuery('body').on('.iw_new_box h3 a, .iw_new_box .postbox h3 a', 'click', function(e) {
                        e.stopPropagation();
                    });
                    // show check mark
                    jQuery('.iw-copy-container').addClass('success');
                }
                return false;  
            }
        );  
        return false;  
}

var iw_delete_meta_box = function (e){ 
        // don't act like a link
        e.stopPropagation();
        // ignore click if we are in process
        if (jQuery(this).hasClass('disabled')) return false;
        // disable the button until ajax returns
        jQuery(this).addClass('disabled');
        // clear previous success/fail icons
        jQuery('.iw-copy-container,.iw-save-container').removeClass('success failure');
        // get id of button
        var thisID = jQuery(this).attr('id');
        // munge selector
        var sel = '#' + thisID;
        // get href from link
        var href = jQuery(this).attr('href');
        // build post data array from query string
        var postData = URLToArray(href);
        // get box id 
        var pre = postData['iwdelete'];
        // show spinner
        jQuery('#intelliwidget_' + pre + '_spinner').show();
        // add wp ajax action to array
        postData['action'] = 'iw_delete';
        // send to wp
        jQuery.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function(response){
                jQuery(sel).removeClass('disabled');
                jQuery('#intelliwidget_' + pre + '_spinner').hide();
                if (response == 'success') {
                    jQuery('#intelliwidget_section_meta_box_' + pre).slideUp('fast', function(){
                        jQuery('#intelliwidget_section_meta_box_' + pre).remove();
                    });
                }
                return false;  
            }
        );  
        return false;  
}
/**
 * nice little url -> name:value pairs codex
 */
function URLToArray(url) {
  var request = {};
  var pairs = url.substring(url.indexOf('?') + 1).split('&');
  for (var i = 0; i < pairs.length; i++) {
    var pair = pairs[i].split('=');
    request[decodeURIComponent(pair[0])] = decodeURIComponent(pair[1]);
  }
  return request;
}