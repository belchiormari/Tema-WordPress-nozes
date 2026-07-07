<?php
/**
 * Página inicial do Blog (lista de posts definida em Configurações → Leitura).
 * Exibe um cabeçalho antes das matérias e a grade de artigos.
 */
get_header();

$nz_posts_page_id = (int) get_option( 'page_for_posts' );
$nz_blog_title    = $nz_posts_page_id ? get_the_title( $nz_posts_page_id ) : get_bloginfo( 'name' );

$nz_blog_lede = '';
if ( $nz_posts_page_id ) {
	$nz_blog_lede = trim( wp_strip_all_tags( get_post_field( 'post_content', $nz_posts_page_id ) ) );
}
if ( '' === $nz_blog_lede ) {
	$nz_blog_lede = __( 'Artigos sobre como as pessoas percebem, decidem e escolhem, e o que isso muda na construção da sua marca.', 'nozes' );
}
?>

<header class="nz-blog-hero">
	<div class="nz-container">
		<?php nozes_breadcrumbs(); ?>
		<span class="u-eyebrow"><?php esc_html_e( 'Blog', 'nozes' ); ?></span>
		<h1 class="nz-blog-hero__title"><?php echo esc_html( $nz_blog_title ); ?></h1>
		<p class="nz-blog-hero__lede u-lede"><?php echo esc_html( $nz_blog_lede ); ?></p>
	</div>
</header>

<section class="nz-section nz-section--tight">
	<div class="nz-container">
		<?php if ( have_posts() ) : ?>
			<div class="nz-grid nz-grid--3">
				<?php while ( have_posts() ) : the_post(); ?>
					<a class="nz-post-card" href="<?php the_permalink(); ?>">
						<div class="nz-post-card__media">
							<?php if ( has_post_thumbnail() ) : ?>
								<?php the_post_thumbnail( 'nozes-card' ); ?>
							<?php endif; ?>
						</div>
						<div class="nz-post-card__body">
							<?php $cats = get_the_category(); ?>
							<?php if ( ! empty( $cats ) ) : ?>
								<span class="nz-post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
							<?php endif; ?>
							<h3><?php the_title(); ?></h3>
							<div class="nz-post-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
						</div>
					</a>
				<?php endwhile; ?>
			</div>

			<div style="margin-top:3rem;">
				<?php nozes_pagination(); ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e( 'Nenhum artigo encontrado.', 'nozes' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
