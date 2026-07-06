<?php
/**
 * Modelo de fallback (raramente usado — o Blog usa archive.php/single.php).
 */
get_header();
?>

<section class="nz-section">
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
			<p><?php esc_html_e( 'Nenhum conteúdo encontrado.', 'nozes' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
