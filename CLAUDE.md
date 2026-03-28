# SWELL Child Theme - SAP-JP トップページ

## プロジェクト概要

WordPress SWELL テーマの子テーマで、**Anthropic風の洗練されたUI**を持つオリジナルトップページを実装済み。

## 環境情報

- **開発環境**: Local by WP Engine
- **サイトURL**: http://swell-local.local
- **管理画面**: http://swell-local.local/wp-admin
- **親テーマ**: SWELL（ライセンス認証済み）
- **本番サーバー**: Xserver（sapjp.net）
- **本番PHP**: **7.4.33**（変更不可 — SWELLテーマの互換性のため）
- **開発PHP**: 8.4.18（本番と異なるため注意）

## 本番環境の制約（重要）

### PHPバージョン制約
- **本番は PHP 7.4.33**。PHP 8.x 以上を要求するライブラリ・SDKは使用禁止
- Composer パッケージ（`composer require`）は原則使用不可（PHP 8.x 依存が多い）
- 外部APIの呼び出しは **cURL（`curl_init`）** または **`wp_remote_get/post`** を使用すること

### デプロイ方法
- GitHubのmainブランチをZIPダウンロード → WordPress管理画面からテーマアップロード
- `composer install` や `npm install` は本番で実行できない
- **`vendor/` や `node_modules/` をリポジトリに含めてはならない**（PHP 7.4非互換のコードが含まれるとサイト全体がダウンする）

### 過去のインシデント
- **PR #15 (2026-03-28)**: PHP SDK導入（`anthropic-ai/sdk` 等、PHP 8.2要求）により本番サイト全体がFatal Errorでダウン。PR #17 でRevert復旧。**functions.php でのFatal Errorはサイト全体を停止させる。**

## ディレクトリ構造

```
swell_child/
├── style.css              # 全カスタムCSS（デザインシステム + コードブロック + チャット + スキル）
├── functions.php          # 子テーマ関数（CSS/JS/Fonts + コードブロック + チャットAPI + スキルAPI）
├── front-page.php         # トップページテンプレート
├── page-chat.php          # チャットページテンプレート（管理者専用）
├── page-skill.php         # スキル一覧ページテンプレート
├── screenshot.png         # テーマスクリーンショット
├── CLAUDE.md              # このファイル
├── js/
│   ├── front-page.js      # トップページ用JS
│   ├── chat.js            # チャットページ用JS（SSE受信、Markdown、モデル切替）
│   ├── code-block.js      # コードブロックUI（コピー、行番号、ハイライト）
│   └── code-block-editor.js  # ブロックエディタ拡張（言語セレクタ）
├── img/
│   ├── visual-desert.jpg  # ビジュアルセクション左画像
│   ├── hero-bg.jpg        # ビジュアルセクション右画像
│   └── chat/              # チャットプロバイダーアイコン
│       ├── claude.png
│       ├── openai.png
│       ├── gemini.png
│       └── grok.png
└── design/                # デザイン参考資料（開発用）
```

## 実装済みセクション（front-page.php）

`get_header()` / `get_footer()` でSWELLのヘッダー・フッターを活用。

| 順序 | セクション | 説明 |
|------|-----------|------|
| 1 | **Hero** | 2カラム（タイトル左 + 説明右）、行ごとのフェードインアニメーション、背景画像下線装飾 |
| 2 | **Visual** | フルワイド非対称グリッド（1.2fr:0.8fr）、互い違い配置、ホバーズーム |
| 3 | **Featured** | スクロール駆動の展開アニメーション + 横スクロール3パネル、newsカテゴリ除外 |
| 4 | **Contents** | カテゴリ別タブ切り替え（SAP, ABAP, AI, 開発基礎, その他）、画像付きカードグリッド |
| 5 | **Popular** | PV数ベースの人気記事6件（`ct_post_views_byloos`）、最新記事フォールバック |
| 6 | **News** | newsカテゴリのニュースフィード、日付+タグ+タイトル形式 |

## デザインシステム

### カラーパレット（ウォームニュートラル）
- `--color-bg: #F8F7F5` / `--color-bg-alt: #FFFFFF` / `--color-bg-dark: #191919`
- `--color-text: #1D1D1F` / `--color-text-muted: #86868B` / `--color-text-light: #AFAFAF`
- `--color-border: #E8E8ED` / `--color-accent: #1D1D1F`

### フォント
- メイン: `Inter` + `Noto Sans JP`（functions.phpでwp_enqueue_style経由、preconnect付き）

### スペーシングスケール
`--space-2xs(4)` → `--space-xs(8)` → `--space-sm(16)` → `--space-ms(24)` → `--space-md(32)` → `--space-lg(64)` → `--space-xl(96)` → `--space-2xl(120)`

