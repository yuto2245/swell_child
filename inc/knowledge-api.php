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
				'q'        => array(
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
			'args'                => array(
				'format' => array(
					'type'              => 'string',
					'required'          => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		)
	);

	register_rest_route(
		'sapjp/v1',
		'/articles/(?P<id>\d+)/code',
		array(
			'methods'             => 'GET',
			'callback'            => 'sapjp_knowledge_rest_article_code',
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
					'required'          => false,
					'sanitize_callback' => 'sapjp_knowledge_normalize_query',
				),
				'q'        => array(
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
		'/topics/(?P<topic>[A-Za-z0-9_-]+)',
		array(
			'methods'             => 'GET',
			'callback'            => 'sapjp_knowledge_rest_topic',
			'permission_callback' => '__return_true',
			'args'                => array(
				'limit' => array(
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
 * Read the API query parameter with q as a CLI-friendly alias.
 *
 * @param WP_REST_Request $request Request.
 * @return string
 */
function sapjp_knowledge_get_query_param( $request ) {
	$query = sapjp_knowledge_normalize_query( $request->get_param( 'query' ) );
	if ( '' === $query ) {
		$query = sapjp_knowledge_normalize_query( $request->get_param( 'q' ) );
	}
	return $query;
}

/**
 * REST callback: search published articles.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function sapjp_knowledge_rest_search( $request ) {
	$query    = sapjp_knowledge_get_query_param( $request );
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

	return rest_ensure_response( sapjp_knowledge_format_article_from_wp( $post, sanitize_text_field( (string) $request->get_param( 'format' ) ) ) );
}

/**
 * REST callback: get code snippets from one published article.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response|WP_Error
 */
function sapjp_knowledge_rest_article_code( $request ) {
	$id   = absint( $request->get_param( 'id' ) );
	$post = get_post( $id );

	if ( ! sapjp_knowledge_is_public_article( $post ) ) {
		return new WP_Error( 'sapjp_not_found', 'Article not found.', array( 'status' => 404 ) );
	}

	return rest_ensure_response(
		sapjp_knowledge_format_code_response(
			sapjp_knowledge_format_article_from_wp( $post ),
			$post->post_content ?? ''
		)
	);
}

/**
 * REST callback: search and return compact context for AI use.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function sapjp_knowledge_rest_context( $request ) {
	$query    = sapjp_knowledge_get_query_param( $request );
	$category = sanitize_text_field( (string) $request->get_param( 'category' ) );
	$limit    = sapjp_knowledge_sanitize_limit( $request->get_param( 'limit' ), 5, 10 );

	if ( '' === $query ) {
		return new WP_Error( 'sapjp_empty_query', 'Query is required.', array( 'status' => 400 ) );
	}

	$articles = sapjp_knowledge_search_articles( $query, $category, $limit );

	return rest_ensure_response( sapjp_knowledge_format_context_response( $query, $articles, 1800 ) );
}

/**
 * REST callback: get articles by category or tag topic.
 *
 * @param WP_REST_Request $request Request.
 * @return WP_REST_Response
 */
function sapjp_knowledge_rest_topic( $request ) {
	$topic = sanitize_title( (string) $request->get_param( 'topic' ) );
	$limit = sapjp_knowledge_sanitize_limit( $request->get_param( 'limit' ), 10, 20 );

	return rest_ensure_response( sapjp_knowledge_format_topic_response( $topic, sapjp_knowledge_search_topic_articles( $topic, $limit ) ) );
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
 * Search published posts that belong to a category or tag slug.
 *
 * @param string $topic Category or tag slug.
 * @param int    $limit Max results.
 * @return array<int,array<string,mixed>>
 */
function sapjp_knowledge_search_topic_articles( $topic, $limit = 10 ) {
	$topic = sanitize_title( (string) $topic );
	if ( '' === $topic ) {
		return array();
	}

	$args = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => sapjp_knowledge_sanitize_limit( $limit, 10, 20 ),
		'ignore_sticky_posts' => true,
		'has_password'        => false,
		'orderby'             => 'date',
		'order'               => 'DESC',
		'tax_query'           => array(
			'relation' => 'OR',
			array(
				'taxonomy' => 'category',
				'field'    => 'slug',
				'terms'    => $topic,
			),
			array(
				'taxonomy' => 'post_tag',
				'field'    => 'slug',
				'terms'    => $topic,
			),
		),
	);

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
function sapjp_knowledge_format_article_from_wp( $post, $format = '' ) {
	return sapjp_knowledge_format_article(
		$post,
		array(
			'url'        => get_permalink( $post ),
			'categories' => wp_get_post_categories( $post->ID, array( 'fields' => 'names' ) ),
			'tags'       => wp_get_post_tags( $post->ID, array( 'fields' => 'names' ) ),
			'format'     => $format,
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

	$article = array(
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

	if ( 'structured' === sanitize_text_field( (string) ( $meta['format'] ?? '' ) ) ) {
		$article['sections'] = sapjp_knowledge_extract_sections( $post->post_content ?? '' );
	}

	return $article;
}

/**
 * Extract h2/h3 sections from article HTML.
 *
 * @param string $html Article HTML.
 * @return array<int,array<string,string>>
 */
function sapjp_knowledge_extract_sections( $html ) {
	$html = (string) $html;
	if ( '' === trim( $html ) ) {
		return array();
	}

	$pattern = '/<(h[23])[^>]*>(.*?)<\/\1>/isu';
	if ( ! preg_match_all( $pattern, $html, $matches, PREG_OFFSET_CAPTURE ) ) {
		return array(
			array(
				'heading' => '本文',
				'level'   => 'body',
				'slug'    => 'body',
				'content' => sapjp_knowledge_plain_text( $html ),
			),
		);
	}

	$sections = array();
	$count    = count( $matches[0] );

	for ( $i = 0; $i < $count; $i++ ) {
		$heading_html = $matches[2][ $i ][0];
		$heading      = sapjp_knowledge_plain_text( $heading_html );
		$level        = strtolower( $matches[1][ $i ][0] );
		$start        = $matches[0][ $i ][1] + strlen( $matches[0][ $i ][0] );
		$end          = ( $i + 1 < $count ) ? $matches[0][ $i + 1 ][1] : strlen( $html );
		$body         = substr( $html, $start, $end - $start );

		$sections[] = array(
			'heading' => $heading,
			'level'   => $level,
			'slug'    => sanitize_title( $heading ),
			'content' => sapjp_knowledge_plain_text( $body ),
		);
	}

	return $sections;
}

/**
 * Extract code examples from article HTML.
 *
 * @param string $html Article HTML.
 * @return array<int,array<string,string>>
 */
function sapjp_knowledge_extract_code_snippets( $html ) {
	$html = (string) $html;
	if ( '' === trim( $html ) ) {
		return array();
	}

	$snippets = array();
	$pattern  = '/<pre[^>]*>\s*(?:<code([^>]*)>)?(.*?)(?:<\/code>)?\s*<\/pre>/isu';

	if ( preg_match_all( $pattern, $html, $matches, PREG_SET_ORDER ) ) {
		foreach ( $matches as $match ) {
			$code = sapjp_knowledge_decode_code_text( $match[2] ?? '' );
			if ( '' === $code ) {
				continue;
			}

			$snippets[] = array(
				'language' => sapjp_knowledge_detect_code_language( $match[1] ?? '', $code ),
				'code'     => $code,
			);
		}
	}

	return $snippets;
}

/**
 * Format article code snippets for the REST API.
 *
 * @param array<string,mixed> $article Formatted article.
 * @param string              $html Original article HTML.
 * @return array<string,mixed>
 */
function sapjp_knowledge_format_code_response( $article, $html ) {
	$snippets = sapjp_knowledge_extract_code_snippets( $html );

	return array(
		'article_id' => (int) ( $article['id'] ?? 0 ),
		'title'      => sanitize_text_field( (string) ( $article['title'] ?? '' ) ),
		'url'        => esc_url_raw( $article['url'] ?? '' ),
		'count'      => count( $snippets ),
		'snippets'   => $snippets,
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
	$chunks  = array();

	foreach ( $articles as $article ) {
		$source = array(
			'id'         => (int) ( $article['id'] ?? 0 ),
			'title'      => sanitize_text_field( (string) ( $article['title'] ?? '' ) ),
			'url'        => esc_url_raw( $article['url'] ?? '' ),
			'content'    => sapjp_knowledge_create_excerpt( (string) ( $article['content'] ?? '' ), $max_content_length ),
			'categories' => array_values( $article['categories'] ?? array() ),
			'tags'       => array_values( $article['tags'] ?? array() ),
		);
		$sources[] = $source;
		$chunks[]  = sprintf(
			"## %s\nURL: %s\n%s",
			$source['title'],
			$source['url'],
			$source['content']
		);
	}

	return array(
		'query'     => sapjp_knowledge_normalize_query( $query ),
		'count'     => count( $sources ),
		'context'   => implode( "\n\n", $chunks ),
		'sources'   => $sources,
		'citations' => sapjp_knowledge_format_citations( $sources ),
	);
}

/**
 * Format topic response using compact article summaries.
 *
 * @param string                    $topic Topic slug or label.
 * @param array<int,array<string,mixed>> $articles Articles.
 * @return array<string,mixed>
 */
function sapjp_knowledge_format_topic_response( $topic, $articles ) {
	$items = array();

	foreach ( $articles as $article ) {
		$items[] = array(
			'id'         => (int) ( $article['id'] ?? 0 ),
			'title'      => sanitize_text_field( (string) ( $article['title'] ?? '' ) ),
			'url'        => esc_url_raw( $article['url'] ?? '' ),
			'excerpt'    => sapjp_knowledge_create_excerpt( (string) ( $article['excerpt'] ?? '' ), 220 ),
			'categories' => array_values( $article['categories'] ?? array() ),
			'tags'       => array_values( $article['tags'] ?? array() ),
			'published'  => sanitize_text_field( (string) ( $article['published'] ?? '' ) ),
		);
	}

	return array(
		'topic' => sanitize_title( (string) $topic ),
		'count' => count( $items ),
		'items' => $items,
	);
}

/**
 * Format source citations for AI answer generation.
 *
 * @param array<int,array<string,mixed>> $sources Context sources.
 * @return array<int,array<string,mixed>>
 */
function sapjp_knowledge_format_citations( $sources ) {
	$citations = array();

	foreach ( $sources as $source ) {
		$citations[] = array(
			'id'    => (int) ( $source['id'] ?? 0 ),
			'title' => sanitize_text_field( (string) ( $source['title'] ?? '' ) ),
			'url'   => esc_url_raw( $source['url'] ?? '' ),
		);
	}

	return $citations;
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
 * Decode code HTML while preserving line breaks.
 *
 * @param string $code_html Code HTML.
 * @return string
 */
function sapjp_knowledge_decode_code_text( $code_html ) {
	$code = preg_replace( '/<br\s*\/?>/i', "\n", (string) $code_html );
	$code = wp_strip_all_tags( $code );
	$code = html_entity_decode( $code, ENT_QUOTES | ENT_HTML5, get_bloginfo( 'charset' ) ?: 'UTF-8' );
	$code = preg_replace( "/\r\n?/", "\n", $code );
	return trim( $code );
}

/**
 * Detect a code block language from class names or content.
 *
 * @param string $attributes Code tag attributes.
 * @param string $code Code text.
 * @return string
 */
function sapjp_knowledge_detect_code_language( $attributes, $code ) {
	if ( preg_match( '/(?:language|lang)-([a-z0-9_-]+)/i', (string) $attributes, $match ) ) {
		return sanitize_text_field( strtolower( $match[1] ) );
	}

	if ( preg_match( '/\b(SELECT|DATA|LOOP|READ TABLE|CALL FUNCTION|CLASS|METHOD|ENDSELECT)\b/i', (string) $code ) ) {
		return 'abap';
	}

	return 'text';
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
