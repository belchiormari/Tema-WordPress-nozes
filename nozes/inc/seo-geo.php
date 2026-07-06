<?php
/**
 * SEO + GEO (Generative Engine Optimization)
 * ------------------------------------------------------------------
 * SEO tradicional: meta description, Open Graph, Twitter Card, canonical
 * e dados estruturados (JSON-LD) para Google.
 *
 * GEO: conteúdo estruturado e citável por ferramentas de IA (ChatGPT,
 * Perplexity, Google AI Overviews) — arquivo /llms.txt, respostas diretas
 * em FAQ com schema, e sinais claros de quem é a empresa (E-E-A-T).
 *
 * Observação: se um plugin de SEO (Rank Math, Yoast) estiver ativo, o tema
 * deixa de emitir meta tags e title-tag por conta própria, para não duplicar.
 * Isso pode ser reforçado manualmente na opção "Desativar dados estruturados"
 * no Personalizador.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nozes_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || class_exists( 'SEOPress' );
}

/**
 * Meta description, canonical e Open Graph/Twitter básicos.
 */
function nozes_meta_tags() {
	if ( nozes_seo_plugin_active() ) {
		return;
	}

	$description = '';
	$image       = '';
	$title       = wp_get_document_title();

	if ( is_singular() ) {
		global $post;
		$description = has_excerpt() ? get_the_excerpt() : wp_trim_words( wp_strip_all_tags( $post->post_content ), 32 );
		if ( has_post_thumbnail() ) {
			$image = get_the_post_thumbnail_url( $post, 'nozes-wide' );
		}
	} elseif ( is_front_page() ) {
		$description = get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) );
	} else {
		$description = get_bloginfo( 'description' );
	}

	$description = wp_strip_all_tags( $description );
	if ( empty( $image ) && has_custom_logo() ) {
		$logo_id = get_theme_mod( 'custom_logo' );
		$image   = $logo_id ? wp_get_attachment_image_url( $logo_id, 'full' ) : '';
	}

	echo "\n<!-- Nozes SEO -->\n";
	if ( $description ) {
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	echo '<link rel="canonical" href="' . esc_url( nozes_current_url() ) . '">' . "\n";

	echo '<meta property="og:type" content="' . ( is_singular( 'post' ) ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $description ) {
		echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	echo '<meta property="og:url" content="' . esc_url( nozes_current_url() ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	echo '<meta property="og:locale" content="pt_BR">' . "\n";
	if ( $image ) {
		echo '<meta property="og:image" content="' . esc_url( $image ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="' . ( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	if ( $description ) {
		echo '<meta name="twitter:description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	echo "<!-- /Nozes SEO -->\n";
}
add_action( 'wp_head', 'nozes_meta_tags', 1 );

function nozes_current_url() {
	global $wp;
	return home_url( add_query_arg( array(), $wp->request ) );
}

/**
 * Dados estruturados JSON-LD: Organization + LocalBusiness (site inteiro)
 * e Article (posts do blog). BreadcrumbList em todas as páginas internas.
 */
function nozes_schema_json_ld() {
	if ( get_theme_mod( 'nozes_disable_schema', false ) ) {
		return;
	}

	$graph = array();

	$logo = has_custom_logo() ? wp_get_attachment_image_url( get_theme_mod( 'custom_logo' ), 'full' ) : NOZES_URI . '/assets/img/logo-mark.png';

	$same_as = array_filter( array(
		get_theme_mod( 'nozes_social_instagram', '' ),
		get_theme_mod( 'nozes_social_linkedin', '' ),
	) );

	$organization = array(
		'@type'       => array( 'Organization', 'ProfessionalService' ),
		'@id'         => home_url( '/#organizacao' ),
		'name'        => get_bloginfo( 'name' ),
		'url'         => home_url( '/' ),
		'logo'        => $logo,
		'image'       => $logo,
		'description' => wp_strip_all_tags( get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) ) ),
		'telephone'   => get_theme_mod( 'nozes_phone_display', '' ),
		'email'       => get_theme_mod( 'nozes_email', '' ),
		'address'     => array(
			'@type'           => 'PostalAddress',
			'addressLocality' => get_theme_mod( 'nozes_address', 'Florianópolis, Santa Catarina, Brasil' ),
			'addressCountry'  => 'BR',
		),
		'areaServed'  => 'BR',
	);
	if ( ! empty( $same_as ) ) {
		$organization['sameAs'] = array_values( $same_as );
	}
	$graph[] = $organization;

	// BreadcrumbList
	if ( ! is_front_page() ) {
		$trail = nozes_get_breadcrumbs();
		$items = array();
		foreach ( $trail as $i => $item ) {
			$entry = array(
				'@type'    => 'ListItem',
				'position' => $i + 1,
				'name'     => $item['title'],
			);
			if ( ! empty( $item['url'] ) ) {
				$entry['item'] = $item['url'];
			}
			$items[] = $entry;
		}
		$graph[] = array(
			'@type'           => 'BreadcrumbList',
			'itemListElement' => $items,
		);
	}

	// Article (posts do blog)
	if ( is_singular( 'post' ) ) {
		global $post;
		$article = array(
			'@type'            => 'Article',
			'headline'         => get_the_title(),
			'datePublished'    => get_the_date( 'c' ),
			'dateModified'     => get_the_modified_date( 'c' ),
			'mainEntityOfPage' => get_permalink(),
			'author'           => array(
				'@type' => 'Person',
				'name'  => get_the_author(),
			),
			'publisher'        => array(
				'@id' => home_url( '/#organizacao' ),
			),
		);
		if ( has_post_thumbnail() ) {
			$article['image'] = get_the_post_thumbnail_url( $post, 'nozes-wide' );
		}
		$graph[] = $article;
	}

	if ( empty( $graph ) ) {
		return;
	}

	$schema = array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);

	echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}
add_action( 'wp_head', 'nozes_schema_json_ld', 5 );

/**
 * Shortcode de FAQ: renderiza um acordeão acessível e gera o schema FAQPage
 * automaticamente a partir do mesmo conteúdo (evita divergência entre o que
 * o visitante vê e o que a IA/buscador lê).
 *
 * Uso:
 * [nozes_faq]
 *   [nozes_faq_item pergunta="O que está incluso na assessoria?"]Resposta aqui.[/nozes_faq_item]
 *   [nozes_faq_item pergunta="Outra pergunta"]Outra resposta.[/nozes_faq_item]
 * [/nozes_faq]
 */
$GLOBALS['nozes_faq_items'] = array();

function nozes_faq_shortcode( $atts, $content = null ) {
	$GLOBALS['nozes_faq_items'] = array();
	$inner = do_shortcode( $content );

	$output = '<div class="nz-faq">' . $inner . '</div>';

	if ( ! empty( $GLOBALS['nozes_faq_items'] ) && ! get_theme_mod( 'nozes_disable_schema', false ) ) {
		$entities = array();
		foreach ( $GLOBALS['nozes_faq_items'] as $item ) {
			$entities[] = array(
				'@type'          => 'Question',
				'name'           => $item['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $item['a'],
				),
			);
		}
		$schema = array(
			'@context'   => 'https://schema.org',
			'@type'      => 'FAQPage',
			'mainEntity' => $entities,
		);
		$output .= '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
	}

	return $output;
}
add_shortcode( 'nozes_faq', 'nozes_faq_shortcode' );

function nozes_faq_item_shortcode( $atts, $content = null ) {
	$atts = shortcode_atts( array( 'pergunta' => '' ), $atts, 'nozes_faq_item' );

	$GLOBALS['nozes_faq_items'][] = array(
		'q' => wp_strip_all_tags( $atts['pergunta'] ),
		'a' => wp_strip_all_tags( $content ),
	);

	return sprintf(
		'<details><summary>%s</summary><div>%s</div></details>',
		esc_html( $atts['pergunta'] ),
		wp_kses_post( wpautop( trim( $content ) ) )
	);
}
add_shortcode( 'nozes_faq_item', 'nozes_faq_item_shortcode' );

/**
 * Shortcode invisível para descrever um serviço em Schema.org/Service,
 * usado dentro da página de Serviços (não altera a exibição visual).
 * Uso: [nozes_service_schema nome="Assessoria em Decisões de Negócio e Valorização de Marca"]Descrição do serviço.[/nozes_service_schema]
 */
function nozes_service_schema_shortcode( $atts, $content = null ) {
	if ( get_theme_mod( 'nozes_disable_schema', false ) ) {
		return '';
	}
	$atts = shortcode_atts( array( 'nome' => '' ), $atts, 'nozes_service_schema' );

	$schema = array(
		'@context'    => 'https://schema.org',
		'@type'       => 'Service',
		'name'        => wp_strip_all_tags( $atts['nome'] ),
		'description' => wp_strip_all_tags( $content ),
		'provider'    => array( '@id' => home_url( '/#organizacao' ) ),
		'areaServed'  => 'BR',
	);

	return '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
}
add_shortcode( 'nozes_service_schema', 'nozes_service_schema_shortcode' );

/**
 * /llms.txt — resumo em texto simples da empresa para IAs e buscadores
 * generativos entenderem rapidamente quem é a Nozes e o que ela oferece (GEO).
 */
function nozes_llms_rewrite_rule() {
	add_rewrite_rule( '^llms\.txt$', 'index.php?nozes_llms=1', 'top' );
}
add_action( 'init', 'nozes_llms_rewrite_rule' );

function nozes_llms_query_var( $vars ) {
	$vars[] = 'nozes_llms';
	return $vars;
}
add_filter( 'query_vars', 'nozes_llms_query_var' );

function nozes_llms_render() {
	if ( ! get_query_var( 'nozes_llms' ) ) {
		return;
	}

	header( 'Content-Type: text/plain; charset=utf-8' );

	$lines   = array();
	$lines[] = '# ' . get_bloginfo( 'name' );
	$lines[] = '';
	$lines[] = get_theme_mod( 'nozes_company_summary', get_bloginfo( 'description' ) );
	$lines[] = '';
	$lines[] = '## Contato';
	$lines[] = 'Telefone/WhatsApp: ' . get_theme_mod( 'nozes_phone_display', '' );
	$lines[] = 'E-mail: ' . get_theme_mod( 'nozes_email', '' );
	$lines[] = 'Endereço: ' . get_theme_mod( 'nozes_address', '' );
	$lines[] = 'Site: ' . home_url( '/' );
	$lines[] = '';
	$lines[] = '## Páginas principais';

	$pages = get_pages( array( 'sort_column' => 'menu_order' ) );
	foreach ( $pages as $page ) {
		$excerpt = has_excerpt( $page ) ? get_the_excerpt( $page ) : wp_trim_words( wp_strip_all_tags( $page->post_content ), 22 );
		$lines[] = '- ' . $page->post_title . ': ' . get_permalink( $page ) . ( $excerpt ? ' — ' . $excerpt : '' );
	}

	$lines[] = '';
	$lines[] = '## Blog';
	$lines[] = 'Artigos e conteúdos: ' . home_url( '/blog/' );

	echo implode( "\n", $lines );
	exit;
}
add_action( 'template_redirect', 'nozes_llms_render' );
