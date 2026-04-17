# HyperFrames demo

探索用のブランチ `claude/explore-hyperframes-QhB8m` でのみ保持するお試し成果物。
本番（main）には含めないこと — `vendor/` や `node_modules/` を除外する運用と同じ理由。

## 内容

### 1. Hello World（最小サンプル）
- `hello.mp4` — 1920x1080 / 30fps / 5s / H.264
- `preview.png` — 2.5秒時点のスナップショット
- `index.html` — レンダリング元のHTML

### 2. Claude Code 機能発表風（ターミナル + タイピング + ブランド表示）
- `claude-code-intro.mp4` — 1920x1080 / 30fps / 10s / H.264
- `claude-code-intro-preview.png` — 8.5秒時点のスナップショット（ブランド表示）
- `claude-code-intro.html` — レンダリング元のHTML
- Anthropic風ウォームパレット（#141413 / #d97757）、macOSターミナルUI、GSAPタイピング

### 3. SAP-JP ブランドイントロ（ライトモード / 3シーン構成）
- `sapjp-brand-intro.mp4` — 1920x1080 / 30fps / 15s / H.264
- `sapjp-brand-intro-preview.png` — 13秒時点（最終ブランドカード）
- `sapjp-brand-intro.html` — レンダリング元のHTML
- ウォームニュートラル (#F8F7F5) + テラコッタアクセント (#C15F3C)
- Scene 1: SAP-JPロゴ + タグライン → Scene 2: トピックピル → Scene 3: カテゴリ → ブランドカード

## 再現手順

```sh
npx --yes hyperframes init sample-video
# index.html を編集
cd sample-video
npx hyperframes lint
npx hyperframes render --output renders/hello.mp4
```

## メモ

- GSAPはCDNから404/403になるサンドボックスがあるので、`npm pack gsap@3.14.2` でローカルにバンドル推奨
- `lint` が `transform: translate(-50%,-50%)` と GSAP の `y` 衝突を検出してくれる
- 生成物は `.gitignore` で除外することも検討（今回は再生確認のために同梱）
