<?php

/* 子テーマのfunctions.phpは、親テーマのfunctions.phpより先に読み込まれることに注意してください。 */


/**
 * 親テーマのfunctions.phpのあとで読み込みたいコードはこの中に。
 */
// add_filter('after_setup_theme', function(){
// }, 11);


/**
 * Google Fonts preconnect ヒント
 */
add_action('wp_head', function() {
	echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
}, 1);

/**
 * SVGロゴ
 */
add_filter( 'swell_parts_head_logo', function( $html, $is_fixbar ) {
	$site_name = get_bloginfo( 'name' );
	$home_url  = home_url( '/' );

	$svg = '<svg class="c-siteLogo__icon" viewBox="0 0 44 32" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" fill="currentColor">'
		. '<path fill-rule="evenodd" clip-rule="evenodd" d="'
		. 'M9 0C4.029 0 0 4.029 0 9V23C0 27.971 4.029 32 9 32H35C39.971 32 44 27.971 44 23V9C44 4.029 39.971 0 35 0H9Z'
		. 'M14 7C11.239 7 9 9.239 9 12V20C9 22.761 11.239 25 14 25H30C32.761 25 35 22.761 35 20V12C35 9.239 32.761 7 30 7H14Z'
		. '"/>'
		. '</svg>';

	$tag = is_front_page() ? 'h1' : 'p';

	return '<' . $tag . ' class="c-headLogo c-siteLogo">'
		. '<a href="' . esc_url( $home_url ) . '" title="' . esc_attr( $site_name ) . '" class="c-headLogo__link c-siteLogo__link" rel="home">'
		. $svg
		. '<span class="c-siteLogo__name">' . esc_html( $site_name ) . '</span>'
		. '</a>'
		. '</' . $tag . '>';
}, 10, 2 );


/**
 * 子テーマでのファイルの読み込み
 */
add_action('wp_enqueue_scripts', function() {

	/* Google Fonts（Inter + Noto Sans JP、font-display=swap） */
	wp_enqueue_style(
		'google-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;600&display=swap',
		[],
		null
	);

	$timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/style.css' ) );
	wp_enqueue_style( 'child_style', get_stylesheet_directory_uri() .'/style.css', ['google-fonts'], $timestamp );

	/* トップページ用JS */
	if ( is_front_page() ) {
		$js_timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/js/front-page.js' ) );
		wp_enqueue_script( 'front-page-js', get_stylesheet_directory_uri() . '/js/front-page.js', [], $js_timestamp, true );
	}

	/* Prism.js シンタックスハイライト（記事ページ） */
	if ( is_singular() ) {
		/* Prism.js コア（デフォルトCSSは読み込まない — style.cssでAnthropicテーマを定義済み） */
		wp_enqueue_script(
			'prismjs-core',
			'https://cdn.jsdelivr.net/npm/prismjs@1.29.0/prism.min.js',
			[],
			'1.29.0',
			true
		);

		/* Prism.js 追加言語（jsDelivrバンドル — 依存関係順に並べる） */
		wp_enqueue_script(
			'prismjs-langs',
			'https://cdn.jsdelivr.net/combine/'
				. 'npm/prismjs@1.29.0/components/prism-markup.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-markup-templating.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-javascript.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-typescript.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-php.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-css-extras.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-abap.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-python.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-bash.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-json.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-yaml.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-sql.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-java.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-go.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-diff.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-ruby.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-csharp.min.js',
			['prismjs-core'],
			'1.29.0',
			true
		);

		/* コードブロック UI（コピーボタン・タブ切り替え） */
		$cb_timestamp = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/js/code-block.js' ) );
		wp_enqueue_script( 'code-block-js', get_stylesheet_directory_uri() . '/js/code-block.js', ['prismjs-langs'], $cb_timestamp, true );
	}

}, 11);

/**
 * コードブロック：エディタ用スクリプト（言語セレクタ追加）
 */
add_action('enqueue_block_editor_assets', function() {
	$ts = date( 'Ymdgis', filemtime( get_stylesheet_directory() . '/js/code-block-editor.js' ) );
	wp_enqueue_script(
		'code-block-editor-js',
		get_stylesheet_directory_uri() . '/js/code-block-editor.js',
		['wp-blocks', 'wp-element', 'wp-compose', 'wp-hooks', 'wp-block-editor', 'wp-components'],
		$ts,
		true
	);
});

