<?php
/**
 * Site pronto para agentes de IA
 * ------------------------------------------------------------------
 * Ajustes para que assistentes e agentes de IA entendam o site com
 * facilidade e saibam como podem usar o conteúdo:
 *
 * 1. Content-Signal no robots.txt (busca e citação sim, treino de IA não).
 * 2. Links institucionais no rodapé (Quem somos, Contato, Privacidade, Termos).
 * 3. Versão em Markdown de qualquer página quando o agente pede
 *    (cabeçalho "Accept: text/markdown").
 * 4. Dados para as ferramentas WebMCP registradas no assets/js/main.js.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/* -----------------------------------------------------------------
 * 1. Content-Signal no robots.txt
 * -----------------------------------------------------------------
 * Funciona com o robots.txt gerado pelo WordPress. Se existir um arquivo
 * robots.txt físico na raiz do site, ou se um plugin de SEO controlar o
 * robots, a linha precisa ser colada lá.
 */
function nozes_content_signal() {
	return apply_filters( 'nozes_content_signal', 'search=yes, ai-input=yes, ai-train=no' );
}

function nozes_robots_content_signal( $output, $public ) {
	if ( ! $public || false !== stripos( $output, 'Content-Signal' ) ) {
		return $output;
	}

	$comment = "# Content-Signal: search = indexar e mostrar em buscas; ai-input = usar para responder\n"
		. "# perguntas citando a fonte; ai-train = treinar modelos de IA (não autorizado).\n";
	$line    = 'Content-Signal: ' . nozes_content_signal();

	if ( preg_match( '/^User-agent:\s*\*\s*$/mi', $output ) ) {
		$output = preg_replace( '/^(User-agent:\s*\*\s*)$/mi', '$1' . "\n" . $line, $output, 1 );
		return $comment . $output;
	}

	return $comment . "User-agent: *\n" . $line . "\nAllow: /\n\n" . $output;
}
add_filter( 'robots_txt', 'nozes_robots_content_signal', 20, 2 );

/* -----------------------------------------------------------------
 * 2. Links institucionais
 * -----------------------------------------------------------------
 * Procura as páginas pelo endereço e só mostra as que existem. Em páginas
 * em inglês, usa a versão vinculada no outro idioma quando houver.
 */
function nozes_legal_links() {
	$pages = array(
		'quem-somos'              => __( 'Quem somos', 'nozes' ),
		'contato'                 => __( 'Contato', 'nozes' ),
		'politica-de-privacidade' => __( 'Política de Privacidade', 'nozes' ),
		'termos-de-uso'           => __( 'Termos de Uso', 'nozes' ),
	);

	$links = array();
	foreach ( $pages as $path => $label ) {
		$page = nozes_find_page( $path );
		if ( ! $page ) {
			continue;
		}
		$id = $page->ID;
		if ( function_exists( 'nozes_is_en' ) && nozes_is_en() ) {
			$translation = nozes_get_translation_id( $id );
			if ( $translation && 'publish' === get_post_status( $translation ) ) {
				$id = $translation;
			}
		}
		$links[] = array(
			'label' => $label,
			'url'   => get_permalink( $id ),
		);
	}
	return $links;
}

/**
 * Acha uma página publicada pelo slug, mesmo quando ela é filha de outra
 * (ex.: /home/quem-somos/).
 */
function nozes_find_page( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page && 'publish' === $page->post_status ) {
		return $page;
	}
	$found = get_posts( array(
		'post_type'        => 'page',
		'name'             => $slug,
		'post_status'      => 'publish',
		'numberposts'      => 1,
		'orderby'          => 'menu_order ID',
		'order'            => 'ASC',
		'suppress_filters' => false,
	) );
	return $found ? $found[0] : null;
}

function nozes_footer_legal_nav() {
	$links = nozes_legal_links();
	if ( empty( $links ) ) {
		return;
	}
	echo '<nav class="nz-footer__legal" aria-label="' . esc_attr__( 'Institucional', 'nozes' ) . '"><ul>';
	foreach ( $links as $link ) {
		echo '<li><a href="' . esc_url( $link['url'] ) . '">' . esc_html( $link['label'] ) . '</a></li>';
	}
	echo '</ul></nav>';
}

/* -----------------------------------------------------------------
 * 3. Markdown para agentes
 * -----------------------------------------------------------------
 * Quando a requisição pede "Accept: text/markdown", a página é entregue
 * em Markdown, sem menu, rodapé, CSS e scripts. Visitantes comuns
 * continuam recebendo o HTML de sempre.
 */
