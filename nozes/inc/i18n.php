<?php
/**
 * Site em inglês sem plugin — só para as páginas fixas (Início, Sobre,
 * Serviços, Contato). Cada página pode ser marcada como pt-BR ou en-US e
 * vinculada à sua correspondente no outro idioma; o vínculo é bidirecional
 * (definir de um lado atualiza o outro sozinho). A partir disso o tema:
 *
 * - mostra o seletor PT/EN no cabeçalho;
 * - troca os links do menu para a versão no idioma certo;
 * - ajusta o atributo lang do <html>, o og:locale e as tags hreflang;
 * - traduz os textos fixos do tema (menu, rodapé, botão de WhatsApp,
 *   breadcrumbs, página 404) via o filtro nativo "gettext" do WordPress.
 *
 * O Blog e a Área do Cliente ficam de fora — atendem só o público brasileiro.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Metabox "Idioma da página" — aparece na edição de qualquer página.
 */
function nozes_i18n_meta_box() {
	add_meta_box(
		'nozes_i18n',
		__( 'Idioma da página', 'nozes' ),
		'nozes_i18n_meta_box_render',
		'page',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'nozes_i18n_meta_box' );

function nozes_i18n_meta_box_render( $post ) {
	wp_nonce_field( 'nozes_i18n_save', 'nozes_i18n_nonce' );

	$lang           = nozes_get_lang( $post->ID );
	$translation_id = nozes_get_translation_id( $post->ID );
	$pages          = get_pages( array(
		'exclude'     => array( $post->ID ),
		'sort_column' => 'post_title',
	) );
	?>
	<p>
		<label for="nozes_lang"><strong><?php esc_html_e( 'Idioma desta página', 'nozes' ); ?></strong></label><br>
		<select name="nozes_lang" id="nozes_lang" style="width:100%;">
			<option value="pt" <?php selected( $lang, 'pt' ); ?>>Português (pt-BR)</option>
			<option value="en" <?php selected( $lang, 'en' ); ?>>English (en-US)</option>
		</select>
	</p>
	<p>
		<label for="nozes_translation_id"><strong><?php esc_html_e( 'Página correspondente no outro idioma', 'nozes' ); ?></strong></label><br>
		<select name="nozes_translation_id" id="nozes_translation_id" style="width:100%;">
			<option value="0"><?php esc_html_e( '— Nenhuma —', 'nozes' ); ?></option>
			<?php foreach ( $pages as $page ) : ?>
				<option value="<?php echo esc_attr( $page->ID ); ?>" <?php selected( $translation_id, $page->ID ); ?>>
					<?php echo esc_html( $page->post_title ); ?>
				</option>
			<?php endforeach; ?>
		</select><br>
		<small><?php esc_html_e( 'Ao salvar, o vínculo é feito nos dois sentidos automaticamente: a outra página passa a apontar de volta para esta.', 'nozes' ); ?></small>
	</p>
	<?php
}

function nozes_i18n_save_meta( $post_id ) {
	if ( ! isset( $_POST['nozes_i18n_nonce'] ) || ! wp_verify_nonce( $_POST['nozes_i18n_nonce'], 'nozes_i18n_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_page', $post_id ) ) {
		return;
	}

	$lang = isset( $_POST['nozes_lang'] ) && 'en' === $_POST['nozes_lang'] ? 'en' : 'pt';
	update_post_meta( $post_id, '_nozes_lang', $lang );

	$translation_id  = isset( $_POST['nozes_translation_id'] ) ? absint( $_POST['nozes_translation_id'] ) : 0;
	$previous_transl = nozes_get_translation_id( $post_id );

	if ( $previous_transl && $previous_transl !== $translation_id && $post_id === nozes_get_translation_id( $previous_transl ) ) {
		delete_post_meta( $previous_transl, '_nozes_translation_id' );
	}

	if ( $translation_id ) {
		update_post_meta( $post_id, '_nozes_translation_id', $translation_id );
		update_post_meta( $translation_id, '_nozes_translation_id', $post_id );
	} else {
		delete_post_meta( $post_id, '_nozes_translation_id' );
	}
}
add_action( 'save_post_page', 'nozes_i18n_save_meta' );

/** ---- Funções auxiliares ---- */

function nozes_get_lang( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	$lang    = get_post_meta( $post_id, '_nozes_lang', true );
	return 'en' === $lang ? 'en' : 'pt';
}

function nozes_get_translation_id( $post_id = 0 ) {
	$post_id = $post_id ? $post_id : get_queried_object_id();
	return (int) get_post_meta( $post_id, '_nozes_translation_id', true );
}

function nozes_is_en() {
	return is_page() && 'en' === nozes_get_lang();
}

/**
 * Seletor de idioma no cabeçalho — só aparece quando a página atual tem uma
 * correspondente publicada no outro idioma.
 */
function nozes_language_switcher() {
	if ( ! is_page() ) {
		return;
	}

	$post_id        = get_queried_object_id();
	$translation_id = nozes_get_translation_id( $post_id );
	if ( ! $translation_id || 'publish' !== get_post_status( $translation_id ) ) {
		return;
	}

	$target_lang = 'en' === nozes_get_lang( $post_id ) ? 'pt' : 'en';
	$label       = 'en' === $target_lang ? 'EN' : 'PT';
	$hreflang    = 'en' === $target_lang ? 'en' : 'pt-BR';

	printf(
		'<a class="nz-lang-switch" href="%s" hreflang="%s" lang="%s">%s</a>',
		esc_url( get_permalink( $translation_id ) ),
		esc_attr( $hreflang ),
		esc_attr( $hreflang ),
		esc_html( $label )
	);
}

/**
 * Local de menu a usar na página atual. Numa página em inglês, usa o menu
 * em inglês se houver um montado em Aparência → Menus; caso contrário cai no
 * menu em português, que é traduzido item a item por nozes_i18n_nav_menu_items().
 */
function nozes_menu_location( $location ) {
	if ( nozes_is_en() && has_nav_menu( $location . '_en' ) ) {
		return $location . '_en';
	}
	return $location;
}

/**
 * Troca os itens do menu (principal e rodapé) para a versão em inglês quando
 * a página atual é em inglês — assim não é preciso manter um menu separado.
 */
function nozes_i18n_nav_menu_items( $items ) {
	if ( ! nozes_is_en() ) {
		return $items;
	}
	foreach ( $items as $item ) {
		if ( 'page' !== $item->object ) {
			continue;
		}
		$translation_id = nozes_get_translation_id( $item->object_id );
		if ( $translation_id && 'publish' === get_post_status( $translation_id ) ) {
			$item->url   = get_permalink( $translation_id );
			$item->title = get_the_title( $translation_id );
		}
	}
	return $items;
}
add_filter( 'wp_nav_menu_objects', 'nozes_i18n_nav_menu_items' );

/**
 * Atributo lang do <html> e og:locale corretos numa página em inglês.
 */
function nozes_i18n_language_attributes( $output ) {
	if ( nozes_is_en() ) {
		$output = preg_replace( '/lang="[^"]*"/', 'lang="en-US"', $output, 1 );
	}
	return $output;
}
add_filter( 'language_attributes', 'nozes_i18n_language_attributes' );

/**
 * Tags hreflang no <head> ligando as duas versões (bom para o Google não
 * tratar a versão em inglês como conteúdo duplicado).
 */
function nozes_i18n_hreflang_tags() {
	if ( ! is_page() ) {
		return;
	}
	$post_id        = get_queried_object_id();
	$translation_id = nozes_get_translation_id( $post_id );
	if ( ! $translation_id || 'publish' !== get_post_status( $translation_id ) ) {
		return;
	}

	$lang       = nozes_get_lang( $post_id );
	$pt_id      = 'en' === $lang ? $translation_id : $post_id;
	$en_id      = 'en' === $lang ? $post_id : $translation_id;

	printf( '<link rel="alternate" hreflang="pt-BR" href="%s">' . "\n", esc_url( get_permalink( $pt_id ) ) );
	printf( '<link rel="alternate" hreflang="en" href="%s">' . "\n", esc_url( get_permalink( $en_id ) ) );
	printf( '<link rel="alternate" hreflang="x-default" href="%s">' . "\n", esc_url( get_permalink( $pt_id ) ) );
}
add_action( 'wp_head', 'nozes_i18n_hreflang_tags', 2 );

/**
 * Traduções das strings fixas do tema (menu, rodapé, WhatsApp, breadcrumbs,
 * 404) para quando a página atual está marcada como inglês. Usa o filtro
 * nativo "gettext" do WordPress, então nenhum outro arquivo do tema precisa
 * ser alterado: continuam chamando __()/_e() normalmente.
 */
function nozes_i18n_strings() {
	return array(
		'Pular para o conteúdo'  => 'Skip to content',
		'Menu principal'         => 'Main menu',
		'Meus relatórios'        => 'My reports',
		'Área do Cliente'        => 'Client Area',
		'Falar no WhatsApp'      => 'Chat on WhatsApp',
		'Chamar no WhatsApp'     => 'Chat on WhatsApp',
		'Abrir menu'             => 'Open menu',
		'Navegação'              => 'Navigation',
		'Acessar relatórios'     => 'Access reports',
		'Nozes Estratégia de Marca.' => 'Nozes Brand Strategy.',
		'Todos os direitos reservados.' => 'All rights reserved.',
		'Início'                 => 'Home',
		'Resultados da busca'    => 'Search results',
		'Página não encontrada'  => 'Page not found',
		'Trilha de navegação'    => 'Breadcrumb',
		'Erro 404'               => '404 Error',
		'Essa página saiu de rota.' => 'This page seems to have wandered off.',
		'O conteúdo que você procura não existe ou foi movido. Que tal voltar para o início ou falar com a gente no WhatsApp?' => 'The page you are looking for does not exist or has moved. Try heading back home or reaching out to us on WhatsApp.',
		'Voltar ao início'       => 'Back to home',
	);
}

function nozes_i18n_gettext( $translated, $text, $domain ) {
	if ( 'nozes' !== $domain || ! nozes_is_en() ) {
		return $translated;
	}
	$map = nozes_i18n_strings();
	return isset( $map[ $text ] ) ? $map[ $text ] : $translated;
}
add_filter( 'gettext', 'nozes_i18n_gettext', 10, 3 );
