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
					<?php nozes_post_card(); ?>
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
