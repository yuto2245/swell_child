<?php
/**
 * Template Name: Chat
 *
 * フルスクリーンAIチャットUI（管理者専用）
 */

if ( ! current_user_can( 'manage_options' ) ) {
	wp_redirect( home_url() );
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex, nofollow">
<?php wp_head(); ?>
</head>
<body <?php body_class( 'chat-page is-empty' ); ?>>

<div class="chat-header">
	<div id="chat-model-dropdown" class="chat-dropdown">
		<button id="chat-model-trigger" class="chat-dropdown__trigger" type="button">
			<img id="chat-model-icon" class="chat-dropdown__icon" src="" alt="">
			<span id="chat-model-label" class="chat-dropdown__label"></span>
			<svg class="chat-dropdown__chevron" width="12" height="12" viewBox="0 0 12 12"><path d="M3 5l3 3 3-3" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
		</button>
		<div id="chat-model-menu" class="chat-dropdown__menu"></div>
	</div>
	<button id="chat-new" class="chat-header__new" type="button">+ New Chat</button>
</div>

<div id="chat-messages" class="chat-messages" role="log" aria-live="polite"></div>

<div class="chat-composer">
	<div class="chat-composer__inner">
		<textarea id="chat-textarea" class="chat-composer__textarea" placeholder="本日はどのようなお手伝いをさせていただけますか？" rows="1" aria-label="Chat message input"></textarea>
		<div class="chat-composer__controls">
			<div class="chat-composer__left"></div>
			<div class="chat-composer__right">
				<span id="chat-composer-model" class="chat-composer__model-name"></span>
				<button id="chat-send" class="chat-composer__send" type="button" aria-label="Send message">
					<svg width="14" height="14" viewBox="0 0 24 24"><polyline points="6 15 12 9 18 15"/></svg>
				</button>
			</div>
		</div>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
