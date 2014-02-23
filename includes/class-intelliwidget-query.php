<?php
// Exit if accessed directly
if ( !defined('ABSPATH')) exit;
/**
 * class-intelliwidget-query.php - IntelliWidget Query Class
 * based in part on code from Wordpress core post.php and query.php
 *
 * @package IntelliWidget
 * @subpackage includes
 * @author Lilaea Media
 * @copyright 2013
 * @access public
 */
class IntelliWidget_Query {
    
    var $post;

    var $posts;
    
    var $post_count   = 0;
    
    var $in_the_loop  = false;
    
    var $current_post = -1;
    
    function __construct($instance = array()) {
        if (! empty($instance))
            $this->iw_query($instance);
    }
    
	/**
	 * Set up the next post and iterate current post index.
	 *
	 * @return next post.
	 */
	function next_post() {

		$this->current_post++;

		$this->post = $this->posts[$this->current_post];
		return $this->post;
	}
	/**
	 * Whether there are more posts available in the loop.
	 *
	 *
	 * @return bool True if posts are available, false if end of loop.
	 */
	function have_posts() {
		if ( $this->current_post + 1 < $this->post_count ) {
			return true;
		} elseif ( $this->current_post + 1 == $this->post_count && $this->post_count > 0 ) {
			// Do some cleaning up after the loop
			$this->rewind_posts();
		}

		$this->in_the_loop = false;
		return false;
	}

	/**
	 * Rewind the posts and reset post index.
	 *
	 */
	function rewind_posts() {
		$this->current_post = -1;
		if ( $this->post_count > 0 ) {
			$this->post = $this->posts[0];
		}
	}


