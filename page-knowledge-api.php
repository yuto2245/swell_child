<?php
/**
 * Template Name: Knowledge API
 * Template Post Type: page
 *
 * Documentation page for the SAPJP Knowledge API.
 *
 * @package swell_child
 */

get_header();

$api_base = home_url( '/wp-json/sapjp/v1' );
?>

<main class="sapjp-docs">
	<aside class="sapjp-docs__sidebar" aria-label="Knowledge API navigation">
		<a class="sapjp-docs__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<span class="sapjp-docs__mark" aria-hidden="true">S</span>
			<span>SAPJP Docs</span>
		</a>
		<nav class="sapjp-docs__nav">
			<a href="#overview">Overview</a>
			<a href="#endpoints">Endpoints</a>
			<a href="#search">Search</a>
			<a href="#article">Article</a>
			<a href="#context">Context</a>
			<a href="#safety">Safety</a>
			<a href="#next">Next</a>
		</nav>
	</aside>

	<div class="sapjp-docs__content">
		<section id="overview" class="sapjp-docs__hero">
			<p class="sapjp-docs__eyebrow">SAPJP Knowledge API</p>
			<h1>WordPressの記事を、AIが読める知識ソースにする。</h1>
			<p class="sapjp-docs__lead">
				このAPIは sapjp.net の公開記事を検索し、MCPサーバーや外部AIアプリに渡しやすいJSONとして返します。
				チャット本体をWordPressで運用せず、記事資産だけを安全に参照するための入口です。
			</p>
			<div class="sapjp-docs__actions">
				<a class="sapjp-docs__button" href="#endpoints">View endpoints</a>
				<a class="sapjp-docs__button sapjp-docs__button--ghost" href="<?php echo esc_url( $api_base . '/search?query=ABAP' ); ?>">Try search</a>
			</div>
		</section>

		<section class="sapjp-docs__section sapjp-docs__grid" aria-label="API summary">
			<div class="sapjp-docs__card">
				<p class="sapjp-docs__card-label">Use case</p>
				<h2>記事検索</h2>
				<p>ABAP、SAP、S/4HANAなどのキーワードで関連記事を取得します。</p>
			</div>
			<div class="sapjp-docs__card">
				<p class="sapjp-docs__card-label">Use case</p>
				<h2>本文取得</h2>
				<p>記事IDからタイトル、URL、本文、カテゴリ、タグを取得します。</p>
			</div>
			<div class="sapjp-docs__card">
				<p class="sapjp-docs__card-label">Use case</p>
				<h2>AI文脈生成</h2>
				<p>AIに渡しやすい短いコンテキストとして複数記事をまとめます。</p>
			</div>
		</section>

		<section id="endpoints" class="sapjp-docs__section">
			<div class="sapjp-docs__section-head">
				<p class="sapjp-docs__eyebrow">Endpoints</p>
				<h2>API一覧</h2>
			</div>
			<div class="sapjp-docs__table-wrap">
				<table class="sapjp-docs__table">
					<thead>
						<tr>
							<th>Method</th>
							<th>Path</th>
							<th>Purpose</th>
						</tr>
					</thead>
					<tbody>
						<tr>
							<td><code>GET</code></td>
							<td><code>/wp-json/sapjp/v1/search</code></td>
							<td>公開記事をキーワード検索する</td>
						</tr>
						<tr>
							<td><code>GET</code></td>
							<td><code>/wp-json/sapjp/v1/articles/{id}</code></td>
							<td>1記事の本文とメタデータを取得する</td>
						</tr>
						<tr>
							<td><code>GET</code></td>
							<td><code>/wp-json/sapjp/v1/context</code></td>
							<td>AI向けに短く整形された文脈を取得する</td>
						</tr>
					</tbody>
				</table>
			</div>
		</section>

		<section id="search" class="sapjp-docs__section sapjp-docs__split">
			<div>
				<p class="sapjp-docs__eyebrow">Search</p>
				<h2>記事を検索する</h2>
				<p>
					<code>query</code> に検索語を指定すると、関連する公開記事を返します。
					<code>category</code> でカテゴリスラッグを指定し、<code>limit</code> で最大件数を制限できます。
				</p>
				<ul class="sapjp-docs__list">
					<li><code>query</code>: 検索キーワード</li>
					<li><code>category</code>: カテゴリスラッグ。任意</li>
					<li><code>limit</code>: 最大20件。任意</li>
				</ul>
			</div>
			<div class="sapjp-docs__code">
				<div class="sapjp-docs__code-title">Request</div>
				<pre><code>curl "<?php echo esc_html( $api_base ); ?>/search?query=ABAP&amp;limit=5"</code></pre>
			</div>
		</section>

		<section id="article" class="sapjp-docs__section sapjp-docs__split">
			<div>
				<p class="sapjp-docs__eyebrow">Article</p>
				<h2>1記事を取得する</h2>
				<p>
					記事IDを指定して、AIが参照できるプレーンテキスト本文を取得します。
					下書き、非公開、パスワード保護記事は返しません。
				</p>
			</div>
			<div class="sapjp-docs__code">
				<div class="sapjp-docs__code-title">Request</div>
				<pre><code>curl "<?php echo esc_html( $api_base ); ?>/articles/123"</code></pre>
			</div>
		</section>

		<section id="context" class="sapjp-docs__section sapjp-docs__split">
			<div>
				<p class="sapjp-docs__eyebrow">Context</p>
				<h2>AI向けの文脈を取得する</h2>
				<p>
					MCPサーバーやAIアプリから使う場合は、まずこのエンドポイントを使うのが扱いやすいです。
					検索結果を短く整形し、`sources` 配列として返します。
				</p>
				<ul class="sapjp-docs__list">
					<li>空の <code>query</code> はエラーになります。</li>
					<li>本文はAIに渡しやすい長さに切り詰められます。</li>
					<li>回答時の出典表示に使えるURLを含みます。</li>
				</ul>
			</div>
			<div class="sapjp-docs__code">
				<div class="sapjp-docs__code-title">Response</div>
				<pre><code>{
  "query": "ABAP SELECT",
  "count": 1,
  "sources": [
    {
      "id": 123,
      "title": "ABAP SELECTの基本",
      "url": "https://sapjp.net/example/",
      "content": "SELECT文の基本...",
      "categories": ["ABAP"],
      "tags": ["SELECT"]
    }
  ]
}</code></pre>
			</div>
		</section>

		<section id="safety" class="sapjp-docs__section">
			<div class="sapjp-docs__section-head">
				<p class="sapjp-docs__eyebrow">Safety</p>
				<h2>公開範囲</h2>
			</div>
			<div class="sapjp-docs__callout">
				<p>
					このAPIは通常の公開投稿だけを返します。下書き、非公開投稿、パスワード保護投稿はレスポンスに含めません。
					WordPressをチャット実行基盤にせず、記事コンテンツの提供に役割を限定しています。
				</p>
			</div>
		</section>

		<section id="next" class="sapjp-docs__section">
			<div class="sapjp-docs__section-head">
				<p class="sapjp-docs__eyebrow">Next</p>
				<h2>MCP連携の次の形</h2>
			</div>
			<div class="sapjp-docs__steps">
				<div>
					<span>1</span>
					<p>外部MCPサーバーから <code>/context</code> を呼び出す。</p>
				</div>
				<div>
					<span>2</span>
					<p>GitHub上のABAPメモやコード例も同じ検索対象に加える。</p>
				</div>
				<div>
					<span>3</span>
					<p>別アプリ側でABAPレビューAIやSAP用語検索UIを提供する。</p>
				</div>
			</div>
		</section>
	</div>
</main>

<?php
get_footer();
