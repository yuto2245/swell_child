# SWELL Child Theme - SAP-JP カスタムトップページ

WordPress SWELL テーマの子テーマで、Anthropic風の洗練されたUIを持つオリジナルトップページを実装。

## 📋 前提条件

- **SWELL親テーマ**: このリポジトリには含まれていません。別途購入・ダウンロードが必要です
  - 公式サイト: https://swell-theme.com/
  - ライセンス認証が必要
- **Local by WP Engine**: ローカルWordPress環境
  - ダウンロード: https://localwp.com/
- **PHP**: 8.1以上
- **WordPress**: 最新版推奨

## 🚀 セットアップ手順

### Step 1: Local (by WP Engine) のインストール

1. https://localwp.com/ からMac版をダウンロード・インストール
2. Localアプリを起動
3. **「+」ボタン** で新規サイト作成
   - サイト名: `swell-local` （任意）
   - PHP: **8.1以上**
   - WordPress: 最新版
4. サイト作成完了後、管理画面にアクセス

### Step 2: SWELL親テーマのインストール

1. SWELL公式サイトから親テーマ（`swell.zip`）をダウンロード
2. WordPress管理画面で以下を実行：
   - **外観 → テーマ → 新規追加 → テーマのアップロード**
   - `swell.zip` をアップロード
   - **有効化はしない**（子テーマを有効化するため）

### Step 3: この子テーマのインストール

#### 方法A: Gitクローン（推奨）

```bash
# テーマディレクトリに移動
cd ~/Local\ Sites/swell-local/app/public/wp-content/themes/

# このリポジトリをクローン
git clone <your-repo-url> swell_child
```

#### 方法B: 手動ダウンロード

1. GitHubからZIPをダウンロード
2. 解凍して `swell_child` にリネーム
3. `~/Local Sites/swell-local/app/public/wp-content/themes/` に配置

### Step 4: 子テーマの有効化

1. WordPress管理画面で **外観 → テーマ**
2. **SWELL CHILD** を見つけて **有効化**

### Step 5: 必要なカテゴリの作成

トップページのセクション表示に必要なカテゴリを作成：

WordPress管理画面で **投稿 → カテゴリー** から以下を作成：

| カテゴリ名 | スラッグ | 用途 |
|-----------|---------|------|
| SAP | `sap` | Contentsタブ |
| ABAP | `abap` | Contentsタブ |
| AI | `ai` | Contentsタブ |
| 開発基礎 | `development` | Contentsタブ |
| その他 | `others` | Contentsタブ |
| News | `news` | Newsセクション |

### Step 6: サイトの確認

1. Localアプリで **「Open Site」** をクリック
2. ブラウザで http://swell-local.local にアクセス
3. トップページが表示されることを確認

## 📁 ディレクトリ構造

```
swell_child/                 # このリポジトリ
├── README.md                # このファイル
├── CLAUDE.md                # Claude Code向け開発ドキュメント
├── style.css                # 全カスタムCSS（Anthropic風デザイン）
├── functions.php            # CSS/JS/Fonts読み込み
├── front-page.php           # トップページテンプレート
├── screenshot.png           # テーマスクリーンショット
├── js/
│   └── front-page.js        # トップページJS
├── img/
│   ├── visual-desert.jpg    # ビジュアルセクション画像
│   └── hero-bg.jpg          # ヒーロー背景画像
└── design/                  # デザイン参考資料
    ├── design_system.md
    └── *.png
```

### WordPress全体の構造（参考）

```
~/Local Sites/swell-local/app/public/
├── wp-content/
│   └── themes/
│       ├── swell/           # 親テーマ（別途インストール）
│       └── swell_child/     # この子テーマ
└── wp-config.php
```

## 🎨 実装内容

### トップページのセクション

1. **Hero** - 2カラムレイアウト、フェードインアニメーション
2. **Visual** - フルワイド非対称グリッド、画像ホバーエフェクト
3. **Featured** - スクロール駆動アニメーション、横スクロールパネル
4. **Contents** - カテゴリタブ切り替え（SAP, ABAP, AI, 開発基礎, その他）
5. **Popular** - PV数ベースの人気記事一覧
6. **News** - newsカテゴリのニュースフィード

### デザインシステム

- **カラー**: ウォームニュートラル（#F8F7F5ベース）
- **フォント**: Inter + Noto Sans JP
- **スタイル**: Anthropic/OpenAI風のモダン・ミニマルデザイン
- **レスポンシブ**: モバイル・タブレット・デスクトップ対応
- **アクセシビリティ**: `prefers-reduced-motion`、ARIA属性対応

詳細は [`CLAUDE.md`](./CLAUDE.md) を参照。

## 🛠️ 開発（Claude Code使用）

```bash
# 子テーマディレクトリに移動
cd ~/Local\ Sites/swell-local/app/public/wp-content/themes/swell_child

# Claude Code起動
claude
```

初回プロンプト：
```
CLAUDE.mdを読んで、プロジェクトの概要を把握してください。
```

## ⚠️ よくある問題

| 問題 | 対処 |
|------|------|
| front-page.phpが反映されない | SWELL CHILD（子テーマ）が有効化されているか確認 |
| CSSが当たらない | functions.phpの `wp_enqueue_style` を確認 |
| 白紙ページが表示される | `wp-content/debug.log` でPHPエラーを確認 |
| カテゴリセクションが空 | 必要なカテゴリ（sap, abap, ai等）が作成されているか確認 |
| 画像が表示されない | `img/` ディレクトリに画像ファイルがあるか確認 |

## 📝 ライセンス

このカスタマイズは自由に利用可能ですが、SWELL親テーマは別途ライセンスが必要です。

## 🔗 参考リンク

- SWELL公式: https://swell-theme.com/
- Local by WP Engine: https://localwp.com/
- WordPress Codex: https://wpdocs.osdn.jp/