/**
 * コードブロック：フロントエンド出力時に <code> へ言語クラスを注入
 * エディタで選択した codeLanguage 属性を language-xxx クラスとして付与する
 */
add_filter('render_block_core/code', function( $block_content, $block ) {
	$lang = $block['attrs']['codeLanguage'] ?? '';
	if ( empty( $lang ) ) {
		return $block_content;
	}

	$lang_class = 'language-' . esc_attr( $lang );

	/* <code> タグにクラスを追加 */
	if ( strpos( $block_content, '<code ' ) !== false ) {
		/* 既にclass属性がある場合 */
		$block_content = preg_replace(
			'/(<code\s+[^>]*class=")/i',
			'$1' . $lang_class . ' ',
			$block_content
		);
	} elseif ( strpos( $block_content, '<code>' ) !== false ) {
		/* class属性がない場合 */
		$block_content = str_replace( '<code>', '<code class="' . $lang_class . '">', $block_content );
	}

	return $block_content;
}, 10, 2);

/**
 * スキルページ：GitHub API からスキル一覧を取得（1時間キャッシュ）
 */
function swell_child_fetch_skills() {
	$cache_key  = 'swell_child_skills';
	$fallback_key = $cache_key . '_fallback';

	/* キャッシュ確認 */
	$cached = get_transient( $cache_key );
	if ( false !== $cached ) {
		return $cached;
	}

	$repo = 'yuto2245/claude-skills';
	$api  = "https://api.github.com/repos/{$repo}/contents/skills";
	$headers = [
		'Accept'     => 'application/vnd.github.v3+json',
		'User-Agent' => 'SWELL-Child-Theme',
	];

	/* フォルダ一覧取得 */
	$response = wp_remote_get( $api, [ 'headers' => $headers, 'timeout' => 10 ] );
	if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
		/* APIエラー時はフォールバックキャッシュを返す */
		$fallback = get_option( $fallback_key, [] );
		return $fallback;
	}

	$dirs = json_decode( wp_remote_retrieve_body( $response ), true );
	if ( ! is_array( $dirs ) ) {
		return get_option( $fallback_key, [] );
	}

	$skills = [];
	foreach ( $dirs as $dir ) {
		if ( 'dir' !== ( $dir['type'] ?? '' ) ) continue;

		$name = $dir['name'];
		$md_url = "https://raw.githubusercontent.com/{$repo}/main/skills/{$name}/SKILL.md";

		$md_response = wp_remote_get( $md_url, [ 'headers' => $headers, 'timeout' => 10 ] );
		if ( is_wp_error( $md_response ) || 200 !== wp_remote_retrieve_response_code( $md_response ) ) {
			$skills[] = [ 'name' => $name, 'description' => '' ];
			continue;
		}

		$md_body = wp_remote_retrieve_body( $md_response );
		$parsed  = swell_child_parse_frontmatter( $md_body );

		$skills[] = [
			'name'        => $parsed['name'] ?: $name,
			'description' => $parsed['description'] ?: '',
		];
	}

	/* キャッシュ保存（1時間） */
	set_transient( $cache_key, $skills, HOUR_IN_SECONDS );
	/* フォールバック用（期限なし） */
	update_option( $fallback_key, $skills );

	return $skills;
}

/**
 * SKILL.md の YAML frontmatter をパース
 */
function swell_child_parse_frontmatter( $markdown ) {
	$result = [ 'name' => '', 'description' => '' ];

	if ( ! preg_match( '/\A---\s*\n(.*?)\n---/s', $markdown, $m ) ) {
		return $result;
	}

	$fm = $m[1];

	/* name */
	if ( preg_match( '/^name:\s*(.+)$/m', $fm, $n ) ) {
		$result['name'] = trim( $n[1] );
	}

	/* description（複数行対応） */
	if ( preg_match( '/^description:\s*(?:>\s*)?\n?((?:\s{2,}.+\n?)+)/m', $fm, $d ) ) {
		$result['description'] = trim( preg_replace( '/\s+/', ' ', $d[1] ) );
	} elseif ( preg_match( '/^description:\s*(.+)$/m', $fm, $d ) ) {
		$result['description'] = trim( $d[1] );
	}

	return $result;
}

/**
 * スキルのカテゴリマッピング
 */
