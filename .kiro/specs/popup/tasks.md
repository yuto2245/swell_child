# Tasks: sapjp.net 新機能ポップアップ

## 凡例
- `[P]` = 並列実行可能
- `_Depends: T###_` = 先行タスク
- `_Boundary: path_` = 編集対象ファイル
- `_Requirements: AC-###_` = 対応する受け入れ基準
- `_Design: §N_` = 対応する design.md セクション

---

## T001 [P] Customizer セクション登録
_Boundary: inc/popup-settings.php_
_Requirements: AC-007_
_Design: §5_

- [ ] `inc/popup-settings.php` を新規作成
- [ ] `customize_register` フックで `sapjp_popup` セクションを追加
- [ ] Design §5.1 の全設定項目を `add_setting` + `add_control` で登録
- [ ] Cards JSON は `textarea` コントロール + 説明文でJSON形式を案内
- [ ] アイコン選択は `select` コントロール（8種）
- [ ] `sanitize_callback` を各設定に付与（text → `sanitize_text_field`, bool → `wp_validate_boolean`）

## T002 [P] ポップアップ CSS 作成
_Boundary: assets/popup.css_
_Design: §3, §4, §6, §7_

- [ ] `assets/popup.css` を新規作成
- [ ] Design §3 の CSS変数を `:root` に定義（`--popup-*` 名前空間）
- [ ] Design §4.1〜4.8 の全コンポーネントを BEM 記法で実装
  - `.sapjp-popup-backdrop`
  - `.sapjp-popup`
  - `.sapjp-popup__head`, `__head-icon`, `__eyebrow`, `__title`
  - `.sapjp-popup__body`, `__intro`
  - `.sapjp-popup__cards`, `__card`, `__card-icon`, `__card-title`, `__card-desc`
  - `.sapjp-popup__new-tag`
  - `.sapjp-popup__foot`, `__version-link`
  - `.sapjp-popup__close`
  - `.sapjp-popup__btn`, `--primary`, `--ghost`
- [ ] Design §6.3, §6.4 の hover/active トランジションを実装
- [ ] Design §7 のレスポンシブブレイクポイントを `@media` で実装
- [ ] 初期状態: backdrop は `opacity: 0; pointer-events: none`
- [ ] `.sapjp-popup-backdrop.open` で表示アニメーション
- [ ] popup.html の Carded バリアント CSS と pixel 単位で一致することを確認
- [ ] SWELL のダークモード用 `prefers-color-scheme: dark` メディアクエリ追加

## T003 [P] ポップアップ JS 作成
_Boundary: assets/popup.js_
_Requirements: AC-001, AC-002, AC-003_
_Design: §6_

- [ ] `assets/popup.js` を新規作成（Vanilla JS, IIFE, strict mode）
- [ ] Cookie 読み書きユーティリティ関数
  - `getCookie(name)` → value | null
  - `setCookie(name, value, days)` → void（Secure; SameSite=Lax; path=/）
- [ ] `openPopup()`: backdrop に `.open` 付与、body に `overflow: hidden`、
  `#wrapper` に `aria-hidden="true"`、フォーカストラップ開始
- [ ] `closePopup()`: `.open` 除去、cookie セット、body/wrapper 復元、フォーカス返却
- [ ] トリガーロジック: DOMContentLoaded → delay → cookie チェック → openPopup()
- [ ] 閉じるトリガー: ×ボタン click / backdrop click / Escape keydown
- [ ] フォーカストラップ: Tab / Shift+Tab でモーダル内をループ
- [ ] popup_id / cookie_days / delay_ms は PHP から `wp_localize_script` で注入
- [ ] Cookie 不可時の sessionStorage → in-memory フォールバック（Design §8）

## T004 PHP: HTML出力 + アセット読み込み
_Boundary: functions.php_
_Depends: T001, T002, T003_
_Requirements: AC-001, AC-004, AC-005_
_Design: §2, §4_

- [ ] `functions.php` に `require_once get_stylesheet_directory() . '/inc/popup-settings.php';` 追加
- [ ] `sapjp_popup_enqueue()` を `wp_enqueue_scripts` に登録（priority 50）
  - popup.css を enqueue（`media="print" onload="this.media='all'"` で非クリティカル化）
  - popup.js を enqueue（`defer` 属性付き）
  - `wp_localize_script` で popup_id / cookie_days / delay_ms を JS に渡す
- [ ] `sapjp_render_popup()` を `wp_footer` に登録（priority 99）
  - `get_theme_mod('sapjp_popup_enabled')` が false → return
  - Design §4 の HTML 構造を出力
  - Cards JSON をデコードしてループ出力
  - SVGアイコンマップ（8種）をインラインで出力
  - エスケープ: `esc_html()` / `esc_url()` / `esc_attr()` を全出力に適用
- [ ] `is_customize_preview()` の場合は cookie チェックをスキップ（プレビューで常に表示）

## T005 Visual QA
_Depends: T004_
_Design: §9_

- [ ] Local で desktop（1440px）スクリーンショット取得
- [ ] Local で mobile（375px）スクリーンショット取得
- [ ] popup.html のCarded バリアントと並べてピクセル比較
- [ ] 差異があれば popup.css を修正
- [ ] SWELL の各記事レイアウト（リスト/カード/ワイド）でポップアップが正常表示されることを確認
- [ ] SWELL の固定ヘッダーの上にポップアップが表示されることを確認（z-index）

## T006 A11y テスト
_Depends: T004_
_Requirements: AC-004_
_Design: §9_

- [ ] axe DevTools でモーダル開閉をスキャン（violation 0）
- [ ] キーボードのみで操作: Tab → 閉じるボタン → ゴーストボタン → プライマリボタン → Tab でループ
- [ ] Escape で閉じた後、フォーカスがページ本文に戻ることを確認
- [ ] VoiceOver (macOS) でモーダルのラベル読み上げを確認
- [ ] WebAIM Contrast Checker で §3.1 のコントラスト比を実測確認

## T007 Performance テスト
_Depends: T004_
_Requirements: AC-005_

- [ ] Lighthouse (mobile) で CLS = 0 を確認（ポップアップ表示時に CLS が発生しないこと）
- [ ] popup.css + popup.js の合計サイズが 10KB 未満であることを確認
- [ ] 非クリティカル CSS 読み込み（print→all trick）が機能していることを確認
- [ ] defer JS がページの初期レンダリングをブロックしていないことを確認

## T008 Cross-browser テスト
_Depends: T004_
_Design: §9_

- [ ] Chrome (desktop) で開閉・hover・cookie を確認
- [ ] Safari (desktop) で backdrop-filter が効いていることを確認
- [ ] Firefox で開閉・cookie を確認
- [ ] iOS Safari (実機 or Simulator) でモーダルサイズ・スクロール防止を確認
- [ ] Android Chrome で確認

## T009 ステージング検証 → 本番デプロイ
_Depends: T005, T006, T007, T008_

- [ ] feature ブランチを main にマージ、タグ `popup-v1.0` を切る
- [ ] ステージングに git pull、最終確認
- [ ] 本番に git pull（Claude Code は使わない）
- [ ] カスタマイザーで sapjp_popup_enabled = true に設定
- [ ] 実際の告知内容を入力して公開
- [ ] 30分後に自分のスマホで確認、cookie が効いていることを確認
