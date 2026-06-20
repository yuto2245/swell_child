<?php
/**
 * トップページテンプレート（Anthropic風）
 * シンプル、洗練、ミニマル
 */

get_header(); // Swellのヘッダー

$hero_rotate_words = array( 'AI', 'ABAP', 'Context', 'API' );
$hero_rotate_sizer = 'Context';
$hero_title_words  = array( 'SAP', 'Knowledge', 'Built', 'for' );

$developer_api_base = home_url( '/wp-json/sapjp/v1' );
$developer_tabs     = array(
	array(
		'id'      => 'context',
		'label'   => 'cURL',
		'command' => 'curl "' . $developer_api_base . '/context?query=ABAP"',
	),
	array(
		'id'      => 'search',
		'label'   => 'Search',
		'command' => 'curl "' . $developer_api_base . '/search?query=ABAP&limit=5"',
	),
	array(
		'id'      => 'articles',
		'label'   => 'Articles',
		'command' => 'curl "' . $developer_api_base . '/articles/123"',
	),
);
$developer_stats    = array(
	array(
		'value' => '5+',
		'label' => 'API endpoints',
	),
	array(
		'value' => 'JSON',
		'label' => 'Structured output',
	),
	array(
		'value' => 'RAG',
		'label' => 'Context-ready',
	),
);

$hero_glass_video_url = get_stylesheet_directory_uri() . '/assets/videos/sapjp-terminal-api-demo.mp4';
$hero_glass_cards     = array(
	array(
		'label'      => 'ABAP',
		'aria_label' => 'ABAP カテゴリの記事を見る',
		'href'       => home_url( '/category/abap/' ),
		'stage'      => 'chat',
	),
	array(
		'label'      => 'API',
		'aria_label' => 'Knowledge API ドキュメントを見る',
		'href'       => home_url( '/api' ),
		'stage'      => 'api',
	),
	array(
		'label'      => 'SAP',
		'aria_label' => 'SAP カテゴリの記事を見る',
		'href'       => home_url( '/category/sap/' ),
		'stage'      => 'sap',
	),
	array(
		'label'      => '開発基礎',
		'aria_label' => '開発基礎カテゴリの記事を見る',
		'href'       => home_url( '/category/development/' ),
		'stage'      => 'terminal',
	),
);
?>

