# HyperFrames demo

探索用のブランチ `claude/explore-hyperframes-QhB8m` でのみ保持するお試し成果物。
本番（main）には含めないこと — `vendor/` や `node_modules/` を除外する運用と同じ理由。

## 内容

- `hello.mp4` — HyperFrames で書き出したサンプル動画（1920x1080 / 30fps / 5s / H.264）
- `preview.png` — 2.5秒時点のスナップショット
- `index.html` — レンダリング元のHTML（GSAPで"Hello, HyperFrames"をフェードイン）

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