function nozes_wants_markdown() {
	$accept = isset( $_SERVER['HTTP_ACCEPT'] ) ? strtolower( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) : '';
	return false !== strpos( $accept, 'text/markdown' );
}

/**
 * Avisa caches e proxies que a resposta muda conforme o cabeçalho Accept.
 */
function nozes_vary_accept( $headers ) {
	if ( is_admin() ) {
		return $headers;
	}
	$headers['Vary'] = isset( $headers['Vary'] ) && $headers['Vary'] ? $headers['Vary'] . ', Accept' : 'Accept';
	return $headers;
}
add_filter( 'wp_headers', 'nozes_vary_accept' );

/**
 * Converte HTML em Markdown simples: títulos, parágrafos, listas, links,
 * negrito, itálico, imagens (texto alternativo) e citações.
 */
function nozes_html_to_markdown( $html ) {
	// Remove o que não é conteúdo.
	$html = preg_replace( '#<(script|style|noscript|svg|iframe|form|template)\b[^>]*>.*?</\1>#is', '', $html );
	$html = preg_replace( '#<!--.*?-->#s', '', $html );

	// Títulos.
	$html = preg_replace_callback(
		'#<h([1-6])\b[^>]*>(.*?)</h\1>#is',
		function ( $m ) {
			$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $m[2] ) ) );
			return $text ? "\n\n" . str_repeat( '#', (int) $m[1] ) . ' ' . $text . "\n\n" : '';
		},
		$html
	);

	// Imagens: só o texto alternativo, quando houver.
	$html = preg_replace_callback(
		'#<img\b[^>]*>#i',
		function ( $m ) {
			if ( preg_match( '#alt=(["\'])(.*?)\1#i', $m[0], $alt ) && trim( $alt[2] ) ) {
				return '[Imagem: ' . trim( $alt[2] ) . ']';
			}
			return '';
		},
		$html
	);

	// Links.
	$html = preg_replace_callback(
		'#<a\b[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>#is',
		function ( $m ) {
			$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( $m[3] ) ) );
			$href = html_entity_decode( $m[2], ENT_QUOTES, 'UTF-8' );
			if ( '' === $text ) {
				return '';
			}
			if ( '' === $href || '#' === $href[0] || 0 === stripos( $href, 'javascript:' ) ) {
				return $text;
			}
			if ( '/' === $href[0] && ( ! isset( $href[1] ) || '/' !== $href[1] ) ) {
				$href = home_url( $href );
			}
			return '[' . $text . '](' . $href . ')';
		},
		$html
	);

	// Ênfases.
	$html = preg_replace( '#<(strong|b)\b[^>]*>(.*?)</\1>#is', '**$2**', $html );
	$html = preg_replace( '#<(em|i)\b[^>]*>(.*?)</\1>#is', '*$2*', $html );

	// Listas, citações e quebras.
	$html = preg_replace( '#<li\b[^>]*>#i', "\n- ", $html );
	$html = preg_replace( '#<blockquote\b[^>]*>#i', "\n\n> ", $html );
	$html = preg_replace( '#<br\s*/?>#i', "\n", $html );
	$html = preg_replace( '#</(p|div|section|article|header|footer|ul|ol|blockquote|table|tr|details|summary|figure)>#i', "\n\n", $html );
	$html = preg_replace( '#</(li|td|th)>#i', "\n", $html );

	$text = wp_strip_all_tags( $html );
	$text = html_entity_decode( $text, ENT_QUOTES | ENT_HTML5, 'UTF-8' );

	// Limpa espaços e linhas em branco em excesso.
	$lines = array_map(
		function ( $line ) {
			return rtrim( preg_replace( '/[ \t\x{00A0}]+/u', ' ', $line ) );
		},
		explode( "\n", $text )
	);
	$lines = array_map( 'ltrim', $lines );
	$text  = implode( "\n", $lines );
	$text  = preg_replace( "/\n- \n/", "\n", $text );
	$text  = preg_replace( "/\n{3,}/", "\n\n", $text );

	return trim( $text );
}

