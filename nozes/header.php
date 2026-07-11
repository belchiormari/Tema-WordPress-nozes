<?php
/**
 * Cabeçalho do site.
 */
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="icon" href="<?php echo esc_url( NOZES_URI . '/assets/img/favicon.png' ); ?>" sizes="any">
	<link rel="apple-touch-icon" href="<?php echo esc_url( NOZES_URI . '/assets/img/favicon.png' ); ?>">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#conteudo-principal"><?php esc_html_e( 'Pular para o conteúdo', 'nozes' ); ?></a>

<header class="nz-header">
	<div class="nz-container nz-header__bar">
		<a class="nz-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<img src="<?php echo esc_url( NOZES_URI . '/assets/img/logo-wordmark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" />
			<?php endif; ?>
		</a>

		<nav class="nz-nav" id="menu-principal" aria-label="<?php esc_attr_e( 'Menu principal', 'nozes' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'fallback_cb'    => false,
				'menu_class'     => 'nz-nav__list',
			) );
			?>
		</nav>

		<div class="nz-header__actions">
			<?php
			// Acesso à Área do Cliente como item destacado do site (fora do menu de navegação).
			$nz_client_url   = function_exists( 'nozes_get_client_area_url' ) ? nozes_get_client_area_url() : home_url( '/area-do-cliente/' );
			$nz_client_label = is_user_logged_in() ? __( 'Meus relatórios', 'nozes' ) : __( 'Área do Cliente', 'nozes' );
			?>
			<a class="nz-client-access" href="<?php echo esc_url( $nz_client_url ); ?>">
				<?php echo nozes_icon( 'user' ); ?>
				<span><?php echo esc_html( $nz_client_label ); ?></span>
			</a>
			<a class="nz-btn nz-btn--yellow-lime" href="<?php echo esc_url( nozes_get_whatsapp_link() ); ?>" target="_blank" rel="noopener noreferrer">
				<?php echo nozes_icon( 'whatsapp' ); ?>
				<span><?php esc_html_e( 'Falar no WhatsApp', 'nozes' ); ?></span>
			</a>
			<button class="nz-burger" id="nz-burger" aria-expanded="false" aria-controls="menu-principal" aria-label="<?php esc_attr_e( 'Abrir menu', 'nozes' ); ?>">
				<?php echo nozes_icon( 'menu' ); ?>
			</button>
		</div>
	</div>
</header>

<main id="conteudo-principal">
