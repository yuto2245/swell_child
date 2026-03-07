# SAP-JP デザインシステム

## 参考デザイン
- `www.anthropic.com_.png` - Anthropic公式サイト（メインリファレンス）
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
- **セリフ**: Crimson Pro（未使用だが読み込み済み）

## スペーシング
- `--space-xs`: 8px
- `--space-sm`: 16px
- `--space-md`: 32px
- `--space-lg`: 64px
- `--space-xl`: 96px
- `--space-2xl`: 120px

## ブレークポイント
- タブレット: `max-width: 1024px`
- モバイル: `max-width: 640px`
