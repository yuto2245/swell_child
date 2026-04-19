# sapjp.net ポップアップ機能 — Claude Code コンテキスト

## プロジェクト概要
WordPress + SWELL 子テーマで構成された SAP 技術ブログに、
新機能告知用モーダルポップアップを追加する。
Claude Design の popup.html（Classic バリアント）をベースに、
**カスタム投稿タイプ `sapjp_popup` で複数件・バリアント切替を管理可能**な構造で実装。

## 絶対ルール
- **親テーマ（swell/）は READ ONLY。一切変更しない。**
- **wp-config.php、.htaccess、plugins/ は触らない。**
- **jQuery に依存しない。Vanilla JS のみ。**
- **全ての子テーマ変更は swell_child/ 内に限定する。**

## 仕様書の場所
```
.kiro/specs/popup/
├── requirements.md   ← 何を作るか（EARS記法の受け入れ基準）
├── design.md         ← どう作るか（トークン・コンポーネント・インタラクション）
└── tasks.md          ← どの順で作るか（依存関係付きチェックリスト）
```
**popup.html はデモ。実装では Classic バリアントを CPT 化した構造を採用。**

## ファイル構造
```
swell_child/
├── style.css              # 既存（触らない）
├── functions.php          # 追記: require + enqueue + wp_footer レンダラー
├── assets/
│   ├── popup.css          # Classic バリアント CSS（.sapjp-popup 名前空間）
│   └── popup.js           # Cookie制御 + 開閉 + フォーカストラップ
└── inc/
    └── popup-cpt.php      # CPT 登録 + メタボックス + プレビュー + 配信クエリ
```

## アーキテクチャ
- **CPT `sapjp_popup`**: 1投稿 = 1ポップアップ
  - `post_title` → モーダル h2
  - `post_content` → 説明文
  - アイキャッチ画像 → ヒーロー画像（未設定なら CSS モック）
- **メタフィールド**: `_popup_variant` / `_popup_badge_text` / `_popup_later_text` /
  `_popup_sub_text` / `_popup_sub_url` / `_popup_cta_text` / `_popup_cta_url` /
  `_popup_cookie_days` / `_popup_delay_ms` / `_popup_schedule_end`
- **配信ロジック**: `publish` かつスケジュール内の最新1件を `wp_footer` で出力。
  Cookie キー = `sapjp_popup_{post_id}`
- **プレビュー**: 編集画面に iframe 埋込。
  `?sapjp_popup_preview={id}&sapjp_popup_nonce={nonce}` で Cookie バイパス、
  `current_user_can('edit_post', $id)` と nonce で二重保護
- **バリアント拡張**: `sapjp_popup_render_variant_{slug}($post)` 関数を追加し、
  `sapjp_popup_variants()` にキーを登録するだけで新バリアント対応

## SWELL 固有の注意点
- SWELL の z-index 階層: 固定ヘッダー = 100、ドロワーメニュー = 999
  → ポップアップ backdrop は z-index: 9999 で設定
- SWELL は `#wrapper` > `#header` / `#content` / `#footer` の構造
  → ポップアップ HTML は body 直下（wp_footer）に挿入
- SWELL のスクロール防止クラス: `.is-menu-opened` で body に overflow: hidden
  → 競合しないよう、popup 側は `body.style.overflow` を直接制御

## CSS 命名規則
- 全セレクタに `.sapjp-popup` プレフィックス（SWELL と衝突防止）
- BEM: `.sapjp-popup__element--modifier`
- CSS 変数: `--popup-*` 名前空間（`.sapjp-popup-backdrop` スコープで定義）

## PHP コーディング規約
- WordPress Coding Standards に従う（phpcs --standard=WordPress）
- エスケープ: `esc_html()` / `esc_url()` / `esc_attr()` を全出力に適用
- メタ保存は `save_post_{cpt}` フックで `wp_verify_nonce` + `current_user_can` 必須
- プレビューでは `isPreview=true` を JS に渡し Cookie チェックをスキップ

## JS コーディング規約
- IIFE + 'use strict'
- グローバル変数なし（wp_localize_script で PHP → JS データ受け渡し）
- Cookie: Secure（HTTPS時のみ）; SameSite=Lax; path=/
- Cookie 書き込み不可時: sessionStorage → in-memory の順でフォールバック

## テスト手順
1. 管理画面 /wp-admin/edit.php?post_type=sapjp_popup で新規追加
2. タイトル・本文・バッジ・ボタンを入力、アイキャッチ画像をセット（任意）
3. 下書き保存 → プレビュー iframe で確認
4. 公開 → フロントで表示確認（Cookie をクリアして再表示）
5. DevTools > Application > Cookies で `sapjp_popup_{post_id}` を確認

## リファレンス
- popup.html（Claude Design 出力）: デモファイル、Classic バリアントがベース
- SWELL 公式: https://swell-theme.com/
- SWELL CSS変数: --color_main / --color_text / --color_link / --color_bg