function swell_child_skill_categories() {
	return [
		'Document Creation' => [ 'docx', 'pptx', 'xlsx', 'pdf' ],
		'Design & Visual' => [ 'canvas-design', 'eyecatch-maker', 'brand-guidelines' ],
		'Learning & Development' => [ 'quiz-maker', 'review-scheduler', 'mentoring-harness-teaching-style-absorber' ],
		'Productivity' => [ 'notion-sync', 'chat-summarizer', 'schedule' ],
		'Technical' => [ 'mcp-builder', 'web-artifacts-builder', 'mactextui-control' ],
		'Professional Services' => [ 'career-consultant', 'question-analyzer' ],
		'Skill Management' => [ 'skill-creator', 'skill-updater' ],
	];
}

/* ========================================================================
 * Chat Page — Backend
 * ======================================================================== */

function swell_child_chat_models() {
	return [
		// Anthropic (2026年3月)
		[ 'id' => 'claude-opus-4-6',              'label' => 'Claude Opus 4.6',    'type' => 'anthropic', 'icon' => 'claude.png' ],
		[ 'id' => 'claude-sonnet-4-6',            'label' => 'Claude Sonnet 4.6',  'type' => 'anthropic', 'icon' => 'claude.png' ],
		[ 'id' => 'claude-haiku-4-5-20251001',    'label' => 'Claude Haiku 4.5',   'type' => 'anthropic', 'icon' => 'claude.png' ],
		// OpenAI (2026年3月)
		[ 'id' => 'gpt-5.4',                      'label' => 'GPT-5.4',           'type' => 'openai',    'icon' => 'openai.png' ],
		[ 'id' => 'gpt-5.3-instant',              'label' => 'GPT-5.3 Instant',   'type' => 'openai',    'icon' => 'openai.png' ],
		[ 'id' => 'o3',                            'label' => 'o3',                'type' => 'openai',    'icon' => 'openai.png' ],
		[ 'id' => 'o4-mini',                       'label' => 'o4-mini',           'type' => 'openai',    'icon' => 'openai.png' ],
		// Google Gemini (2026年3月)
		[ 'id' => 'gemini-3-flash',               'label' => 'Gemini 3 Flash',    'type' => 'google',    'icon' => 'gemini.png' ],
		[ 'id' => 'gemini-3.1-pro',               'label' => 'Gemini 3.1 Pro',    'type' => 'google',    'icon' => 'gemini.png' ],
		// xAI (2026年3月)
		[ 'id' => 'grok-4.20-0309',               'label' => 'Grok 4.20',         'type' => 'xai',       'icon' => 'grok.png' ],
		[ 'id' => 'grok-4.1',                     'label' => 'Grok 4.1',          'type' => 'xai',       'icon' => 'grok.png' ],
	];
}

/* --- 管理画面: Chat Settings --- */

add_action( 'admin_menu', function () {
	add_options_page( 'Chat Settings', 'Chat Settings', 'manage_options', 'swell-child-chat', 'swell_child_chat_settings_page' );
} );

add_action( 'admin_init', function () {
	$fields = [
		'swell_child_anthropic_key' => 'Anthropic API Key',
		'swell_child_openai_key'    => 'OpenAI API Key',
		'swell_child_google_key'    => 'Google AI API Key',
		'swell_child_xai_key'       => 'xAI API Key',
	];
	add_settings_section( 'swell_child_chat_keys', 'API Keys', '__return_false', 'swell-child-chat' );
	foreach ( $fields as $key => $label ) {
		register_setting( 'swell_child_chat', $key, [ 'sanitize_callback' => 'sanitize_text_field' ] );
		add_settings_field( $key, $label, function () use ( $key ) {
			$val = get_option( $key, '' );
			echo '<input type="password" name="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text" autocomplete="off">';
		}, 'swell-child-chat', 'swell_child_chat_keys' );
	}
} );

function swell_child_chat_settings_page() {
	echo '<div class="wrap"><h1>Chat Settings</h1><form method="post" action="options.php">';
	settings_fields( 'swell_child_chat' );
	do_settings_sections( 'swell-child-chat' );
	submit_button();
	echo '</form></div>';
}

/* --- REST API エンドポイント --- */

add_action( 'rest_api_init', function () {
	register_rest_route( 'swell-child/v1', '/chat', [
		'methods'             => 'POST',
		'callback'            => 'swell_child_chat_handler',
		'permission_callback' => function () { return current_user_can( 'manage_options' ); },
	] );
} );

