<?php
/**
 * Related posts query customizations.
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show three related posts for the card grid layout.
 *
 * @param int $maxnum Default max number.
 * @return int
 */
add_filter(
	'swell_related_post_maxnum',
	function ( $maxnum ) {
		return 3;
	}
);

/**
 * Order related posts by publish date (newest first).
 *
 * @param array $args WP_Query arguments.
 * @return array
 */
add_filter(
	'swell_related_post_args',
	function ( $args ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';

		return $args;
	}
);
