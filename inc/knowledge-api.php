<?php
/**
 * sapjp.net Knowledge API.
 *
 * Provides AI-readable article search and context endpoints for external MCP
 * servers or AI applications.
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) && ! defined( 'SAPJP_KNOWLEDGE_API_TESTING' ) ) {
	exit;
}

if ( defined( 'ABSPATH' ) ) {
	add_action( 'rest_api_init', 'sapjp_knowledge_register_routes' );
	add_filter( 'rest_json_encode_options', 'sapjp_knowledge_rest_json_encode_options', 10, 3 );
}

/**
 * Register REST API routes.
 */
function sapjp_knowledge_register_routes() {
	register_rest_route(
		'sapjp/v1',
		'/search',
		array(
			'methods'             => 'GET',
			'callback'            => 'sapjp_knowledge_rest_search',
			'permission_callback' => '__return_true',
			'args'                => array(
				'query'    => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sapjp_knowledge_normalize_query',
				),
				'category' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'limit'    => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);

	register_rest_route(
		'sapjp/v1',
		'/articles/(?P<id>\d+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'sapjp_knowledge_rest_article',
			'permission_callback' => '__return_true',
		)
	);

	register_rest_route(
		'sapjp/v1',
		'/context',
		array(
			'methods'             => 'GET',
			'callback'            => 'sapjp_knowledge_rest_context',
			'permission_callback' => '__return_true',
			'args'                => array(
				'query'    => array(
					'type'              => 'string',
					'required'          => true,
					'sanitize_callback' => 'sapjp_knowledge_normalize_query',
				),
				'category' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'limit'    => array(
					'type'              => 'integer',
					'required'          => false,
					'sanitize_callback' => 'absint',
				),
			),
		)
	);
}

/**
 * Keep Knowledge API JSON readable for command-line users.
 *
 * @param int                  $options JSON encode options.
 * @param mixed                $result REST response data.
 * @param WP_REST_Request|null $request REST request.
 * @return int
 */
function sapjp_knowledge_rest_json_encode_options( $options, $result = null, $request = null ) {
	if ( ! is_object( $request ) || ! method_exists( $request, 'get_param' ) ) {
		return $options;
	}

	$route = method_exists( $request, 'get_route' ) ? (string) $request->get_route() : '';
	if ( '' === $route ) {
		$route = (string) $request->get_param( 'rest_route' );
	}

	if ( 0 !== strpos( $route, '/sapjp/v1/' ) ) {
		return $options;
	}

	return $options | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
}

/**
 * Normalize a query for article search.
 *
 * @param mixed $query Raw query.
 * @return string
 */
function sapjp_knowledge_normalize_query( $query ) {
	$query = sanitize_text_field( (string) $query );
	return trim( preg_replace( '/\s+/u', ' ', $query ) );
}

/**
 * REST callback: search published articles.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function sapjp_knowledge_rest_search( $request ) {
	$query    = sapjp_knowledge_normalize_query( $request->get_param( 'query' ) );
	$category = sanitize_text_field( (string) $request->get_param( 'category' ) );
	$limit    = sapjp_knowledge_sanitize_limit( $request->get_param( 'limit' ), 10, 20 );
	$articles = sapjp_knowledge_search_articles( $query, $category, $limit );

	return rest_ensure_response(
		array(
			'query' => $query,
			'count' => count( $articles ),
			'items' => $articles,
		)
	);
}

/**
 * REST callback: get one published article.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function sapjp_knowledge_rest_article( $request ) {
	$id   = absint( $request->get_param( 'id' ) );
	$post = get_post( $id );

	if ( ! sapjp_knowledge_is_public_article( $post ) ) {
		return new WP_Error( 'sapjp_not_found', 'Article not found.', array( 'status' => 404 ) );
	}

	return rest_ensure_response( sapjp_knowledge_format_article_from_wp( $post ) );
}

/**
 * REST callback: search and return compact context for AI use.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function sapjp_knowledge_rest_context( $request ) {
	$query    = sapjp_knowledge_normalize_query( $request->get_param( 'query' ) );
	$category = sanitize_text_field( (string) $request->get_param( 'category' ) );
	$limit    = sapjp_knowledge_sanitize_limit( $request->get_param( 'limit' ), 5, 10 );

	if ( '' === $query ) {
		return new WP_Error( 'sapjp_empty_query', 'Query is required.', array( 'status' => 400 ) );
	}

	$articles = sapjp_knowledge_search_articles( $query, $category, $limit );

	return rest_ensure_response( sapjp_knowledge_format_context_response( $query, $articles, 1800 ) );
}

/**
 * Search published WordPress posts.
 *
 * @param string $query Search query.
 * @param string $category Category slug or name.
 * @param int    $limit Max results.
 * @return array<int,array<string,mixed>>
 */