### 角丸
- `--radius-sm: 4px` — カード、画像コンテナ
- `--radius-pill: 100px` — タブ、タグ、CTAボタン

### イージング
- `--ease-out-expo` — メイン（フェードイン、スクロール）
- `--ease-out-quart` — サブ（画像ホバー、ボーダー変化）

### カードのホバー
- `border-color` 変更のみ（box-shadow、translateY は使わない）

### アクセシビリティ
- `prefers-reduced-motion` 対応済み
- タブUI: `role="tab"` / `role="tabpanel"` / `aria-selected` / キーボードナビ（矢印キー）
- `:focus-visible` スタイル全要素対応

## WordPress カテゴリ要件

以下のカテゴリ（スラッグ）がWordPress管理画面で必要：

| スラッグ | 用途 |
|---------|------|
| `sap` | Contentsタブ: SAP |
| `abap` | Contentsタブ: ABAP |
| `ai` | Contentsタブ: AI |
| `development` | Contentsタブ: 開発基礎 |
| `others` | Contentsタブ: その他 |
| `news` | Newsセクション用 |

## 技術的な注意事項

### フルワイド表現
SWELLの親テーマコンテナ制約を突破するため以下のテクニックを使用：
```css
margin-left: calc(-50vw + 50%);
width: 100vw;
```
`visual-wrapper` と `featured-banner-wrapper` で使用。

### Featuredセクションのスクロール制御
- `position: sticky` + `height: 300vh` でスクロールジャック
- CSS変数 `--expand`（0〜1）を `requestAnimationFrame` で更新
- 展開アニメーション → 横スクロールの2段階制御

### SWELLヘッダー・フッター上書き
- ヘッダー: `backdrop-filter: blur(20px)` すりガラス効果
- フッター: ダークカラー（`#191919`）

## 開発コマンド

```bash
cd ~/Local\ Sites/swell-local/app/public/wp-content/themes/swell_child
claude
```

## よくある問題

| 問題 | 対処 |
|------|------|
| front-page.phpが反映されない | WP管理画面でSWELL CHILD有効化を確認 |
| CSSが当たらない | functions.phpの`wp_enqueue_style`を確認 |
| 白紙ページ | `wp-content/debug.log`を確認 |
| 画像が表示されない | `get_stylesheet_directory_uri()`を使用 |
| ブロックエディタで「ブロックされました」 | SWELLライセンス認証を確認 |
| style.css編集でテーマ名が変わった | テーマヘッダーコメントを保持すること |
| **サイト全体がFatal Error** | functions.phpでのPHP互換性エラー。本番PHP 7.4を確認。`vendor/`が含まれていないか確認 |
| Composerパッケージを使いたい | **使用不可**。本番PHP 7.4では大半のSDKが動作しない。cURLを使用すること |

## Design Context

### Users
SAP professionals, ABAP developers, IT decision makers, and general tech readers visiting a Japanese SAP/AI technical blog. They come to find reliable technical articles, evaluate SAP solutions, and stay current on AI and development topics. The audience ranges from hands-on engineers to management—all expect authority and clarity.

### Brand Personality
**Modern, Minimal, Intelligent.** The site should feel like a peer among Anthropic and OpenAI—a credible, forward-thinking voice in the SAP and AI space. Not corporate-stuffy, but quietly confident.

### Aesthetic Direction
- **Visual tone**: Clean, restrained, typographically driven. Warm neutrals with generous whitespace. Content takes center stage.
- **Primary reference**: Anthropic.com — the warm palette, editorial spacing, and understated motion design.
- **Secondary references**: OpenAI, Claude.ai — surface texture quality, intelligent layouts.
- **Anti-references**: Cluttered WordPress blogs, heavy gradient hero banners, overly colorful SaaS landing pages, generic template aesthetics.
- **Theme**: Light mode primary. Dark sections used sparingly for contrast (Featured, Footer).
- **Shape language**: No border-radius, no box-shadows, no overlapping card elements. Sharp, flat, editorial.

### Design Principles
1. **Content over chrome** — Every visual decision should make the content easier to read, not compete with it. Decoration is removed, not added.
2. **Quiet confidence** — The design communicates expertise through restraint. Generous spacing, considered typography, and deliberate absence of ornament signal quality.
3. **Motion with purpose** — Animations exist to orient the reader (scroll-driven reveals, fade-ins) not to entertain. Respect `prefers-reduced-motion`.
4. **Typographic hierarchy** — Use weight, size, case, and spacing to create clear information layers. Small uppercase labels (14px) for section heads, large display type for hero statements.
5. **Warm minimalism** — Not cold or sterile. The warm neutral palette (#F8F7F5) and editorial serif accents (Crimson Pro) keep the minimalism inviting.
