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

/* --- AJAX SSEエンドポイント（PHP 7.4互換、cURL直接呼び出し） --- */

add_action( 'wp_ajax_swell_chat_stream', 'swell_child_ajax_chat_handler' );

function swell_child_ajax_chat_handler() {
	if ( ! current_user_can( 'manage_options' ) ) {
		http_response_code( 403 );
		exit;
	}
	check_ajax_referer( 'swell_chat_nonce', '_wpnonce' );

	$model    = sanitize_text_field( isset( $_POST['model'] ) ? $_POST['model'] : '' );
	$type     = sanitize_text_field( isset( $_POST['type'] ) ? $_POST['type'] : '' );
	$messages = json_decode( wp_unslash( isset( $_POST['messages'] ) ? $_POST['messages'] : '[]' ), true );
	$web_search = ! empty( $_POST['web_search'] ) && $_POST['web_search'] === '1';
	$reasoning  = ! empty( $_POST['reasoning'] ) && $_POST['reasoning'] === '1';
	$skill_ids  = isset( $_POST['skills'] ) ? json_decode( wp_unslash( $_POST['skills'] ), true ) : array();
	if ( ! is_array( $skill_ids ) ) { $skill_ids = array(); }
	$skill_ids = array_map( 'sanitize_text_field', $skill_ids );
	$rag_enabled = ! empty( $_POST['rag'] ) && $_POST['rag'] === '1';

	/* ホワイトリスト検証 */
	$allowed_models = array_column( swell_child_chat_models(), 'id' );
	$allowed_types  = array( 'anthropic', 'openai', 'google', 'xai' );
	if ( ! in_array( $model, $allowed_models, true ) || ! in_array( $type, $allowed_types, true ) || ! is_array( $messages ) ) {
		header( 'Content-Type: text/event-stream; charset=utf-8' );
		echo "data: " . wp_json_encode( array( 'error' => 'Invalid parameters.' ) ) . "\n\n";
		exit;
	}

	/* メッセージサニタイズ */
	$allowed_roles = array( 'system', 'user', 'assistant' );
	$clean = array();
	foreach ( $messages as $msg ) {
		if ( ! isset( $msg['role'], $msg['content'] ) ) continue;
		$role = sanitize_text_field( $msg['role'] );
		if ( ! in_array( $role, $allowed_roles, true ) ) continue;
		$clean[] = array( 'role' => $role, 'content' => $msg['content'] );
	}

	/* SSEヘッダー + バッファ完全無効化 */
	header( 'Content-Type: text/event-stream; charset=utf-8' );
	header( 'Cache-Control: no-cache' );
	header( 'X-Accel-Buffering: no' );
	@ini_set( 'zlib.output_compression', 0 );
	while ( ob_get_level() ) { ob_end_flush(); }

	/* RAG: ブログ記事をsystemプロンプトに注入 */
	if ( $rag_enabled && ! empty( $clean ) ) {
		$last_msg = end( $clean );
		$rag_context = swell_child_rag_search( $last_msg['content'] );
		if ( $rag_context ) {
			array_unshift( $clean, array(
				'role'    => 'system',
				'content' => "以下はブログ記事の関連情報です。回答の参考にしてください。\n\n" . $rag_context,
			) );
		}
	}

	try {
		switch ( $type ) {
			case 'anthropic': swell_child_stream_anthropic( $model, $clean, $web_search, $reasoning, $skill_ids ); break;
			case 'openai':    swell_child_stream_openai( $model, $clean, $web_search, $reasoning );    break;
			case 'google':    swell_child_stream_google( $model, $clean, $web_search, $reasoning );    break;
			case 'xai':       swell_child_stream_xai( $model, $clean, $web_search, $reasoning );      break;
		}
	} catch ( Exception $e ) {
		error_log( '[swell_chat] ' . $e->getMessage() );
		echo "data: " . wp_json_encode( array( 'error' => 'An error occurred.' ) ) . "\n\n";
	}

	echo "data: [DONE]\n\n";
	flush();
	exit;
}

/* --- ストリーミング: Anthropic --- */