function swell_child_chat_handler( WP_REST_Request $request ) {
	$model    = sanitize_text_field( $request->get_param( 'model' ) );
	$type     = sanitize_text_field( $request->get_param( 'type' ) );
	$messages = $request->get_param( 'messages' );

	if ( empty( $model ) || empty( $type ) || ! is_array( $messages ) ) {
		return new WP_Error( 'invalid_params', 'Missing required parameters.', [ 'status' => 400 ] );
	}

	$clean = [];
	foreach ( $messages as $msg ) {
		if ( ! isset( $msg['role'], $msg['content'] ) ) continue;
		// contentはAI APIに送信するためsanitize_text_fieldを適用しない（改行・特殊文字を保持する必要がある）
		$clean[] = [ 'role' => sanitize_text_field( $msg['role'] ), 'content' => $msg['content'] ];
	}
	if ( empty( $clean ) ) {
		return new WP_Error( 'invalid_messages', 'No valid messages.', [ 'status' => 400 ] );
	}

	header( 'Content-Type: text/event-stream' );
	header( 'Cache-Control: no-cache' );
	header( 'X-Accel-Buffering: no' );
	while ( ob_get_level() ) { ob_end_flush(); }

	switch ( $type ) {
		case 'anthropic': swell_child_stream_anthropic( $model, $clean ); break;
		case 'openai':    swell_child_stream_openai( $model, $clean );    break;
		case 'google':    swell_child_stream_google( $model, $clean );    break;
		case 'xai':       swell_child_stream_xai( $model, $clean );      break;
		default:
			echo "data: " . wp_json_encode( [ 'error' => 'Unknown provider.' ] ) . "\n\n";
			flush();
	}

	echo "data: [DONE]\n\n";
	flush();
	exit;
}

/* --- ストリーミング: Anthropic --- */

function swell_child_stream_anthropic( $model, $messages ) {
	$key = get_option( 'swell_child_anthropic_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'Anthropic API key not set.' ] ) . "\n\n"; flush(); return; }

	$system = '';
	$api_msgs = [];
	foreach ( $messages as $m ) {
		if ( $m['role'] === 'system' ) { $system = $m['content']; } else { $api_msgs[] = $m; }
	}

	$body = [ 'model' => $model, 'messages' => $api_msgs, 'max_tokens' => 4096, 'stream' => true ];
	if ( $system ) { $body['system'] = $system; }

	$buffer = '';
	$ch = curl_init( 'https://api.anthropic.com/v1/messages' );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [ 'Content-Type: application/json', 'x-api-key: ' . $key, 'anthropic-version: 2023-06-01' ],
		CURLOPT_POSTFIELDS => wp_json_encode( $body ),
		CURLOPT_RETURNTRANSFER => false,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_WRITEFUNCTION => function ( $ch, $chunk ) use ( &$buffer ) {
			$buffer .= $chunk;
			while ( ( $pos = strpos( $buffer, "\n" ) ) !== false ) {
				$line = substr( $buffer, 0, $pos );
				$buffer = substr( $buffer, $pos + 1 );
				$line = trim( $line );
				if ( strpos( $line, 'data: ' ) !== 0 ) continue;
				$d = json_decode( substr( $line, 6 ), true );
				if ( ! $d ) continue;
				if ( ( $d['type'] ?? '' ) === 'content_block_delta' && ( $d['delta']['text'] ?? '' ) !== '' ) {
					echo "data: " . wp_json_encode( [ 'token' => $d['delta']['text'] ] ) . "\n\n"; flush();
				}
			}
			return strlen( $chunk );
		},
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( [ 'error' => curl_error( $ch ) ] ) . "\n\n"; flush(); }
	curl_close( $ch );
}

/* --- ストリーミング: OpenAI --- */

function swell_child_stream_openai( $model, $messages ) {
	$key = get_option( 'swell_child_openai_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'OpenAI API key not set.' ] ) . "\n\n"; flush(); return; }

	$buffer = '';
	$ch = curl_init( 'https://api.openai.com/v1/chat/completions' );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [ 'Content-Type: application/json', 'Authorization: Bearer ' . $key ],
		CURLOPT_POSTFIELDS => wp_json_encode( [ 'model' => $model, 'messages' => $messages, 'stream' => true ] ),
		CURLOPT_RETURNTRANSFER => false,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_WRITEFUNCTION => function ( $ch, $chunk ) use ( &$buffer ) { swell_child_parse_openai_sse( $chunk, $buffer ); return strlen( $chunk ); },
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( [ 'error' => curl_error( $ch ) ] ) . "\n\n"; flush(); }
	curl_close( $ch );
}

/* --- ストリーミング: Gemini --- */

