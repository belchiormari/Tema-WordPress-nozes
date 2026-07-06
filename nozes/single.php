<?php
/**
 * Post único do blog.
 */
get_header();
?>

<?php while ( have_posts() ) : the_post(); ?>
<article id="post-<?php the_ID(); ?>" <?php post_class( 'nz-section' ); ?>>
	<div class="nz-container nz-single-post">
		<?php nozes_breadcrumbs(); ?>

		<?php $cats = get_the_category(); ?>
		<?php if ( ! empty( $cats ) ) : ?>
			<span class="u-eyebrow"><?php echo esc_html( $cats[0]->name ); ?></span>
		<?php endif; ?>

		<h1><?php the_title(); ?></h1>
		<p class="nz-post-card__meta" style="margin-bottom:2rem;">
			<?php
			printf(
				/* translators: 1: data, 2: autor */
				esc_html__( 'Publicado em %1$s por %2$s', 'nozes' ),
				esc_html( get_the_date() ),
				esc_html( get_the_author() )
			);
			?>
		</p>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="nz-post-thumb">
				<?php the_post_thumbnail( 'nozes-wide' ); ?>
			</div>
		<?php endif; ?>

		<div class="nz-single-post__content">
			<?php the_content(); ?>
		</div>

		<?php
		wp_link_pages( array(
			'before' => '<nav class="page-links">' . esc_html__( 'Páginas:', 'nozes' ),
			'after'  => '</nav>',
		) );
		?>

		<div class="nz-author-box">
			<?php echo get_avatar( get_the_author_meta( 'ID' ), 64 ); ?>
			<div>
				<strong><?php the_author(); ?></strong>
				<?php if ( get_the_author_meta( 'description' ) ) : ?>
					<p><?php the_author_meta( 'description' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( comments_open() || get_comments_number() ) : ?>
			<div style="margin-top:3rem;">
				<?php comments_template(); ?>
			</div>
		<?php endif; ?>
	</div>
</article>
<?php endwhile; ?>

<?php get_footer(); ?>
