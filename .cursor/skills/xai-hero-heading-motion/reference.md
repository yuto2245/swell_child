# x.ai ヒーロー見出し — パラメータ・ファイル対応表

## タイミング定数（SAPJP 実装値）

| 定数 | 値 | 用途 |
|------|-----|------|
| 単語スタッガー | `40ms × --hero-word-index` | CSS `transition-delay` |
| 単語 transition | `0.55s` expo | blur + opacity + transform |
| 文字スタッガー | `28ms × --hero-char-index` | ローテーション进入 |
| 文字退出スタッガー | `18ms × index` | `is-exiting` |
| 退出完了待ち | `exitCount * 18 + 160` ms | 次語挿入前 |
| ローテーション間隔 | `3200` ms | `setInterval` |
| 静的語完了→ローテ開始 | `40 * staticWords.length + 320` ms | 4 語なら 480ms |
| clip width transition | `0.45s` expo | `.hero-rotate__clip` |
| 下線シマー | `2.8s` linear infinite | `heroRotateUnderlineShimmer` |

## easing

```css
--ease-out-expo: cubic-bezier(0.16, 1, 0.3, 1);
```

## DOM 構造（最小例）

```html
<h1 class="hero-title" id="hero-title">
  <span class="hero-line">
    <span class="hero-word" style="--hero-word-index: 0">SAP</span>
    <span class="hero-word" style="--hero-word-index: 1">Knowledge</span>
  </span>
  <span class="hero-line">
    <span class="hero-word" style="--hero-word-index: 2">Built</span>
    <span class="hero-word" style="--hero-word-index: 3">for</span>
    <span class="hero-word hero-word--rotate" style="--hero-word-index: 4">
      <span class="hero-rotate" data-rotate-words="AI,ABAP,Context,API">
        <span class="hero-rotate__sizer" aria-hidden="true">Context</span>
        <span class="hero-rotate__clip">
          <span class="hero-rotate__chars" aria-live="polite">AI</span>
        </span>
        <span class="hero-rotate__underline" aria-hidden="true"></span>
      </span>
    </span>
  </span>
</h1>
```

## JS 実行順（必須）

```
DOMContentLoaded
  → startHeroWhenReady()     // 最優先
  → try { initStarCanvas() }
  → IntersectionObserver / tabs 等
```

## reduce motion 時の挙動

- `revealStaticWords()` + `setRotateWord(..., animateIn: false)`
- `setInterval` は開始しない
- CSS で blur/transform/animation を `!important` 無効化

## コミット粒度の推奨

1. `feat:` マークアップ + CSS + JS + フォント
2. `fix:` Chrome 安定化（canvas 分離、fonts.ready、filter:none）— 別コミット可
