<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;

/*
    Plugin Name: IntelliWidget Per Page Featured Posts and Menus
    Plugin URI: http://www.lilaeamedia.com/plugins/intelliwidget
    Description: Display featured posts, custom menus, html content and more within a single dynamic sidebar that can be customized on a per-page or site-wide basis.
    Version: 1.4.5
    Author: Lilaea Media
    Author URI: http://www.lilaeamedia.com/
    Text Domain: intelliwidget
    Domain Path: /lang
    License: GPLv2
    * *************************************************************************
    Copyright (C) 2013 Lilaea Media
    Portions adapted from Featured Page Widget 
    Copyright (C) 2009-2011 GrandSlambert http://grandslambert.com/
*/

require_once( 'includes/class-intelliwidget.php' );
require_once( 'includes/class-widget-intelliwidget.php' );
require_once( 'includes/template-tags.php' );
global $intelliwidget;
$intelliwidget = new IntelliWidget( __FILE__ );
