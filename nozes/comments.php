<?php
/**
 * Comentários do post.
 */

if ( post_password_required() ) {
	return;
}
?>

<div class="nz-comments">
	<?php if ( have_comments() ) : ?>
		<h2><?php comments_number( __( 'Nenhum comentário', 'nozes' ), __( '1 comentário', 'nozes' ), __( '% comentários', 'nozes' ) ); ?></h2>
		<ul class="comment-list">
			<?php
			wp_list_comments( array(
				'style'      => 'ul',
				'short_ping' => true,
			) );
			?>
		</ul>
		<?php the_comments_navigation(); ?>
	<?php endif; ?>

	<?php if ( ! comments_open() && get_comments_number() ) : ?>
		<p><?php esc_html_e( 'Os comentários estão fechados.', 'nozes' ); ?></p>
	<?php endif; ?>

	<?php comment_form(); ?>
</div>