function swell_child_stream_anthropic( $model, $messages, $web_search = false, $reasoning = false, $skill_ids = array() ) {
	$key = get_option( 'swell_child_anthropic_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'Anthropic API key not set.' ] ) . "\n\n"; flush(); return; }

	$system = '';
	$api_msgs = [];
	foreach ( $messages as $m ) {
		if ( $m['role'] === 'system' ) { $system = $m['content']; } else { $api_msgs[] = $m; }
	}

	$body = array( 'model' => $model, 'messages' => $api_msgs, 'max_tokens' => 4096, 'stream' => true );
	if ( $system ) { $body['system'] = $system; }
	if ( $reasoning ) {
		$body['thinking'] = array( 'type' => 'enabled', 'budget_tokens' => 10000 );
		$body['max_tokens'] = 16000;
	}

	/* Skills対応: container.skillsとベータヘッダー */
	$headers = array( 'Content-Type: application/json', 'x-api-key: ' . $key, 'anthropic-version: 2023-06-01' );
	if ( ! empty( $skill_ids ) ) {
		$headers[] = 'anthropic-beta: code-execution-2025-08-25,skills-2025-10-02,files-api-2025-04-14';
		$skills_param = array();
		foreach ( $skill_ids as $sid ) {
			$skills_param[] = array( 'type' => 'custom', 'skill_id' => $sid, 'version' => 'latest' );
		}
		$body['container'] = array( 'skills' => $skills_param );
		$body['tools'] = array( array( 'type' => 'code_execution_20250825', 'name' => 'code_execution' ) );
	}

	$buffer = '';
	$ch = curl_init( 'https://api.anthropic.com/v1/messages' );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => $headers,
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
				$evt_type = isset( $d['type'] ) ? $d['type'] : '';
				if ( $evt_type === 'content_block_delta' ) {
					$delta_type = isset( $d['delta']['type'] ) ? $d['delta']['type'] : '';
					if ( $delta_type === 'thinking_delta' && isset( $d['delta']['thinking'] ) ) {
						echo "data: " . wp_json_encode( array( 'thinking' => $d['delta']['thinking'] ) ) . "\n\n"; flush();
					} elseif ( isset( $d['delta']['text'] ) && $d['delta']['text'] !== '' ) {
						echo "data: " . wp_json_encode( array( 'token' => $d['delta']['text'] ) ) . "\n\n"; flush();
					}
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

function swell_child_stream_openai( $model, $messages, $web_search = false, $reasoning = false ) {
	$key = get_option( 'swell_child_openai_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'OpenAI API key not set.' ] ) . "\n\n"; flush(); return; }

	$buffer = '';
	$ch = curl_init( 'https://api.openai.com/v1/chat/completions' );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [ 'Content-Type: application/json', 'Authorization: Bearer ' . $key ],
		CURLOPT_POSTFIELDS => wp_json_encode( swell_child_build_openai_body( $model, $messages, $web_search, $reasoning ) ),
		CURLOPT_RETURNTRANSFER => false,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_WRITEFUNCTION => function ( $ch, $chunk ) use ( &$buffer ) { swell_child_parse_openai_sse( $chunk, $buffer ); return strlen( $chunk ); },
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( [ 'error' => curl_error( $ch ) ] ) . "\n\n"; flush(); }
	curl_close( $ch );
}

function swell_child_build_openai_body( $model, $messages, $web_search, $reasoning = false ) {
	$body = array( 'model' => $model, 'messages' => $messages, 'stream' => true );
	if ( $web_search ) {
		$body['tools'] = array( array( 'type' => 'web_search_preview' ) );
	}
	if ( $reasoning && in_array( $model, array( 'o3', 'o4-mini' ), true ) ) {
		$body['reasoning_effort'] = 'medium';
	}
	return $body;
}

/* --- ストリーミング: Gemini --- */

function swell_child_stream_google( $model, $messages, $web_search = false, $reasoning = false ) {
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
		CURLOPT_POSTFIELDS => wp_json_encode( swell_child_build_google_body( $contents, $web_search ) ),
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
				$t = isset( $d['candidates'][0]['content']['parts'][0]['text'] ) ? $d['candidates'][0]['content']['parts'][0]['text'] : '';
				if ( $t !== '' ) { echo "data: " . wp_json_encode( array( 'token' => $t ) ) . "\n\n"; flush(); }
			}
			return strlen( $chunk );
		},
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( array( 'error' => curl_error( $ch ) ) ) . "\n\n"; flush(); }
	curl_close( $ch );
}

