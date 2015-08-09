/*!
 * intelliwidget.js - Javascript for the Admin.
 *
 * @package IntelliWidget
 * @subpackage js
 * @author Jason C Fleming
 * @copyright 2014-2015 Lilaea Media LLC
 */

jQuery( document ).ready( function( $ ) {
    /* 
     * Use localization object to store tab and panel data
     */
    IWAjax.openPanels   = {};
    IWAjax.ajaxSemaphore   = false;
    var     
    /* 
     * BEGIN FUNCTIONS -- FIXME: can we combine these ajax calls???
     */
     // store panel open state so it can persist across ajax refreshes
    updateOpenPanels = function( container ) {
        container.find( '.inside' ).each( function(){
            var inside = $( this ).prop( 'id' );
            //console.log( 'update panels: ' + inside );
            IWAjax.openPanels[ inside ] = $( this ).parent( '.postbox' ).hasClass( 'closed' ) ? 0 : 1;
        } );
    },
    refreshOpenPanels = function( a,b,c ) {
        // only process IW responses
        if ( 'undefined' == typeof b.responseText || !b.responseText.match( /intelliwidget/ ) ) return;
        
        for ( var key in IWAjax.openPanels ) {
            //console.log( 'refresh panels: ' + key );
            if (
                IWAjax.openPanels.hasOwnProperty( key ) 
                && 
                IWAjax.openPanels[ key ] == 1
                ) {
            //console.log( 'refresh panels: ' + key );
                $( '#' + key ).parent( '.postbox' ).removeClass( 'closed' );
                $( '#' + key ).show();
            }
        }
    },
    initTabs = function() {
        $( '.iw-tabbed-sections' ).each( function(){
            var container = $( this );
            container.data( 'viewWidth', 0 );
            container.data( 'visWidth',  0 );
            container.data( 'leftTabs',  [] ); 
            container.data( 'rightTabs', [] );
            container.data( 'visTabs',   [] );
            container.find( '.iw-tab' ).each( function(){
                container.data( 'visTabs' ).push( $( this ).prop( 'id' ) );
                container.data( 'visWidth', container.data( 'visWidth' ) + $( this ).outerWidth() );
                $( this ).show();
            } );
        } );
        reflowTabs();
    },
    reflowTabs = function() {
        $( '.iw-tabbed-sections' ).each( function(){
            var container = $( this );
            container.data( 'viewWidth', container.find( '.iw-tabs' ).width() - 24 ); // minus space for arrows
            if ( container.data( 'viewWidth' ) > 0 ) {
                count = 0;
                while ( container.data( 'visTabs' ).length && container.data( 'visWidth' ) > container.data( 'viewWidth' ) ) {
                    var leftMost = container.data( 'visTabs' ).shift(),
                        tabWidth = $( '#' + leftMost ).outerWidth();
                    container.data( 'visWidth', container.data( 'visWidth' ) - tabWidth );
                    $( '#' + leftMost ).hide();
                    container.data( 'leftTabs' ).push( leftMost );
                    if ( ++count > 50 ) break; // infinite loop safety check
                }
            }
        } );
        setArrows();
    },
    rightShiftTabs = function( el ) {
        // left arrow clicked, shift all tabs to the right
        var container = el.parent( '.iw-tabbed-sections' ),
            rightMost;
        if ( rightMost = container.data( 'visTabs' ).pop() ) {
            container.data( 'visWidth', container.data( 'visWidth' ) - $( '#' + rightMost ).outerWidth() );
            $( '#' + rightMost ).hide();
            container.data( 'rightTabs' ).unshift( rightMost );
        }
        if ( rightMost = container.data( 'leftTabs' ).pop() ){
            container.data( 'visWidth', container.data( 'visWidth' ) + $( '#' + rightMost ).outerWidth() );
            $( '#' + rightMost ).show();
            container.data( 'visTabs' ).unshift( rightMost );
        }
        setArrows();
    },
    leftShiftTabs = function( el ) {
        // right arrow clicked, shift all tabs to the left
        var container = el.parent( '.iw-tabbed-sections' ),
            leftMost;
        if ( leftMost = container.data( 'visTabs' ).shift() ) {
            container.data( 'visWidth', container.data( 'visWidth' ) - $( '#' + leftMost ).outerWidth() );
            $( '#' + leftMost ).hide();
            container.data( 'leftTabs' ).push( leftMost );
        }
        if ( leftMost = container.data( 'rightTabs' ).shift() ){
            container.data( 'visWidth', container.data( 'visWidth' ) + $( '#' + leftMost ).outerWidth() );
            $( '#' + leftMost ).show();
            container.data( 'visTabs' ).push( leftMost );
        }
        setArrows();
    },
    setArrows = function() {
        $( '.iw-larr, .iw-rarr' ).css( 'visibility', 'hidden' );
        $( '.iw-tabbed-sections' ).each( function(){
            var container = $( this );
            // if rightTabs, show >>
            if ( container.data( 'rightTabs' ).length ) container.find( '.iw-rarr' ).css( 'visibility', 'visible' );
            // if leftTabs, show <<
            if ( container.data( 'leftTabs' ).length ) container.find( '.iw-larr' ).css( 'visibility', 'visible' );
        } );
    },
    bind_events = function( el ) {
        // since postbox.js does not delegate events, 
        // we have to rebind toggles on refresh
        $( el ).find( '.postbox .hndle, .handlediv' ).on( 'click', function( e ){
            var p = $( this ).parent( '.postbox' ), id = p.attr( 'id' );
            p.toggleClass( 'closed' );
            if ( id ) {
                if ( !p.hasClass( 'closed' ) && $.isFunction( postboxes.pbshow ) )
                    postboxes.pbshow( id );
                else if ( p.hasClass( 'closed' ) && $.isFunction( postboxes.pbhide ) )
                    postboxes.pbhide( id );
            }
        } );
    },
    /**
     * Ajax Save Custom Post Type Data
     */
    save_cdfdata = function(){
        // disable the button until ajax returns
        $( this ).prop( 'disabled', true );
        // clear previous success/fail icons
        $( '.iw-copy-container,.iw-save-container,.iw-cdf-container' ).removeClass( 'success failure' );
        // unbind button from click event
        $( 'body' ).off( 'click', '#iw_cdfsave', save_cdfdata );
        // show spinner
        $( '#intelliwidget_cpt_spinner' ).show();
        // build post data array
        var postData = {};
        // find inputs for this section
        $( 'input[name=post_ID],input[name=iwpage],.intelliwidget-input' ).each( function( index, element ) {
            // get field id
            fieldID = $( this ).attr( 'id' );
            postData[ fieldID ] = $( this ).val();
        } );
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_' + IWAjax.objtype + '_cdfsave';
        // console.log( postData );
        // send to wp
        $.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                // console.log( response );
                // release button
                $( '#iw_cdfsave' ).prop( 'disabled', false );
                // hide spinner
                $( '#intelliwidget_cpt_spinner' ).hide();
                // show check mark
                $( '.iw-cdf-container' ).addClass( 'success' );
                return false;  
            }
        ).fail( function(){
            // release button
            $( '#iw_cdfsave' ).prop( 'disabled', false );
            // hide spinner
            $( '#intelliwidget_cpt_spinner' ).hide();
            // show red X
            $( '.iw-cdf-container' ).addClass( 'failure' );
            return false;  
        } );  
        return false;  
    },
    parse_ids = function( id ) {
            // parse id to get section number
        var idparts         = id.split( '_' ),
            boxid           = idparts.pop(),
            objid           = idparts.pop();
        return objid + '_' + boxid;
    },
    /**
     * Ajax Save IntelliWidget Meta Box Data
     */
    save_postdata = function (){ 
        // don't allow add/delete section while saving
        if ( true === IWAjax.ajaxSemaphore ) return false;
        IWAjax.ajaxSemaphore   = true;
        
        $( '.iw-copy-container,.iw-save-container,.iw-cdf-container' ).removeClass( 'success failure' );
        var // get section selector
            sectionform     = $( this ).parents( '.iw-tabbed-section' ).first(),
            container       = sectionform.parent( '.iw-tabbed-sections' );
            thisID          = sectionform.attr( 'id' ),
            // get controls container selector
            savecontainer   = sectionform.find( '.iw-save-container' ),
            // get button selector
            savebutton      = sectionform.find( '.iw-save' ),
            pre             = parse_ids( thisID ),
            // build post data array
            postData        = {};
        //console.log( 'thisID: ' + thisID + ' pre: ' + pre );
        // disable the button until ajax returns
        $( savebutton ).prop( 'disabled', true );
        updateOpenPanels( sectionform );
        // show spinner
        $( '.intelliwidget_' + pre + '_spinner' ).show();
        // unbind button from click event
        $( 'body' ).off( 'click', savebutton, save_postdata );
        // special handling for post types ( array of checkboxes )
        postData[ 'intelliwidget_' + pre + '_post_types' ] = [];
        postData[ 'iwpage' ] = $( '#iwpage' ).val();
        postData[ IWAjax.idfield ] = $( '#' + IWAjax.idfield ).val();
        // find inputs for this section
        sectionform.find( 'select,textarea,input[type=text],input[type=checkbox]:checked,input[type=hidden]' ).each(
            function() {
            // get field id
            fieldID = $( this ).prop( 'id' );
            //console.log( 'fieldID: ' + fieldID );
            if ( fieldID.indexOf( '_post_types' ) > 0 ) {
                // special handling for post types
                postData[ 'intelliwidget_' + pre + '_post_types' ].push( $( this ).val() );
            } else {
                // otherwise add to post data
                postData[ fieldID ] = $( this ).val();
            }
            if ( fieldID.indexOf( '_menu_location' ) > 0 ) {
                // special case for menu_location
                if ( '' != $( this ).val() ) postData[ 'intelliwidget_' + pre + '_replace_widget' ] = 'nav_menu_location-' + $( this ).val();
            }
        } );
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_' + IWAjax.objtype + '_save';
        // console.log( postData );
        // send to wp
        $.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                // console.log( response );
                if ( 'fail' == response ) {
                    // show red X
                    savecontainer.addClass( 'failure' );
                } else {
                    // refresh section form
                    var tab = $( response.tab ),
                        curtab = $( '.iw-tabs' ).find( '#' + tab.prop( 'id' ) );
                    curtab.html( tab.html() );
                    sectionform.html( response.form );
                    if ( 'post' == IWAjax.objtype ) bind_events( sectionform );
                    container.tabs( 'refresh' ).tabs( { active: curtab.index() } );
                    // show check mark
                    sectionform.find( '.iw-save-container' ).addClass( 'success' );
                    sectionform.find( '.intelliwidget-multiselect' ).multiSelect();
                    sectionform.find( '.ms-list' ).on( 'scroll', chk_scroll_end );
                }
                // release button
                savebutton.prop( 'disabled', false );
                // hide spinner
                $( '.intelliwidget_' + pre + '_spinner' ).hide();
                // release ajax
                IWAjax.ajaxSemaphore   = false;
                return false;  
            }, 'json'
        ).fail( function(){
            // console.log( 'fail' );
            // release button
            savebutton.prop( 'disabled', false );
            // hide spinner
            $( '.intelliwidget_' + pre + '_spinner' ).hide();
            // show red X
            savecontainer.addClass( 'failure' );
            // release ajax
            IWAjax.ajaxSemaphore   = false;
            return false;  
        } );  
        // release ajax 
        IWAjax.ajaxSemaphore   = false;
        return false;  
    },
    /**
     * Ajax Save Copy Page Input
     */
    copy_profile = function (){ 
        // disable the button until ajax returns
        $( this ).prop( 'disabled', true );
        // clear previous success/fail icons
        $( '.iw-copy-container,.iw-save-container,.iw-cdf-container' ).removeClass( 'success failure' );
        // unbind button from click event
        $( 'body' ).off( 'click', '#iw_copy', copy_profile );
        // show spinner
        $( '#intelliwidget_spinner' ).show();
        // build post data array
        var postData = {};
        // find inputs for this section
        postData[ 'iwpage' ] = $( '#iwpage' ).val();
        postData[ IWAjax.idfield ] = $( '#' + IWAjax.idfield ).val();
        postData[ 'intelliwidget_widget_page_id' ] = $( '#intelliwidget_widget_page_id' ).val();
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_' + IWAjax.objtype + '_copy';
        // console.log( postData );
        // send to wp
        $.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                // console.log( response );
                // release button
                $( '#iw_copy' ).prop( 'disabled', false );
                // hide spinner
                $( '#intelliwidget_spinner' ).hide();
                // show check mark
                $( '.iw-copy-container' ).addClass( 'success' );
                // release ajax
                IWAjax.ajaxSemaphore   = false;
                return false;  
            }
        ).fail( function(){
            // console.log( 'fail' );
            // release button
            $( '#iw_copy' ).prop( 'disabled', false );
            // hide spinner
            $( '#intelliwidget_spinner' ).hide();
            // show red X
            $( '.iw-copy-container' ).addClass( 'failure' );
            // release ajax
            IWAjax.ajaxSemaphore   = false;
            return false;  
        } );  
        // release ajax
        IWAjax.ajaxSemaphore   = false;
        return false;  
    },
    /**
     * Ajax Add new IntelliWidget Tab Section
     */
    add_tabbed_section = function ( e ){ 
        // don't allow add/delete section while saving
        if ( true === IWAjax.ajaxSemaphore ) return false;
        IWAjax.ajaxSemaphore   = true;
        // don't act like a link
        e.preventDefault();
        e.stopPropagation();
        
        // ignore click if we are in process
        if ( $( this ).hasClass( 'disabled' ) ) return false;
        // disable the button until ajax returns
        $( this ).addClass( 'disabled', true );
        // clear previous success/fail icons
        $( '.iw-copy-container,.iw-save-container,.iw-cdf-container' ).removeClass( 'success failure' );
        // get id of button
        var container   = $( this ).parent( '.inside' ).find( '.iw-tabbed-sections' ),
            thisID      = container.prop( 'id' ),
            // munge selector
            sel         = $( this ),
            // get href from link
            href        = $( this ).attr( 'href' ),
            // build post data array from query string
            postData    = url_to_array( href );
        // show spinner
        $( '#intelliwidget_spinner' ).show();
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_' + IWAjax.objtype + '_add';
        // send to wp
        $.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                // console.log( response );
                sel.removeClass( 'disabled' );
                $( '#intelliwidget_spinner' ).hide();
                if ( 'fail' == response ) {
                    $( '.iw-copy-container' ).addClass( 'failure' );
                } else {
                    form = $( response.form ).hide();
                    tab  = $( response.tab ).hide();
                    container.append( form );
                    if ( 'post' == IWAjax.objtype ) bind_events( form );
                    container.find( '.iw-tabs' ).append( tab );
                    tab.show();
                    container.tabs( 'refresh' ).tabs( { active: tab.index() } );
                    initTabs();
                    // show check mark
                    $( '.iw-copy-container' ).addClass( 'success' );
                }
                // release ajax
                IWAjax.ajaxSemaphore   = false;
                return false;  
            }, 'json'
        ).fail( function(){
            // console.log( 'fail' );
            // release button
            sel.removeClass( 'disabled' );
            // hide spinner
            $( '#intelliwidget_spinner' ).hide();
            // show red X
            $( '.iw-copy-container' ).addClass( 'failure' );
            // release ajax
            IWAjax.ajaxSemaphore   = false;
            return false;  
        } );  
        // release ajax
        IWAjax.ajaxSemaphore   = false;
        return false;  
    },
    /**
     * Ajax Delete IntelliWidget Tab Section
     */
    delete_tabbed_section = function ( e ){ 
        // don't allow add/delete section while saving
        if ( true === IWAjax.ajaxSemaphore ) return false;
        IWAjax.ajaxSemaphore   = true;
        // don't act like a link
        e.preventDefault();
        e.stopPropagation();
        
        // ignore click if we are in process
        if ( $( this ).hasClass( 'disabled' ) ) return false;
        // disable the button until ajax returns
        $( this ).addClass( 'disabled' );
        // clear previous success/fail icons
        $( '.iw-copy-container,.iw-save-container,.iw-cdf-container' ).removeClass( 'success failure' );
        // munge selectors
        var sel             = $( this ),
            sectionform     = sel.parents( '.iw-tabbed-section' ).first(),
            container       = sectionform.parent( '.iw-tabbed-sections' ),
            thisID          = sectionform.prop( 'id' ),
            savecontainer   = sectionform.find( '.iw-save-container' ),
            // get box id 
            pre             = parse_ids( thisID ),
            // get href from link
            href            = $( this ).attr( 'href' ),
            // build post data array from query string
            postData        = url_to_array( href );
        //console.log( 'thisID: ' + thisID + ' pre: ' + pre );
        // show spinner
        $( '.intelliwidget_' + pre + '_spinner' ).show();
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_' + IWAjax.objtype + '_delete';
        // console.log( postData );
        // send to wp
        $.post(  
        // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                // console.log( response );
                sel.removeClass( 'disabled' );
                $( '.intelliwidget_' + pre + '_spinner' ).hide();
                if ( 'success' == response ) {
                        var survivor = sectionform.index();
                        sectionform.remove();
                        $( '#iw_tab_' + pre ).remove();
                        container.tabs( 'refresh' );
                        initTabs();
                        //survivor -= target.parent( '.iw-tabbed-sections' ).data( 'leftTabs' ).length;
                        container.tabs( { active:survivor } );
                } else {
                    savecontainer.addClass( 'failure' );
                }
                // release ajax
                IWAjax.ajaxSemaphore   = false;
                return false;  
            }
        ).fail( function(){
            // console.log( 'fail' );
            // release button
            sel.removeClass( 'disabled' );
            // hide spinner
            $( '.intelliwidget_' + pre + '_spinner' ).hide();
            savecontainer.addClass( 'failure' );
            // release ajax
            IWAjax.ajaxSemaphore   = false;
            return false;  
        } );  
        // release ajax
        IWAjax.ajaxSemaphore   = false;
        return false;  
    },
    
    /**
     * Ajax Fetch multiselect menus
     */
    get_menus = function (){ 
        $( '.iw-copy-container,.iw-save-container,.iw-cdf-container' ).removeClass( 'success failure' );
        var sectionform     = $( this ).parents( '.iw-tabbed-section' ).first(),
            // parse id to get section number
            thisID          = sectionform.prop( 'id' ),
            // get section selector
            pre             = parse_ids( thisID ),
            // get menu container
            menucontainer   = sectionform.find( '#intelliwidget_' + pre + '_menus' ),
            // get controls container selector
            savecontainer   = sectionform.find( '.iw-save-container' ),
            // get button selector
            savebutton      = sectionform.find( '.iw-save' ),
            // build post data array
            postData        = {};
        // only load once
        if ( menucontainer.has( 'select' ).length ) return false;
        // disable the button until ajax returns
        $( savebutton ).prop( 'disabled', true );
        //menucontainer.hide();
        // show spinner
        $( '.intelliwidget_' + pre + '_spinner' ).show();
        // find inputs for this section
        postData[ 'iwpage' ] = $( '#iwpage' ).val();
        postData[ IWAjax.idfield ] = $( '#' + IWAjax.idfield ).val();
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_' + IWAjax.objtype + '_menus';
        // find inputs for this section
        sectionform.find( 'input[type="hidden"]' ).each(
            function( index, element ) {
            // get field id
            fieldID = $( this ).attr( 'id' );
            // add to post data
            postData[ fieldID ] = $( this ).val();
        } );
        //console.log( postData );
        // send to wp
        $.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                //console.log( response );
                if ( 'fail' == response ) {
                    // show red X
                    savecontainer.addClass( 'failure' );
                } else {
                    // refresh menus
                    menucontainer.html( response ).find( '.intelliwidget-multiselect' ).multiSelect();
                    menucontainer.find( '.ms-list' ).on( 'scroll', chk_scroll_end );
                    //menucontainer.slideDown();
                    // show check mark
                    sectionform.find( '.iw-save-container' ).addClass( 'success' );
                }
                // release button
                savebutton.prop( 'disabled', false );
                // hide spinner
                $( '.intelliwidget_' + pre + '_spinner' ).hide();
                return false;  
            }
        ).fail( function(){
            //console.log( 'FAIL :( ' );
            // release button
            savebutton.prop( 'disabled', false );
            // hide spinner
            $( '.intelliwidget_' + pre + '_spinner' ).hide();
            // show red X
            savecontainer.addClass( 'failure' );
            return false;  
        } );  
        return false;  
    },

    update_menu = function( id, type, query, offset ) {
    },
    /**
     * Ajax Fetch widget multiselect menus
     */
    get_widget_menus = function (){ 
        var sectionform     = $( this ).parents( '.widget' ).first(),
            // parse id to get section number
            thisID          = sectionform.find( '.widget-id' ).val(),
            nonce           = $( '#_wpnonce_widgets' ).val(),
            // get section selector
            pre             = 'widget-' + thisID,
            // get menu container
            menucontainer   = sectionform.find( '#' + pre + '-menus' ),
            // build post data array
            postData        = {};
        // only load once
        if ( menucontainer.has( 'select' ).length ) return false;
        // find inputs for this section
        postData[ 'widget-id' ] = thisID;
        postData[ '_wpnonce_widgets' ] = nonce;
        // add wp ajax action to array
        postData[ 'action' ] = 'iw_widget_menus';
        // send to wp
        $.post(  
            // get ajax url from localized object
            IWAjax.ajaxurl,  
            //Data  
            postData,
            //on success function  
            function( response ){
                if ( 'fail' == response ) {
                    // show red X
                    //$( savecontainer ).addClass( 'failure' );
                } else {
                    // refresh menus
                    menucontainer.html( response ).find( '.intelliwidget-multiselect' ).multiSelect();
                    menucontainer.find( '.ms-list' ).on( 'scroll', chk_scroll_end );

                    //menucontainer.slideDown();
                    // show check mark
                    //$( sectionform ).find( '.iw-save-container' ).addClass( 'success' );
                }
                // release button
                //$( savebutton ).prop( 'disabled', false );
                // hide spinner
                //$( '.' + pre + '_spinner' ).hide();
                return false;  
            }
        ).fail( function(){
            // release button
            //$( savebutton ).prop( 'disabled', false );
            // hide spinner
            //$( '.' + pre + '_spinner' ).hide();
            // show red X
            //$( savecontainer ).addClass( 'failure' );
            return false;  
        } );  
        return false;  
    },

    /**
     * nice little url -> name:value pairs codex
     */
    url_to_array = function( url ) {
        var pair, i, request = {},
            pairs = url.substring( url.indexOf( '?' ) + 1 ).split( '&' );
        for ( i = 0; i < pairs.length; i++ ) {
            pair = pairs[ i ].split( '=' );
            request[ decodeURIComponent( pair[ 0 ] ) ] = decodeURIComponent( pair[ 1 ] );
        }
        return request;
    },
    // set visible timestamp and timestamp hidden inputs to form inputs 
    // only validates form if validate param is true
    // this allows values to be reset/cleared
    iwUpdateTimestampText = function( field, validate ) {
        // retrieve values from form
        var attemptedDate, 
            div         = '#' + field + '_div', 
            clearForm   = ( !validate && !$( '#' + field ).val() ),  
            aa          = $( '#' + field + '_aa' ).val(),
            mm          = ( '00' + $( '#' + field + '_mm' ).val() ).slice( -2 ), 
            jj          = ( '00' + $( '#' + field + '_jj' ).val() ).slice( -2 ), 
            hh          = ( '00' + $( '#' + field + '_hh' ).val() ).slice( -2 ), 
            mn          = ( '00' + $( '#' + field + '_mn' ).val() ).slice( -2 ),
            og          = $( '#' + field + '_og' ).val();
        if ( ! $( div ).length ) return true;
        // construct date object
        attemptedDate = new Date( aa, mm - 1, jj, hh, mn );
        // validate inputs by comparing to date object
        if ( attemptedDate.getFullYear() != aa || 
            ( 1 + attemptedDate.getMonth() ) != mm || 
            attemptedDate.getDate() != jj ||
            attemptedDate.getMinutes() != mn ) {
            // date object returned invalid
            // if validating, display error and return invalid
                if ( true == validate && !og ) {
                    $( div ).addClass( 'form-invalid' );
                    $( '.iw-cdfsave' ).prop( 'disabled', true );
                    return false;
                }
                // otherwise clear form ( value is/was null )  
                clearForm = true;
        }
        // date validated or ignored, reset invalid class
        $( div ).removeClass( 'form-invalid' );
        
        $( '.iw-cdfsave' ).prop( 'disabled', false );
        if ( clearForm ) {
            // replace date fields with empty string
            if ( ! og ) $( '#' + field + '_timestamp' ).html( '' );
            $( '#' + field ).val( '' );
        } else {
            // format displayed date string from form values
            if ( 'intelliwidget_expire_date' == field ) {
                $( '#intelliwidget_ongoing' ).val( $( '#' + field + '_og' ).is( ':checked' ) ? 1 : 0 );
                if ( $( '#' + field + '_og' ).is( ':checked' ) ) {
                    $( '#' + field + '_timestamp' ).html( $( '#intelliwidget_ongoing_label' ).text() );
                    $( '#' + field ).val( '' );
                    return true;
                }
            }
            $( '#' + field + '_timestamp' ).html(
                '<b>' +
                $( 'option[value="' + $( '#' + field + '_mm' ).val() + '" ]', '#' + field + '_mm' ).text() + ' ' +
                jj + ', ' +
                aa + ' @ ' +
                hh + ':' +
                mn + '</b> '
            );
            // format date field from form values
            $( '#' + field ).val(
                aa + '-' +
                $( '#' + field + '_mm' ).val() + '-' +
                jj + ' ' +
                hh + ':' +
                mn                    
            );
        }
        return true;
    },
    chk_scroll_end = function( e ) {
        var elem    = $( e.currentTarget );
        console.log( 'height: ' + elem.outerHeight() + ' top: ' + ( elem[0].scrollHeight - elem.scrollTop() ) );
        if ( elem[0].scrollHeight - elem.scrollTop() <= elem.outerHeight() ) {
            console.log( 'at bottom' );
        }
    };
    /* END OF FUNCTIONS
     *
     * START EVENT BINDINGS ( delegate where posible )
     */
    // if panels were open before ajax save, reopen
    $( document ).ajaxComplete( refreshOpenPanels );
    $( '.iw-tabbed-sections' ).tabs( { active: ( $( 'iw-tab' ).length - 1 ) } );
    // for object types other than post we can delegate postbox collapse behavior once on load
    // postbox bindings on edit post/page must be refreshed using bind_events() after refresh
    if ( 'post' != IWAjax.objtype ) {
        $( 'body' ).on( 'click', '.iw-collapsible > .handlediv, .iw-collapsible > h4, .iw-collapsible > h3', function( e ) {
            e.stopPropagation();
            var p = $( this ).parent( '.postbox' ), 
                //id = p.attr( 'id' ),
                sectionform = $( this ).parents( 'div.widget, div.iw-tabbed-section' ).first();
            p.toggleClass( 'closed' )
                //.find( '#' + id + '-inside' )
                //.stop().slideToggle( function(){} );
            updateOpenPanels( sectionform );
        } );
    }
    $( 'body' ).on( 'click', '.iw-tabbed-sections .panel-selection h3, .iw-tabbed-sections .panel-selection .handlediv', get_menus );
    $( 'body' ).on( 'click', '.widget-inside .panel-selection h4, .widget-inside .panel-selection .handlediv', get_widget_menus );
    // bind click events to edit page meta box buttons
    $( 'body' ).on( 'click', '.iw-save', save_postdata );    
    $( 'body' ).on( 'click', '.iw-cdfsave', save_cdfdata );    
    $( 'body' ).on( 'click', '.iw-copy', copy_profile );    
    $( 'body' ).on( 'click', '.iw-add', add_tabbed_section );    
    $( 'body' ).on( 'click', '.iw-delete', delete_tabbed_section );
    // update visibility of form inputs
    $( 'body' ).on( 'change', '.iw-control', save_postdata );    
    $( 'body' ).on( 'change', '.iw-widget-control', function( e ){
        var sectionform = $( this ).parents( 'div.widget' ).first();
        updateOpenPanels( sectionform );
        wpWidgets.save( sectionform, 0, 0, 0 );
    } );  
  
    
    /**
     * manipulate IntelliWidget timestamp inputs
     * Adapted from wp-admin/js/post.js in Wordpress Core
     */
     
    // format visible timestamp values
    iwUpdateTimestampText( 'intelliwidget_event_date', false );
    iwUpdateTimestampText( 'intelliwidget_expire_date', false );
    
    // bind edit links to reveal timestamp input form
    $( 'body' ).on( 'click', 'a.intelliwidget-edit-timestamp', function() {
        var field = $( this ).attr( 'id' ).split( '-', 1 );
        if ( $( '#' + field + '_div' ).is( ":hidden" ) ) {
            $( '#' + field + '_div' ).slideDown( 'fast' );
            $( '#' + field + '_mm' ).focus();
            $( this ).hide();
        }
        return false;
    } );
    // bind click to clear timestamp ( resets form to current date/time and clears date fields )
    $( 'body' ).on( 'click', '.intelliwidget-clear-timestamp', function() {
        var field = $( this ).attr( 'id' ).split( '-', 1 );
        $( '#' + field + '_div' ).slideUp( 'fast' );
        $( '#' + field + '_mm' ).val( $( '#' + field + '_cur_mm' ).val() );
        $( '#' + field + '_jj' ).val( $( '#' + field + '_cur_jj' ).val() );
        $( '#' + field + '_aa' ).val( $( '#' + field + '_cur_aa' ).val() );
        $( '#' + field + '_hh' ).val( $( '#' + field + '_cur_hh' ).val() );
        $( '#' + field + '_mn' ).val( $( '#' + field + '_cur_mn' ).val() );
        $( '#' + field + '_og' ).prop( 'checked', false );
        $( '#' + field + '_timestamp' ).html( '' );
        $( '#' + field ).val( '' );
        $( 'a#' + field + '-edit' ).show();
        iwUpdateTimestampText( field, false );
        return false;
    } );
    // bind cancel button to reset values ( or empty string if orig field is empty ) 
    $( 'body' ).on( 'click', '.intelliwidget-cancel-timestamp', function() {
        var field = $( this ).attr( 'id' ).split( '-', 1 );
        $( '#' + field + '_div' ).slideUp( 'fast' );
        $( '#' + field + '_mm' ).val( $( '#' + field + '_hidden_mm' ).val() );
        $( '#' + field + '_jj' ).val( $( '#' + field + '_hidden_jj' ).val() );
        $( '#' + field + '_aa' ).val( $( '#' + field + '_hidden_aa' ).val() );
        $( '#' + field + '_hh' ).val( $( '#' + field + '_hidden_hh' ).val() );
        $( '#' + field + '_mn' ).val( $( '#' + field + '_hidden_mn' ).val() );
        $( '#' + field + '_og' ).prop( 'checked', $( '#' + field + '_hidden_og' ).val() ? true : false );
        $( 'a#' + field + '-edit' ).show();
        iwUpdateTimestampText( field, false );
        return false;
    } );

    // bind 'Ok' button to update timestamp to inputs
    $( 'body' ).on( 'click', '.intelliwidget-save-timestamp', function () { 
        var field = $( this ).attr( 'id' ).split( '-', 1 );
        if ( iwUpdateTimestampText( field, true ) ) {
            $( '#' + field + '_div' ).slideUp( 'fast' );
            $( 'a#' + field + '-edit' ).show();
        }
        return false;
    } );
    // bind right and left scroll arrows
    $( '.iw-tabbed-sections' ).on( 'click', '.iw-larr, .iw-rarr', function( e ) {
        e.preventDefault();
        e.stopPropagation();
        if ( $( this ).is( ':visible' ) ) {
            if ( $( this ).hasClass( 'iw-larr' ) ) rightShiftTabs( $( this ) );
            else leftShiftTabs( $( this ) );
        }
    } );
    //$( '.intelliwidget-multiselect' ).multiSelect();
    // reflow tabs on resize
    $( window ).resize( reflowTabs );
    // END EVENT BINDINGS
    // reveal intelliwidget sections
    $( '.iw-tabbed-sections' ).slideDown();
    // set up tabs
    initTabs();
} );

