<?php
/**
 * Página 404 — não encontrada.
 */
get_header();
?>

<section class="nz-section nz-404">
	<div class="nz-container u-max-content">
		<span class="u-eyebrow"><?php esc_html_e( 'Erro 404', 'nozes' ); ?></span>
		<h1><?php esc_html_e( 'Essa página saiu de rota.', 'nozes' ); ?></h1>
		<p class="u-lede"><?php esc_html_e( 'O conteúdo que você procura não existe ou foi movido. Que tal voltar para o início ou falar com a gente no WhatsApp?', 'nozes' ); ?></p>
		<div class="nz-hero__actions u-center" style="justify-content:center;">
			<a class="nz-btn nz-btn--lime" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Voltar ao início', 'nozes' ); ?></a>
			<a class="nz-btn nz-btn--outline" href="<?php echo esc_url( nozes_get_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Falar no WhatsApp', 'nozes' ); ?></a>
		</div>
	</div>
</section>

<?php get_footer(); ?>