function swell_child_build_google_body( $contents, $web_search ) {
	$body = array( 'contents' => $contents );
	if ( $web_search ) {
		$body['tools'] = array( array( 'google_search' => new stdClass() ) );
	}
	return $body;
}

/* --- ストリーミング: xAI (Grok) — OpenAI互換 --- */

function swell_child_stream_xai( $model, $messages, $web_search = false, $reasoning = false ) {
	$key = get_option( 'swell_child_xai_key', '' );
	if ( ! $key ) { echo "data: " . wp_json_encode( [ 'error' => 'xAI API key not set.' ] ) . "\n\n"; flush(); return; }

	$buffer = '';
	$ch = curl_init( 'https://api.x.ai/v1/chat/completions' );
	curl_setopt_array( $ch, [
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [ 'Content-Type: application/json', 'Authorization: Bearer ' . $key ],
		CURLOPT_POSTFIELDS => wp_json_encode( swell_child_build_xai_body( $model, $messages, $web_search ) ),
		CURLOPT_RETURNTRANSFER => false,
		CURLOPT_TIMEOUT => 120,
		CURLOPT_WRITEFUNCTION => function ( $ch, $chunk ) use ( &$buffer ) { swell_child_parse_openai_sse( $chunk, $buffer ); return strlen( $chunk ); },
	] );
	curl_exec( $ch );
	if ( curl_errno( $ch ) ) { echo "data: " . wp_json_encode( array( 'error' => curl_error( $ch ) ) ) . "\n\n"; flush(); }
	curl_close( $ch );
}

function swell_child_build_xai_body( $model, $messages, $web_search ) {
	$body = array( 'model' => $model, 'messages' => $messages, 'stream' => true );
	if ( $web_search ) {
		$body['search_parameters'] = array( 'mode' => 'auto' );
	}
	return $body;
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
		/* 推論トークン（o3/o4-mini） */
		$rc = isset( $d['choices'][0]['delta']['reasoning_content'] ) ? $d['choices'][0]['delta']['reasoning_content'] : '';
		if ( $rc !== '' ) { echo "data: " . wp_json_encode( array( 'thinking' => $rc ) ) . "\n\n"; flush(); }
		/* 通常トークン */
		$t = isset( $d['choices'][0]['delta']['content'] ) ? $d['choices'][0]['delta']['content'] : '';
		if ( $t !== '' ) { echo "data: " . wp_json_encode( array( 'token' => $t ) ) . "\n\n"; flush(); }
	}
}

/* --- RAG: ブログ記事検索 --- */

function swell_child_rag_search( $query, $max_articles = 3, $max_chars = 2000 ) {
	$words = array_filter( preg_split( '/[\s　]+/u', $query ) );
	if ( empty( $words ) ) return '';

	$args = array(
		'post_type'      => 'post',
		'post_status'    => 'publish',
		'posts_per_page' => $max_articles,
		's'              => implode( ' ', $words ),
	);
	$posts = get_posts( $args );
	if ( empty( $posts ) ) return '';

	$context = '';
	foreach ( $posts as $p ) {
		$title   = $p->post_title;
		$content = strip_shortcodes( $p->post_content );
		$content = wp_strip_all_tags( $content );
		$content = preg_replace( '/\s+/', ' ', $content );
		if ( strlen( $content ) > $max_chars ) {
			$content = mb_substr( $content, 0, $max_chars ) . '...';
		}
		$context .= "<article>\n<title>" . esc_html( $title ) . "</title>\n<content>" . $content . "</content>\n</article>\n\n";
	}
	return $context;
}

/* --- Claude Skills一覧取得（AJAX） --- */

