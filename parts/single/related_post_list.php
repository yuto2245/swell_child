<?php
/**
 * Related posts section (child theme override).
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$the_id = get_the_ID();
$not_in = array( $the_id );
$maxnum = apply_filters( 'swell_related_post_maxnum', 8 );

/**
 * 指定された記事を優先的に表示する
 */
$priority_posts = get_post_meta( $the_id, 'swell_meta_related_posts', true ) ?: '';
$priority_list  = '';

if ( $priority_posts ) {
	$priority_posts = explode( ',', $priority_posts );
	$priority_posts = array_map( 'intval', $priority_posts );
	$priority_posts = array_values(
		array_filter(
			$priority_posts,
			function ( $priority_id ) {
				return $priority_id > 0 && is_post_publicly_viewable( $priority_id );
			}
		)
	);

	if ( ! empty( $priority_posts ) ) {
		ob_start();
		foreach ( $priority_posts as $priority_id ) {
			SWELL_Theme::get_parts( 'parts/post_list/related', array( 'post_id' => $priority_id ) );
		}
		$priority_list = ob_get_clean();
		wp_reset_postdata();

		$not_in = array_merge( $not_in, $priority_posts );
		$maxnum = $maxnum - count( $priority_posts );
		$maxnum = $maxnum > 0 ? $maxnum : 0;
	}
}

/**
 * ここから普通に関連記事を取得
 */
$args = array(
	'post__not_in'        => $not_in,
	'post_type'           => get_post_type(),
	'post_status'         => 'publish',
	'no_found_rows'       => true,
	'ignore_sticky_posts' => true,
	'orderby'             => SWELL_Theme::get_setting( 'related_post_orderby' ),
);

if ( 'category' === SWELL_Theme::get_setting( 'post_relation_type' ) ) {
	$categories = get_the_category( $the_id );
	$cat_array  = array();

	foreach ( $categories as $the_cat ) {
		array_push( $cat_array, $the_cat->cat_ID );
	}
	if ( ! empty( $cat_array ) ) {
		$args['category__in'] = $cat_array;
	}
} else {
	$tags      = wp_get_post_tags( $the_id );
	$tag_array = array();

	foreach ( $tags as $the_tag ) {
		array_push( $tag_array, $the_tag->term_id );
	}

	if ( ! empty( $tag_array ) ) {
		$args['tag__in'] = $tag_array;
	}
}

$list_class = 'sapjp-related-grid';
?>
<section class="l-articleBottom__section -related sapjp-related-section">
	<?php
	echo '<h2 class="l-articleBottom__title c-secTitle sapjp-related-section__title">' .
		wp_kses( SWELL_Theme::get_setting( 'related_post_title' ), SWELL_Theme::$allowed_text_html ) .
		'</h2>';

	if ( 0 === $maxnum ) :
		echo '<ul class="' . esc_attr( $list_class ) . '">';
		echo $priority_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</ul>';
	else :
		$args = apply_filters( 'swell_related_post_args', $args );
		$args['posts_per_page'] = $maxnum;

		$q = new WP_Query( $args );

		if ( '' === $priority_list && ! $q->have_posts() ) :
			$not_founded_text = __( '関連する記事はまだ見つかりませんでした。', 'swell' );
			echo apply_filters( 'swell_related_post_404_text', '<p class="sapjp-related-section__empty">' . $not_founded_text . '</p>' ); // phpcs:ignore
		elseif ( $q->have_posts() ) :
			echo '<ul class="' . esc_attr( $list_class ) . '">';
			echo $priority_list; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			while ( $q->have_posts() ) :
				$q->the_post();
				SWELL_Theme::get_parts( 'parts/post_list/related' );
			endwhile;

			echo '</ul>';
		endif;
		wp_reset_postdata();
	endif;
	?>
</section>
