<?php
/**
 * Rodapé do site.
 */
?>
</main>

<footer class="nz-footer">
	<div class="nz-container">
		<div class="nz-footer__grid">
			<div>
				<img src="<?php echo esc_url( NOZES_URI . '/assets/img/logo-wordmark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" style="height:34px;width:auto;margin-bottom:1.2rem;" />
				<p class="nz-footer__desc"><?php echo esc_html( get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) ) ); ?></p>
				<div class="nz-social">
					<?php $nz_instagram = get_theme_mod( 'nozes_social_instagram' ); ?>
					<?php if ( $nz_instagram ) : ?>
						<a href="<?php echo esc_url( $nz_instagram ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><?php echo nozes_icon( 'instagram' ); ?></a>
					<?php endif; ?>
					<a href="<?php echo esc_url( nozes_get_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><?php echo nozes_icon( 'whatsapp' ); ?></a>
					<?php if ( get_theme_mod( 'nozes_social_linkedin' ) ) : ?>
						<a href="<?php echo esc_url( get_theme_mod( 'nozes_social_linkedin' ) ); ?>" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn"><?php echo nozes_icon( 'linkedin' ); ?></a>
					<?php endif; ?>
				</div>
			</div>

			<div>
				<h4><?php esc_html_e( 'Navegação', 'nozes' ); ?></h4>
				<?php
				wp_nav_menu( array(
					'theme_location' => 'footer',
					'container'      => false,
					'menu_class'     => '',
					'fallback_cb'    => false,
				) );
				?>
			</div>

			<div>
				<h4><?php esc_html_e( 'Área do Cliente', 'nozes' ); ?></h4>
				<ul>
					<li><a href="<?php echo esc_url( nozes_get_client_area_url() ); ?>"><?php esc_html_e( 'Acessar relatórios', 'nozes' ); ?></a></li>
				</ul>
				<div class="nz-footer__contact">
					<strong><?php esc_html_e( 'Nozes Estratégia de Marca.', 'nozes' ); ?></strong>
					<?php $nz_footer_email = get_theme_mod( 'nozes_footer_email', 'ola@somosnozes.com.br' ); ?>
					<?php if ( $nz_footer_email ) : ?>
						<a href="mailto:<?php echo esc_attr( $nz_footer_email ); ?>"><?php echo esc_html( $nz_footer_email ); ?></a>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<div class="nz-footer__bottom">
			<span>&copy; <?php echo esc_html( gmdate( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?>. <?php esc_html_e( 'Todos os direitos reservados.', 'nozes' ); ?></span>
			<span><?php echo esc_html( get_theme_mod( 'nozes_address', '' ) ); ?></span>
		</div>
	</div>
</footer>

<?php nozes_whatsapp_float(); ?>

<?php wp_footer(); ?>
</body>
</html>
