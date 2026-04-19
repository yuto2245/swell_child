# Design: sapjp.net 新機能ポップアップ（Carded バリアント）

## 1. Overview

Claude Design で作成した popup.html の **Carded バリアント**（changelog形式）を、
SWELL 子テーマに移植する。popup.html からデザイントークン・コンポーネント構造・
インタラクションを抽出し、WordPress の `wp_footer` フックで注入する方式で実装する。

### ソースファイル → 実装ファイルの対応

| Claude Design 出力（popup.html） | SWELL 子テーマ実装 |
|---|---|
| `:root` CSS変数 → | `assets/popup.css` の `:root` セクション |
| `.v-carded` 系セレクタ → | `.sapjp-popup` 系セレクタにリネーム |
| `.backdrop` → | `.sapjp-popup-backdrop` |
| Tweaks Panel / Edit Mode Protocol → | **削除**（デモ専用） |
| Mock App (`.app` `.sidebar` `.main`) → | **削除**（デモ専用） |
| Variant 1,2,3,5 (classic/split/cinematic/playful) → | **削除**（Cardedのみ採用） |
| `openPopup()` / `closePopup()` → | cookie制御付きに拡張 |

## 2. Architecture

```
wp_footer フック（priority 99）
  └─ sapjp_render_popup()
       ├─ カスタマイザー設定を get_theme_mod() で取得
       ├─ enabled === false → return（何も出力しない）
       ├─ HTML をインラインで出力（<div class="sapjp-popup-backdrop">...）
       └─ wp_enqueue_script('sapjp-popup', defer)

assets/popup.css   ← wp_enqueue_style (priority 50, 非クリティカル)
assets/popup.js    ← wp_enqueue_script (defer, no jQuery dependency)
```

**なぜ wp_footer か**: モーダルはDOM末尾に置くのがz-index管理の定石。
SWELL は `#wrapper` > `#header` / `#content` / `#footer` の構造なので、
`#wrapper` と同階層（body直下）に挿入することで z-index の衝突を避ける。

**なぜ Customizer か**: SWELL はカスタマイザーを主要な設定UIとして使っており、
管理者（Yuto）が一貫した操作体験でポップアップ内容を変更できる。
ACF や カスタム投稿タイプは SWELL のアップデートと干渉するリスクがある。

## 3. Design Tokens（popup.html から抽出・SWELL 適合）

### 3.1 Colors

popup.html の `:root` から抽出。SWELL の `--color_*` 変数との棲み分けを明確にする。

```css
/* ===== popup.html 原文（Claude Design 出力）===== */
/*
  --bg: #F7F5F1;
  --surface: #FFFFFF;
  --surface-2: #FAF8F4;
  --ink: #1A1817;
  --ink-2: #2B2826;
  --muted: #6E6863;
  --muted-2: #A39C95;
  --line: #EAE5DD;
  --line-2: #D9D3CA;
  --accent: #D97757;
  --accent-ink: #7A3E24;
  --accent-soft: #F4E4D8;
*/

/* ===== SWELL 子テーマ用（名前空間付き）===== */
:root {
  /* ポップアップ専用トークン（SWELL変数と衝突しない） */
  --popup-bg:          #F7F5F1;
  --popup-surface:     #FFFFFF;
  --popup-surface-2:   #FAF8F4;
  --popup-ink:         #1A1817;
  --popup-ink-2:       #2B2826;
  --popup-muted:       #6E6863;
  --popup-line:        #EAE5DD;
  --popup-line-2:      #D9D3CA;
  --popup-accent:      #D97757;
  --popup-accent-ink:  #7A3E24;
  --popup-accent-soft: #F4E4D8;

  /* Shadow（popup.html の shadow-xl を採用） */
  --popup-shadow: 0 40px 100px -20px rgba(26,24,23,0.30),
                  0 20px 40px -12px rgba(26,24,23,0.12);

  /* Radius */
  --popup-radius:    20px;
  --popup-radius-sm: 12px;
  --popup-radius-xs: 8px;

  /* Animation */
  --popup-ease: cubic-bezier(0.16, 1, 0.3, 1);
  --popup-duration: 420ms;
}
```

