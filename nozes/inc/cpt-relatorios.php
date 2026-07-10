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
		'supports'           => array( 'title', 'editor', 'custom-fields' ),
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
 * Verifica se um usuário tem o papel "Cliente" (checa o papel, não uma
 * capability — usar current_user_can('cliente') não funciona, pois "cliente"
 * é um papel, não uma permissão).
 */
function nozes_user_is_client( $user = null ) {
	if ( null === $user ) {
		$user = wp_get_current_user();
	}
	return ( $user instanceof WP_User ) && in_array( 'cliente', (array) $user->roles, true );
}

/**
 * Clientes nunca veem o wp-admin nem a barra de admin — são levados direto
 * para a Área do Cliente no site.
 */
function nozes_redirect_clients_from_admin() {
	if ( is_admin() && ! wp_doing_ajax() && nozes_user_is_client() ) {
		wp_safe_redirect( nozes_get_client_area_url() );
		exit;
	}
}
add_action( 'admin_init', 'nozes_redirect_clients_from_admin' );

/**
 * Esconde a barra de administração do WordPress para clientes.
 */
function nozes_hide_admin_bar_for_clients( $show ) {
	return nozes_user_is_client() ? false : $show;
}
add_filter( 'show_admin_bar', 'nozes_hide_admin_bar_for_clients' );

/**
 * Impede que a Área do Cliente (e os relatórios abertos por ela) sejam
 * guardados em cache — de página, do navegador ou de plugins/servidor.
 * Sem isso, é comum o cache servir a versão "deslogada" (tela de login)
 * e o cliente precisar digitar a senha de novo ao voltar.
 */
function nozes_client_area_no_cache() {
	if ( ! is_page_template( 'template-area-cliente.php' ) ) {
		return;
	}
	nocache_headers();
	header( 'Cache-Control: no-cache, no-store, must-revalidate, max-age=0' );
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}
}
add_action( 'template_redirect', 'nozes_client_area_no_cache' );

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
 * Caixa "Cliente dono deste relatório" na tela de edição do relatório.
 * Lista os usuários com o papel "Cliente" (que são só-leitura e por isso não
 * aparecem no seletor de Autor padrão do WordPress). Ao salvar, define o
 * "autor" do relatório como o cliente escolhido — assim o cliente continua
 * travado como "Cliente", sem acesso a posts, mídias ou comentários.
 */
function nozes_relatorio_owner_metabox() {
	add_meta_box(
		'nozes_relatorio_owner',
		__( 'Cliente dono deste relatório', 'nozes' ),
		'nozes_relatorio_owner_metabox_cb',
		'relatorio',
		'side',
		'high'
	);
}
add_action( 'add_meta_boxes', 'nozes_relatorio_owner_metabox' );

function nozes_relatorio_owner_metabox_cb( $post ) {
	wp_nonce_field( 'nozes_relatorio_owner', 'nozes_relatorio_owner_nonce' );

	$clients = get_users( array(
		'role'    => 'cliente',
		'orderby' => 'display_name',
		'order'   => 'ASC',
	) );

	if ( empty( $clients ) ) {
		echo '<p>' . esc_html__( 'Nenhum usuário com a função "Cliente" foi encontrado.', 'nozes' ) . '</p>';
		echo '<p>' . wp_kses_post( __( 'Crie o cliente em <strong>Usuários → Adicionar novo</strong> e escolha a função <strong>Cliente</strong>. Depois volte aqui e selecione-o.', 'nozes' ) ) . '</p>';
		return;
	}

	$current = (int) $post->post_author;
	echo '<p>' . esc_html__( 'Escolha quem poderá ver este relatório na Área do Cliente:', 'nozes' ) . '</p>';
	echo '<select name="nozes_relatorio_owner" style="width:100%;">';
	echo '<option value="0">' . esc_html__( '— Selecione o cliente —', 'nozes' ) . '</option>';
	foreach ( $clients as $client ) {
		printf(
			'<option value="%d"%s>%s</option>',
			(int) $client->ID,
			selected( $current, $client->ID, false ),
			esc_html( $client->display_name . ' (' . $client->user_login . ')' )
		);
	}
	echo '</select>';
}

function nozes_save_relatorio_owner( $post_id, $post ) {
	if ( empty( $_POST['nozes_relatorio_owner_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nozes_relatorio_owner_nonce'] ) ), 'nozes_relatorio_owner' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'relatorio' !== $post->post_type || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( ! isset( $_POST['nozes_relatorio_owner'] ) ) {
		return;
	}

	$owner = absint( $_POST['nozes_relatorio_owner'] );
	if ( $owner && $owner !== (int) $post->post_author ) {
		remove_action( 'save_post_relatorio', 'nozes_save_relatorio_owner', 10 );
		wp_update_post( array(
			'ID'          => $post_id,
			'post_author' => $owner,
		) );
		add_action( 'save_post_relatorio', 'nozes_save_relatorio_owner', 10, 2 );
	}
}
add_action( 'save_post_relatorio', 'nozes_save_relatorio_owner', 10, 2 );

/**
 * Verifica se um usuário pode ver um relatório específico.
 * Administradores veem todos; o cliente só vê os relatórios em que é o "Autor".
 * Usado ao abrir um relatório em página própria (?relatorio=ID), para impedir
 * que um cliente troque o número no link e tente ver o relatório de outro.
 */
function nozes_user_can_view_report( $user_id, $report ) {
	if ( ! $report instanceof WP_Post ) {
		return false;
	}
	if ( 'relatorio' !== $report->post_type || 'publish' !== $report->post_status ) {
		return false;
	}
	if ( user_can( $user_id, 'manage_options' ) ) {
		return true;
	}
	return (int) $report->post_author === (int) $user_id;
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