add_action( 'wp_ajax_swell_chat_skills', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_send_json_error( 'Unauthorized', 403 ); }
	check_ajax_referer( 'swell_chat_nonce', '_wpnonce' );

	$cached = get_transient( 'swell_child_chat_skills' );
	if ( false !== $cached ) { wp_send_json_success( $cached ); }

	$key = get_option( 'swell_child_anthropic_key', '' );
	if ( ! $key ) { wp_send_json_success( array() ); }

	$ch = curl_init( 'https://api.anthropic.com/v1/skills?limit=100&source=custom' );
	curl_setopt_array( $ch, array(
		CURLOPT_HTTPHEADER => array(
			'x-api-key: ' . $key,
			'anthropic-version: 2023-06-01',
			'anthropic-beta: skills-2025-10-02',
		),
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 15,
	) );
	$response = curl_exec( $ch );
	curl_close( $ch );

	$data = json_decode( $response, true );
	$skills = array();
	if ( isset( $data['data'] ) && is_array( $data['data'] ) ) {
		foreach ( $data['data'] as $s ) {
			$skills[] = array(
				'id'    => isset( $s['id'] ) ? $s['id'] : '',
				'title' => isset( $s['display_title'] ) ? $s['display_title'] : '',
			);
		}
	}

	set_transient( 'swell_child_chat_skills', $skills, HOUR_IN_SECONDS );
	wp_send_json_success( $skills );
} );

/* --- チャットページ用アセット読み込み --- */

add_action( 'wp_enqueue_scripts', function () {
	if ( ! is_page( 'chat' ) ) return;

	$path = get_stylesheet_directory() . '/js/chat.js';
	$ts   = file_exists( $path ) ? date( 'Ymdgis', filemtime( $path ) ) : '1';
	wp_enqueue_script( 'chat-js', get_stylesheet_directory_uri() . '/js/chat.js', [], $ts, true );
	wp_localize_script( 'chat-js', 'chatConfig', array(
		'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
		'nonce'       => wp_create_nonce( 'swell_chat_nonce' ),
		'models'      => swell_child_chat_models(),
		'iconBaseUrl' => get_stylesheet_directory_uri() . '/img/chat/',
	) );
}, 20 );

/* ========================================================================
 * Popup — CPT + Enqueue + Render
 * ======================================================================== */

require_once get_stylesheet_directory() . '/inc/popup-cpt.php';

/**
 * フロント配信判定：配信対象 or プレビュー対象を返す
 *
 * @return array{post: WP_Post, is_preview: bool}|null
 */
function sapjp_popup_resolve_target() {
	$preview = sapjp_popup_get_preview();
	if ( $preview ) {
		return array( 'post' => $preview, 'is_preview' => true );
	}
	$active = sapjp_popup_get_active();
	if ( $active ) {
		return array( 'post' => $active, 'is_preview' => false );
	}
	return null;
}

/**
 * Popup アセット読み込み + JS に設定値を渡す
 */
add_action( 'wp_enqueue_scripts', function () {
	$target = sapjp_popup_resolve_target();
	if ( ! $target ) {
		return;
	}
	$post       = $target['post'];
	$is_preview = $target['is_preview'];

	$css_path = get_stylesheet_directory() . '/assets/popup.css';
	$js_path  = get_stylesheet_directory() . '/assets/popup.js';
	$css_ver  = file_exists( $css_path ) ? date( 'Ymdgis', filemtime( $css_path ) ) : '1';
	$js_ver   = file_exists( $js_path ) ? date( 'Ymdgis', filemtime( $js_path ) ) : '1';

	wp_enqueue_style(
		'sapjp-popup',
		get_stylesheet_directory_uri() . '/assets/popup.css',
		array(),
		$css_ver
	);

	wp_enqueue_script(
		'sapjp-popup',
		get_stylesheet_directory_uri() . '/assets/popup.js',
		array(),
		$js_ver,
		true
	);

	$cookie_days = (int) get_post_meta( $post->ID, '_popup_cookie_days', true );
	$delay_ms    = (int) get_post_meta( $post->ID, '_popup_delay_ms', true );

	wp_localize_script(
		'sapjp-popup',
		'sapjpPopupConfig',
		array(
			'popupId'    => (string) $post->ID,
			'cookieDays' => $cookie_days > 0 ? $cookie_days : 30,
			'delayMs'    => $delay_ms >= 0 ? $delay_ms : 1500,
			'isPreview'  => $is_preview,
		)
	);
}, 50 );

