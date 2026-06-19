<?php
/**
 * Hero glass cards (x.ai-inspired 2x2 grid).
 *
 * @package swell_child
 *
 * @var array<int, array<string, string>> $hero_glass_cards Card definitions.
 * @var string                           $hero_glass_video_url Demo video URL.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $hero_glass_cards ) || ! is_array( $hero_glass_cards ) ) {
	return;
}

/**
 * Render stage content for a glass card.
 *
 * @param array<string, string> $card Card definition.
 * @param string                $video_url Demo video URL.
 */
if ( ! function_exists( 'sapjp_render_hero_glass_stage' ) ) {
	function sapjp_render_hero_glass_stage( $card, $video_url ) {
		$stage = $card['stage'] ?? '';

		if ( 'api' === $stage ) {
			?>
			<video
				class="hero-glass-card__video"
				src="<?php echo esc_url( $video_url ); ?>"
				muted
				autoplay
				loop
				playsinline
				preload="metadata"
				aria-label="<?php echo esc_attr__( 'SAPJP Knowledge APIのターミナル実行デモ', 'swell_child' ); ?>"></video>
			<?php
			return;
		}
		?>
		<div class="hero-glass-mock hero-glass-mock--<?php echo esc_attr( $stage ); ?>" aria-hidden="true">
			<?php if ( 'chat' === $stage ) : ?>
				<div class="hero-glass-mock__bubble hero-glass-mock__bubble--user">ABAPのSELECT文を最適化するには？</div>
				<div class="hero-glass-mock__bubble hero-glass-mock__bubble--ai">
					<p class="hero-glass-mock__bubble-text">WHERE句とインデックスを確認し、必要な列だけ取得しましょう。</p>
					<pre class="hero-glass-mock__code"><code>SELECT matnr, werks
  FROM mara
 WHERE matnr = @lv_id.</code></pre>
				</div>
			<?php elseif ( 'sap' === $stage ) : ?>
				<div class="hero-glass-mock__sap-pills">
					<span><?php esc_html_e( 'S/4HANA', 'swell_child' ); ?></span>
					<span><?php esc_html_e( 'BTP', 'swell_child' ); ?></span>
					<span><?php esc_html_e( 'Fiori', 'swell_child' ); ?></span>
				</div>
				<div class="hero-glass-mock__sap-grid">
					<div class="hero-glass-mock__sap-tile hero-glass-mock__sap-tile--s4">
						<span class="hero-glass-mock__sap-tile-label"><?php esc_html_e( 'S/4HANA', 'swell_child' ); ?></span>
						<span class="hero-glass-mock__sap-tile-desc"><?php esc_html_e( '移行・運用', 'swell_child' ); ?></span>
					</div>
					<div class="hero-glass-mock__sap-tile hero-glass-mock__sap-tile--btp">
						<span class="hero-glass-mock__sap-tile-label"><?php esc_html_e( 'SAP BTP', 'swell_child' ); ?></span>
						<span class="hero-glass-mock__sap-tile-desc"><?php esc_html_e( '拡張・連携', 'swell_child' ); ?></span>
					</div>
					<div class="hero-glass-mock__sap-tile hero-glass-mock__sap-tile--fiori">
						<span class="hero-glass-mock__sap-tile-label"><?php esc_html_e( 'Fiori', 'swell_child' ); ?></span>
						<span class="hero-glass-mock__sap-tile-desc"><?php esc_html_e( 'UX 設計', 'swell_child' ); ?></span>
					</div>
					<div class="hero-glass-mock__sap-tile hero-glass-mock__sap-tile--basis">
						<span class="hero-glass-mock__sap-tile-label"><?php esc_html_e( 'Basis', 'swell_child' ); ?></span>
						<span class="hero-glass-mock__sap-tile-desc"><?php esc_html_e( '基盤・運用', 'swell_child' ); ?></span>
					</div>
				</div>
			<?php elseif ( 'terminal' === $stage ) : ?>
				<div class="hero-glass-mock__term-chrome">
					<span></span>
					<span></span>
					<span></span>
				</div>
				<pre class="hero-glass-mock__term-body"><code><span class="hero-glass-mock__term-line"><span class="hero-glass-mock__term-prompt">sapdev % </span><span class="hero-glass-mock__term-cmd">git log --oneline -3</span></span>
<span class="hero-glass-mock__term-out">a1b2c3d Add breakpoint helper</span>
<span class="hero-glass-mock__term-out">d4e5f6a Fix RFC timeout handling</span>
<span class="hero-glass-mock__term-out">b7c8d9e Introduce CDS view tests</span>
<span class="hero-glass-mock__term-line"><span class="hero-glass-mock__term-prompt">sapdev % </span><span class="hero-glass-mock__term-cmd">adb</span></span>
<span class="hero-glass-mock__term-accent">Stopped at LINE 42  ZCL_HELPER=&gt;RUN</span></code></pre>
			<?php endif; ?>
		</div>
		<?php
	}
}
?>
<div class="hero-glass-grid" aria-label="<?php esc_attr_e( 'SAP-JP のカテゴリ', 'swell_child' ); ?>">
	<?php foreach ( $hero_glass_cards as $index => $card ) : ?>
	<a
		class="hero-glass-card"
		href="<?php echo esc_url( $card['href'] ); ?>"
		style="--glass-index: <?php echo esc_attr( (string) $index ); ?>"
		aria-label="<?php echo esc_attr( $card['aria_label'] ); ?>">
		<div class="hero-glass-card__shell">
			<div class="hero-glass-card__stage hero-glass-card__stage--<?php echo esc_attr( $card['stage'] ); ?>">
				<div class="hero-glass-card__stage-content">
					<?php sapjp_render_hero_glass_stage( $card, $hero_glass_video_url ); ?>
				</div>
			</div>
			<div class="hero-glass-card__footer-fade" aria-hidden="true">
				<span class="hero-glass-card__footer-fade-layer hero-glass-card__footer-fade-layer--1"></span>
				<span class="hero-glass-card__footer-fade-layer hero-glass-card__footer-fade-layer--2"></span>
				<span class="hero-glass-card__footer-fade-layer hero-glass-card__footer-fade-layer--3"></span>
				<span class="hero-glass-card__footer-fade-layer hero-glass-card__footer-fade-layer--4"></span>
			</div>
			<div class="hero-glass-card__footer">
				<span class="hero-glass-card__label"><?php echo esc_html( $card['label'] ); ?></span>
				<span class="hero-glass-card__cta"><?php esc_html_e( 'Explore →', 'swell_child' ); ?></span>
			</div>
		</div>
	</a>
	<?php endforeach; ?>
</div>
