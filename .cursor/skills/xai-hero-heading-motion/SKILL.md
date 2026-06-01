---
name: xai-hero-heading-motion
description: >-
  Reproduces x.ai-style hero H1 motion (word blur stagger, rotating tail word,
  gradient underline shimmer) in WordPress child themes with Vanilla JS. Use when
  the user asks to match x.ai hero typography/animation, implement hero-title
  blur reveal, rotate words like AI/ABAP, or debug hero animation in Chrome.
---

# x.ai 風ヒーロー見出しモーション

## 適用範囲

- WordPress **子テーマのみ**（親 `swell/` は READ ONLY）
- **Vanilla JS**（jQuery / Framer Motion 不可）
- ライトテーマ維持が前提（全面ダーク化はスコープ外）
- 文言はブランド用に差し替え、**動きの型**だけ x.ai から借りる

## x.ai モーションの分解（再現対象）

| 層 | 挙動 |
|----|------|
| タイポ | Display sans, weight 500, line-height ~1.05, letter-spacing ~-0.025em |
| 登場 | 単語ごと blur(12px) + translateY → `filter: none`、40ms スタッガー |
| 末尾 | 語リストを約 3.2s ごとに **1 文字ずつ** 入退場 |
| 幅 | 非表示 `.hero-rotate__sizer` で `clip` の width を transition |
| 下線 | 虹色グラデ 3px + `background-position` シマー |
| a11y | `prefers-reduced-motion: reduce` で即時表示・ローテ停止 |

## 実装チェックリスト

### 1. PHP（`front-page.php`）

- [ ] `#hero-title` on `<h1 class="hero-title">`
- [ ] 固定語: `.hero-word` + inline `--hero-word-index: 0..n`
- [ ] 2 行: `.hero-line` で `SAP` / `Knowledge` と `Built` / `for` + rotate slot
- [ ] `.hero-rotate` + `data-rotate-words="AI,ABAP,Context,API"`（カンマ区切り）
- [ ] `.hero-rotate__sizer` = 最長語（例: `Context`）
- [ ] `.hero-rotate__chars` 初期テキスト + `aria-live="polite"`

### 2. CSS（`style.css`）

- [ ] `--font-display`: Plus Jakarta Sans（Google Fonts）
- [ ] `.hero-title`: `perspective: 1200px`
- [ ] **PE**: 通常 `.hero-word` は表示可 → `.hero-title--js .hero-word` のみ `opacity:0` + blur
- [ ] `.is-visible`: `filter: none`（`blur(0)` は Chrome で残りやすい）
- [ ] `.hero-rotate__clip`: `clip-path: inset(-4px 0 -4px 0)` + `transition: width 0.45s`
- [ ] `@keyframes heroRotateUnderlineShimmer` + `.hero-rotate.is-active .hero-rotate__underline`
- [ ] `@media (prefers-reduced-motion: reduce)` で word/char/underline を一括無効化

### 3. JS（`js/front-page.js`）

- [ ] **`startHeroWhenReady()` を canvas より先**に実行
- [ ] `document.fonts.ready.then(initHeroTitleAnimation)`
- [ ] 流れ: `hero-title--js` → `revealStaticWords()` (rAF) → delay `40 * staticWords.length + 320` → `initRotate()`
- [ ] `buildCharSpans` / `exitRotateChars` / `measureRotateWidth`（scrollWidth、0 なら offsetWidth）
- [ ] `setInterval` 3200ms、`cycling` フラグで重複防止
- [ ] 星空は `initStarCanvas()` + `try/catch`（ヒーローを殺さない）

### 4. PHP enqueue（`functions.php`）

- [ ] Plus Jakarta Sans を `google-fonts` に追加
- [ ] `front-page.js` は `filemtime` を ver に（トップのみ）

## 検証

### Console

```js
(function () {
  var t = document.getElementById('hero-title');
  return {
    heroTitleJs: t?.classList.contains('hero-title--js'),
    visibleWords: t?.querySelectorAll('.hero-word.is-visible').length,
    rotateText: document.querySelector('.hero-rotate__chars')?.textContent,
    reduceMotion: matchMedia('(prefers-reduced-motion: reduce)').matches
  };
})();
```

期待: `heroTitleJs: true`, `visibleWords: 5`, `reduceMotion: false`（アニメあり）

### 「動かない」切り分け

| 症状 | 原因 |
|------|------|
| SyntaxError 512 行付近 | 壊れた JS のブラウザキャッシュ。Network で Response 行数・`initStarCanvas` の有無を確認 |
| 文字は見えるがアニメなし、`reduceMotion: true` | macOS **アニメーションを減らす** ON（仕様） |
| `heroTitleJs: false` | JS 未実行 or `#hero-title` なし |

### ハードリロード

DevTools → Network → Disable cache → Cmd+Shift+R

## 参照実装

このリポジトリの正本:

- `front-page.php`（見出しマークアップ）
- `style.css`（`.hero-title` / `.hero-rotate` ブロック）
- `js/front-page.js`（`initHeroTitleAnimation` / `startHeroWhenReady`）

詳細パラメータ・タイミング表は [reference.md](reference.md)。

## やらないこと

- x.ai のローテーション文言のコピー（ブランド不一致）
- Universal Sans / Framer Motion の導入
- 親テーマ・プラグイン・`wp-config.php` の変更
