<?php
/**
 * Setup base do tema: suporte a recursos do WP, menus, assets e compatibilidade com Elementor.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Recursos do tema.
 */
function nozes_setup() {
	load_theme_textdomain( 'nozes', NOZES_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'custom-logo', array(
		'height'      => 80,
		'width'       => 240,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'style.css' );

	// Compatibilidade com Elementor (o construtor visual escolhido para editar as páginas).
	add_theme_support( 'elementor' );
	add_theme_support( 'elementor-pro' );

	// Paleta de cores oficial da Nozes disponível no editor de blocos.
	add_theme_support( 'editor-color-palette', array(
		array( 'name' => __( 'Preto Nozes', 'nozes' ), 'slug' => 'preto', 'color' => '#1d1d1f' ),
		array( 'name' => __( 'Branco', 'nozes' ), 'slug' => 'branco', 'color' => '#ffffff' ),
		array( 'name' => __( 'Rosa', 'nozes' ), 'slug' => 'rosa', 'color' => '#ff0066' ),
		array( 'name' => __( 'Verde Limão', 'nozes' ), 'slug' => 'verde-limao', 'color' => '#abf705' ),
		array( 'name' => __( 'Amarelo Limão', 'nozes' ), 'slug' => 'amarelo-limao', 'color' => '#d8ff01' ),
		array( 'name' => __( 'Azul', 'nozes' ), 'slug' => 'azul', 'color' => '#1fbdc6' ),
		array( 'name' => __( 'Amarelo', 'nozes' ), 'slug' => 'amarelo', 'color' => '#ffd100' ),
		array( 'name' => __( 'Coral', 'nozes' ), 'slug' => 'coral', 'color' => '#ff6b6b' ),
		array( 'name' => __( 'Roxo', 'nozes' ), 'slug' => 'roxo', 'color' => '#7b4fe0' ),
	) );

	register_nav_menus( array(
		'primary' => __( 'Menu Principal', 'nozes' ),
		'footer'  => __( 'Menu do Rodapé', 'nozes' ),
	) );

	add_image_size( 'nozes-card', 640, 400, true );
	add_image_size( 'nozes-wide', 1600, 900, true );
}
add_action( 'after_setup_theme', 'nozes_setup' );

/**
 * Larguras do editor de blocos.
 */
function nozes_content_width() {
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'nozes_content_width', 0 );

/**
 * Estilos e scripts do site.
 */
function nozes_assets() {
	wp_enqueue_style( 'nozes-style', get_stylesheet_uri(), array(), NOZES_VERSION );
	wp_enqueue_script( 'nozes-main', NOZES_URI . '/assets/js/main.js', array(), NOZES_VERSION, true );

	wp_localize_script( 'nozes-main', 'nozesData', array(
		'whatsappUrl' => nozes_get_whatsapp_link(),
	) );

	if ( is_singular() && comments_open() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'nozes_assets' );

/**
 * Áreas de widget (usadas apenas se alguma página optar por sidebar).
 */
function nozes_widgets_init() {
	register_sidebar( array(
		'name'          => __( 'Barra Lateral do Blog', 'nozes' ),
		'id'            => 'blog-sidebar',
		'before_widget' => '<div class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h4>',
		'after_title'   => '</h4>',
	) );
}
add_action( 'widgets_init', 'nozes_widgets_init' );

/**
 * Remove informações que expõem a versão do WordPress (boas práticas de segurança/SEO técnico).
 */
remove_action( 'wp_head', 'wp_generator' );

/**
 * Tamanhos de imagem extras disponíveis no seletor do editor.
 */
function nozes_custom_image_sizes( $sizes ) {
	return array_merge( $sizes, array(
		'nozes-card' => __( 'Card Nozes (640x400)', 'nozes' ),
		'nozes-wide' => __( 'Largo Nozes (1600x900)', 'nozes' ),
	) );
}
add_filter( 'image_size_names_choose', 'nozes_custom_image_sizes' );

/**
 * Ajusta o resumo automático dos posts (usado nos cards do blog).
 */
function nozes_excerpt_length( $length ) {
	return 24;
}
add_filter( 'excerpt_length', 'nozes_excerpt_length' );

function nozes_excerpt_more( $more ) {
	return '…';
}
add_filter( 'excerpt_more', 'nozes_excerpt_more' );