**コントラスト比検証**:
| 組み合わせ | 比率 | WCAG AA |
|---|---|---|
| `--popup-ink` (#1A1817) on `--popup-surface` (#FFFFFF) | 17.1:1 | ✅ |
| `--popup-muted` (#6E6863) on `--popup-surface` (#FFFFFF) | 4.8:1 | ✅ |
| `--popup-accent` (#D97757) on white | 3.1:1 | ❌ バッジ用のみ（テキストリンクには使わない） |
| `--popup-accent-ink` (#7A3E24) on `--popup-accent-soft` (#F4E4D8) | 5.2:1 | ✅ |

### 3.2 Typography

popup.html のフォント設定をそのまま移植。SWELL が読み込む Noto Sans JP と共用。

```css
/* popup.html 原文 */
--font-jp:    "Noto Sans JP", system-ui, -apple-system, sans-serif;
--font-serif: "Instrument Serif", Georgia, serif;
--font-mono:  "JetBrains Mono", ui-monospace, monospace;
```

**SWELL 適合方針**:
- `--font-jp`: SWELLの `body { font-family }` と一致させる → 追加読み込み不要
- `--font-serif`: Instrument Serif は popup.html のデモアプリ用。
  Carded バリアントでは `.head-icon` のイニシャル表示のみに使用。
  **sapjp.net では JetBrains Mono に置き換え**（既にブログで使用中）
- `--font-mono`: JetBrains Mono はブログのコードブロックで既に読み込み済み → 追加不要

| 要素 | popup.html 原文 | sapjp.net 実装 |
|---|---|---|
| eyebrow（バージョン） | `font-mono 11px 0.04em uppercase` | 同じ |
| h2（タイトル） | `18px 600 -0.01em` | 同じ |
| intro（説明文） | `13.5px/1.7 color: muted` | 同じ |
| feature-card h4 | `13.5px 600` | 同じ |
| feature-card p | `12.5px/1.55 color: muted` | 同じ |
| footer version | `font-mono 12px color: muted` | 同じ |
| btn | `14px 500` | 同じ |
| NEW バッジ | `9.5px 600 0.04em uppercase` | 同じ |

### 3.3 Spacing

popup.html の Carded バリアントから抽出した padding/gap/margin 値:

```
head:          padding 24px 28px 20px
body:          padding 20px 28px 8px
foot:          padding 16px 28px 24px
feature-cards: gap 8px, margin-bottom 20px
feature-card:  padding 14px 16px, gap 14px
fc-icon:       32×32px
head-icon:     44×44px
btn:           padding 10px 18px
```

## 4. Component Inventory

### 4.1 `sapjp-popup-backdrop`（popup.html: `.backdrop`）

```
構造: 固定オーバーレイ（position: fixed, inset: 0）
背景: rgba(26, 24, 23, 0.28) + backdrop-filter: blur(8px)
z-index: 9999（SWELLの固定ヘッダー z-index: 100 より上）
状態: .open → opacity: 1, pointer-events: auto
閉じる: backdrop自体のクリックで closePopup()
```

### 4.2 `sapjp-popup`（popup.html: `.modal.v-carded`）

```
幅: 520px（モバイル: calc(100vw - 32px)）
背景: var(--popup-surface)
角丸: var(--popup-radius)
影: var(--popup-shadow)
枠線: 1px solid var(--popup-line)
アニメーション: translateY(16px) scale(0.98) → translateY(0) scale(1)
              duration: 420ms, easing: cubic-bezier(0.16, 1, 0.3, 1)
```

### 4.3 `sapjp-popup__head`（popup.html: `.v-carded .head`）

```
レイアウト: flex, align-items: flex-start, gap: 16px
下線: border-bottom 1px solid var(--popup-line)
子要素:
  - __head-icon: 44×44px, radius 12px, bg accent-soft, color accent-ink
    → sapjp.net ではサイトイニシャル "S" をJetBrains Monoで表示
  - __eyebrow: mono 11px, color muted, letter-spacing 0.04em
  - __title: 18px 600, color ink
```

### 4.4 `sapjp-popup__body`（popup.html: `.v-carded .body`）

```
子要素:
  - __intro: 13.5px/1.7, color muted, margin-bottom 18px
  - __cards: flex column, gap 8px
```

### 4.5 `sapjp-popup__card`（popup.html: `.feature-card`）

```
枠線: 1px solid var(--popup-line), radius 12px
padding: 14px 16px
hover: border-color line-2, bg surface-2
レイアウト: flex, gap 14px, align-items flex-start
子要素:
  - __card-icon: 32×32px, radius 8px, bg surface-2, border 1px line
    → SVGアイコン（stroke 1.5px, currentColor）
  - __card-title: 13.5px 600 + オプションで .sapjp-popup__new-tag
  - __card-desc: 12.5px/1.55, color muted
```

### 4.6 `sapjp-popup__new-tag`（popup.html: `.new-tag`）

```
display: inline-block
padding: 1px 6px, radius 4px
bg: var(--popup-accent), color: white
font: 9.5px 600, letter-spacing 0.04em, uppercase
```

### 4.7 `sapjp-popup__foot`（popup.html: `.v-carded .foot`）

```
bg: var(--popup-surface-2)
border-top: 1px solid var(--popup-line)
レイアウト: flex, align-items center, gap 8px
子要素:
  - __version-link: mono 12px, color muted（→ 変更ログ全文へのリンク）
  - spacer (flex: 1)
  - btn-ghost: border line-2, bg transparent, hover surface-2
  - btn-primary: bg ink, color bg, hover ink-2
```

### 4.8 `sapjp-popup__close`（popup.html: `.modal-close`）

```
position: absolute, top 14px, right 14px
32×32px, radius 8px
bg: rgba(255,255,255,0.7), backdrop-filter: blur(8px)
border: 1px solid var(--popup-line)
SVG ×アイコン: 16×16, stroke 1.5
hover: color ink, bg surface
aria-label: "閉じる"
```

## 5. Data Models

### 5.1 Customizer Settings（`sapjp_popup_*`）

| Setting ID | Type | Default | 説明 |
|---|---|---|---|
| `sapjp_popup_enabled` | boolean | false | ポップアップの有効/無効 |
| `sapjp_popup_id` | string | "popup-001" | Cookie キー（変更すると全員に再表示） |
| `sapjp_popup_cookie_days` | number | 30 | Cookie 有効期限（日） |
| `sapjp_popup_delay_ms` | number | 1500 | 表示遅延（ミリ秒） |
| `sapjp_popup_eyebrow` | string | "v1.0 · 2026年4月" | バージョンラベル |
| `sapjp_popup_title` | string | "今週のアップデート" | タイトル |
| `sapjp_popup_intro` | textarea | "" | 説明文 |
| `sapjp_popup_cards` | JSON(text) | "[]" | 機能カード配列 |
| `sapjp_popup_cta_text` | string | "了解" | プライマリボタンテキスト |
| `sapjp_popup_cta_url` | string | "" | プライマリボタンリンク（空なら閉じるだけ） |
| `sapjp_popup_sub_text` | string | "詳細を見る" | ゴーストボタンテキスト |
| `sapjp_popup_sub_url` | string | "" | ゴーストボタンリンク |
| `sapjp_popup_changelog_url` | string | "" | 変更ログリンク |

### 5.2 Cards JSON 形式

```json
[
  {
    "icon": "calendar",
    "title": "新機能名",
    "description": "説明テキスト",
    "is_new": true
  }
]
```

アイコン選択肢（SVG インライン）: calendar / mic / chart / code / search / star / bell / book

## 6. Interactions

### 6.1 開く

```
trigger: DOMContentLoaded + delay(sapjp_popup_delay_ms)
         → cookie "sapjp_popup_{id}" が無い場合のみ
animation:
  backdrop: opacity 0→1 (360ms, ease)
  modal: translateY(16px) scale(0.98) → translateY(0) scale(1)
         (420ms, cubic-bezier(0.16, 1, 0.3, 1))
side-effects:
  - body に overflow: hidden を付与（背景スクロール防止）
  - #wrapper に aria-hidden="true" を付与
  - モーダル内の最初のフォーカス可能要素にフォーカス移動
```

### 6.2 閉じる

```
triggers: ×ボタン click / backdrop click / Escape keydown
animation: 逆再生（opacity 1→0, translateY(0)→16px）
side-effects:
  - cookie "sapjp_popup_{id}" を {cookie_days} 日間セット
    path=/; SameSite=Lax; Secure
  - body から overflow: hidden を除去
  - #wrapper から aria-hidden を除去
  - フォーカスをトリガー前の要素に返す
```

### 6.3 feature-card hover

```
border-color: var(--popup-line) → var(--popup-line-2) (120ms)
background: transparent → var(--popup-surface-2) (120ms)
```

### 6.4 ボタン hover/active

```
btn-primary hover: bg ink → ink-2
btn-ghost hover: bg transparent → surface-2
btn:active: transform scale(0.98)
```

## 7. Responsive Strategy

| Breakpoint | 変更点 |
|---|---|
| ≥ 769px | 幅 520px 固定、中央配置 |
| ≤ 768px | 幅 calc(100vw - 32px)、padding各所を4px縮小 |
| ≤ 480px | feature-card 内の flex → column（アイコンを上に） |

## 8. Error Handling

- フォント読み込み失敗: system-ui にフォールバック（font stack 既定済み）
- Customizer JSON パースエラー: cards が空配列として扱われ、カード非表示
- Cookie 設定不可（プライベートブラウズ）: sessionStorage にフォールバック、
  それも不可なら in-memory フラグ（タブ内でのみ有効）
- JavaScript 無効: ポップアップ自体が表示されない（CSS の初期状態が非表示のため、
  コンテンツアクセスを阻害しない = progressive enhancement）

## 9. Testing Strategy

- **Visual**: Local で desktop / mobile スクリーンショット、popup.html と並べて比較
- **A11y**: axe DevTools でモーダル開閉時のフォーカス管理を確認
- **Interaction**: ×/backdrop/Escape の3経路で閉じることを手動確認
- **Cookie**: DevTools Application > Cookies で `sapjp_popup_*` の生成・有効期限を確認
- **Performance**: Lighthouse で LCP / CLS に影響がないことを確認
- **Cross-browser**: Safari / Chrome / Firefox / iOS Safari / Android Chrome

## 10. File Structure Plan

```
swell_child/
├── functions.php          ← sapjp_popup_customizer() と sapjp_render_popup() を追加
├── assets/
│   ├── popup.css          ← Carded バリアントの CSS（名前空間付き）
│   └── popup.js           ← Cookie制御 + 開閉ロジック（Vanilla JS, defer）
└── inc/
    └── popup-settings.php ← Customizer セクション・設定の登録
```

## 11. Open Questions

- [ ] ダークモード対応: SWELL のダークモードが ON のとき、ポップアップも暗くするか？
      → 初期実装では prefers-color-scheme: dark で ink/surface を反転させるだけにする
- [ ] Google Analytics イベント: 将来的に閉じる/CTAクリックを GA4 に送るか？
      → スコープ外（将来タスク）
- [ ] 複数ポップアップのキュー: 同時に複数の告知がある場合は？
      → 初期実装では1件のみ。popup_id を変えれば別の告知として独立管理
