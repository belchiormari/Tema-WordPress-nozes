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
			'taxonomies'         => array( 'categoria_relatorio' ),
	) );
}
add_action( 'init', 'nozes_register_relatorio_cpt' );

/**
 * Taxonomia hierárquica "Categorias de relatório": permite organizar os
 * relatórios em categorias e subcategorias (ex.: "2024 → Junho", ou
 * "Marca → Pesquisas"). Na Área do Cliente os relatórios são agrupados por
 * essas categorias, o que facilita encontrar e acessar cada um.
 */
function nozes_register_relatorio_taxonomy() {
	register_taxonomy( 'categoria_relatorio', 'relatorio', array(
		'labels' => array(
			'name'              => __( 'Categorias de relatório', 'nozes' ),
			'singular_name'     => __( 'Categoria', 'nozes' ),
			'search_items'      => __( 'Buscar categorias', 'nozes' ),
			'all_items'         => __( 'Todas as categorias', 'nozes' ),
			'parent_item'       => __( 'Categoria mãe', 'nozes' ),
			'parent_item_colon' => __( 'Categoria mãe:', 'nozes' ),
			'edit_item'         => __( 'Editar categoria', 'nozes' ),
			'update_item'       => __( 'Atualizar categoria', 'nozes' ),
			'add_new_item'      => __( 'Adicionar nova categoria', 'nozes' ),
			'new_item_name'     => __( 'Nome da nova categoria', 'nozes' ),
			'menu_name'         => __( 'Categorias', 'nozes' ),
		),
		'hierarchical'       => true,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_admin_column'  => true,
		'show_in_rest'       => true,
	) );
}
add_action( 'init', 'nozes_register_relatorio_taxonomy' );

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
 *
 * Para usuários logados, acrescenta um parâmetro que muda a cada acesso
 * (cache-buster). Isso garante que a navegação dentro da Área do Cliente
 * (ex.: "Voltar aos relatórios") nunca caia numa versão em cache da tela de
 * login — mesmo em hospedagens com cache de página no servidor.
 */
function nozes_get_client_area_url( $bust_cache = true ) {
	$page = get_page_by_path( 'area-do-cliente' );
	if ( $page ) {
		$url = get_permalink( $page );
	} else {
		$pages = get_posts( array(
			'post_type'  => 'page',
			'meta_key'   => '_wp_page_template',
			'meta_value' => 'template-area-cliente.php',
			'numberposts'=> 1,
		) );
		$url = $pages ? get_permalink( $pages[0] ) : home_url( '/' );
	}

	// O cache-buster só faz sentido para a navegação do próprio cliente logado.
	// Em e-mails (enviados pelo admin), pedimos a URL "limpa" com $bust_cache = false.
	if ( $bust_cache && is_user_logged_in() ) {
		$url = add_query_arg( 'nzc', time(), $url );
	}

	return $url;
}

/**
 * Processa o formulário de login da Área do Cliente (front-end).
 * Retorna WP_Error em caso de falha, ou true em caso de sucesso (com redirect).
 */