/**
 * defer 属性を popup.js に付与 / CSS を非クリティカル化
 */
add_filter( 'script_loader_tag', function ( $tag, $handle ) {
	if ( 'sapjp-popup' === $handle && false === strpos( $tag, ' defer' ) ) {
		$tag = str_replace( ' src=', ' defer src=', $tag );
	}
	return $tag;
}, 10, 2 );

add_filter( 'style_loader_tag', function ( $tag, $handle ) {
	if ( 'sapjp-popup' !== $handle ) {
		return $tag;
	}
	$tag = str_replace( "media='all'", "media='print' onload=\"this.media='all'\"", $tag );
	$tag = str_replace( 'media="all"', 'media="print" onload="this.media=\'all\'"', $tag );
	return $tag;
}, 10, 2 );

/**
 * バリアント別レンダリングを呼び出す
 */
add_action( 'wp_footer', function () {
	$target = sapjp_popup_resolve_target();
	if ( ! $target ) {
		return;
	}
	$post    = $target['post'];
	$variant = get_post_meta( $post->ID, '_popup_variant', true ) ?: 'classic';

	$fn = 'sapjp_popup_render_variant_' . $variant;
	if ( ! function_exists( $fn ) ) {
		$fn = 'sapjp_popup_render_variant_classic';
	}
	$fn( $post );
}, 99 );

/**
 * Classic バリアント出力
 *
 * @param WP_Post $post
 */
