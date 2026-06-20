<?php
/**
 * Image delivery optimizations (sizes, lazy, fetchpriority, deferred tab loads).
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * 1x1 transparent SVG placeholder for deferred images.
 */
define( 'SAPJP_IMG_PLACEHOLDER', "data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 9'%3E%3C/svg%3E" );

/**
 * Register custom image sizes (no crop — preserve aspect ratio).
 */
add_action(
	'after_setup_theme',
	function () {
		add_image_size( 'sapjp_card', 640, 0, false );
		add_image_size( 'sapjp_feature', 960, 0, false );
		add_image_size( 'sapjp_hero', 1200, 0, false );
	},
	20
);

/**
 * Context-specific image delivery config.
 *
 * @param string $context Delivery context key.
 * @return array<string, mixed>|null
 */
function sapjp_get_image_context_config( $context ) {
	$configs = array(
		'article_hero'        => array(
			'size'          => 'sapjp_feature',
			'sizes'         => '(min-width: 960px) 960px, 100vw',
			'loading'       => 'eager',
			'fetchpriority' => 'high',
			'class'         => 'p-articleThumb__img',
			'deferred'      => false,
		),
		'front_feature'       => array(
			'size'          => 'sapjp_feature',
			'sizes'         => '(min-width: 1024px) 50vw, 100vw',
			'loading'       => 'lazy',
			'fetchpriority' => '',
			'class'         => '',
			'deferred'      => false,
		),
		'front_card'          => array(
			'size'          => 'sapjp_card',
			'sizes'         => '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
			'loading'       => 'lazy',
			'fetchpriority' => 'auto',
			'class'         => '',
			'deferred'      => false,
		),
		'front_card_deferred' => array(
			'size'          => 'sapjp_card',
			'sizes'         => '(min-width: 1024px) 33vw, (min-width: 640px) 50vw, 100vw',
			'loading'       => '',
			'fetchpriority' => '',
			'class'         => '',
			'deferred'      => true,
		),
		'related_card'        => array(
			'size'          => 'sapjp_card',
			'sizes'         => '(min-width: 1024px) 360px, (min-width: 640px) 50vw, 100vw',
			'loading'       => 'lazy',
			'fetchpriority' => 'auto',
			'class'         => 'sapjp-related-card__img u-obf-cover',
			'deferred'      => false,
		),
	);

	return $configs[ $context ] ?? null;
}

/**
 * Build attachment image attributes for a context.
 *
 * @param int                  $attachment_id Attachment ID.
 * @param array<string, mixed> $config        Context config.
 * @return array<string, string|false>
 */
function sapjp_build_attachment_image_attrs( $attachment_id, $config ) {
	$alt = trim( (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) );

	$attrs = array(
		'class'    => $config['class'] ?? '',
		'alt'      => $alt,
		'decoding' => 'async',
		'sizes'    => $config['sizes'] ?? false,
	);

	if ( ! empty( $config['loading'] ) ) {
		$attrs['loading'] = $config['loading'];
	}

	if ( ! empty( $config['fetchpriority'] ) && 'auto' !== $config['fetchpriority'] ) {
		$attrs['fetchpriority'] = $config['fetchpriority'];
	}

	return $attrs;
}

/**
 * Render a post thumbnail for the given delivery context.
 *
 * @param int    $post_id Post ID.
 * @param string $context Context key.
 * @return string HTML img tag or empty string.
 */
function sapjp_render_post_image( $post_id, $context ) {
	$config = sapjp_get_image_context_config( $context );
	if ( ! $config || ! has_post_thumbnail( $post_id ) ) {
		return '';
	}

	$attachment_id = (int) get_post_thumbnail_id( $post_id );
	if ( ! $attachment_id ) {
		return '';
	}

	$size  = $config['size'];
	$attrs = sapjp_build_attachment_image_attrs( $attachment_id, $config );

	if ( ! empty( $config['deferred'] ) ) {
		$src_data = wp_get_attachment_image_src( $attachment_id, $size );
		if ( ! $src_data ) {
			return '';
		}

		$image_meta = wp_get_attachment_metadata( $attachment_id );
		$srcset     = '';
		if ( is_array( $image_meta ) ) {
			$srcset = (string) wp_calculate_image_srcset(
				array( absint( $src_data[1] ), absint( $src_data[2] ) ),
				$src_data[0],
				$image_meta,
				$attachment_id
			);
		}

		$class = $attrs['class'] ? ' class="' . esc_attr( $attrs['class'] ) . '"' : '';
		$alt   = ' alt="' . esc_attr( $attrs['alt'] ) . '"';

		$html  = '<img src="' . esc_attr( SAPJP_IMG_PLACEHOLDER ) . '"';
		$html .= $class . $alt;
		$html .= ' decoding="async"';
		$html .= ' data-sapjp-src="' . esc_attr( $src_data[0] ) . '"';
		if ( $srcset ) {
			$html .= ' data-sapjp-srcset="' . esc_attr( $srcset ) . '"';
		}
		if ( ! empty( $attrs['sizes'] ) ) {
			$html .= ' data-sapjp-sizes="' . esc_attr( $attrs['sizes'] ) . '"';
		}
		$html .= '>';

		return $html;
	}

	return wp_get_attachment_image(
		$attachment_id,
		$size,
		false,
		$attrs
	);
}

/**
 * Optimize single-post featured image (LCP).
 *
 * @param string $return     Original HTML.
 * @param int    $post_id    Post ID.
 * @param bool   $is_youtube Whether the thumbnail is a YouTube embed.
 * @return string
 */
add_filter(
	'swell_parts_post_thumbnail',
	function ( $return, $post_id, $is_youtube ) {
		if ( $is_youtube ) {
			return $return;
		}

		$thumb = sapjp_render_post_image( (int) $post_id, 'article_hero' );
		if ( '' === $thumb ) {
			return $return;
		}

		$caption = get_post_meta( $post_id, 'swell_meta_thumb_caption', true ) ?: '';
		if ( $caption ) {
			$caption = '<figcaption class="p-articleThumb__caption">' . wp_kses( $caption, \SWELL_Theme::$allowed_text_html ) . '</figcaption>';
		}

		return '<figure class="p-articleThumb">' . $thumb . $caption . '</figure>';
	},
	10,
	3
);

/**
 * Optimize in-content images on single posts.
 *
 * @param string $filtered_image Full img tag.
 * @param string $context        Filter context.
 * @param int    $attachment_id  Attachment ID.
 * @return string
 */
add_filter(
	'wp_content_img_tag',
	function ( $filtered_image, $context, $attachment_id ) {
		if ( is_admin() || ! is_singular( 'post' ) || 'the_content' !== $context ) {
			return $filtered_image;
		}

		static $content_img_count = 0;
		++$content_img_count;

		$filtered_image = preg_replace( '/\s+sizes="[^"]*"/', '', $filtered_image );
		$filtered_image = preg_replace( '/\s+loading="[^"]*"/', '', $filtered_image );
		$filtered_image = preg_replace( '/\s+decoding="[^"]*"/', '', $filtered_image );
		$filtered_image = preg_replace( '/\s+fetchpriority="[^"]*"/', '', $filtered_image );

		$inject  = ' sizes="(min-width: 960px) 844px, 100vw"';
		$inject .= ' decoding="async"';

		if ( $content_img_count > 1 ) {
			$inject .= ' loading="lazy"';
		}

		return preg_replace( '/<img /', '<img' . $inject . ' ', $filtered_image, 1 );
	},
	10,
	3
);
