<?php
/**
 * sapjp.net ポップアップ — カスタム投稿タイプ
 *
 * 1投稿 = 1ポップアップ。
 * - title: ポップアップのタイトル（モーダル内 h2 に表示）
 * - content: 説明文（description）
 * - featured image: 上部イラスト（未設定なら CSS モック）
 * - post_status = publish かつスケジュール内のものが配信対象
 *
 * @package swell_child
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

const SAPJP_POPUP_CPT = 'sapjp_popup';

/**
 * バリアント選択肢。将来 split / carded / cinematic などを追加できる。
 *
 * @return array<string,string> slug => 表示名
 */
function sapjp_popup_variants() {
	return array(
		'classic' => 'Classic（ヒーロー + タイトル + 説明）',
		/* 将来: 'carded' => 'Carded（changelog形式）', */
	);
}

/**
 * CPT 登録
 */
add_action( 'init', function () {
	register_post_type( SAPJP_POPUP_CPT, array(
		'labels' => array(
			'name'               => 'ポップアップ',
			'singular_name'      => 'ポップアップ',
			'add_new'            => '新規追加',
			'add_new_item'       => '新しいポップアップを追加',
			'edit_item'          => 'ポップアップを編集',
			'new_item'           => '新しいポップアップ',
			'view_item'          => 'ポップアップを表示',
			'search_items'       => 'ポップアップを検索',
			'not_found'          => 'ポップアップが見つかりません',
			'not_found_in_trash' => 'ゴミ箱にポップアップはありません',
			'menu_name'          => 'ポップアップ',
		),
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_admin_bar'   => false,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-megaphone',
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'capability_type'     => 'post',
		'map_meta_cap'        => true,
		'has_archive'         => false,
		'rewrite'             => false,
		'show_in_rest'        => false,
		'exclude_from_search' => true,
	) );
} );

/**
 * メタボックス登録
 */
add_action( 'add_meta_boxes', function () {
	add_meta_box(
		'sapjp_popup_meta',
		'ポップアップ設定',
		'sapjp_popup_render_meta_box',
		SAPJP_POPUP_CPT,
		'normal',
		'high'
	);

	add_meta_box(
		'sapjp_popup_preview',
		'プレビュー',
		'sapjp_popup_render_preview_box',
		SAPJP_POPUP_CPT,
		'normal',
		'default'
	);
} );

/**
 * メタボックス本体
 *
 * @param WP_Post $post
 */