function nozes_markdown_render() {
	if ( ! nozes_wants_markdown() || is_admin() || is_feed() || is_robots() || is_404() || get_query_var( 'nozes_llms' ) ) {
		return;
	}

	// A Área do Cliente e páginas protegidas continuam só em HTML.
	if ( is_singular() ) {
		$post = get_queried_object();
		if ( post_password_required( $post ) || 'template-area-cliente.php' === get_page_template_slug( $post ) || 'relatorio' === get_post_type( $post ) ) {
			return;
		}
	}

	$out   = array();
	$title = '';

	if ( is_singular() ) {
		$post  = get_queried_object();
		$title = get_the_title( $post );
		setup_postdata( $GLOBALS['post'] = $post );
		$content = apply_filters( 'the_content', $post->post_content );
		wp_reset_postdata();

		if ( is_front_page() ) {
			$out[] = '# ' . get_bloginfo( 'name' );
		} else {
			$out[] = '# ' . $title;
		}
		$out[] = '';
		$out[] = 'URL: ' . get_permalink( $post );
		if ( is_singular( 'post' ) ) {
			$out[] = 'Publicado em: ' . get_the_date( 'Y-m-d', $post ) . ' | Autoria: ' . get_the_author_meta( 'display_name', $post->post_author );
		}
		$out[] = '';
		$out[] = nozes_html_to_markdown( $content );
	} else {
		// Blog, categorias, busca e home com lista de posts.
		$out[] = '# ' . wp_strip_all_tags( wp_get_document_title() );
		$out[] = '';
		$out[] = 'URL: ' . nozes_current_url();
		$out[] = '';
		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				$out[] = '## [' . get_the_title() . '](' . get_permalink() . ')';
				$out[] = get_the_date( 'Y-m-d' ) . ' | ' . wp_strip_all_tags( get_the_excerpt() );
				$out[] = '';
			}
			wp_reset_postdata();
		}
	}

	// Contato no fim de toda página, para o agente não precisar procurar.
	$out[] = '';
	$out[] = '---';
	$out[] = '';
	$out[] = '## ' . get_bloginfo( 'name' );
	$out[] = wp_strip_all_tags( get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) ) );
	$out[] = '';
	$out[] = '- Telefone/WhatsApp: ' . nozes_business_phone() . ' (' . nozes_get_whatsapp_link() . ')';
	$out[] = '- E-mail: ' . get_theme_mod( 'nozes_footer_email', 'ola@somosnozes.com.br' );
	$out[] = '- Endereço: ' . implode( ', ', array_filter( array( isset( nozes_postal_address()['streetAddress'] ) ? nozes_postal_address()['streetAddress'] : '', 'Florianópolis', 'SC', 'Brasil' ) ) );
	foreach ( nozes_legal_links() as $link ) {
		$out[] = '- ' . $link['label'] . ': ' . $link['url'];
	}

	$markdown = trim( implode( "\n", $out ) ) . "\n";

	// Evita que o cache do LiteSpeed (Hostinger) guarde a versão em Markdown
	// e entregue para visitantes comuns.
	do_action( 'litespeed_control_set_nocache', 'nozes markdown' );
	if ( ! defined( 'DONOTCACHEPAGE' ) ) {
		define( 'DONOTCACHEPAGE', true );
	}

	status_header( 200 );
	header( 'Content-Type: text/markdown; charset=utf-8' );
	header( 'Vary: Accept' );
	header( 'Cache-Control: no-store' );
	header( 'X-Robots-Tag: noindex' );
	header( 'Link: <' . esc_url_raw( is_singular() ? get_permalink( get_queried_object() ) : nozes_current_url() ) . '>; rel="canonical"' );
	echo $markdown; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- texto puro.
	exit;
}
add_action( 'template_redirect', 'nozes_markdown_render', 5 );

/* -----------------------------------------------------------------
 * 4. Dados para as ferramentas WebMCP (assets/js/main.js)
 * ----------------------------------------------------------------- */
function nozes_webmcp_data() {
	if ( ! wp_script_is( 'nozes-main', 'enqueued' ) ) {
		return;
	}
	$pages = array();
	foreach ( nozes_legal_links() as $link ) {
		$pages[] = $link;
	}
	$data = array(
		'name'      => get_bloginfo( 'name' ),
		'summary'   => wp_strip_all_tags( get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) ) ),
		'phone'     => nozes_business_phone(),
		'email'     => get_theme_mod( 'nozes_footer_email', 'ola@somosnozes.com.br' ),
		'address'   => 'Florianópolis, SC, Brasil',
		'whatsapp'  => preg_replace( '/\D/', '', get_theme_mod( 'nozes_whatsapp_number', '554830507538' ) ),
		'siteUrl'   => home_url( '/' ),
		'blogUrl'   => home_url( '/blog/' ),
		'searchApi' => esc_url_raw( rest_url( 'wp/v2/search' ) ),
		'pages'     => $pages,
	);
	wp_add_inline_script( 'nozes-main', 'window.nozesAgent = ' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . ';', 'before' );
}
add_action( 'wp_enqueue_scripts', 'nozes_webmcp_data', 20 );

