<?php
/**
 * Modelo padrão de página — usado por Início, Sobre, Serviços, Contato etc.
 * Compatível com o editor nativo do WordPress e com o Elementor: se a página
 * foi montada no Elementor, o tema não aplica seu próprio container/padding.
 */

get_header();

$built_with_elementor = nozes_is_built_with_elementor();
?>

<?php if ( $built_with_elementor ) : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php the_content(); ?>
	<?php endwhile; ?>

<?php else : ?>

	<?php while ( have_posts() ) : the_post(); ?>
		<?php if ( ! is_front_page() ) : ?>
			<div class="nz-container" style="padding-top:2rem;">
				<?php nozes_breadcrumbs(); ?>
			</div>
		<?php endif; ?>

		<div class="nz-page-content">
			<?php the_content(); ?>
		</div>
	<?php endwhile; ?>

<?php endif; ?>

<?php get_footer(); ?>
