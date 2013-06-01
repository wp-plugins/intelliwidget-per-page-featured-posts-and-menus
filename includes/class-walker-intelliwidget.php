<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-walker-intelliwidget.php - IntelliWidget Walker Class
 * based in part on code from Wordpress core post-template.php
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
class Walker_IntelliWidget extends Walker {
	/**
	 * @see Walker::$tree_type
	 * @var string
	 */
	var $tree_type = 'page';

	/**
	 * @see Walker::$db_fields
	 * @var array
	 */
	var $db_fields = array ('parent' => 'post_parent', 'id' => 'ID');

	/**
	 * @see Walker::start_el()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param object $page Page data object.
	 * @param int $depth Depth of page in reference to parent pages. Used for padding.
	 * @param array $args Uses 'selected' argument for selected page to set selected HTML attribute for option element.
	 * @param int $id
	 */
	function start_el(&$output, $page, $depth, $args, $id = 0) {
		$pad = str_repeat('-&nbsp;', $depth);

		$output .= "\t<option class=\"level-$depth\" value=\"$page->ID\"";
		if (in_array( $page->ID, $args['page'] ))
			$output .= ' selected="selected"';
		$output .= '>';
		$title = substr($pad . $page->post_title, 0, 60) . ' (' . ucfirst($page->post_type) . ')';
		$output .= esc_html( $title );
		$output .= "</option>\n";
	}
}