/* -----------------------------------------------------------------
 * 5. Rank Math: completa o schema da empresa
 * -----------------------------------------------------------------
 * O Rank Math gera o bloco LocalBusiness/Organization sem telefone e
 * endereço quando o SEO Local dele não está preenchido. Aqui o tema
 * completa só o que estiver faltando, com os dados do Personalizador.
 */
function nozes_rank_math_business( $data ) {
	if ( ! is_array( $data ) ) {
		return $data;
	}
	foreach ( $data as $key => $entity ) {
		if ( ! is_array( $entity ) || empty( $entity['@type'] ) ) {
			continue;
		}
		$types = (array) $entity['@type'];
		if ( ! array_intersect( $types, array( 'Organization', 'LocalBusiness', 'ProfessionalService' ) ) ) {
			continue;
		}
		if ( empty( $entity['telephone'] ) ) {
			$entity['telephone'] = nozes_business_phone();
		}
		if ( empty( $entity['address'] ) ) {
			$entity['address'] = nozes_postal_address();
		}
		if ( empty( $entity['email'] ) ) {
			$entity['email'] = get_theme_mod( 'nozes_footer_email', 'ola@somosnozes.com.br' );
		}
		if ( empty( $entity['description'] ) ) {
			$entity['description'] = wp_strip_all_tags( get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) ) );
		}
		if ( empty( $entity['sameAs'] ) ) {
			$same_as = array_values( array_filter( array(
				get_theme_mod( 'nozes_social_instagram', '' ),
				get_theme_mod( 'nozes_social_linkedin', '' ),
			) ) );
			if ( $same_as ) {
				$entity['sameAs'] = $same_as;
			}
		}
		$entity['areaServed'] = isset( $entity['areaServed'] ) ? $entity['areaServed'] : 'BR';
		$data[ $key ]         = $entity;
	}
	return $data;
}
add_filter( 'rank_math/json_ld', 'nozes_rank_math_business', 99 );

/* -----------------------------------------------------------------
 * 6. LiteSpeed: deixa as ferramentas WebMCP rodarem no carregamento
 * -----------------------------------------------------------------
 * O LiteSpeed Cache atrasa os scripts até o visitante mexer na página.
 * Agentes leem a página assim que ela carrega, então o script do tema
 * (que é pequeno) fica fora desse atraso.
 */
function nozes_script_no_delay( $tag, $handle ) {
	if ( 'nozes-main' !== $handle || false !== strpos( $tag, 'data-no-defer' ) ) {
		return $tag;
	}
	return str_replace( '<script ', '<script data-no-optimize="1" data-no-defer="1" ', $tag );
}
add_filter( 'script_loader_tag', 'nozes_script_no_delay', 10, 2 );

function nozes_inline_script_no_delay( $attributes ) {
	if ( isset( $attributes['id'] ) && 0 === strpos( $attributes['id'], 'nozes-main-js' ) ) {
		$attributes['data-no-optimize'] = '1';
		$attributes['data-no-defer']    = '1';
	}
	return $attributes;
}
add_filter( 'wp_inline_script_attributes', 'nozes_inline_script_no_delay' );

/* -----------------------------------------------------------------
 * 7. LiteSpeed: pedidos em Markdown não usam o cache
 * -----------------------------------------------------------------
 * Sem isso, o cache entrega o HTML guardado mesmo quando o agente pede
 * Markdown. A regra entra no .htaccess uma única vez, num bloco próprio.
 */
function nozes_htaccess_markdown_rule() {
	if ( '1' === get_option( 'nozes_htaccess_markdown' ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/misc.php';
	$file = get_home_path() . '.htaccess';
	if ( ! file_exists( $file ) || ! is_writable( $file ) ) {
		return;
	}
	$rules = array(
		'<IfModule LiteSpeed>',
		'RewriteEngine On',
		'RewriteCond %{HTTP:Accept} text/markdown [NC]',
		'RewriteRule .* - [E=Cache-Control:no-cache]',
		'</IfModule>',
	);
	if ( insert_with_markers( $file, 'Nozes Markdown', $rules ) ) {
		update_option( 'nozes_htaccess_markdown', '1', false );
	}
}
add_action( 'admin_init', 'nozes_htaccess_markdown_rule' );
