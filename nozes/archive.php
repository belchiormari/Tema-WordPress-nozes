<?php
/**
 * Arquivo/listagem de posts do blog.
 */
get_header();
?>

<section class="nz-section">
	<div class="nz-container">
		<?php nozes_breadcrumbs(); ?>
		<span class="u-eyebrow"><?php esc_html_e( 'Blog', 'nozes' ); ?></span>
		<h1><?php echo wp_kses_post( get_the_archive_title() ); ?></h1>
		<?php if ( get_the_archive_description() ) : ?>
			<div class="u-lede"><?php echo wp_kses_post( get_the_archive_description() ); ?></div>
		<?php endif; ?>

		<?php if ( have_posts() ) : ?>
			<div class="nz-grid nz-grid--3" style="margin-top:2.5rem;">
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
