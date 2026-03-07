# SWELL Child Theme - SAP-JP トップページ

## プロジェクト概要

WordPress SWELL テーマの子テーマで、**Anthropic風の洗練されたUI**を持つオリジナルトップページを実装済み。

## 環境情報

- **開発環境**: Local by WP Engine
- **サイトURL**: http://swell-local.local
- **管理画面**: http://swell-local.local/wp-admin
- **親テーマ**: SWELL（ライセンス認証済み）

## ディレクトリ構造

```
swell_child/
├── style.css            # 全カスタムCSS（Anthropic風デザインシステム）
├── functions.php        # 子テーマ関数（style.css読み込み）
├── front-page.php       # トップページテンプレート
├── screenshot.png       # テーマスクリーンショット
├── CLAUDE.md            # このファイル
├── img/
│   ├── visual-desert.jpg  # ビジュアルセクション左画像
│   └── hero-bg.jpg        # ビジュアルセクション右画像
├── design/              # デザイン参考資料（開発用）
│   ├── design_system.md
│   ├── www.anthropic.com_.png
│   ├── current_website_design.png
│   ├── design_image1.png
│   ├── design_sample1.png
│   ├── openai.png
│   ├── claude.png
│   └── slider_design.png
└── swell_child_backup/  # 元のデフォルトファイルバックアップ
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
- メイン: `Inter` + `Noto Sans JP`
- セリフ: `Crimson Pro`

### イージング
- `--ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1)`
- `--ease-out-quart: cubic-bezier(0.25, 1, 0.5, 1)`

### アクセシビリティ
- `prefers-reduced-motion` 対応済み
- タブUI: `role="tab"` / `role="tabpanel"` / `aria-selected`

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
