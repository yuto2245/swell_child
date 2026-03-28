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
<body <?php body_class( 'chat-page' ); ?>>

<div class="chat-header">
	<select id="chat-model-select" class="chat-header__model" aria-label="Model selector"></select>
	<button id="chat-new" class="chat-header__new" type="button">+ New Chat</button>
</div>

<div id="chat-messages" class="chat-messages" role="log" aria-live="polite"></div>

<div class="chat-composer">
	<div class="chat-composer__inner">
		<textarea id="chat-textarea" class="chat-composer__textarea" placeholder="Send a message..." rows="1" aria-label="Chat message input"></textarea>
		<button id="chat-send" class="chat-composer__send" type="button" aria-label="Send message"></button>
	</div>
</div>

<?php wp_footer(); ?>
</body>
</html>
