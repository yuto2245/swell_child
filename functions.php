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

		/* Prism.js 追加言語（jsDelivrバンドルで1リクエストにまとめる） */
		wp_enqueue_script(
			'prismjs-langs',
			'https://cdn.jsdelivr.net/combine/'
				. 'npm/prismjs@1.29.0/components/prism-abap.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-python.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-javascript.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-typescript.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-php.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-bash.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-json.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-yaml.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-sql.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-java.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-go.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-css-extras.min.js,'
				. 'npm/prismjs@1.29.0/components/prism-markup.min.js,'
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
