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
	// Usa a data de modificação dos arquivos como versão: assim, toda vez que o
	// CSS/JS do tema é atualizado, o navegador e o cache do servidor buscam a
	// versão nova automaticamente, em vez de continuar servindo a versão antiga.
	$style_version  = file_exists( get_stylesheet_directory() . '/style.css' ) ? filemtime( get_stylesheet_directory() . '/style.css' ) : NOZES_VERSION;
	$script_version = file_exists( get_stylesheet_directory() . '/assets/js/main.js' ) ? filemtime( get_stylesheet_directory() . '/assets/js/main.js' ) : NOZES_VERSION;

	wp_enqueue_style( 'nozes-style', get_stylesheet_uri(), array(), $style_version );
	wp_enqueue_script( 'nozes-main', NOZES_URI . '/assets/js/main.js', array(), $script_version, true );

	wp_localize_script( 'nozes-main', 'nozesData', array(
		'whatsappUrl' => nozes_get_whatsapp_link(),
	) );

	if ( is_singular() && comments_open() ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'nozes_assets' );

/**
 * Mostra 9 posts por página no Blog (home de posts e arquivos) — múltiplo de 3,
 * para a grade de 3 colunas fechar sempre certinho, sem "sobrar" um card na
 * última linha.
 */
function nozes_blog_posts_per_page( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_home() || $query->is_category() || $query->is_tag() || $query->is_date() || $query->is_author() ) {
		$query->set( 'posts_per_page', 9 );
	}
}
add_action( 'pre_get_posts', 'nozes_blog_posts_per_page' );

/**
 * Nas páginas de HTML livre (páginas montadas 100% em HTML, geralmente com
 * fundo escuro), pinta o fundo do site de escuro. Assim, se sobrar qualquer
 * frestinha entre o conteúdo e o rodapé, ela fica invisível (em vez de virar
 * uma linha branca), já que o rodapé também é escuro.
 */
function nozes_body_classes( $classes ) {
	if ( is_page_template( 'template-html-livre.php' ) ) {
		$classes[] = 'nz-dark-canvas';
	}
	return $classes;
}
add_filter( 'body_class', 'nozes_body_classes' );

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

/**
 * Faz os e-mails automáticos do WordPress (novo usuário, redefinição de senha
 * da Área do Cliente, etc.) chegarem como remetente "Nozes" em vez de
 * "WordPress" genérico. Para garantir a entrega (não cair em spam), configure
 * também um plugin de SMTP (ex: WP Mail SMTP) usando este mesmo e-mail —
 * veja o GUIA-DE-USO.md.
 */
function nozes_mail_from( $original_email ) {
	$custom_email = get_theme_mod( 'nozes_email', '' );
	return $custom_email ? $custom_email : $original_email;
}
add_filter( 'wp_mail_from', 'nozes_mail_from' );

function nozes_mail_from_name( $original_name ) {
	return get_bloginfo( 'name' );
}
add_filter( 'wp_mail_from_name', 'nozes_mail_from_name' );

/**
 * E-mail de suporte mostrado aos clientes nos e-mails automáticos do WordPress.
 * Usa o e-mail do rodapé (ou o de contato) do Personalizador, evitando expor o
 * e-mail pessoal do administrador. Cai para o e-mail do admin só se nenhum
 * estiver configurado.
 */
function nozes_support_email() {
	$email = get_theme_mod( 'nozes_footer_email', '' );
	if ( ! $email ) {
		$email = get_theme_mod( 'nozes_email', '' );
	}
	return $email ? $email : get_option( 'admin_email' );
}

/**
 * No aviso de "senha alterada" enviado ao usuário, o WordPress usa o e-mail do
 * administrador como contato de suporte — que costuma ser um e-mail pessoal.
 * Aqui trocamos por um e-mail profissional (o mesmo do rodapé/contato).
 */
function nozes_password_change_email( $email, $user = null, $userdata = null ) {
	$support = nozes_support_email();
	$admin   = get_option( 'admin_email' );
	if ( $support && $support !== $admin && ! empty( $email['message'] ) ) {
		$email['message'] = str_replace( $admin, $support, $email['message'] );
	}
	return $email;
}
add_filter( 'password_change_email', 'nozes_password_change_email', 10, 3 );
