<?php
/**
 * Resultados de busca.
 */
get_header();
?>

<section class="nz-section">
	<div class="nz-container">
		<span class="u-eyebrow"><?php esc_html_e( 'Busca', 'nozes' ); ?></span>
		<h1>
			<?php
			printf(
				/* translators: %s: termo buscado */
				esc_html__( 'Resultados para: %s', 'nozes' ),
				'<span>' . esc_html( get_search_query() ) . '</span>'
			);
			?>
		</h1>

		<form role="search" method="get" class="nz-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Buscar no site…', 'nozes' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>">
			<button type="submit" class="nz-btn nz-btn--primary"><?php esc_html_e( 'Buscar', 'nozes' ); ?></button>
		</form>

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
			<p><?php esc_html_e( 'Nenhum resultado encontrado para essa busca.', 'nozes' ); ?></p>
		<?php endif; ?>
	</div>
</section>

<?php get_footer(); ?>
