<?php
/**
 * Template Name: Skills
 * Template Post Type: page
 *
 * GitHub リポジトリ（yuto2245/claude-skills）からスキル一覧を取得して表示
 */

get_header();

$skills     = swell_child_fetch_skills();
$categories = swell_child_skill_categories();

/* スキルを name でインデックス化 */
$skill_map = [];
foreach ( $skills as $skill ) {
	$skill_map[ $skill['name'] ] = $skill;
}

/* カテゴリに分類されていないスキルを「その他」に */
$categorized = [];
foreach ( $categories as $cat => $names ) {
	$categorized = array_merge( $categorized, $names );
}
$uncategorized = [];
foreach ( $skill_map as $name => $skill ) {
	if ( ! in_array( $name, $categorized, true ) ) {
		$uncategorized[] = $name;
	}
}
if ( ! empty( $uncategorized ) ) {
	$categories['Others'] = $uncategorized;
}

/* カテゴリアイコンマッピング */
$cat_icons = [
	'Document Creation'      => '📄',
	'Design & Visual'        => '🎨',
	'Learning & Development' => '📚',
	'Productivity'           => '⚡',
	'Technical'              => '🔧',
	'Professional Services'  => '💼',
	'Skill Management'       => '🛠',
	'Others'                 => '📦',
];
?>

<main class="l-content l-container skill-page">

	<div class="skill-page__header">
		<p class="skill-page__label">Claude Skills</p>
		<h1 class="skill-page__title">Custom Skills</h1>
		<p class="skill-page__desc">Claude.ai / Cowork で使用するカスタムスキル一覧。<br>GitHub リポジトリから自動取得しています。</p>
	</div>

	<?php if ( empty( $skills ) ) : ?>
		<div class="skill-page__empty">
			<p>スキル情報を取得できませんでした。しばらく経ってから再読み込みしてください。</p>
		</div>
	<?php else : ?>

		<div class="skill-page__stats">
			<span class="skill-page__stat"><?php echo count( $skills ); ?> skills</span>
			<span class="skill-page__stat"><?php echo count( $categories ); ?> categories</span>
		</div>

		<?php foreach ( $categories as $cat_name => $cat_skills ) : ?>
			<?php
			/* このカテゴリに属するスキルがリポジトリに存在するかチェック */
			$visible = [];
			foreach ( $cat_skills as $s ) {
				if ( isset( $skill_map[ $s ] ) ) {
					$visible[] = $skill_map[ $s ];
				}
			}
			if ( empty( $visible ) ) continue;
			?>

			<section class="skill-category">
				<h2 class="skill-category__title">
					<span class="skill-category__icon"><?php echo $cat_icons[ $cat_name ] ?? '📦'; ?></span>
					<?php echo esc_html( $cat_name ); ?>
					<span class="skill-category__count"><?php echo count( $visible ); ?></span>
				</h2>

				<div class="skill-category__grid">
					<?php foreach ( $visible as $skill ) : ?>
						<div class="skill-card">
							<h3 class="skill-card__name"><?php echo esc_html( $skill['name'] ); ?></h3>
							<?php if ( ! empty( $skill['description'] ) ) : ?>
								<p class="skill-card__desc"><?php echo esc_html( $skill['description'] ); ?></p>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			</section>

		<?php endforeach; ?>

	<?php endif; ?>

</main>

<?php get_footer(); ?>