function nozes_process_client_login() {
	if ( empty( $_POST['nozes_client_login_nonce'] ) ) {
		return null;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nozes_client_login_nonce'] ) ), 'nozes_client_login' ) ) {
		return new WP_Error( 'nonce_invalido', __( 'A sessão expirou. Recarregue a página e tente entrar novamente.', 'nozes' ) );
	}

	// "Manter conectado / Lembrar de mim": marcado por padrão para o cliente não
	// precisar logar de novo a cada visita. Se desmarcado, a sessão dura só
	// enquanto o navegador estiver aberto.
	$remember = ! empty( $_POST['nozes_remember'] );

	// Importante: NÃO usar sanitize_user() no login nem deixar a senha "escapada".
	// sanitize_user() remove caracteres válidos de e-mail (ex.: o "+" de
	// nome+cliente@gmail.com) e a senha chega com barras extras do WordPress
	// (magic quotes). Nos dois casos o valor mudaria e o login correto seria
	// recusado. Passamos o usuário/e-mail e a senha exatamente como digitados
	// (só tirando as barras extras); o wp_signon faz a validação com segurança.
	$creds = array(
		'user_login'    => isset( $_POST['nozes_username'] ) ? trim( wp_unslash( $_POST['nozes_username'] ) ) : '',
		'user_password' => isset( $_POST['nozes_password'] ) ? (string) wp_unslash( $_POST['nozes_password'] ) : '',
		'remember'      => $remember,
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
 * Caixa "Clientes com acesso a este relatório" na tela de edição do relatório.
 * Lista os usuários com o papel "Cliente" (que são só-leitura e por isso não
 * aparecem no seletor de Autor padrão do WordPress) e permite escolher um ou
 * vários. Ao salvar, a lista fica em meta (_nozes_client) e o autor recebe o
 * primeiro selecionado — assim o cliente continua travado como "Cliente".
 */
function nozes_relatorio_owner_metabox() {
	add_meta_box(
		'nozes_relatorio_owner',
		__( 'Clientes com acesso a este relatório', 'nozes' ),
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

	$selected = nozes_get_report_client_ids( $post );
	echo '<p>' . esc_html__( 'Marque um ou mais clientes que poderão ver este relatório na Área do Cliente:', 'nozes' ) . '</p>';
	echo '<input type="hidden" name="nozes_clients_submitted" value="1">';
	echo '<div style="max-height:220px;overflow:auto;border:1px solid #dcdcde;border-radius:4px;padding:.5rem .7rem;">';
	foreach ( $clients as $client ) {
		printf(
			'<label style="display:flex;gap:.5em;align-items:center;padding:.3rem 0;line-height:1.3;"><input type="checkbox" name="nozes_relatorio_clients[]" value="%d"%s style="margin:0;flex-shrink:0;"> <span>%s</span></label>',
			(int) $client->ID,
			in_array( (int) $client->ID, $selected, true ) ? ' checked' : '',
			esc_html( $client->display_name . ' (' . $client->user_login . ')' )
		);
	}
	echo '</div>';
	echo '<p class="description" style="margin-top:.4rem;">' . esc_html__( 'Marque quantos clientes quiser. Todos os marcados veem o relatório — e recebem o e-mail, se a opção abaixo estiver marcada.', 'nozes' ) . '</p>';

	// Aviso por e-mail ao cliente (caixinha de opção).
	$last = get_post_meta( $post->ID, '_nozes_last_notified', true );
	echo '<hr style="margin:1rem 0;border:0;border-top:1px solid #dcdcde;">';
	echo '<label style="display:flex;gap:.5em;align-items:flex-start;line-height:1.4;">';
	echo '<input type="checkbox" name="nozes_notify_client" value="1" style="margin-top:2px;">';
	echo '<span>' . esc_html__( 'Avisar os clientes por e-mail ao salvar', 'nozes' ) . '</span>';
	echo '</label>';
	echo '<p class="description" style="margin-top:.5rem;">' . esc_html__( 'Envia a todos os clientes selecionados acima um e-mail avisando que há um novo relatório, com link para a Área do Cliente. Só é enviado se o relatório estiver publicado. O texto é editável em Personalizar → Configurações da Nozes.', 'nozes' ) . '</p>';
	if ( $last ) {
		echo '<p class="description" style="color:#2271b1;">' . sprintf(
			/* translators: %s: data/hora do último aviso */
			esc_html__( 'Último aviso enviado em %s.', 'nozes' ),
			esc_html( date_i18n( 'd/m/Y H:i', (int) $last ) )
		) . '</p>';
	}
}

/**
 * IDs de todos os clientes com acesso a um relatório: o autor (compatível com
 * relatórios antigos de cliente único) mais os clientes salvos em meta.
 */
function nozes_get_report_client_ids( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}
	$ids = array();
	if ( $post->post_author ) {
		$ids[] = (int) $post->post_author;
	}
	foreach ( (array) get_post_meta( $post->ID, '_nozes_client', false ) as $m ) {
		$ids[] = (int) $m;
	}
	return array_values( array_unique( array_filter( $ids ) ) );
}

/**
 * Texto padrão do e-mail de novo relatório (usado no Personalizador e no envio).
 * Marcadores disponíveis: {cliente}, {relatorio}, {link}, {site}.
 */
function nozes_report_email_default_body() {
	return "Olá, {cliente}!\n\n"
		. "Um novo relatório já está disponível na sua Área do Cliente: \"{relatorio}\".\n\n"
		. "Acesse com seu login para visualizar:\n{link}\n\n"
		. "Qualquer dúvida, é só responder este e-mail.\n\n"
		. "Atenciosamente,\n{site}";
}

/**
 * Envia ao cliente o aviso de novo relatório, usando o assunto/texto definidos
 * no Personalizador (com os marcadores {cliente} {relatorio} {link} {site}).
 * Retorna true se o e-mail foi disparado.
 */
function nozes_send_report_notification( $post_id, $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user || ! is_email( $user->user_email ) ) {
		return false;
	}

	$subject = get_theme_mod( 'nozes_report_email_subject', 'Seu novo relatório já está disponível' );
	$body    = get_theme_mod( 'nozes_report_email_body', nozes_report_email_default_body() );

	$replace = array(
		'{cliente}'   => $user->display_name,
		'{relatorio}' => get_the_title( $post_id ),
		'{link}'      => nozes_get_client_area_url( false ),
		'{site}'      => get_bloginfo( 'name' ),
	);
	$subject = strtr( $subject, $replace );
	$body    = strtr( $body, $replace );

	$sent = wp_mail( $user->user_email, $subject, $body );
	if ( $sent ) {
		update_post_meta( $post_id, '_nozes_last_notified', time() );
	}
	return $sent;
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
	// Se a caixa do metabox não veio no POST (ex.: edição rápida), não mexe.
	if ( ! isset( $_POST['nozes_clients_submitted'] ) ) {
		return;
	}

	// Clientes selecionados (um ou vários), mantendo só usuários válidos "Cliente".
	$ids = isset( $_POST['nozes_relatorio_clients'] ) ? array_map( 'absint', (array) wp_unslash( $_POST['nozes_relatorio_clients'] ) ) : array();
	$ids = array_values( array_unique( array_filter( $ids, function ( $id ) {
		$u = get_userdata( $id );
		return $u && nozes_user_is_client( $u );
	} ) ) );

	// Regrava a lista de clientes com acesso (uma linha de meta por cliente).
	delete_post_meta( $post_id, '_nozes_client' );
	foreach ( $ids as $id ) {
		add_post_meta( $post_id, '_nozes_client', $id );
	}

	// Autor = primeiro cliente selecionado (mantém compatibilidade com o WP).
	if ( ! empty( $ids ) && (int) $ids[0] !== (int) $post->post_author ) {
		remove_action( 'save_post_relatorio', 'nozes_save_relatorio_owner', 10 );
		wp_update_post( array(
			'ID'          => $post_id,
			'post_author' => (int) $ids[0],
		) );
		add_action( 'save_post_relatorio', 'nozes_save_relatorio_owner', 10, 2 );
	}

	// Caixinha "Avisar os clientes por e-mail": envia a todos os selecionados,
	// e só se o relatório estiver publicado (o cliente só vê relatórios publicados).
	if ( ! empty( $_POST['nozes_notify_client'] ) && 'publish' === $post->post_status ) {
		$recipients = ! empty( $ids ) ? $ids : array( (int) $post->post_author );
		foreach ( array_unique( $recipients ) as $rid ) {
			if ( $rid ) {
				nozes_send_report_notification( $post_id, $rid );
			}
		}
	}
}
add_action( 'save_post_relatorio', 'nozes_save_relatorio_owner', 10, 2 );

/**
 * Verifica se um usuário pode ver um relatório específico.
 * Administradores veem todos; o cliente vê os relatórios em que é o autor ou
 * está na lista de clientes com acesso (relatórios com mais de um cliente).
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
	if ( (int) $report->post_author === (int) $user_id ) {
		return true;
	}
	$allowed = array_map( 'intval', (array) get_post_meta( $report->ID, '_nozes_client', false ) );
	return in_array( (int) $user_id, $allowed, true );
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

	// Administrador vê todos.
	if ( user_can( $user_id, 'manage_options' ) ) {
		return get_posts( $args );
	}

	// Cliente vê os relatórios onde é o autor (compatível com o modelo antigo)
	// OU está na lista de clientes com acesso (relatórios com vários clientes).
	$by_author = get_posts( array_merge( $args, array( 'author' => $user_id, 'fields' => 'ids' ) ) );
	$by_meta   = get_posts( array_merge( $args, array(
		'fields'     => 'ids',
		'meta_key'   => '_nozes_client',
		'meta_value' => $user_id,
	) ) );

	$ids = array_unique( array_merge( (array) $by_author, (array) $by_meta ) );
	if ( empty( $ids ) ) {
		return array();
	}

	return get_posts( array_merge( $args, array( 'post__in' => $ids ) ) );
}

/**
 * Mantém o cliente conectado por mais tempo quando ele marca "Manter conectado".
 * Sem isso, o cookie de login padrão dura 14 dias; aqui estendemos para 30 dias,
 * para reduzir a chance de o cliente precisar logar de novo (ex.: ao navegar
 * pelo site e voltar à Área do Cliente).
 */
function nozes_client_cookie_expiration( $length, $user_id, $remember ) {
	if ( $remember ) {
		return 30 * DAY_IN_SECONDS;
	}
	return $length;
}
add_filter( 'auth_cookie_expiration', 'nozes_client_cookie_expiration', 10, 3 );

/**
 * Organiza os relatórios do cliente em categorias e subcategorias (taxonomia
 * "categoria_relatorio"). Cada relatório é colocado no seu termo mais específico
 * (a subcategoria, se houver; senão a categoria de topo). Retorna uma árvore
 * ordenada por nome, além dos relatórios sem categoria.
 *
 * Estrutura devolvida:
 *   array(
 *     'tree' => array( top_id => array(
 *         'term'    => WP_Term,           // categoria de topo
 *         'reports' => WP_Post[],         // relatórios direto na categoria
 *         'subs'    => array( sub_id => array( 'term' => WP_Term, 'reports' => WP_Post[] ) ),
 *     ) ),
 *     'uncategorized' => WP_Post[],       // relatórios sem nenhuma categoria
 *     'has_categories' => bool,
 *   )
 */
function nozes_group_reports_by_category( $reports ) {
	$taxonomy      = 'categoria_relatorio';
	$tree          = array();
	$uncategorized = array();

	foreach ( $reports as $report ) {
		$terms = get_the_terms( $report->ID, $taxonomy );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			$uncategorized[] = $report;
			continue;
		}

		// Entre os termos atribuídos, prefere o mais específico (com "pai").
		$chosen = $terms[0];
		foreach ( $terms as $t ) {
			if ( $t->parent && ! $chosen->parent ) {
				$chosen = $t;
			}
		}

		if ( $chosen->parent ) {
			$parent = get_term( $chosen->parent, $taxonomy );
			if ( $parent && ! is_wp_error( $parent ) ) {
				$top_id   = $parent->term_id;
				$top_term = $parent;
			} else {
				$top_id   = $chosen->term_id;
				$top_term = $chosen;
			}

			if ( ! isset( $tree[ $top_id ] ) ) {
				$tree[ $top_id ] = array( 'term' => $top_term, 'reports' => array(), 'subs' => array() );
			}
			if ( ! isset( $tree[ $top_id ]['subs'][ $chosen->term_id ] ) ) {
				$tree[ $top_id ]['subs'][ $chosen->term_id ] = array( 'term' => $chosen, 'reports' => array() );
			}
			$tree[ $top_id ]['subs'][ $chosen->term_id ]['reports'][] = $report;
		} else {
			$top_id = $chosen->term_id;
			if ( ! isset( $tree[ $top_id ] ) ) {
				$tree[ $top_id ] = array( 'term' => $chosen, 'reports' => array(), 'subs' => array() );
			}
			$tree[ $top_id ]['reports'][] = $report;
		}
	}

	// Ordena categorias e subcategorias por nome (mais previsível para o cliente).
	uasort( $tree, 'nozes_sort_groups_by_term_name' );
	foreach ( $tree as &$group ) {
		uasort( $group['subs'], 'nozes_sort_groups_by_term_name' );
	}
	unset( $group );

	return array(
		'tree'           => $tree,
		'uncategorized'  => $uncategorized,
		'has_categories' => ! empty( $tree ),
	);
}

/**
 * Comparador auxiliar: ordena dois grupos pelo nome do termo.
 */
function nozes_sort_groups_by_term_name( $a, $b ) {
	return strcasecmp( $a['term']->name, $b['term']->name );
}
