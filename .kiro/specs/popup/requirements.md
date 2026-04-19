# Requirements: sapjp.net 新機能ポップアップ

## 背景

sapjp.net（SAP技術ブログ / WordPress + SWELL）に、新機能告知・アップデート通知用の
モーダルポップアップを導入する。Claude Design で作成したプロトタイプ（popup.html）を
SWELL 子テーマに移植する。

## ソース

- **Claude Design 出力**: `popup.html`（5バリアント：Classic / Split / Cinematic / Carded / Playful）
- **採用バリアント**: Carded（changelog形式、技術ブログとの親和性が最も高い）

## ユーザーストーリー

### US-001: 新機能の告知を見る
**AS** sapjp.net の訪問者
**I WANT** サイトに新機能やアップデートがあったときにポップアップで通知を受ける
**SO THAT** 見逃さずに最新のコンテンツや機能変更を把握できる

### US-002: ポップアップを閉じる
**AS** sapjp.net の訪問者
**I WANT** ×ボタン、背景クリック、Escキーでポップアップを閉じられる
**SO THAT** 記事閲覧を妨げられない

### US-003: 同じ通知を繰り返し見ない
**AS** sapjp.net のリピーター
**I WANT** 一度閉じた通知が再訪問時に再表示されない
**SO THAT** 毎回同じ告知に煩わされない

### US-004: 管理者がポップアップを管理する
**AS** sapjp.net の管理者（Yuto）
**I WANT** ポップアップの表示/非表示・内容をWordPress管理画面から制御できる
**SO THAT** コードを触らずに告知を更新できる

## 受け入れ基準（EARS記法）

### AC-001: 表示トリガー
WHEN 訪問者がsapjp.netの任意のページを読み込み
AND アクティブなポップアップが存在し
AND そのポップアップIDのcookieが存在しない
THEN システムは 1.5秒の遅延後にポップアップを表示する SHALL

### AC-002: 閉じる操作
WHEN ポップアップが表示されている状態で
AND 訪問者が×ボタンをクリック OR 背景をクリック OR Escキーを押す
THEN システムはポップアップを閉じ、該当IDのcookieを30日間保存する SHALL

### AC-003: Cookie制御
WHEN 訪問者が再度sapjp.netを訪問し
AND 該当ポップアップIDのcookieが有効期限内である
THEN システムはそのポップアップを表示しない SHALL

### AC-004: アクセシビリティ
WHEN ポップアップが表示されている
THEN 背景コンテンツに `aria-hidden="true"` が付与される SHALL
AND モーダル内にフォーカスがトラップされる SHALL
AND 閉じボタンに `aria-label` が付与される SHALL

### AC-005: パフォーマンス
WHEN ポップアップのCSS/JSが読み込まれた
THEN LCPに影響を与えない（非クリティカルCSS + defer JS） SHALL
AND 追加リクエストは CSS 1件 + JS 1件 以下とする SHALL

### AC-006: レスポンシブ
WHEN 画面幅が 768px 未満の
THEN ポップアップは幅 calc(100vw - 32px) で表示される SHALL
AND feature-card は縦スタックになる SHALL

### AC-007: 管理画面
WHEN 管理者がカスタマイザーの「ポップアップ設定」を開く
THEN 以下を編集できる SHALL:
  - 有効/無効トグル
  - ポップアップID（cookieキー）
  - バージョンラベル（例: v2.5.0 · 2026年4月）
  - タイトル
  - 説明文
  - 機能カード（最大5件：アイコン選択、タイトル、説明、NEWバッジ有無）
  - CTAボタンテキスト・リンク

## 非機能要件

- SWELL親テーマを一切改変しない
- jQuery に依存しない（Vanilla JS のみ）
- Core Web Vitals を悪化させない（CLS 0 / LCP 無影響 / INP < 200ms）
- WCAG 2.1 AA 準拠（コントラスト比 4.5:1 以上）
- SWELL のダークモード設定がONの場合でも崩れない

## スコープ外

- 複数バリアントの切り替え機能（Cardedのみ実装）
- Tweaksパネル（Claude Design専用、本番不要）
- モックアプリ背景（デモ専用）
- A/Bテスト機能
- Analytics連携（将来対応）
