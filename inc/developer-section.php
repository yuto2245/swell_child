<?php
/**
 * トップ: For developers セクション（x.ai 風・2カラム）
 *
 * @var string $developer_api_base
 * @var array  $developer_tabs
 * @var array  $developer_stats
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * cURL 1行コマンドの簡易シンタックス表示
 *
 * @param string $command Raw command.
 */
function sapjp_developer_render_curl( $command ) {
	$command_text = (string) $command;
	$url_pos      = strpos( $command_text, '"' );
	$command_lead = false !== $url_pos ? substr( $command_text, 0, $url_pos ) : $command_text;
	$command_url  = false !== $url_pos ? substr( $command_text, $url_pos ) : '';

	echo '<span class="sapjp-developer__tok sapjp-developer__tok--cmd">' . esc_html( $command_lead ) . '</span>';
	if ( '' !== $command_url ) {
		echo '<span class="sapjp-developer__tok sapjp-developer__tok--str">' . esc_html( $command_url ) . '</span>';
	}
}

$developer_default = $developer_tabs[0];
$api_docs_url      = home_url( '/api' );
?>

<section class="sapjp-developer js-fade-in" id="sapjp-developer" aria-labelledby="sapjp-developer-heading" data-active-tab="<?php echo esc_attr( $developer_default['id'] ); ?>">
	<div class="sapjp-developer__inner">
		<div class="sapjp-developer__grid">
			<div class="sapjp-developer__lead">
				<p class="sapjp-developer__eyebrow">For developers</p>
				<h2 class="sapjp-developer__heading" id="sapjp-developer-heading">
					One API.<br>
					<span class="sapjp-developer__heading-muted">Every SAP context.</span>
				</h2>
				<p class="sapjp-developer__desc">
					SAP専門知識を REST で取得。検索・記事・RAG 向けコンテキストを、エージェントやツールからそのまま呼び出せます。
				</p>
				<div class="sapjp-developer__actions">
					<a class="sapjp-developer__btn sapjp-developer__btn--primary" href="<?php echo esc_url( $api_docs_url ); ?>">
						API ドキュメント
					</a>
					<a class="sapjp-developer__btn sapjp-developer__btn--ghost" href="<?php echo esc_url( $api_docs_url ); ?>">
						Read Docs
					</a>
				</div>
				<div class="sapjp-developer__stats">
					<?php foreach ( $developer_stats as $stat ) : ?>
					<div class="sapjp-developer__stat">
						<p class="sapjp-developer__stat-value"><?php echo esc_html( $stat['value'] ); ?></p>
						<p class="sapjp-developer__stat-label"><?php echo esc_html( $stat['label'] ); ?></p>
					</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="sapjp-developer__showcase">
				<div class="sapjp-developer__frame" aria-hidden="true">
					<span class="sapjp-developer__corner sapjp-developer__corner--tl"></span>
					<span class="sapjp-developer__corner sapjp-developer__corner--tr"></span>
					<span class="sapjp-developer__corner sapjp-developer__corner--bl"></span>
					<span class="sapjp-developer__corner sapjp-developer__corner--br"></span>
				</div>
				<div class="sapjp-developer__code-stage">
					<div class="sapjp-developer__window">
						<div class="sapjp-developer__titlebar">
							<span class="sapjp-developer__dot" aria-hidden="true"></span>
							<span class="sapjp-developer__dot" aria-hidden="true"></span>
							<span class="sapjp-developer__dot" aria-hidden="true"></span>
							<button type="button" class="sapjp-developer__copy" data-sapjp-dev-copy aria-label="コマンドをコピー">
								<svg class="sapjp-developer__copy-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
									<rect x="3" y="8" width="13" height="13" rx="4" stroke="currentColor" stroke-width="2"></rect>
									<path fill-rule="evenodd" clip-rule="evenodd" d="M13 2.00004L12.8842 2.00002C12.0666 1.99982 11.5094 1.99968 11.0246 2.09611C9.92585 2.31466 8.95982 2.88816 8.25008 3.69274C7.90896 4.07944 7.62676 4.51983 7.41722 5.00004H9.76392C10.189 4.52493 10.7628 4.18736 11.4147 4.05768C11.6802 4.00488 12.0228 4.00004 13 4.00004H14.6C15.7366 4.00004 16.5289 4.00081 17.1458 4.05121C17.7509 4.10066 18.0986 4.19283 18.362 4.32702C18.9265 4.61464 19.3854 5.07358 19.673 5.63807C19.8072 5.90142 19.8994 6.24911 19.9488 6.85428C19.9992 7.47112 20 8.26343 20 9.40004V11C20 11.9773 19.9952 12.3199 19.9424 12.5853C19.8127 13.2373 19.4748 13.8114 19 14.2361V16.5829C20.4795 15.9374 21.5804 14.602 21.9039 12.9755C22.0004 12.4907 22.0002 11.9334 22 11.1158L22 11V9.40004V9.35725C22 8.27346 22 7.3993 21.9422 6.69141C21.8826 5.96256 21.7568 5.32238 21.455 4.73008C20.9757 3.78927 20.2108 3.02437 19.27 2.545C18.6777 2.24322 18.0375 2.1174 17.3086 2.05785C16.6007 2.00002 15.7266 2.00003 14.6428 2.00004L14.6 2.00004H13Z" fill="currentColor"></path>
								</svg>
								<span class="sapjp-developer__copy-text">Copy</span>
							</button>
						</div>
						<div class="sapjp-developer__code-wrap">
							<?php foreach ( $developer_tabs as $tab_index => $tab ) : ?>
							<pre class="sapjp-developer__code<?php echo 0 === $tab_index ? ' is-active' : ''; ?>"
								 id="sapjp-dev-code-<?php echo esc_attr( $tab['id'] ); ?>"
								 data-sapjp-dev-panel="<?php echo esc_attr( $tab['id'] ); ?>"
								 data-command="<?php echo esc_attr( $tab['command'] ); ?>"
								 <?php echo 0 === $tab_index ? '' : ' hidden'; ?>><code><?php sapjp_developer_render_curl( $tab['command'] ); ?></code></pre>
							<?php endforeach; ?>
						</div>
					</div>
					<div class="sapjp-developer__tabs" role="tablist" aria-label="API コマンド例">
						<?php foreach ( $developer_tabs as $tab_index => $tab ) : ?>
						<button type="button"
								class="sapjp-developer__tab<?php echo 0 === $tab_index ? ' is-active' : ''; ?>"
								role="tab"
								id="sapjp-dev-tab-<?php echo esc_attr( $tab['id'] ); ?>"
								aria-selected="<?php echo 0 === $tab_index ? 'true' : 'false'; ?>"
								aria-controls="sapjp-dev-code-<?php echo esc_attr( $tab['id'] ); ?>"
								data-sapjp-dev-tab="<?php echo esc_attr( $tab['id'] ); ?>">
							<?php echo esc_html( $tab['label'] ); ?>
						</button>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