function sapjp_popup_render_meta_box( $post ) {
	wp_nonce_field( 'sapjp_popup_save_' . $post->ID, 'sapjp_popup_nonce' );

	$variant       = get_post_meta( $post->ID, '_popup_variant', true ) ?: 'classic';
	$badge_text    = get_post_meta( $post->ID, '_popup_badge_text', true );
	$later_text    = get_post_meta( $post->ID, '_popup_later_text', true );
	$sub_text      = get_post_meta( $post->ID, '_popup_sub_text', true );
	$sub_url       = get_post_meta( $post->ID, '_popup_sub_url', true );
	$cta_text      = get_post_meta( $post->ID, '_popup_cta_text', true );
	$cta_url       = get_post_meta( $post->ID, '_popup_cta_url', true );
	$cookie_days   = get_post_meta( $post->ID, '_popup_cookie_days', true );
	$delay_ms      = get_post_meta( $post->ID, '_popup_delay_ms', true );
	$schedule_end  = get_post_meta( $post->ID, '_popup_schedule_end', true );

	if ( '' === $badge_text )  { $badge_text  = '新機能'; }
	if ( '' === $later_text )  { $later_text  = '後で'; }
	if ( '' === $sub_text )    { $sub_text    = '詳細を見る'; }
	if ( '' === $cta_text )    { $cta_text    = '試してみる'; }
	if ( '' === $cookie_days ) { $cookie_days = 30; }
	if ( '' === $delay_ms )    { $delay_ms    = 1500; }

	$variants = sapjp_popup_variants();
	?>
	<style>
		.sapjp-popup-fields { display: grid; grid-template-columns: 180px 1fr; gap: 12px 16px; align-items: start; }
		.sapjp-popup-fields label { font-weight: 600; padding-top: 6px; }
		.sapjp-popup-fields input[type="text"],
		.sapjp-popup-fields input[type="url"],
		.sapjp-popup-fields input[type="number"],
		.sapjp-popup-fields input[type="date"],
		.sapjp-popup-fields select { width: 100%; max-width: 480px; }
		.sapjp-popup-fields .field-desc { color: #646970; font-size: 12px; margin-top: 4px; }
		.sapjp-popup-fields hr { grid-column: 1 / -1; border: 0; border-top: 1px solid #dcdcde; margin: 8px 0; }
		.sapjp-popup-fields h4 { grid-column: 1 / -1; margin: 8px 0 0; font-size: 13px; color: #2c3338; }
	</style>

	<div class="sapjp-popup-fields">
		<label for="popup_variant">バリアント</label>
		<div>
			<select name="popup_variant" id="popup_variant">
				<?php foreach ( $variants as $slug => $label ) : ?>
					<option value="<?php echo esc_attr( $slug ); ?>" <?php selected( $variant, $slug ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<p class="field-desc">ポップアップのテンプレート種別。今後バリアントが追加されます。</p>
		</div>

		<h4>表示内容</h4>

		<label for="popup_badge_text">バッジ（ピル）テキスト</label>
		<div>
			<input type="text" name="popup_badge_text" id="popup_badge_text" value="<?php echo esc_attr( $badge_text ); ?>" placeholder="新機能">
			<p class="field-desc">空欄でバッジ非表示。</p>
		</div>

		<h4>アクションボタン</h4>

		<label for="popup_later_text">「後で」テキスト</label>
		<div>
			<input type="text" name="popup_later_text" id="popup_later_text" value="<?php echo esc_attr( $later_text ); ?>">
			<p class="field-desc">左側のテキストリンク（クリックで閉じる）。空欄で非表示。</p>
		</div>

		<label for="popup_sub_text">ゴースト ラベル</label>
		<div>
			<input type="text" name="popup_sub_text" id="popup_sub_text" value="<?php echo esc_attr( $sub_text ); ?>">
		</div>

		<label for="popup_sub_url">ゴースト URL</label>
		<div>
			<input type="url" name="popup_sub_url" id="popup_sub_url" value="<?php echo esc_attr( $sub_url ); ?>" placeholder="https://...">
			<p class="field-desc">空欄の場合、クリックで閉じるのみ。</p>
		</div>

		<label for="popup_cta_text">CTA ラベル</label>
		<div>
			<input type="text" name="popup_cta_text" id="popup_cta_text" value="<?php echo esc_attr( $cta_text ); ?>">
		</div>

		<label for="popup_cta_url">CTA URL</label>
		<div>
			<input type="url" name="popup_cta_url" id="popup_cta_url" value="<?php echo esc_attr( $cta_url ); ?>" placeholder="https://...">
			<p class="field-desc">空欄の場合、クリックで閉じるのみ。</p>
		</div>

		<h4>配信設定</h4>

		<label for="popup_cookie_days">Cookie 有効期限（日）</label>
		<div>
			<input type="number" name="popup_cookie_days" id="popup_cookie_days" value="<?php echo esc_attr( $cookie_days ); ?>" min="1" max="365" step="1">
			<p class="field-desc">閉じた後、再表示までの日数。</p>
		</div>

		<label for="popup_delay_ms">表示遅延（ミリ秒）</label>
		<div>
			<input type="number" name="popup_delay_ms" id="popup_delay_ms" value="<?php echo esc_attr( $delay_ms ); ?>" min="0" max="60000" step="100">
		</div>

		<label for="popup_schedule_end">配信終了日</label>
		<div>
			<input type="date" name="popup_schedule_end" id="popup_schedule_end" value="<?php echo esc_attr( $schedule_end ); ?>">
			<p class="field-desc">この日付を過ぎると自動的に非表示（空欄なら無期限）。</p>
		</div>
	</div>
	<?php
}

/**
 * 保存処理
 */
add_action( 'save_post_' . SAPJP_POPUP_CPT, function ( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! isset( $_POST['sapjp_popup_nonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['sapjp_popup_nonce'] ) ), 'sapjp_popup_save_' . $post_id ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	$variants = sapjp_popup_variants();

	$variant = isset( $_POST['popup_variant'] ) ? sanitize_key( wp_unslash( $_POST['popup_variant'] ) ) : 'classic';
	if ( ! isset( $variants[ $variant ] ) ) {
		$variant = 'classic';
	}
	update_post_meta( $post_id, '_popup_variant', $variant );

	$text_fields = array( 'badge_text', 'later_text', 'sub_text', 'cta_text' );
	foreach ( $text_fields as $f ) {
		$val = isset( $_POST[ "popup_$f" ] ) ? sanitize_text_field( wp_unslash( $_POST[ "popup_$f" ] ) ) : '';
		update_post_meta( $post_id, "_popup_$f", $val );
	}

	$url_fields = array( 'sub_url', 'cta_url' );
	foreach ( $url_fields as $f ) {
		$val = isset( $_POST[ "popup_$f" ] ) ? esc_url_raw( wp_unslash( $_POST[ "popup_$f" ] ) ) : '';
		update_post_meta( $post_id, "_popup_$f", $val );
	}

	$cookie_days = isset( $_POST['popup_cookie_days'] ) ? absint( $_POST['popup_cookie_days'] ) : 30;
	if ( $cookie_days < 1 )   { $cookie_days = 1; }
	if ( $cookie_days > 365 ) { $cookie_days = 365; }
	update_post_meta( $post_id, '_popup_cookie_days', $cookie_days );

	$delay_ms = isset( $_POST['popup_delay_ms'] ) ? absint( $_POST['popup_delay_ms'] ) : 1500;
	if ( $delay_ms > 60000 ) { $delay_ms = 60000; }
	update_post_meta( $post_id, '_popup_delay_ms', $delay_ms );

	$schedule_end = isset( $_POST['popup_schedule_end'] ) ? sanitize_text_field( wp_unslash( $_POST['popup_schedule_end'] ) ) : '';
	if ( $schedule_end && ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', $schedule_end ) ) {
		$schedule_end = '';
	}
	update_post_meta( $post_id, '_popup_schedule_end', $schedule_end );
}, 10, 1 );

/**
 * プレビュー iframe
 *
 * @param WP_Post $post
 */
function sapjp_popup_render_preview_box( $post ) {
	if ( 'auto-draft' === $post->post_status ) {
		echo '<p>保存するとプレビューできます。</p>';
		return;
	}
	$nonce = wp_create_nonce( 'sapjp_popup_preview_' . $post->ID );
	$url   = add_query_arg(
		array(
			'sapjp_popup_preview' => $post->ID,
			'sapjp_popup_nonce'   => $nonce,
		),
		home_url( '/' )
	);
	?>
	<p style="margin-top:0;">
		<a href="<?php echo esc_url( $url ); ?>" target="_blank" class="button">別タブで開く</a>
	</p>
	<iframe src="<?php echo esc_url( $url ); ?>"
		style="width:100%;height:720px;border:1px solid #dcdcde;border-radius:4px;background:#fff;"
		title="プレビュー"></iframe>
	<p class="description" style="margin-top:8px;">
		※ プレビューでは Cookie チェックをバイパスし、下書き/非公開でも強制表示します。投稿を保存してからリロードすると最新の内容が反映されます。
	</p>
	<?php
}

/**
 * 一覧カラム（バリアント・終了日・ステータス）
 */
add_filter( 'manage_' . SAPJP_POPUP_CPT . '_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['popup_variant']  = 'バリアント';
			$new['popup_end']      = '配信終了日';
		}
	}
	return $new;
} );

add_action( 'manage_' . SAPJP_POPUP_CPT . '_posts_custom_column', function ( $col, $post_id ) {
	if ( 'popup_variant' === $col ) {
		$v = get_post_meta( $post_id, '_popup_variant', true ) ?: 'classic';
		echo esc_html( $v );
	} elseif ( 'popup_end' === $col ) {
		$end = get_post_meta( $post_id, '_popup_schedule_end', true );
		if ( ! $end ) {
			echo '<span style="color:#646970;">無期限</span>';
		} else {
			$expired = ( strtotime( $end . ' 23:59:59' ) < current_time( 'timestamp' ) );
			$color = $expired ? '#d63638' : '#50575e';
			echo '<span style="color:' . esc_attr( $color ) . ';">' . esc_html( $end ) . ( $expired ? '（期限切れ）' : '' ) . '</span>';
		}
	}
}, 10, 2 );

/**
 * ポップアップ1件取得（フロント配信用）
 *
 * publish かつスケジュール内のうち、最新1件。
 *
 * @return WP_Post|null
 */
function sapjp_popup_get_active() {
	$query = new WP_Query( array(
		'post_type'              => SAPJP_POPUP_CPT,
		'post_status'            => 'publish',
		'posts_per_page'         => 1,
		'orderby'                => 'date',
		'order'                  => 'DESC',
		'no_found_rows'          => true,
		'update_post_term_cache' => false,
		'meta_query'             => array(
			'relation' => 'OR',
			array(
				'key'     => '_popup_schedule_end',
				'value'   => '',
				'compare' => '=',
			),
			array(
				'key'     => '_popup_schedule_end',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_popup_schedule_end',
				'value'   => current_time( 'Y-m-d' ),
				'compare' => '>=',
				'type'    => 'CHAR',
			),
		),
	) );

	if ( $query->have_posts() ) {
		return $query->posts[0];
	}
	return null;
}

/**
 * プレビュー対象の post を取得（管理者のみ、nonce 検証済み）
 *
 * @return WP_Post|null
 */
function sapjp_popup_get_preview() {
	if ( ! isset( $_GET['sapjp_popup_preview'], $_GET['sapjp_popup_nonce'] ) ) {
		return null;
	}
	$preview_id = absint( $_GET['sapjp_popup_preview'] );
	if ( ! $preview_id ) {
		return null;
	}
	$nonce = sanitize_key( wp_unslash( $_GET['sapjp_popup_nonce'] ) );
	if ( ! wp_verify_nonce( $nonce, 'sapjp_popup_preview_' . $preview_id ) ) {
		return null;
	}
	if ( ! current_user_can( 'edit_post', $preview_id ) ) {
		return null;
	}
	$post = get_post( $preview_id );
	if ( ! $post || SAPJP_POPUP_CPT !== $post->post_type ) {
		return null;
	}
	return $post;
}
