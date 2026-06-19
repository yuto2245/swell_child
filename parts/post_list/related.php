<?php
/**
 * Related post card item (child theme override).
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$the_id = ( is_array( $variable ) && ! empty( $variable['post_id'] ) ) ? (int) $variable['post_id'] : 0;

$post_data = $the_id ? get_post( $the_id ) : get_post();
if ( ! $post_data instanceof WP_Post ) {
	return;
}

$the_id = $the_id ?: $post_data->ID;

if ( ! is_post_publicly_viewable( $post_data ) ) {
	return;
}

$the_title = get_the_title( $the_id );

$categories       = get_the_category( $the_id );
$primary_category = ! empty( $categories ) ? $categories[0]->name : '';

$raw_excerpt = get_the_excerpt( $the_id );
if ( '' === $raw_excerpt ) {
	$raw_excerpt = wp_strip_all_tags( get_post_field( 'post_content', $the_id ) );
}
$excerpt = wp_trim_words( $raw_excerpt, 40, '...' );

$has_thumbnail = has_post_thumbnail( $the_id );
?>
<li class="sapjp-related-card">
	<a href="<?php echo esc_url( get_permalink( $the_id ) ); ?>" class="sapjp-related-card__link">
		<div class="sapjp-related-card__media">
			<?php if ( $has_thumbnail ) : ?>
				<?php
				echo sapjp_render_post_image( $the_id, 'related_card' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				?>
			<?php else : ?>
				<div class="sapjp-related-card__placeholder" aria-hidden="true"></div>
			<?php endif; ?>
		</div>
		<div class="sapjp-related-card__body">
			<div class="sapjp-related-card__meta">
				<?php if ( $primary_category ) : ?>
					<span><?php echo esc_html( $primary_category ); ?></span>
				<?php endif; ?>
				<time datetime="<?php echo esc_attr( get_the_date( 'c', $the_id ) ); ?>">
					<?php echo esc_html( get_the_date( 'Y.m.d', $the_id ) ); ?>
				</time>
			</div>
			<h3 class="sapjp-related-card__title">
				<?php echo wp_kses( $the_title, \SWELL_Theme::$allowed_text_html ); ?>
			</h3>
			<?php if ( $excerpt ) : ?>
				<p class="sapjp-related-card__excerpt"><?php echo esc_html( $excerpt ); ?></p>
			<?php endif; ?>
			<span class="sapjp-related-card__cta"><?php esc_html_e( '続きを読む', 'swell_child' ); ?></span>
		</div>
	</a>
</li>