<main class="anthropic-page">

    <!-- ヒーローセクション（ダーク + ビデオ + マーキー） -->
    <section class="hero-section">
        <!-- Space Background -->
        <div class="hero-stars" aria-hidden="true">
            <canvas id="star-canvas"></canvas>
        </div>
        <div class="hero-overlay" aria-hidden="true"></div>

        <!-- Hero Content -->
        <div class="hero-container">
            <div class="hero-content">
                <h1 class="hero-title" id="hero-title">
                    <span class="hero-line">
                        <?php foreach ( array_slice( $hero_title_words, 0, 2 ) as $word_index => $word ) : ?>
                        <span class="hero-word" style="--hero-word-index: <?php echo esc_attr( (string) $word_index ); ?>"><?php echo esc_html( $word ); ?></span>
                        <?php endforeach; ?>
                    </span>
                    <span class="hero-line">
                        <?php foreach ( array_slice( $hero_title_words, 2, 2 ) as $offset => $word ) : ?>
                        <?php $word_index = $offset + 2; ?>
                        <span class="hero-word" style="--hero-word-index: <?php echo esc_attr( (string) $word_index ); ?>"><?php echo esc_html( $word ); ?></span>
                        <?php endforeach; ?>
                        <span class="hero-word hero-word--rotate" style="--hero-word-index: 4">
                            <span class="hero-rotate" data-rotate-words="<?php echo esc_attr( implode( ',', $hero_rotate_words ) ); ?>">
                                <span class="hero-rotate__sizer" aria-hidden="true"><?php echo esc_html( $hero_rotate_sizer ); ?></span>
                                <span class="hero-rotate__clip">
                                    <span class="hero-rotate__chars" aria-live="polite"><?php echo esc_html( $hero_rotate_words[0] ); ?></span>
                                </span>
                                <span class="hero-rotate__underline" aria-hidden="true"></span>
                            </span>
                        </span>
                    </span>
                </h1>
            </div>
            <div class="hero-description">
                <p class="hero-subtitle">
                    SAPは世界中の企業を支える基幹システムです。SAP-JPは、SAPプロフェッショナルのキャリア形成と最新技術の実践的な情報をお届けします。
                </p>
                <?php require get_stylesheet_directory() . '/inc/hero-glass-cards.php'; ?>
            </div>
        </div>
    </section>

    <?php require get_stylesheet_directory() . '/inc/developer-section.php'; ?>

    <!-- Topics マーキー（developer セクション直下） -->
    <div class="hero-marquee hero-marquee--after-dev">
        <div class="hero-marquee__label">Topics we<br>cover</div>
        <div class="hero-marquee__track-wrap">
            <div class="hero-marquee__track">
                <?php
                $brands = array( 'S/4HANA', 'ABAP', 'Fiori', 'BTP', 'AI', 'SAP Basis', 'RFC', 'CDS View' );
                for ( $loop = 0; $loop < 2; $loop++ ) :
                    foreach ( $brands as $brand ) :
                        ?>
                <div class="hero-marquee__item">
                    <span class="hero-marquee__name"><?php echo esc_html( $brand ); ?></span>
                </div>
                        <?php
                    endforeach;
                endfor;
                ?>
            </div>
        </div>
    </div>

    <!-- 最新記事 -->
    <section class="latest-feature js-fade-in" aria-label="最新記事">
        <div class="latest-feature__inner">
            <?php
            $news_cat = get_category_by_slug('news');
            $latest_post = new WP_Query(array(
                'post_type'        => 'post',
                'posts_per_page'   => 1,
                'orderby'          => 'date',
                'order'            => 'DESC',
                'category__not_in' => $news_cat ? array($news_cat->term_id) : array(),
            ));
            if ($latest_post->have_posts()):
                while ($latest_post->have_posts()):
                    $latest_post->the_post();
                    $categories = get_the_category();
                    $primary_category = !empty($categories) ? $categories[0]->name : 'Article';
                    $latest_excerpt = get_the_excerpt();
                    if ('' === trim($latest_excerpt)) {
                        $latest_excerpt = wp_strip_all_tags(get_the_content());
                    }
            ?>
            <a href="<?php the_permalink(); ?>" class="latest-feature__card">
                <div class="latest-feature__media">
                    <?php
                    $youtube_id = get_post_meta(get_the_ID(), 'swell_meta_youtube', true);
                    if (!empty($youtube_id)): ?>
                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/hqdefault.jpg"
                             alt="<?php the_title_attribute(); ?>"
                             loading="lazy"
                             decoding="async">
                    <?php elseif (has_post_thumbnail()): ?>
                        <?php echo sapjp_render_post_image(get_the_ID(), 'front_feature'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                    <?php else: ?>
                        <div class="latest-feature__placeholder"></div>
                    <?php endif; ?>
                </div>
                <div class="latest-feature__body">
                    <div class="latest-feature__meta">
                        <span><?php echo esc_html($primary_category); ?></span>
                        <time datetime="<?php echo esc_attr(get_the_date('c')); ?>"><?php echo esc_html(get_the_date('Y.m.d')); ?></time>
                    </div>
                    <h2 class="latest-feature__title"><?php the_title(); ?></h2>
                    <p class="latest-feature__excerpt"><?php echo esc_html(wp_trim_words($latest_excerpt, 58, '...')); ?></p>
                    <span class="latest-feature__cta">続きを読む</span>
                </div>
            </a>
            <?php
                endwhile;
                wp_reset_postdata();
            endif;
            ?>
        </div>
    </section>

    <!-- Contents（カテゴリ別タブ） -->
    <section class="contents" id="contents">
        <div class="contents__inner">
            <h2 class="contents__heading js-fade-in">Contents</h2>
            <?php
$content_tabs = array(
    array('slug' => 'sap',         'label' => 'SAP'),
    array('slug' => 'abap',        'label' => 'ABAP'),
    array('slug' => 'ai',          'label' => 'AI'),
    array('slug' => 'development', 'label' => '開発基礎'),
    array('slug' => 'others',      'label' => 'その他'),
);
// スラッグを小文字に正規化（大文字カテゴリにも対応）
foreach ($content_tabs as &$t) {
    $t['slug'] = strtolower($t['slug']);
}
unset($t);
?>
            <div class="contents__tabs js-fade-in" role="tablist" aria-label="カテゴリ別コンテンツ">
                <?php foreach ($content_tabs as $idx => $tab): ?>
                <button class="contents__tab<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                        role="tab"
                        id="tab-<?php echo esc_attr($tab['slug']); ?>"
                        data-tab="<?php echo esc_attr($tab['slug']); ?>"
                        aria-selected="<?php echo $idx === 0 ? 'true' : 'false'; ?>"
                        aria-controls="panel-<?php echo esc_attr($tab['slug']); ?>"
                        tabindex="<?php echo $idx === 0 ? '0' : '-1'; ?>">
                    <?php echo esc_html($tab['label']); ?>
                </button>
                <?php endforeach; ?>
            </div>

            <?php
            // 「その他」タブ用：他タブのカテゴリIDを収集
            $exclude_slugs = array('sap', 'abap', 'ai', 'development', 'news');
            $exclude_cat_ids = array();
            foreach ($exclude_slugs as $es) {
                $ec = get_category_by_slug($es);
                if ($ec) $exclude_cat_ids[] = $ec->term_id;
            }

            foreach ($content_tabs as $idx => $tab):
                if ($tab['slug'] === 'others') {
                    $tab_query_args = array(
                        'post_type'        => 'post',
                        'posts_per_page'   => 6,
                        'category__not_in' => $exclude_cat_ids,
                        'orderby'          => 'date',
                        'order'            => 'DESC',
                    );
                } else {
                    $tab_query_args = array(
                        'post_type'      => 'post',
                        'posts_per_page' => 6,
                        'category_name'  => $tab['slug'],
                        'orderby'        => 'date',
                        'order'          => 'DESC',
                    );
                }
                $tab_posts = new WP_Query($tab_query_args);
            ?>
            <div class="contents__panel<?php echo $idx === 0 ? ' is-active' : ''; ?>"
                 role="tabpanel"
                 id="panel-<?php echo esc_attr($tab['slug']); ?>"
                 aria-labelledby="tab-<?php echo esc_attr($tab['slug']); ?>"
                 data-panel="<?php echo esc_attr($tab['slug']); ?>"
                 tabindex="0">
                <?php if ($tab_posts->have_posts()): ?>
                <div class="contents__grid">
                    <?php while ($tab_posts->have_posts()): $tab_posts->the_post(); ?>
                    <a href="<?php the_permalink(); ?>" class="contents__card">
                        <div class="contents__thumb">
                            <?php if (has_post_thumbnail()): ?>
                                <?php
                                $thumb_context = ( 0 === $idx ) ? 'front_card' : 'front_card_deferred';
                                echo sapjp_render_post_image(get_the_ID(), $thumb_context); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                ?>
                            <?php else: ?>
                                <div class="contents__thumb-placeholder"></div>
                            <?php endif; ?>
                        </div>
                        <div class="contents__body">
                            <time class="contents__date"><?php echo get_the_date('Y.m.d'); ?></time>
                            <h3 class="contents__name"><?php the_title(); ?></h3>
                        </div>
                    </a>
                    <?php endwhile; wp_reset_postdata(); ?>
                </div>
                <?php else: ?>
                <p class="contents__empty">記事がまだありません。</p>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- Popular posts（人気記事） -->
    <section class="popular">
        <div class="contents__inner">
            <h2 class="contents__heading js-fade-in">Popular</h2>
            <div class="contents__grid">
                <?php
$popular_posts = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 6,
    'meta_key'       => 'ct_post_views_byloos',
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
));

