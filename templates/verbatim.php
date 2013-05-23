<?php
if ( !defined('ABSPATH')) exit;
/**
 * verbatim.php - Echos post content verbatim - use for "CMS-style" content blocks
 *
 * This can be copied to a folder named 'intelliwidget' in your theme
 * to customize the output.
 *
 * @package IntelliWidget
 * @subpackage templates
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
global $iwgt_post;
if ( !empty($selected)) : foreach($selected as $iwgt_post) : 
    the_iwgt_content(); 
endforeach; endif; 

?>