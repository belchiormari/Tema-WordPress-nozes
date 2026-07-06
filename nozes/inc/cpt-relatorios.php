<?php
/**
 * Área do Cliente
 * ------------------------------------------------------------------
 * - Cria o tipo de conteúdo "Relatório" (relatorio), onde a Nozes cola o
 *   HTML de cada relatório mensal e escolhe o cliente (autor) dono dele.
 * - Cria o papel de usuário "Cliente", que só consegue logar e ver os
 *   próprios relatórios na página com o modelo "Área do Cliente".
 * - Não existe URL pública para cada relatório: tudo é listado dentro da
 *   página protegida, então não há como um cliente adivinhar o link de
 *   outro cliente.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Custom Post Type: Relatório.
 */
function nozes_register_relatorio_cpt() {
	register_post_type( 'relatorio', array(
		'labels' => array(
			'name'               => __( 'Relatórios', 'nozes' ),
			'singular_name'      => __( 'Relatório', 'nozes' ),
			'add_new_item'       => __( 'Adicionar novo relatório', 'nozes' ),
			'edit_item'          => __( 'Editar relatório', 'nozes' ),
			'all_items'          => __( 'Todos os relatórios', 'nozes' ),
			'search_items'       => __( 'Buscar relatórios', 'nozes' ),
			'not_found'          => __( 'Nenhum relatório encontrado', 'nozes' ),
			'menu_name'          => __( 'Relatórios (Clientes)', 'nozes' ),
		),
		'public'             => false,
		'publicly_queryable' => false,
		'exclude_from_search'=> true,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => true,
		'menu_icon'          => 'dashicons-media-text',
		'supports'           => array( 'title', 'editor', 'author', 'custom-fields' ),
		'capability_type'    => 'post',
		'map_meta_cap'       => true,
	) );
}
add_action( 'init', 'nozes_register_relatorio_cpt' );

/**
 * Papel de usuário "Cliente": só pode ler o próprio conteúdo, nada de admin.
 */
function nozes_register_client_role() {
	if ( ! get_role( 'cliente' ) ) {
		add_role( 'cliente', __( 'Cliente', 'nozes' ), array(
			'read' => true,
		) );
	}
}
add_action( 'init', 'nozes_register_client_role' );

/**
 * Clientes nunca veem o wp-admin nem a barra de admin — são levados direto
 * para a Área do Cliente no site.
 */
function nozes_redirect_clients_from_admin() {
	if ( is_admin() && current_user_can( 'cliente' ) && ! wp_doing_ajax() ) {
		wp_safe_redirect( nozes_get_client_area_url() );
		exit;
	}
}
add_action( 'admin_init', 'nozes_redirect_clients_from_admin' );

function nozes_hide_admin_bar_for_clients() {
	if ( current_user_can( 'cliente' ) ) {
		show_admin_bar( false );
	}
}
add_action( 'after_setup_theme', 'nozes_hide_admin_bar_for_clients' );

/**
 * URL da página que usa o modelo "Área do Cliente".
 */
function nozes_get_client_area_url() {
	$page = get_page_by_path( 'area-do-cliente' );
	if ( $page ) {
		return get_permalink( $page );
	}
	$pages = get_posts( array(
		'post_type'  => 'page',
		'meta_key'   => '_wp_page_template',
		'meta_value' => 'template-area-cliente.php',
		'numberposts'=> 1,
	) );
	return $pages ? get_permalink( $pages[0] ) : home_url( '/' );
}

/**
 * Processa o formulário de login da Área do Cliente (front-end).
 * Retorna WP_Error em caso de falha, ou true em caso de sucesso (com redirect).
 */
function nozes_process_client_login() {
	if ( empty( $_POST['nozes_client_login_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nozes_client_login_nonce'] ) ), 'nozes_client_login' ) ) {
		return null;
	}

	$creds = array(
		'user_login'    => isset( $_POST['nozes_username'] ) ? sanitize_user( wp_unslash( $_POST['nozes_username'] ) ) : '',
		'user_password' => isset( $_POST['nozes_password'] ) ? (string) $_POST['nozes_password'] : '',
		'remember'      => true,
	);

	if ( empty( $creds['user_login'] ) || empty( $creds['user_password'] ) ) {
		return new WP_Error( 'campos_vazios', __( 'Preencha usuário e senha.', 'nozes' ) );
	}

	$user = wp_signon( $creds, is_ssl() );

	if ( is_wp_error( $user ) ) {
		return new WP_Error( 'login_invalido', __( 'Usuário ou senha inválidos.', 'nozes' ) );
	}

	wp_safe_redirect( nozes_get_client_area_url() );
	exit;
}

/**
 * Busca os relatórios do cliente logado (ou de todos, se for administrador).
 */
function nozes_get_client_reports( $user_id ) {
	$args = array(
		'post_type'      => 'relatorio',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	if ( ! user_can( $user_id, 'manage_options' ) ) {
		$args['author'] = $user_id;
	}

	return get_posts( $args );
}