/**

$('.searchable').multiSelect({
  selectableHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='try \"12\"'>",
  selectionHeader: "<input type='text' class='search-input' autocomplete='off' placeholder='try \"4\"'>",
  afterInit: function(ms){
    var that = this,
        $selectableSearch = that.$selectableUl.prev(),
        $selectionSearch = that.$selectionUl.prev(),
        selectableSearchString = '#'+that.$container.attr('id')+' .ms-elem-selectable:not(.ms-selected)',
        selectionSearchString = '#'+that.$container.attr('id')+' .ms-elem-selection.ms-selected';

    that.qs1 = $selectableSearch.quicksearch(selectableSearchString)
    .on('keydown', function(e){
      if (e.which === 40){
        that.$selectableUl.focus();
        return false;
      }
    });

    that.qs2 = $selectionSearch.quicksearch(selectionSearchString)
    .on('keydown', function(e){
      if (e.which == 40){
        that.$selectionUl.focus();
        return false;
      }
    });
  },
  afterSelect: function(){
    this.qs1.cache();
    this.qs2.cache();
  },
  afterDeselect: function(){
    this.qs1.cache();
    this.qs2.cache();
  }
});
*/

/*
* MultiSelect v0.9.11
* Copyright (c) 2012 Louis Cuny
*
* This program is free software. It comes without any warranty, to
* the extent permitted by applicable law. You can redistribute it
* and/or modify it under the terms of the Do WTF You Want
* To Public License, Version 2, as published by Sam Hocevar. See
* http://sam.zoy.org/wtfpl/COPYING for more details.
*/