function sapjp_knowledge_search_articles( $query, $category = '', $limit = 10 ) {
	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => sapjp_knowledge_sanitize_limit( $limit, 10, 20 ),
		'ignore_sticky_posts' => true,
		'has_password'        => false,
		'orderby'             => 'relevance',
	);

	if ( '' !== $query ) {
		$args['s'] = $query;
	} else {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
	}

	if ( '' !== $category ) {
		$args['category_name'] = $category;
	}

	$wp_query = new WP_Query( $args );
	$items    = array();

	foreach ( $wp_query->posts as $post ) {
		if ( ! sapjp_knowledge_is_public_article( $post ) ) {
			continue;
		}
		$items[] = sapjp_knowledge_format_article_from_wp( $post );
	}

	return $items;
}

/**
 * Check whether a post may be exposed through the public knowledge API.
 *
 * @param mixed $post Post-like object.
 * @return bool
 */
function sapjp_knowledge_is_public_article( $post ) {
	if ( ! is_object( $post ) ) {
		return false;
	}

	if ( 'post' !== ( $post->post_type ?? '' ) || 'publish' !== ( $post->post_status ?? '' ) ) {
		return false;
	}

	if ( function_exists( 'post_password_required' ) && post_password_required( $post ) ) {
		return false;
	}

	if ( ! empty( $post->post_password ) ) {
		return false;
	}

	return true;
}

/**
 * Format a WordPress post with live metadata.
 *
 * @param WP_Post $post Post object.
 * @return array<string,mixed>
 */
function sapjp_knowledge_format_article_from_wp( $post ) {
	return sapjp_knowledge_format_article(
		$post,
		array(
			'url'        => get_permalink( $post ),
			'categories' => wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) ),
			'tags'       => wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) ),
		)
	);
}

/**
 * Format article data for AI-readable API responses.
 *
 * @param object               $post Post-like object.
 * @param array<string,mixed>  $meta Additional metadata.
 * @return array<string,mixed>
 */
function sapjp_knowledge_format_article( $post, $meta = array() ) {
	$content = sapjp_knowledge_plain_text( $post->post_content ?? '' );
	$excerpt = sapjp_knowledge_plain_text( $post->post_excerpt ?? '' );

	if ( '' === $excerpt ) {
		$excerpt = sapjp_knowledge_create_excerpt( $content, 180 );
	}

	return array(
		'id'          => (int) ( $post->ID ?? 0 ),
		'title'       => sapjp_knowledge_plain_text( $post->post_title ?? '' ),
		'url'         => esc_url_raw( $meta['url'] ?? '' ),
		'excerpt'     => $excerpt,
		'content'     => $content,
		'categories'  => array_values( array_map( 'sanitize_text_field', $meta['categories'] ?? array() ) ),
		'tags'        => array_values( array_map( 'sanitize_text_field', $meta['tags'] ?? array() ) ),
		'published'   => sanitize_text_field( (string) ( $post->post_date ?? '' ) ),
		'modified'    => sanitize_text_field( (string) ( $post->post_modified ?? '' ) ),
		'source_type' => 'wordpress_post',
	);
}

/**
 * Format compact context response for AI applications.
 *
 * @param string                    $query Query.
 * @param array<int,array<string,mixed>> $articles Articles.
 * @param int                       $max_content_length Max content length per source.
 * @return array<string,mixed>
 */
function sapjp_knowledge_format_context_response( $query, $articles, $max_content_length = 1800 ) {
	$sources = array();

	foreach ( $articles as $article ) {
		$sources[] = array(
			'id'         => (int) ( $article['id'] ?? 0 ),
			'title'      => sanitize_text_field( (string) ( $article['title'] ?? '' ) ),
			'url'        => esc_url_raw( $article['url'] ?? '' ),
			'content'    => sapjp_knowledge_create_excerpt( (string) ( $article['content'] ?? '' ), $max_content_length ),
			'categories' => array_values( $article['categories'] ?? array() ),
			'tags'       => array_values( $article['tags'] ?? array() ),
		);
	}

	return array(
		'query'   => sapjp_knowledge_normalize_query( $query ),
		'count'   => count( $sources ),
		'sources' => $sources,
	);
}

/**
 * Convert content to normalized plain text.
 *
 * @param string $content Content.
 * @return string
 */
function sapjp_knowledge_plain_text( $content ) {
	$content = apply_filters( 'the_content', (string) $content );
	$content = wp_strip_all_tags( $content );
	$content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' );
	return trim( preg_replace( '/\s+/u', ' ', $content ) );
}

/**
 * Create a multibyte-safe excerpt.
 *
 * @param string $content Content.
 * @param int    $length Max length.
 * @return string
 */
function sapjp_knowledge_create_excerpt( $content, $length = 180 ) {
	$content = trim( preg_replace( '/\s+/u', ' ', (string) $content ) );
	$length  = max( 1, (int) $length );

	if ( mb_strlen( $content ) <= $length ) {
		return $content;
	}

	return mb_substr( $content, 0, $length ) . '…';
}

/**
 * Sanitize API result limits.
 *
 * @param mixed $value Raw limit.
 * @param int   $default Default limit.
 * @param int   $max Max limit.
 * @return int
 */
function sapjp_knowledge_sanitize_limit( $value, $default = 10, $max = 20 ) {
	$value = absint( $value );
	if ( 0 === $value ) {
		return $default;
	}
	return min( $value, $max );
}
