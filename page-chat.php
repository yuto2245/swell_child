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
			<div class="chat-composer__left">
				<div id="chat-plus-menu" class="chat-plus-menu">
					<button id="chat-plus-trigger" class="chat-plus-menu__trigger" type="button" aria-label="Add">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
					</button>
					<div id="chat-plus-dropdown" class="chat-plus-menu__dropdown">
						<button id="chat-web-search-toggle" class="chat-plus-menu__item" data-action="web-search" type="button">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
							<span>ウェブ検索</span>
							<span class="chat-plus-menu__toggle-indicator"></span>
						</button>
						<button class="chat-plus-menu__item" data-action="code" type="button">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
							<span>コード生成</span>
						</button>
						<button class="chat-plus-menu__item" data-action="image" type="button">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
							<span>画像生成</span>
						</button>
						<div class="chat-plus-menu__divider"></div>
						<button id="chat-reasoning-toggle" class="chat-plus-menu__item" data-action="reasoning" type="button">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
							<span>推論モード</span>
							<span class="chat-plus-menu__toggle-indicator"></span>
						</button>
					</div>
				</div>
			</div>
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