	/**
	 * Sets up the current post.
	 *
	 * Retrieves the next post, sets up the post, sets the 'in the loop'
	 * property to true.
	 *
	 * @uses $post
	 */
	function the_post() {
		global $post;
		$this->in_the_loop = true;

		if ( -1 == $this->current_post ){ // loop has just started
            // stub for future functionality
        }
        
		$post = $this->next_post();
	}
    /* Intelliwidget has a lot of internal logic that can't be done efficiently using the standard
     * WP_Query parameters. This function dyanamically builds a custom query so that the majority of the 
     * post and postmeta data can be retrieved in a single db query.
     */
    function iw_query($instance = null) {
        if (empty($instance)) return;
        global $wpdb, $post;
        // filter = raw lets IW posts play nice with WP post functions for backward compatability
        $select = "
SELECT DISTINCT
    p1.ID,
    p1.post_content, 
    p1.post_excerpt, 
    p1.post_title,
    COALESCE(NULLIF(pm2.meta_value, ''), p1.post_date) AS post_date,
    p1.post_author,
    'raw' AS filter,
    pm1.meta_value AS expire_date, 
    pm2.meta_value AS event_date, 
    pm3.meta_value AS link_classes,
    pm4.meta_value AS alt_title,
    pm5.meta_value AS link_target,
    pm6.meta_value AS external_url,
    pm7.meta_value AS thumbnail_id
FROM {$wpdb->posts} p1
";
    $joins = array("
LEFT JOIN {$wpdb->postmeta} pm1 ON pm1.post_id = p1.ID
    AND pm1.meta_key = 'intelliwidget_expire_date'
            ", "
LEFT JOIN {$wpdb->postmeta} pm2 ON pm2.post_id = p1.ID
    AND pm2.meta_key = 'intelliwidget_event_date'
            ", "
LEFT JOIN {$wpdb->postmeta} pm3 ON pm3.post_id = p1.ID
    AND pm3.meta_key = 'intelliwidget_link_classes'
            ", "
LEFT JOIN {$wpdb->postmeta} pm4 ON pm4.post_id = p1.ID
    AND pm4.meta_key = 'intelliwidget_alt_title'
            ", "
LEFT JOIN {$wpdb->postmeta} pm5 ON pm5.post_id = p1.ID
    AND pm5.meta_key = 'intelliwidget_link_target'
            ", "
LEFT JOIN {$wpdb->postmeta} pm6 ON pm6.post_id = p1.ID
    AND pm6.meta_key = 'intelliwidget_external_url'
            ", "
LEFT JOIN {$wpdb->postmeta} pm7 ON pm7.post_id = p1.ID
    AND pm7.meta_key = '_thumbnail_id'
            ");
        $clauses = array(
            "(p1.post_status = 'publish')",
            "(p1.post_password = '' OR p1.post_password IS NULL)",
        );
        // categories
        $prepargs = array();
        if (-1 != $instance['category']):
            $clauses[] = '( tx1.term_taxonomy_id IN ('. $this->prep_array($instance['category'], $prepargs, 'd') . ') )';
            $joins[] = "INNER JOIN {$wpdb->term_relationships} tx1 ON p1.ID = tx1.object_id ";
        endif;
        
        // specific posts
        if (!empty($instance['page'])):
            $clauses[] = '(p1.ID IN ('. $this->prep_array($instance['page'], $prepargs, 'd') . ') )';
        endif;
        /* Remove current page from list of pages if set */
        if ( $instance['skip_post'] && !empty($post)):
            $clauses[] = "(p1.ID != %d )";
            $prepargs[] = $post->ID;
        endif;
        
        // post types
        if (empty($instance['post_types'])):
            $instance['post_types'] = 'none';
        endif;
        $clauses[] = '(p1.post_type IN ('. $this->prep_array($instance['post_types'], $prepargs) . ') )';
        // time-based clauses //
        
        $time_adj = gmdate('Y-m-d H:i', current_time('timestamp') );

        // skip all expired posts
        // postmeta intelliwidget_expire_date date format 
        // MUST be YYYY-MM-DD HH:MM for this to work correctly!
        if ($instance['skip_expired']):
            $clauses[] = "(pm1.meta_value IS NULL OR (pm1.meta_value IS NOT NULL AND CAST( pm1.meta_value AS CHAR ) > '" . $time_adj . "'))";
        endif;
        // show posts that have not started yet only
        // postmeta intelliwidget_event_date date format 
        // MUST be YYYY-MM-DD HH:MM for this to work correctly!
        if ($instance['future_only'] ):
            $clauses[] = "(pm2.meta_value IS NOT NULL AND CAST( pm2.meta_value AS CHAR ) > '" . $time_adj . "')";
        endif;
        // skip posts that have not started yet
        // postmeta intelliwidget_event_date date format 
        // MUST be YYYY-MM-DD HH:MM for this to work correctly!
        if ($instance['active_only'] ):
            $clauses[] = "(pm2.meta_value IS NULL OR (pm2.meta_value IS NOT NULL AND CAST( pm2.meta_value AS CHAR ) < '" . $time_adj . "'))";
        endif;

        $order = $instance['sortorder'] == 'ASC' ? 'ASC' : 'DESC';
        switch ($instance['sortby']):
            case 'event_date':
                $orderby = 'pm2.meta_value ' . $order;
                break;
            case 'rand':
                $orderby = 'RAND()';
                break;
            case 'menu_order':
                $orderby = 'p1.menu_order ' . $order;
                break;
            case 'date':
                $orderby = 'p1.post_date ' . $order;
                break;
            case 'title':
            default:
                $orderby = 'p1.post_title ' . $order;
                break;
            
        endswitch;
        $orderby = ' ORDER BY ' . $orderby;
        $items = intval($instance['items']);
        $limit = '';
        if (!empty($items)): 
            $limit = ' LIMIT 0, %d';
            $prepargs[] = $items;
        endif;
        $query = $select . implode(' ', $joins) . ' WHERE ' . implode("\n AND ", $clauses) . $orderby . $limit;
        //echo 'query: ' . "\n" . $query . " \n";
        $this->posts      = $wpdb->get_results($wpdb->prepare($query, $prepargs), OBJECT);
        $this->post_count = count($this->posts);
    }

    function prep_array($value, &$args, $type = 's') {
        $values = is_array($value) ? $value : explode(',', $value);
        $placeholders = array();
        foreach($values as $val):
            $placeholders[] = ('d' == $type ? '%d' : '%s');
            $args[] = trim($val);
        endforeach;
        
/*
        array_walk_recursive($values, array($this, 'trimming'),  use (&$placeholders, &$args, $type) { 
            $placeholders[] = ('s' == $type ? '%s' : '%d');
            $args[] = trim($a);
        });
*/
        return implode(',', $placeholders);
    }
    function trimming($data) {
        if ('array' === gettype($data))
            return array_map('trimming', $data);
        else
        return trim($data);
    }
}