<?php
/**
 * トップページテンプレート（Anthropic風）
 * シンプル、洗練、ミニマル
 */

get_header(); // Swellのヘッダー
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
                <h1 class="hero-title">
                    <span class="hero-line">SAP<span class="hero-underline">キャリア</span>と</span>
                    <span class="hero-line"><span class="hero-underline">技術</span>の最前線を</span>
                    <span class="hero-line">発信するメディア</span>
                </h1>
            </div>
            <div class="hero-description">
                <p class="hero-subtitle">
                    SAPは世界中の企業を支える基幹システムです。SAP-JPは、SAPプロフェッショナルのキャリア形成と最新技術の実践的な情報をお届けします。
                </p>
                <a href="<?php echo esc_url(home_url('/chat')); ?>" class="hero-cta">Try chat →</a>
            </div>
        </div>

        <!-- Logo Marquee -->
        <div class="hero-marquee">
            <div class="hero-marquee__label">Topics we<br>cover</div>
            <div class="hero-marquee__track-wrap">
                <div class="hero-marquee__track">
                    <?php
                    $brands = array('S/4HANA', 'ABAP', 'Fiori', 'BTP', 'AI', 'SAP Basis', 'RFC', 'CDS View');
                    for ($loop = 0; $loop < 2; $loop++):
                        foreach ($brands as $brand):
                    ?>
                    <div class="hero-marquee__item">
                        <span class="hero-marquee__name"><?php echo esc_html($brand); ?></span>
                    </div>
                    <?php endforeach; endfor; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- ビジュアルセクション -->
    <div class="visual-wrapper">
        <section class="visual-section js-fade-in" aria-label="ビジュアル">
            <div class="visual__col visual__col--left">
                <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/img/visual-desert.jpg" alt="砂漠の風景" class="visual__img" loading="lazy">
            </div>
            <div class="visual__col visual__col--right">
                <img src="<?php echo esc_url(get_stylesheet_directory_uri()); ?>/img/hero-bg.jpg" alt="技術イメージ" class="visual__img" loading="lazy">
            </div>
        </section>
    </div>

    <!-- 特集（横スクロール） -->
    <div class="featured-banner-wrapper">
    <div class="featured-pin">
        <section class="featured-banner">
            <div class="featured-track">
                <?php
$news_cat = get_category_by_slug('news');
$featured_posts = new WP_Query(array(
    'post_type'      => 'post',
    'posts_per_page' => 3,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'category__not_in' => $news_cat ? array($news_cat->term_id) : array(),
));
if ($featured_posts->have_posts()):
    while ($featured_posts->have_posts()):
        $featured_posts->the_post();
?>
                <a href="<?php the_permalink(); ?>" class="featured-panel">
                    <div class="featured-panel__img">
                        <?php
                        $youtube_id = get_post_meta(get_the_ID(), 'swell_meta_youtube', true);
                        if (!empty($youtube_id)): ?>
                            <div class="featured-panel__yt">
                                <img src="https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/maxresdefault.jpg"
                                     alt="<?php the_title_attribute(); ?>"
                                     loading="lazy">
                                <span class="featured-panel__play" aria-hidden="true"></span>
                            </div>
                        <?php elseif (has_post_thumbnail()): ?>
                            <?php the_post_thumbnail('full'); ?>
                        <?php else: ?>
                            <div class="featured-panel__placeholder"></div>
                        <?php endif; ?>
                    </div>
                    <div class="featured-panel__body">
                        <span class="featured-panel__label">特集</span>
                        <h2 class="featured-panel__title"><?php the_title(); ?></h2>
                        <p class="featured-panel__excerpt"><?php echo esc_html(mb_substr(get_the_excerpt(), 0, 60)); ?>…</p>
                        <span class="featured-panel__cta">続きを読む →</span>
                    </div>
                </a>
                <?php
    endwhile;
    wp_reset_postdata();
endif;
?>
            </div>
        </section>
    </div>
    </div>

    <!-- Contents（カテゴリ別タブ） -->
    <section class="contents">
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
                                <?php the_post_thumbnail('medium_large'); ?>
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
        <div class="popular__inner">
            <h2 class="popular__heading js-fade-in">Popular</h2>
            <div class="popular__grid">
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

$i = 0;
if ($popular_posts->have_posts()):
    while ($popular_posts->have_posts()):
        $popular_posts->the_post();
        $cats = get_the_category();
?>
                <article class="popular__card js-fade-in" style="--card-index: <?php echo esc_attr($i); ?>">
                    <a href="<?php the_permalink(); ?>" class="popular__link">
                        <?php if (has_post_thumbnail()): ?>
                        <div class="popular__thumb">
                            <?php the_post_thumbnail('medium_large'); ?>
                        </div>
                        <?php endif; ?>
                        <div class="popular__body">
                            <?php if (!empty($cats)): ?>
                            <span class="popular__cat"><?php echo esc_html($cats[0]->name); ?></span>
                            <?php endif; ?>
                            <h3 class="popular__title"><?php the_title(); ?></h3>
                            <time class="popular__date"><?php echo get_the_date('Y.m.d'); ?></time>
                        </div>
                    </a>
                </article>
                <?php
        $i++;
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