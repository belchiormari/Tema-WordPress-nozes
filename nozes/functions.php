<?php
/**
 * Nozes — funções do tema
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NOZES_VERSION', '1.0.0' );
define( 'NOZES_DIR', get_template_directory() );
define( 'NOZES_URI', get_template_directory_uri() );

$nozes_includes = array(
	'/inc/setup.php',          // Suporte do tema, menus, enqueue de assets
	'/inc/customizer.php',     // Personalizador (WhatsApp, redes sociais, textos)
	'/inc/cpt-relatorios.php', // Área do cliente: papel "Cliente" + Relatórios
	'/inc/seo-geo.php',        // SEO, dados estruturados e GEO (llms.txt, FAQ)
	'/inc/starter-content.php',// Conteúdo inicial criado na ativação do tema
	'/inc/template-tags.php',  // Funções auxiliares usadas nos templates
);

foreach ( $nozes_includes as $file ) {
	$path = NOZES_DIR . $file;
	if ( is_readable( $path ) ) {
		require_once $path;
	}
}
