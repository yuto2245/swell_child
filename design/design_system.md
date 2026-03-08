# SAP-JP デザインシステム

## 参考デザイン
- `design_image1.png` - 互い違い画像配置の参考
- `design_sample1.png` - OpenAI風デザインサンプル
- `openai.png` - OpenAIサイト参考
- `claude.png` - Claude.aiサイト参考（表面の加工感が好み）
- `slider_design.png` - スライダーデザイン参考

## 旧デザイン
- `current_website_design.png` - リファクタリング前のSWELLデフォルト状態

## カラーパレット

| 変数名 | 値 | 用途 |
|--------|-----|------|
| `--color-bg` | `#F8F7F5` | ページ背景（ウォームグレー） |
| `--color-bg-alt` | `#FFFFFF` | セクション背景（白） |
| `--color-bg-dark` | `#191919` | Featured/フッター背景 |
| `--color-text` | `#1D1D1F` | 本文テキスト |
| `--color-text-muted` | `#86868B` | 補助テキスト |
| `--color-text-light` | `#AFAFAF` | 薄い補助テキスト |
| `--color-border` | `#E8E8ED` | ボーダー |
| `--color-accent` | `#1D1D1F` | アクセント |

## フォント
- **メイン**: Inter + Noto Sans JP（400, 500, 600）
- functions.phpでwp_enqueue_style経由、preconnect付き

## スペーシング

| 変数名 | 値 | 用途例 |
|--------|-----|--------|
| `--space-2xs` | 4px | 微小ギャップ、バッジpadding |
| `--space-xs` | 8px | タブgap、小マージン |
| `--space-sm` | 16px | カード内padding、標準gap |
| `--space-ms` | 24px | カード本文padding、グリッドgap |
| `--space-md` | 32px | セクション内マージン |
| `--space-lg` | 64px | コンテナpadding |
| `--space-xl` | 96px | セクション間padding |
| `--space-2xl` | 120px | 大セクション間padding |

## 角丸

| 変数名 | 値 | 用途 |
|--------|-----|------|
| `--radius-sm` | 4px | カード、画像コンテナ |
| `--radius-pill` | 100px | タブ、タグ、CTAボタン |

**方針**: box-shadow は使わない。ホバーは border-color 変更のみ。

## イージング

| 変数名 | 用途 |
|--------|------|
| `--ease-out-expo` | メインアニメーション（フェードイン、スクロール制御） |
| `--ease-out-quart` | サブアニメーション（画像ホバー、ボーダー変化） |

## ブレークポイント
- タブレット: `max-width: 1024px`
- モバイル: `max-width: 640px`