function swell_child_stream_google( $model, $messages ) {
	$key = get_option( 'swell_child_google_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'Google API key not set.' ] ) . "\n\n"; flush(); return; }

	$contents = [];
	foreach ( $messages as $m ) {
		$role = $m['role'] === 'assistant' ? 'model' : ( $m['role'] === 'system' ? 'user' : $m['role'] );
		$contents[] = [ 'role' => $role, 'parts' => [ [ 'text' => $m['content'] ] ] ];
	}

	$url = 'https://generativelanguage.googleapis.com/v1beta/models/' . urlencode( $model ) . ':streamGenerateContent?key=' . urlencode( $key ) . '&alt=sse';
	$buffer = '';
	$ch = curl_init( $url );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [ 'Content-Type: application/json' ],
		CURLOPT_POSTFIELDS => wp_json_encode( [ 'contents' => $contents ] ),
		CURLOPT_RETURNTRANSFER => false,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_WRITEFUNCTION => function ( $ch, $chunk ) use ( &$buffer ) {
			$buffer .= $chunk;
			while ( ( $pos = strpos( $buffer, "\n" ) ) !== false ) {
				$line = substr( $buffer, 0, $pos );
				$buffer = substr( $buffer, $pos + 1 );
				$line = trim( $line );
				if ( strpos( $line, 'data: ' ) !== 0 ) continue;
				$d = json_decode( substr( $line, 6 ), true );
				if ( ! $d ) continue;
				$t = $d['candidates'][0]['content']['parts'][0]['text'] ?? '';
				if ( $t !== '' ) { echo "data: " . wp_json_encode( [ 'token' => $t ] ) . "\n\n"; flush(); }
			}
			return strlen( $chunk );
		},
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( [ 'error' => curl_error( $ch ) ] ) . "\n\n"; flush(); }
	curl_close( $ch );
}

/* --- ストリーミング: xAI (Grok) — OpenAI互換 --- */

function swell_child_stream_xai( $model, $messages ) {
	$key = get_option( 'swell_child_xai_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'xAI API key not set.' ] ) . "\n\n"; flush(); return; }

	$buffer = '';
	$ch = curl_init( 'https://api.x.ai/v1/chat/completions' );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [ 'Content-Type: application/json', 'Authorization: Bearer ' . $key ],
		CURLOPT_POSTFIELDS => wp_json_encode( [ 'model' => $model, 'messages' => $messages, 'stream' => true ] ),
		CURLOPT_RETURNTRANSFER => false,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_WRITEFUNCTION => function ( $ch, $chunk ) use ( &$buffer ) { swell_child_parse_openai_sse( $chunk, $buffer ); return strlen( $chunk ); },
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( [ 'error' => curl_error( $ch ) ] ) . "\n\n"; flush(); }
	curl_close( $ch );
}

/* --- OpenAI形式SSE共通パーサ（OpenAI / xAI 共用） --- */

function swell_child_parse_openai_sse( $chunk, &$buffer ) {
	$buffer .= $chunk;
	while ( ( $pos = strpos( $buffer, "\n" ) ) !== false ) {
		$line = substr( $buffer, 0, $pos );
		$buffer = substr( $buffer, $pos + 1 );
		$line = trim( $line );
		if ( strpos( $line, 'data: ' ) !== 0 ) continue;
		$json = substr( $line, 6 );
		if ( $json === '[DONE]' ) return;
		$d = json_decode( $json, true );
		if ( ! $d ) continue;
		$t = $d['choices'][0]['delta']['content'] ?? '';
		if ( $t !== '' ) { echo "data: " . wp_json_encode( [ 'token' => $t ] ) . "\n\n"; flush(); }
	}
}

/* --- チャットページ用アセット読み込み --- */

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page( 'chat' ) ) return;

	$path = get_stylesheet_directory() . '/js/chat.js';
	$ts   = file_exists( $path ) ? date( 'Ymdgis', filemtime( $path ) ) : '1';
	wp_enqueue_script( 'chat-js', get_stylesheet_directory_uri() . '/js/chat.js', [], $ts, true );
	wp_localize_script( 'chat-js', 'chatConfig', [
		'restUrl'     => rest_url( 'swell-child/v1/chat' ),
		'nonce'       => wp_create_nonce( 'wp_rest' ),
		'models'      => swell_child_chat_models(),
		'iconBaseUrl' => get_stylesheet_directory_uri() . '/img/chat/',
	] );
}, 20 );