!function ($) {

  "use strict";


 /* MULTISELECT CLASS DEFINITION
  * ====================== */

  var MultiSelect = function (element, options) {
    this.options = options;
    this.$element = $(element);
    this.$container = $('<div/>', { 'class': "ms-container" });
    this.$selectableContainer = $('<div/>', { 'class': 'ms-selectable' });
    this.$selectionContainer = $('<div/>', { 'class': 'ms-selection' });
    this.$selectableUl = $('<ul/>', { 'class': "ms-list", 'tabindex' : '-1' });
    this.$selectionUl = $('<ul/>', { 'class': "ms-list", 'tabindex' : '-1' });
    this.scrollTo = 0;
    this.elemsSelector = 'li:visible:not(.ms-optgroup-label,.ms-optgroup-container,.'+options.disabledClass+')';
  };

  MultiSelect.prototype = {
    constructor: MultiSelect,

    init: function(){
      var that = this,
          ms = this.$element;

      if (ms.next('.ms-container').length === 0){
        ms.css({ position: 'absolute', left: '-9999px' });
        ms.attr('id', ms.attr('id') ? ms.attr('id') : Math.ceil(Math.random()*1000)+'multiselect');
        this.$container.attr('id', 'ms-'+ms.attr('id'));
        this.$container.addClass(that.options.cssClass);
        ms.find('option').each(function(){
          that.generateLisFromOption(this);
        });

        this.$selectionUl.find('.ms-optgroup-label').hide();

        if (that.options.selectableHeader){
          that.$selectableContainer.append(that.options.selectableHeader);
        }
        that.$selectableContainer.append(that.$selectableUl);
        if (that.options.selectableFooter){
          that.$selectableContainer.append(that.options.selectableFooter);
        }

        if (that.options.selectionHeader){
          that.$selectionContainer.append(that.options.selectionHeader);
        }
        that.$selectionContainer.append(that.$selectionUl);
        if (that.options.selectionFooter){
          that.$selectionContainer.append(that.options.selectionFooter);
        }

        that.$container.append(that.$selectableContainer);
        that.$container.append(that.$selectionContainer);
        ms.after(that.$container);

        that.activeMouse(that.$selectableUl);
        that.activeKeyboard(that.$selectableUl);

        var action = that.options.dblClick ? 'dblclick' : 'click';

        that.$selectableUl.on(action, '.ms-elem-selectable', function(){
          that.select($(this).data('ms-value'));
        });
        that.$selectionUl.on(action, '.ms-elem-selection', function(){
          that.deselect($(this).data('ms-value'));
        });

        that.activeMouse(that.$selectionUl);
        that.activeKeyboard(that.$selectionUl);

        ms.on('focus', function(){
          that.$selectableUl.focus();
        })
      }

      var selectedValues = ms.find('option:selected').map(function(){ return $(this).val(); }).get();
      that.select(selectedValues, 'init');

      if (typeof that.options.afterInit === 'function') {
        that.options.afterInit.call(this, this.$container);
      }
    },

    'generateLisFromOption' : function(option, index, $container){
      var that = this,
          ms = that.$element,
          attributes = "",
          $option = $(option);

      for (var cpt = 0; cpt < option.attributes.length; cpt++){
        var attr = option.attributes[cpt];

        if(attr.name !== 'value' && attr.name !== 'disabled'){
          attributes += attr.name+'="'+attr.value+'" ';
        }
      }
      var selectableLi = $('<li '+attributes+'><span>'+that.escapeHTML($option.text())+'</span></li>'),
          selectedLi = selectableLi.clone(),
          value = $option.val(),
          elementId = that.sanitize(value);

      selectableLi
        .data('ms-value', value)
        .addClass('ms-elem-selectable')
        .attr('id', elementId+'-selectable');

      selectedLi
        .data('ms-value', value)
        .addClass('ms-elem-selection')
        .attr('id', elementId+'-selection')
        .hide();

      if ($option.prop('disabled') || ms.prop('disabled')){
        selectedLi.addClass(that.options.disabledClass);
        selectableLi.addClass(that.options.disabledClass);
      }

      var $optgroup = $option.parent('optgroup');

      if ($optgroup.length > 0){
        var optgroupLabel = $optgroup.attr('label'),
            optgroupId = that.sanitize(optgroupLabel),
            $selectableOptgroup = that.$selectableUl.find('#optgroup-selectable-'+optgroupId),
            $selectionOptgroup = that.$selectionUl.find('#optgroup-selection-'+optgroupId);

        if ($selectableOptgroup.length === 0){
          var optgroupContainerTpl = '<li class="ms-optgroup-container"></li>',
              optgroupTpl = '<ul class="ms-optgroup"><li class="ms-optgroup-label"><span>'+optgroupLabel+'</span></li></ul>';

          $selectableOptgroup = $(optgroupContainerTpl);
          $selectionOptgroup = $(optgroupContainerTpl);
          $selectableOptgroup.attr('id', 'optgroup-selectable-'+optgroupId);
          $selectionOptgroup.attr('id', 'optgroup-selection-'+optgroupId);
          $selectableOptgroup.append($(optgroupTpl));
          $selectionOptgroup.append($(optgroupTpl));
          if (that.options.selectableOptgroup){
            $selectableOptgroup.find('.ms-optgroup-label').on('click', function(){
              var values = $optgroup.children(':not(:selected, :disabled)').map(function(){ return $(this).val() }).get();
              that.select(values);
            });
            $selectionOptgroup.find('.ms-optgroup-label').on('click', function(){
              var values = $optgroup.children(':selected:not(:disabled)').map(function(){ return $(this).val() }).get();
              that.deselect(values);
            });
          }
          that.$selectableUl.append($selectableOptgroup);
          that.$selectionUl.append($selectionOptgroup);
        }
        index = index == undefined ? $selectableOptgroup.find('ul').children().length : index + 1;
        selectableLi.insertAt(index, $selectableOptgroup.children());
        selectedLi.insertAt(index, $selectionOptgroup.children());
      } else {
        index = index == undefined ? that.$selectableUl.children().length : index;

        selectableLi.insertAt(index, that.$selectableUl);
        selectedLi.insertAt(index, that.$selectionUl);
      }
    },

    'addOption' : function(options){
      var that = this;

      if (options.value !== undefined && options.value !== null){
        options = [options];
      } 
      $.each(options, function(index, option){
        if (option.value !== undefined && option.value !== null &&
            that.$element.find("option[value='"+option.value+"']").length === 0){
          var $option = $('<option value="'+option.value+'">'+option.text+'</option>'),
              index = parseInt((typeof option.index === 'undefined' ? that.$element.children().length : option.index)),
              $container = option.nested == undefined ? that.$element : $("optgroup[label='"+option.nested+"']")

          $option.insertAt(index, $container);
          that.generateLisFromOption($option.get(0), index, option.nested);
        }
      })
    },

    'escapeHTML' : function(text){
      return $("<div>").text(text).html();
    },

    'activeKeyboard' : function($list){
      var that = this;

      $list.on('focus', function(){
        $(this).addClass('ms-focus');
      })
      .on('blur', function(){
        $(this).removeClass('ms-focus');
      })
      .on('keydown', function(e){
        switch (e.which) {
          case 40:
          case 38:
            e.preventDefault();
            e.stopPropagation();
            that.moveHighlight($(this), (e.which === 38) ? -1 : 1);
            return;
          case 37:
          case 39:
            e.preventDefault();
            e.stopPropagation();
            that.switchList($list);
            return;
          case 9:
            if(that.$element.is('[tabindex]')){
              e.preventDefault();
              var tabindex = parseInt(that.$element.attr('tabindex'), 10);
              tabindex = (e.shiftKey) ? tabindex-1 : tabindex+1;
              $('[tabindex="'+(tabindex)+'"]').focus();
              return;
            }else{
              if(e.shiftKey){
                that.$element.trigger('focus');
              }
            }
        }
        if($.inArray(e.which, that.options.keySelect) > -1){
          e.preventDefault();
          e.stopPropagation();
          that.selectHighlighted($list);
          return;
        }
      });
    },

    'moveHighlight': function($list, direction){
      var $elems = $list.find(this.elemsSelector),
          $currElem = $elems.filter('.ms-hover'),
          $nextElem = null,
          elemHeight = $elems.first().outerHeight(),
          containerHeight = $list.height(),
          containerSelector = '#'+this.$container.prop('id');

      $elems.removeClass('ms-hover');
      if (direction === 1){ // DOWN

        $nextElem = $currElem.nextAll(this.elemsSelector).first();
        if ($nextElem.length === 0){
          var $optgroupUl = $currElem.parent();

          if ($optgroupUl.hasClass('ms-optgroup')){
            var $optgroupLi = $optgroupUl.parent(),
                $nextOptgroupLi = $optgroupLi.next(':visible');

            if ($nextOptgroupLi.length > 0){
              $nextElem = $nextOptgroupLi.find(this.elemsSelector).first();
            } else {
              $nextElem = $elems.first();
            }
          } else {
            $nextElem = $elems.first();
          }
        }
      } else if (direction === -1){ // UP

        $nextElem = $currElem.prevAll(this.elemsSelector).first();
        if ($nextElem.length === 0){
          var $optgroupUl = $currElem.parent();

          if ($optgroupUl.hasClass('ms-optgroup')){
            var $optgroupLi = $optgroupUl.parent(),
                $prevOptgroupLi = $optgroupLi.prev(':visible');

            if ($prevOptgroupLi.length > 0){
              $nextElem = $prevOptgroupLi.find(this.elemsSelector).last();
            } else {
              $nextElem = $elems.last();
            }
          } else {
            $nextElem = $elems.last();
          }
        }
      }
      if ($nextElem.length > 0){
        $nextElem.addClass('ms-hover');
        var scrollTo = $list.scrollTop() + $nextElem.position().top - 
                       containerHeight / 2 + elemHeight / 2;

        $list.scrollTop(scrollTo);
      }
    },

    'selectHighlighted' : function($list){
      var $elems = $list.find(this.elemsSelector),
          $highlightedElem = $elems.filter('.ms-hover').first();

      if ($highlightedElem.length > 0){
        if ($list.parent().hasClass('ms-selectable')){
          this.select($highlightedElem.data('ms-value'));
        } else {
          this.deselect($highlightedElem.data('ms-value'));
        }
        $elems.removeClass('ms-hover');
      }
    },

    'switchList' : function($list){
      $list.blur();
      this.$container.find(this.elemsSelector).removeClass('ms-hover');
      if ($list.parent().hasClass('ms-selectable')){
        this.$selectionUl.focus();
      } else {
        this.$selectableUl.focus();
      }
    },

    'activeMouse' : function($list){
      var that = this;

      $('body').on('mouseenter', that.elemsSelector, function(){
        $(this).parents('.ms-container').find(that.elemsSelector).removeClass('ms-hover');
        $(this).addClass('ms-hover');
      });

      $('body').on('mouseleave', that.elemsSelector, function () {
          $(this).parents('.ms-container').find(that.elemsSelector).removeClass('ms-hover');;
      });
    },

    'refresh' : function() {
      this.destroy();
      this.$element.multiSelect(this.options);
    },

    'destroy' : function(){
      $("#ms-"+this.$element.attr("id")).remove();
      this.$element.css('position', '').css('left', '')
      this.$element.removeData('multiselect');
    },

    'select' : function(value, method){
      if (typeof value === 'string'){ value = [value]; }

      var that = this,
          ms = this.$element,
          msIds = $.map(value, function(val){ return(that.sanitize(val)); }),
          selectables = this.$selectableUl.find('#' + msIds.join('-selectable, #')+'-selectable').filter(':not(.'+that.options.disabledClass+')'),
          selections = this.$selectionUl.find('#' + msIds.join('-selection, #') + '-selection').filter(':not(.'+that.options.disabledClass+')'),
          options = ms.find('option:not(:disabled)').filter(function(){ return($.inArray(this.value, value) > -1); });

      if (method === 'init'){
        selectables = this.$selectableUl.find('#' + msIds.join('-selectable, #')+'-selectable'),
        selections = this.$selectionUl.find('#' + msIds.join('-selection, #') + '-selection');
      }

      if (selectables.length > 0){
        selectables.addClass('ms-selected').hide();
        selections.addClass('ms-selected').show();

        options.prop('selected', true);

        that.$container.find(that.elemsSelector).removeClass('ms-hover');

        var selectableOptgroups = that.$selectableUl.children('.ms-optgroup-container');
        if (selectableOptgroups.length > 0){
          selectableOptgroups.each(function(){
            var selectablesLi = $(this).find('.ms-elem-selectable');
            if (selectablesLi.length === selectablesLi.filter('.ms-selected').length){
              $(this).find('.ms-optgroup-label').hide();
            }
          });

          var selectionOptgroups = that.$selectionUl.children('.ms-optgroup-container');
          selectionOptgroups.each(function(){
            var selectionsLi = $(this).find('.ms-elem-selection');
            if (selectionsLi.filter('.ms-selected').length > 0){
              $(this).find('.ms-optgroup-label').show();
            }
          });
        } else {
          if (that.options.keepOrder && method !== 'init'){
            var selectionLiLast = that.$selectionUl.find('.ms-selected');
            if((selectionLiLast.length > 1) && (selectionLiLast.last().get(0) != selections.get(0))) {
              selections.insertAfter(selectionLiLast.last());
            }
          }
        }
        if (method !== 'init'){
          ms.trigger('change');
          if (typeof that.options.afterSelect === 'function') {
            that.options.afterSelect.call(this, value);
          }
        }
      }
    },

    'deselect' : function(value){
      if (typeof value === 'string'){ value = [value]; }

      var that = this,
          ms = this.$element,
          msIds = $.map(value, function(val){ return(that.sanitize(val)); }),
          selectables = this.$selectableUl.find('#' + msIds.join('-selectable, #')+'-selectable'),
          selections = this.$selectionUl.find('#' + msIds.join('-selection, #')+'-selection').filter('.ms-selected').filter(':not(.'+that.options.disabledClass+')'),
          options = ms.find('option').filter(function(){ return($.inArray(this.value, value) > -1); });

      if (selections.length > 0){
        selectables.removeClass('ms-selected').show();
        selections.removeClass('ms-selected').hide();
        options.prop('selected', false);

        that.$container.find(that.elemsSelector).removeClass('ms-hover');

        var selectableOptgroups = that.$selectableUl.children('.ms-optgroup-container');
        if (selectableOptgroups.length > 0){
          selectableOptgroups.each(function(){
            var selectablesLi = $(this).find('.ms-elem-selectable');
            if (selectablesLi.filter(':not(.ms-selected)').length > 0){
              $(this).find('.ms-optgroup-label').show();
            }
          });

          var selectionOptgroups = that.$selectionUl.children('.ms-optgroup-container');
          selectionOptgroups.each(function(){
            var selectionsLi = $(this).find('.ms-elem-selection');
            if (selectionsLi.filter('.ms-selected').length === 0){
              $(this).find('.ms-optgroup-label').hide();
            }
          });
        }
        ms.trigger('change');
        if (typeof that.options.afterDeselect === 'function') {
          that.options.afterDeselect.call(this, value);
        }
      }
    },

    'select_all' : function(){
      var ms = this.$element,
          values = ms.val();

      ms.find('option:not(":disabled")').prop('selected', true);
      this.$selectableUl.find('.ms-elem-selectable').filter(':not(.'+this.options.disabledClass+')').addClass('ms-selected').hide();
      this.$selectionUl.find('.ms-optgroup-label').show();
      this.$selectableUl.find('.ms-optgroup-label').hide();
      this.$selectionUl.find('.ms-elem-selection').filter(':not(.'+this.options.disabledClass+')').addClass('ms-selected').show();
      this.$selectionUl.focus();
      ms.trigger('change');
      if (typeof this.options.afterSelect === 'function') {
        var selectedValues = $.grep(ms.val(), function(item){
          return $.inArray(item, values) < 0;
        });
        this.options.afterSelect.call(this, selectedValues);
      }
    },

    'deselect_all' : function(){
      var ms = this.$element,
          values = ms.val();

      ms.find('option').prop('selected', false);
      this.$selectableUl.find('.ms-elem-selectable').removeClass('ms-selected').show();
      this.$selectionUl.find('.ms-optgroup-label').hide();
      this.$selectableUl.find('.ms-optgroup-label').show();
      this.$selectionUl.find('.ms-elem-selection').removeClass('ms-selected').hide();
      this.$selectableUl.focus();
      ms.trigger('change');
      if (typeof this.options.afterDeselect === 'function') {
        this.options.afterDeselect.call(this, values);
      }
    },

    sanitize: function(value){
      var hash = 0, i, character;
      if (value.length == 0) return hash;
      var ls = 0;
      for (i = 0, ls = value.length; i < ls; i++) {
        character  = value.charCodeAt(i);
        hash  = ((hash<<5)-hash)+character;
        hash |= 0; // Convert to 32bit integer
      }
      return hash;
    }
  };

  /* MULTISELECT PLUGIN DEFINITION
   * ======================= */

  $.fn.multiSelect = function () {
    var option = arguments[0],
        args = arguments;

    return this.each(function () {
      var $this = $(this),
          data = $this.data('multiselect'),
          options = $.extend({}, $.fn.multiSelect.defaults, $this.data(), typeof option === 'object' && option);

      if (!data){ $this.data('multiselect', (data = new MultiSelect(this, options))); }

      if (typeof option === 'string'){
        data[option](args[1]);
      } else {
        data.init();
      }
    });
  };

  $.fn.multiSelect.defaults = {
    keySelect: [32],
    selectableOptgroup: false,
    disabledClass : 'disabled',
    dblClick : false,
    keepOrder: false,
    cssClass: ''
  };

  $.fn.multiSelect.Constructor = MultiSelect;

  $.fn.insertAt = function(index, $parent) {
    return this.each(function() {
      if (index === 0) {
        $parent.prepend(this);
      } else {
        $parent.children().eq(index - 1).after(this);
      }
    });
}

}(window.jQuery);
