<?php

define( 'SAPJP_KNOWLEDGE_API_TESTING', true );

require_once __DIR__ . '/../inc/knowledge-api.php';

function assert_same( $expected, $actual, $message ) {
	if ( $expected !== $actual ) {
		fwrite( STDERR, $message . PHP_EOL );
		fwrite( STDERR, 'Expected: ' . var_export( $expected, true ) . PHP_EOL );
		fwrite( STDERR, 'Actual:   ' . var_export( $actual, true ) . PHP_EOL );
		exit( 1 );
	}
}

function assert_true( $actual, $message ) {
	if ( true !== (bool) $actual ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

function assert_false( $actual, $message ) {
	if ( false !== (bool) $actual ) {
		fwrite( STDERR, $message . PHP_EOL );
		exit( 1 );
	}
}

function sanitize_text_field( $value ) {
	return trim( preg_replace( '/\s+/', ' ', strip_tags( (string) $value ) ) );
}

function wp_strip_all_tags( $value ) {
	return strip_tags( (string) $value );
}

function apply_filters( $name, $value ) {
	return $value;
}

function get_bloginfo( $show ) {
	return 'UTF-8';
}

function esc_url_raw( $url ) {
	return filter_var( (string) $url, FILTER_SANITIZE_URL );
}

function sanitize_title( $value ) {
	$value = strtolower( preg_replace( '/[^a-zA-Z0-9_\-\x{3040}-\x{30ff}\x{3400}-\x{9fff}]+/u', '-', (string) $value ) );
	return trim( $value, '-' );
}

function absint( $value ) {
	return max( 0, (int) $value );
}

function post_password_required( $post ) {
	return ! empty( $post->post_password );
}

class WP_Error {
	public $code;
	public $message;
	public $data;

	public function __construct( $code, $message, $data = array() ) {
		$this->code    = $code;
		$this->message = $message;
		$this->data    = $data;
	}
}

class Sapjp_Test_Request {
	private $params;

	public function __construct( $params ) {
		$this->params = $params;
	}

	public function get_param( $key ) {
		return $this->params[ $key ] ?? null;
	}

	public function get_route() {
		return $this->params['route'] ?? '';
	}
}

$normalized = sapjp_knowledge_normalize_query( "  ABAP\nSELECT\t内部テーブル  " );
assert_same( 'ABAP SELECT 内部テーブル', $normalized, 'Query should be normalized for search.' );

$post = (object) array(
	'ID'           => 42,
	'post_type'    => 'post',
	'post_status'  => 'publish',
	'post_password' => '',
	'post_title'   => 'ABAP SELECTの基本',
	'post_content' => "<h2>概要</h2>\n<p>SELECT 文の基本です。</p>\n<pre><code>SELECT * FROM vbak.</code></pre>",
	'post_excerpt' => '',
	'post_date'    => '2026-05-14 10:00:00',
	'post_modified' => '2026-05-14 11:00:00',
);

$formatted = sapjp_knowledge_format_article(
	$post,
	array(
		'url'        => 'https://sapjp.net/abap-select/',
		'categories' => array( 'ABAP', 'SAP' ),
		'tags'       => array( 'SELECT', '内部テーブル' ),
	)
);

assert_same( 42, $formatted['id'], 'Formatted article should include ID.' );
assert_same( 'ABAP SELECTの基本', $formatted['title'], 'Formatted article should include title.' );
assert_same( 'https://sapjp.net/abap-select/', $formatted['url'], 'Formatted article should include URL.' );
assert_same( array( 'ABAP', 'SAP' ), $formatted['categories'], 'Formatted article should include categories.' );
assert_same( array( 'SELECT', '内部テーブル' ), $formatted['tags'], 'Formatted article should include tags.' );
assert_true( false !== strpos( $formatted['content'], 'SELECT 文の基本です。' ), 'Content should include stripped body text.' );
assert_true( false === strpos( $formatted['content'], '<h2>' ), 'Content should strip HTML tags.' );

$structured = sapjp_knowledge_format_article(
	$post,
	array(
		'url'        => 'https://sapjp.net/abap-select/',
		'categories' => array( 'ABAP' ),
		'tags'       => array( 'SELECT' ),
		'format'     => 'structured',
	)
);

assert_true( isset( $structured['sections'] ), 'Structured article response should include sections.' );
assert_same( '概要', $structured['sections'][0]['heading'], 'Structured sections should use heading text.' );
assert_same( 'h2', $structured['sections'][0]['level'], 'Structured sections should include heading level.' );
assert_true( false !== strpos( $structured['sections'][0]['content'], 'SELECT 文の基本です。' ), 'Structured section should include section body.' );

$code_snippets = sapjp_knowledge_extract_code_snippets( $post->post_content );
assert_same( 1, count( $code_snippets ), 'Code extraction should find fenced article code.' );
assert_same( 'abap', $code_snippets[0]['language'], 'Unmarked SAPJP code examples should default to ABAP.' );
assert_same( 'SELECT * FROM vbak.', $code_snippets[0]['code'], 'Code extraction should keep code text readable.' );

$code_response = sapjp_knowledge_format_code_response( $formatted, $post->post_content );
assert_same( 42, $code_response['article_id'], 'Code response should include article ID.' );
assert_same( 'ABAP SELECTの基本', $code_response['title'], 'Code response should include article title.' );
assert_same( 1, $code_response['count'], 'Code response should include snippet count.' );

$summary = sapjp_knowledge_create_excerpt( str_repeat( 'あ', 160 ), 80 );
assert_same( 81, mb_strlen( $summary ), 'Excerpt should be truncated and include ellipsis.' );
assert_same( '…', mb_substr( $summary, -1 ), 'Excerpt should end with ellipsis when truncated.' );

$context = sapjp_knowledge_format_context_response(
	'ABAP SELECT',
	array( $formatted ),
	120
);

assert_same( 'ABAP SELECT', $context['query'], 'Context response should include query.' );
assert_same( 1, $context['count'], 'Context response should include source count.' );
assert_same( 42, $context['sources'][0]['id'], 'Context source should include article ID.' );
assert_true( mb_strlen( $context['sources'][0]['content'] ) <= 121, 'Context source should respect max content length plus ellipsis.' );
assert_true( isset( $context['citations'][0]['url'] ), 'Context response should include citation URLs for AI answers.' );
assert_true( false !== strpos( $context['context'], 'ABAP SELECTの基本' ), 'Context response should include a prompt-ready context string.' );

$topic = sapjp_knowledge_format_topic_response( 'ABAP', array( $formatted ) );
assert_same( 'abap', $topic['topic'], 'Topic response should normalize the topic key.' );
assert_same( 1, $topic['count'], 'Topic response should include article count.' );
assert_same( 42, $topic['items'][0]['id'], 'Topic response should include matching article summaries.' );

assert_true( sapjp_knowledge_is_public_article( $post ), 'Published posts without a password should be public API sources.' );

$passworded_post = clone $post;
$passworded_post->post_password = 'secret';
assert_false( sapjp_knowledge_is_public_article( $passworded_post ), 'Password-protected posts must not be public API sources.' );

$draft_post = clone $post;
$draft_post->post_status = 'draft';
assert_false( sapjp_knowledge_is_public_article( $draft_post ), 'Draft posts must not be public API sources.' );

$empty_context = sapjp_knowledge_rest_context( new Sapjp_Test_Request( array( 'query' => " \n\t " ) ) );
assert_true( $empty_context instanceof WP_Error, 'Context endpoint should reject effectively empty queries.' );
assert_same( 'sapjp_empty_query', $empty_context->code, 'Context endpoint should use a stable error code for empty queries.' );

$json_options = sapjp_knowledge_rest_json_encode_options( 0, new Sapjp_Test_Request( array( 'rest_route' => '/sapjp/v1/articles/2155' ) ) );
assert_true( (bool) ( $json_options & JSON_UNESCAPED_UNICODE ), 'Knowledge API JSON should keep Japanese readable.' );
assert_true( (bool) ( $json_options & JSON_UNESCAPED_SLASHES ), 'Knowledge API JSON should keep URLs readable.' );

$json_options_from_route = sapjp_knowledge_rest_json_encode_options( 0, new Sapjp_Test_Request( array( 'route' => '/sapjp/v1/context' ) ) );
assert_true( (bool) ( $json_options_from_route & JSON_UNESCAPED_UNICODE ), 'Knowledge API JSON should detect normal REST routes.' );

echo "knowledge-api tests passed\n";
