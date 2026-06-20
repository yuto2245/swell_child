<?php
/**
 * Front-page performance optimizations (LCP, render-blocking CSS, unused assets).
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal above-the-fold CSS for hero LCP when child_style loads async.
 */
define(
	'SAPJP_FRONT_CRITICAL_CSS',
	':root{--color-text:#1D1D1F;--color-text-muted:#86868B;--font-display:"Plus Jakarta Sans","Inter","Noto Sans JP",-apple-system,BlinkMacSystemFont,sans-serif}'
	. '.hero-section{position:relative}'
	. '.hero-container{max-width:min(100%,1320px);margin:0 auto;padding:0 clamp(16px,3vw,40px);text-align:center}'
	. '.hero-title{font-family:var(--font-display);font-size:clamp(2.25rem,1.6rem+2.8vw,3.75rem);font-weight:500;line-height:1.05;margin:0;color:var(--color-text)}'
	. '.hero-word{display:inline-block;margin-right:.22em}'
	. '@media(max-width:767px){.hero-glass-card{opacity:1;animation:none}}'
);

/**
 * Inline critical CSS on the front page only.
 */
add_action(
	'wp_head',
	function () {
		if ( ! is_front_page() ) {
			return;
		}
		echo '<style id="sapjp-front-critical">' . SAPJP_FRONT_CRITICAL_CSS . '</style>' . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	},
	3
);

/**
 * Extend async stylesheet loading to child theme CSS on the front page.
 *
 * @param string $html   Link tag HTML.
 * @param string $handle Style handle.
 * @param string $href   Stylesheet URL.
 * @param string $media  Media attribute.
 * @return string
 */
add_filter(
	'style_loader_tag',
	function ( $html, $handle, $href, $media ) {
		if ( ! is_front_page() || 'child_style' !== $handle ) {
			return $html;
		}

		$default_html = trim( $html );

		// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedStylesheet
		return '<link rel="stylesheet" id="' . esc_attr( $handle ) . '-css" href="' . esc_url( $href ) . '" media="print" onload="this.media=\'all\'">' .
			'<noscript> ' . $default_html . '</noscript>' . "\n";
	},
	10,
	4
);

/**
 * Dequeue block/widget CSS not used by the custom front-page template.
 */
add_action(
	'wp_enqueue_scripts',
	function () {
		if ( ! is_front_page() ) {
			return;
		}

		$handles = array(
			'wp-block-library',
			'swell_blocks',
			'swell_core/tag-cloud',
			'swell_widget/dropdown',
			'swell_core/columns',
			'swell_loos/full-wide',
			'swell_loos/tab',
			'swell_loos/columns',
			'swell_page',
		);

		foreach ( $handles as $handle ) {
			wp_dequeue_style( $handle );
			wp_deregister_style( $handle );
		}
	},
	100
);
