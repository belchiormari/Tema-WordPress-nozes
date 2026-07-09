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

		<div class="nz-post-outro">
			<div class="nz-author-card">
			<span class="nz-author-card__eyebrow"><?php esc_html_e( 'Escrito por', 'nozes' ); ?></span>
			<div class="nz-author-card__inner">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 128, '', '', array( 'class' => 'nz-author-card__avatar' ) ); ?>
				<div class="nz-author-card__body">
					<strong class="nz-author-card__name"><?php the_author(); ?></strong>
					<?php if ( get_the_author_meta( 'description' ) ) : ?>
						<p class="nz-author-card__bio"><?php echo esc_html( get_the_author_meta( 'description' ) ); ?></p>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php
		$nz_prev = get_previous_post();
		$nz_next = get_next_post();
		?>
		<?php if ( $nz_prev || $nz_next ) : ?>
			<nav class="nz-post-nav" aria-label="<?php esc_attr_e( 'Navegação entre artigos', 'nozes' ); ?>">
				<?php if ( $nz_prev ) : ?>
					<a class="nz-post-nav__link nz-post-nav__link--prev" href="<?php echo esc_url( get_permalink( $nz_prev ) ); ?>">
						<span class="nz-post-nav__dir">&larr; <?php esc_html_e( 'Anterior', 'nozes' ); ?></span>
						<span class="nz-post-nav__title"><?php echo esc_html( get_the_title( $nz_prev ) ); ?></span>
					</a>
				<?php endif; ?>
				<?php if ( $nz_next ) : ?>
					<a class="nz-post-nav__link nz-post-nav__link--next" href="<?php echo esc_url( get_permalink( $nz_next ) ); ?>">
						<span class="nz-post-nav__dir"><?php esc_html_e( 'Próximo', 'nozes' ); ?> &rarr;</span>
						<span class="nz-post-nav__title"><?php echo esc_html( get_the_title( $nz_next ) ); ?></span>
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

			<?php if ( comments_open() || get_comments_number() ) : ?>
				<div class="nz-post-comments">
					<?php comments_template(); ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</article>

<?php
$nz_cta_title = get_theme_mod( 'nozes_blog_cta_title', 'Esse assunto apareceu na sua empresa?' );
$nz_cta_text  = get_theme_mod( 'nozes_blog_cta_text', 'Se quiser destrinchar isso pro seu negócio, me escreve. A primeira conversa é sem compromisso.' );
?>
<?php if ( $nz_cta_title || $nz_cta_text ) : ?>
	<section class="nz-cta-final">
		<div class="nz-cta-final__glow" aria-hidden="true"></div>
		<div class="nz-container">
			<div class="nz-cta-final__inner">
				<?php if ( $nz_cta_title ) : ?>
					<h2 class="nz-cta-final__title"><?php echo esc_html( $nz_cta_title ); ?></h2>
				<?php endif; ?>
				<?php if ( $nz_cta_text ) : ?>
					<p class="nz-cta-final__text"><?php echo esc_html( $nz_cta_text ); ?></p>
				<?php endif; ?>
				<a class="nz-btn nz-btn--lime nz-cta-final__btn" href="<?php echo esc_url( nozes_get_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer">
					<?php echo nozes_icon( 'whatsapp' ); ?>
					<span><?php esc_html_e( 'Chamar no WhatsApp', 'nozes' ); ?></span>
				</a>
			</div>
		</div>
	</section>
<?php endif; ?>
<?php endwhile; ?>

<?php get_footer(); ?>