function sapjp_popup_render_variant_classic( $post ) {
	$title       = $post->post_title;
	$description = $post->post_content;
	$badge_text  = get_post_meta( $post->ID, '_popup_badge_text', true );
	$later_text  = get_post_meta( $post->ID, '_popup_later_text', true );
	$sub_text    = get_post_meta( $post->ID, '_popup_sub_text', true );
	$sub_url     = get_post_meta( $post->ID, '_popup_sub_url', true );
	$cta_text    = get_post_meta( $post->ID, '_popup_cta_text', true );
	$cta_url     = get_post_meta( $post->ID, '_popup_cta_url', true );
	$thumb_id    = get_post_thumbnail_id( $post->ID );
	?>
	<div id="sapjp-popup-backdrop"
		class="sapjp-popup-backdrop sapjp-popup-backdrop--classic"
		role="dialog"
		aria-modal="true"
		aria-labelledby="sapjp-popup-title"
		aria-hidden="true"
		data-popup-id="<?php echo esc_attr( $post->ID ); ?>"
		data-variant="classic">
		<div class="sapjp-popup sapjp-popup--classic" role="document">
			<button type="button"
				class="sapjp-popup__close"
				aria-label="<?php echo esc_attr__( '閉じる', 'swell_child' ); ?>">
				<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
			</button>

			<div class="sapjp-popup__hero" aria-hidden="true">
				<?php if ( $thumb_id ) : ?>
					<?php echo wp_get_attachment_image( $thumb_id, 'medium_large', false, array(
						'class'   => 'sapjp-popup__hero-image',
						'alt'     => '',
						'loading' => 'lazy',
					) ); ?>
				<?php else : ?>
					<div class="sapjp-popup__mock">
						<div class="sapjp-popup__mock-dots"><span></span><span></span><span></span></div>
						<div class="sapjp-popup__mock-line sapjp-popup__mock-line--w40"></div>
						<div class="sapjp-popup__mock-box">
							<div class="sapjp-popup__mock-box-icon"></div>
							<div class="sapjp-popup__mock-box-lines">
								<div class="sapjp-popup__mock-line sapjp-popup__mock-line--w60"></div>
								<div class="sapjp-popup__mock-line sapjp-popup__mock-line--w40"></div>
							</div>
						</div>
						<div class="sapjp-popup__mock-line sapjp-popup__mock-line--w80 sapjp-popup__mock-line--accent"></div>
						<div class="sapjp-popup__mock-line sapjp-popup__mock-line--w60"></div>
					</div>
				<?php endif; ?>
			</div>

			<div class="sapjp-popup__body">
				<?php if ( '' !== $badge_text ) : ?>
					<div class="sapjp-popup__pill-row">
						<span class="sapjp-popup__pill">
							<span class="sapjp-popup__pill-dot" aria-hidden="true"></span>
							<?php echo esc_html( $badge_text ); ?>
						</span>
					</div>
				<?php endif; ?>

				<h2 id="sapjp-popup-title" class="sapjp-popup__title"><?php echo esc_html( $title ); ?></h2>

				<?php if ( '' !== trim( $description ) ) : ?>
					<p class="sapjp-popup__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<div class="sapjp-popup__actions">
					<?php if ( '' !== $later_text ) : ?>
						<button type="button" class="sapjp-popup__btn sapjp-popup__btn--text"><?php echo esc_html( $later_text ); ?></button>
					<?php endif; ?>

					<span class="sapjp-popup__actions-spacer"></span>

					<?php if ( '' !== $sub_text ) : ?>
						<?php if ( '' !== $sub_url ) : ?>
							<a class="sapjp-popup__btn sapjp-popup__btn--ghost" href="<?php echo esc_url( $sub_url ); ?>"><?php echo esc_html( $sub_text ); ?></a>
						<?php else : ?>
							<button type="button" class="sapjp-popup__btn sapjp-popup__btn--ghost"><?php echo esc_html( $sub_text ); ?></button>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( '' !== $cta_text ) : ?>
						<?php if ( '' !== $cta_url ) : ?>
							<a class="sapjp-popup__btn sapjp-popup__btn--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
						<?php else : ?>
							<button type="button" class="sapjp-popup__btn sapjp-popup__btn--primary"><?php echo esc_html( $cta_text ); ?></button>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Carded バリアント出力（changelog 形式）
 *
 * @param WP_Post $post
 */
function sapjp_popup_render_variant_carded( $post ) {
	$title         = $post->post_title;
	$eyebrow       = get_post_meta( $post->ID, '_popup_eyebrow', true );
	$intro         = get_post_meta( $post->ID, '_popup_intro', true );
	$head_initial  = get_post_meta( $post->ID, '_popup_head_initial', true );
	if ( '' === $head_initial ) {
		$head_initial = mb_substr( get_bloginfo( 'name' ) ?: 'S', 0, 1 );
	}
	$cards_json    = get_post_meta( $post->ID, '_popup_cards', true ) ?: '[]';
	$cards         = json_decode( $cards_json, true );
	if ( ! is_array( $cards ) ) { $cards = array(); }
	$changelog_url = get_post_meta( $post->ID, '_popup_changelog_url', true );
	$sub_text      = get_post_meta( $post->ID, '_popup_sub_text', true );
	$sub_url       = get_post_meta( $post->ID, '_popup_sub_url', true );
	$cta_text      = get_post_meta( $post->ID, '_popup_cta_text', true );
	$cta_url       = get_post_meta( $post->ID, '_popup_cta_url', true );
	$icons         = sapjp_popup_card_icon_svgs();
	?>
	<div id="sapjp-popup-backdrop"
		class="sapjp-popup-backdrop sapjp-popup-backdrop--carded"
		role="dialog"
		aria-modal="true"
		aria-labelledby="sapjp-popup-title"
		aria-hidden="true"
		data-popup-id="<?php echo esc_attr( $post->ID ); ?>"
		data-variant="carded">
		<div class="sapjp-popup sapjp-popup--carded" role="document">
			<button type="button" class="sapjp-popup__close" aria-label="<?php echo esc_attr__( '閉じる', 'swell_child' ); ?>">
				<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
			</button>

			<header class="sapjp-popup__head">
				<div class="sapjp-popup__head-icon" aria-hidden="true"><?php echo esc_html( $head_initial ); ?></div>
				<div class="sapjp-popup__head-meta">
					<?php if ( '' !== $eyebrow ) : ?>
						<span class="sapjp-popup__eyebrow"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
					<h2 id="sapjp-popup-title" class="sapjp-popup__head-title"><?php echo esc_html( $title ); ?></h2>
				</div>
			</header>

			<div class="sapjp-popup__body">
				<?php if ( '' !== $intro ) : ?>
					<p class="sapjp-popup__intro"><?php echo esc_html( $intro ); ?></p>
				<?php endif; ?>

				<?php if ( ! empty( $cards ) ) : ?>
					<ul class="sapjp-popup__cards">
						<?php foreach ( $cards as $card ) :
							$icon_key = isset( $card['icon'] ) && isset( $icons[ $card['icon'] ] ) ? $card['icon'] : 'star';
							$card_title = isset( $card['title'] ) ? $card['title'] : '';
							$card_desc  = isset( $card['description'] ) ? $card['description'] : '';
							$is_new     = ! empty( $card['is_new'] );
							?>
							<li class="sapjp-popup__card">
								<span class="sapjp-popup__card-icon" aria-hidden="true"><?php
									echo $icons[ $icon_key ]; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								?></span>
								<div class="sapjp-popup__card-body">
									<h3 class="sapjp-popup__card-title">
										<span><?php echo esc_html( $card_title ); ?></span>
										<?php if ( $is_new ) : ?>
											<span class="sapjp-popup__new-tag">NEW</span>
										<?php endif; ?>
									</h3>
									<?php if ( '' !== $card_desc ) : ?>
										<p class="sapjp-popup__card-desc"><?php echo esc_html( $card_desc ); ?></p>
									<?php endif; ?>
								</div>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<footer class="sapjp-popup__foot">
				<?php if ( '' !== $eyebrow ) : ?>
					<?php if ( '' !== $changelog_url ) : ?>
						<a class="sapjp-popup__version-link" href="<?php echo esc_url( $changelog_url ); ?>"><?php echo esc_html( $eyebrow ); ?></a>
					<?php else : ?>
						<span class="sapjp-popup__version-link"><?php echo esc_html( $eyebrow ); ?></span>
					<?php endif; ?>
				<?php endif; ?>

				<span class="sapjp-popup__foot-spacer"></span>

				<?php if ( '' !== $sub_text ) : ?>
					<?php if ( '' !== $sub_url ) : ?>
						<a class="sapjp-popup__btn sapjp-popup__btn--ghost" href="<?php echo esc_url( $sub_url ); ?>"><?php echo esc_html( $sub_text ); ?></a>
					<?php else : ?>
						<button type="button" class="sapjp-popup__btn sapjp-popup__btn--ghost"><?php echo esc_html( $sub_text ); ?></button>
					<?php endif; ?>
				<?php endif; ?>

				<?php if ( '' !== $cta_text ) : ?>
					<?php if ( '' !== $cta_url ) : ?>
						<a class="sapjp-popup__btn sapjp-popup__btn--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
					<?php else : ?>
						<button type="button" class="sapjp-popup__btn sapjp-popup__btn--primary"><?php echo esc_html( $cta_text ); ?></button>
					<?php endif; ?>
				<?php endif; ?>
			</footer>
		</div>
	</div>
	<?php
}

/**
 * Playful バリアント出力（抽象シェイプ + 大見出し）
 *
 * @param WP_Post $post
 */
function sapjp_popup_render_variant_playful( $post ) {
	$title       = $post->post_title;
	$description = $post->post_content;
	$hero_sup    = get_post_meta( $post->ID, '_popup_hero_sup', true );
	$hero_title  = get_post_meta( $post->ID, '_popup_hero_title', true );
	$mini_raw    = get_post_meta( $post->ID, '_popup_mini_tags', true );
	$mini_tags   = sapjp_popup_parse_mini_tags( $mini_raw );
	$badge_text  = get_post_meta( $post->ID, '_popup_badge_text', true );
	$later_text  = get_post_meta( $post->ID, '_popup_later_text', true );
	$sub_text    = get_post_meta( $post->ID, '_popup_sub_text', true );
	$sub_url     = get_post_meta( $post->ID, '_popup_sub_url', true );
	$cta_text    = get_post_meta( $post->ID, '_popup_cta_text', true );
	$cta_url     = get_post_meta( $post->ID, '_popup_cta_url', true );
	?>
	<div id="sapjp-popup-backdrop"
		class="sapjp-popup-backdrop sapjp-popup-backdrop--playful"
		role="dialog"
		aria-modal="true"
		aria-labelledby="sapjp-popup-title"
		aria-hidden="true"
		data-popup-id="<?php echo esc_attr( $post->ID ); ?>"
		data-variant="playful">
		<div class="sapjp-popup sapjp-popup--playful" role="document">
			<button type="button" class="sapjp-popup__close" aria-label="<?php echo esc_attr__( '閉じる', 'swell_child' ); ?>">
				<svg viewBox="0 0 16 16" aria-hidden="true"><path d="M4 4l8 8M12 4l-8 8" stroke-linecap="round"/></svg>
			</button>

			<div class="sapjp-popup__hero" aria-hidden="true">
				<div class="sapjp-popup__shapes">
					<span class="sapjp-popup__shape sapjp-popup__shape--s1"></span>
					<span class="sapjp-popup__shape sapjp-popup__shape--s2"></span>
					<span class="sapjp-popup__shape sapjp-popup__shape--s3"></span>
					<span class="sapjp-popup__shape sapjp-popup__shape--s4"></span>
					<span class="sapjp-popup__shape sapjp-popup__shape--s5"></span>
				</div>
				<?php if ( '' !== $hero_title || '' !== $hero_sup ) : ?>
					<div class="sapjp-popup__hero-text">
						<?php if ( '' !== $hero_sup ) : ?>
							<span class="sapjp-popup__hero-sup"><?php echo esc_html( $hero_sup ); ?></span>
						<?php endif; ?>
						<?php if ( '' !== $hero_title ) : ?>
							<span class="sapjp-popup__hero-title"><?php echo esc_html( $hero_title ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>

			<div class="sapjp-popup__body">
				<?php if ( '' !== $badge_text ) : ?>
					<div class="sapjp-popup__pill-row">
						<span class="sapjp-popup__pill">
							<span class="sapjp-popup__pill-dot" aria-hidden="true"></span>
							<?php echo esc_html( $badge_text ); ?>
						</span>
					</div>
				<?php endif; ?>

				<h2 id="sapjp-popup-title" class="sapjp-popup__title"><?php echo esc_html( $title ); ?></h2>

				<?php if ( '' !== trim( $description ) ) : ?>
					<p class="sapjp-popup__desc"><?php echo esc_html( $description ); ?></p>
				<?php endif; ?>

				<div class="sapjp-popup__actions">
					<?php if ( '' !== $later_text ) : ?>
						<button type="button" class="sapjp-popup__btn sapjp-popup__btn--text"><?php echo esc_html( $later_text ); ?></button>
					<?php endif; ?>

					<span class="sapjp-popup__actions-spacer"></span>

					<?php if ( '' !== $sub_text ) : ?>
						<?php if ( '' !== $sub_url ) : ?>
							<a class="sapjp-popup__btn sapjp-popup__btn--ghost" href="<?php echo esc_url( $sub_url ); ?>"><?php echo esc_html( $sub_text ); ?></a>
						<?php else : ?>
							<button type="button" class="sapjp-popup__btn sapjp-popup__btn--ghost"><?php echo esc_html( $sub_text ); ?></button>
						<?php endif; ?>
					<?php endif; ?>

					<?php if ( '' !== $cta_text ) : ?>
						<?php if ( '' !== $cta_url ) : ?>
							<a class="sapjp-popup__btn sapjp-popup__btn--primary" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_text ); ?></a>
						<?php else : ?>
							<button type="button" class="sapjp-popup__btn sapjp-popup__btn--primary"><?php echo esc_html( $cta_text ); ?></button>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<?php if ( ! empty( $mini_tags ) ) : ?>
					<div class="sapjp-popup__tag-row">
						<?php foreach ( $mini_tags as $tag ) : ?>
							<span class="sapjp-popup__mini-tag"><?php echo esc_html( $tag ); ?></span>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<?php
}
