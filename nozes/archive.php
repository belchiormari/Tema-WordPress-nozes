<?php
/**
 * Arquivo/listagem de posts do blog.
 */
get_header();
?>

<header class="nz-blog-hero">
	<div class="nz-container">
		<?php nozes_breadcrumbs(); ?>
		<span class="u-eyebrow"><?php esc_html_e( 'Blog', 'nozes' ); ?></span>
		<h1 class="nz-blog-hero__title"><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="nz-blog-hero__lede u-lede"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
		<?php endif; ?>
	</div>
</header>

<section class="nz-section nz-section--tight">
	<div class="nz-container">
		<?php nozes_blog_category_nav(); ?>
		<?php if ( have_posts() ) : ?>
			<div class="nz-grid nz-grid--3">
				<?php while ( have_posts() ) : the_post(); ?>
					<?php nozes_post_card(); ?>
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