// PVデータがなければ最新記事にフォールバック
if (!$popular_posts->have_posts()) {
    $popular_posts = new WP_Query(array(
        'post_type'      => 'post',
        'posts_per_page' => 6,
        'orderby'        => 'date',
        'order'          => 'DESC',
    ));
}

if ($popular_posts->have_posts()):
    while ($popular_posts->have_posts()):
        $popular_posts->the_post();
?>
                <a href="<?php the_permalink(); ?>" class="contents__card">
                    <div class="contents__thumb">
                        <?php if (has_post_thumbnail()): ?>
                            <?php echo sapjp_render_post_image(get_the_ID(), 'front_card'); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        <?php else: ?>
                            <div class="contents__thumb-placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="contents__body">
                        <time class="contents__date"><?php echo get_the_date('Y.m.d'); ?></time>
                        <h3 class="contents__name"><?php the_title(); ?></h3>
                    </div>
                </a>
                <?php
    endwhile;
    wp_reset_postdata();
endif;
?>
            </div>
        </div>
    </section>

    <!-- News -->
    <section class="news">
        <div class="news__inner">
            <h2 class="news__heading js-fade-in">News</h2>
            <ul class="news__list js-fade-in">
                <?php
$news_posts = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 5,
    'category_name'  => 'news',
    'orderby'        => 'date',
    'order'          => 'DESC',
));
if ($news_posts->have_posts()):
    while ($news_posts->have_posts()):
        $news_posts->the_post();
        $cats = get_the_category();
?>
                <li class="news__item">
                    <span class="news__meta">
                        <time class="news__date"><?php echo get_the_date('Y.m.d'); ?></time>
                        <?php if (!empty($cats)): ?>
                        <span class="news__tag"><?php echo esc_html($cats[0]->name); ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="news__title">
                        <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                    </span>
                </li>
                <?php
    endwhile;
    wp_reset_postdata();
else:
?>
                <p class="news__empty">ニュースはまだありません。「news」カテゴリの記事を公開すると表示されます。</p>
                <?php endif; ?>
            </ul>
        </div>
    </section>

</main>

<?php get_footer(); // Swellのフッター ?>
