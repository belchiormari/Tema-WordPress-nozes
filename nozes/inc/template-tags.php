<?php
/**
 * Funções auxiliares usadas nos templates: breadcrumbs, ícones SVG e botão de WhatsApp.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Monta a trilha de navegação (breadcrumbs) da página atual.
 * Reaproveitada tanto na exibição visual quanto no schema BreadcrumbList.
 */
function nozes_get_breadcrumbs() {
	$trail = array(
		array( 'title' => __( 'Início', 'nozes' ), 'url' => home_url( '/' ) ),
	);

	if ( is_singular( 'post' ) || is_home() ) {
		$trail[] = array( 'title' => __( 'Blog', 'nozes' ), 'url' => get_permalink( get_option( 'page_for_posts' ) ) ? get_permalink( get_option( 'page_for_posts' ) ) : home_url( '/blog/' ) );
		if ( is_singular( 'post' ) ) {
			$trail[] = array( 'title' => get_the_title(), 'url' => get_permalink() );
		}
	} elseif ( is_page() && ! is_front_page() ) {
		$post = get_queried_object();
		if ( $post && $post->post_parent ) {
			$trail[] = array( 'title' => get_the_title( $post->post_parent ), 'url' => get_permalink( $post->post_parent ) );
		}
		$trail[] = array( 'title' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_search() ) {
		$trail[] = array( 'title' => __( 'Resultados da busca', 'nozes' ), 'url' => '' );
	} elseif ( is_404() ) {
		$trail[] = array( 'title' => __( 'Página não encontrada', 'nozes' ), 'url' => '' );
	} elseif ( is_archive() ) {
		$trail[] = array( 'title' => get_the_archive_title(), 'url' => '' );
	}

	return $trail;
}

/**
 * Exibe as breadcrumbs visíveis (bom para SEO e para o usuário se orientar).
 */
function nozes_breadcrumbs() {
	if ( is_front_page() ) {
		return;
	}
	$trail = nozes_get_breadcrumbs();
	if ( count( $trail ) < 2 ) {
		return;
	}
	echo '<nav class="nz-breadcrumbs" aria-label="' . esc_attr__( 'Trilha de navegação', 'nozes' ) . '">';
	$parts = array();
	foreach ( $trail as $i => $item ) {
		if ( $item['url'] && ( $i < count( $trail ) - 1 ) ) {
			$parts[] = '<a href="' . esc_url( $item['url'] ) . '">' . esc_html( $item['title'] ) . '</a>';
		} else {
			$parts[] = '<span>' . esc_html( $item['title'] ) . '</span>';
		}
	}
	echo wp_kses_post( implode( ' <span aria-hidden="true">/</span> ', $parts ) );
	echo '</nav>';
}

/**
 * Botão flutuante de WhatsApp (aparece em todas as páginas, se ativado no Personalizador).
 */
function nozes_whatsapp_float() {
	if ( ! get_theme_mod( 'nozes_whatsapp_float_show', true ) ) {
		return;
	}
	printf(
		'<a class="nz-whatsapp-float" href="%s" target="_blank" rel="noopener noreferrer" aria-label="%s">%s</a>',
		esc_url( nozes_get_whatsapp_link() ),
		esc_attr__( 'Falar no WhatsApp', 'nozes' ),
		nozes_icon( 'whatsapp' )
	);
}

/**
 * Pequena biblioteca de ícones SVG inline (leve, sem dependências externas).
 */
function nozes_icon( $name ) {
	$icons = array(
		'whatsapp' => '<svg viewBox="0 0 32 32" fill="currentColor" aria-hidden="true"><path d="M16.02 3C9.4 3 4 8.4 4 15.02c0 2.35.66 4.55 1.8 6.44L4 29l7.72-1.75a12.9 12.9 0 0 0 4.3.73h.01c6.62 0 12.02-5.4 12.02-12.02C28.05 8.4 22.65 3 16.02 3zm0 21.9c-1.5 0-2.97-.38-4.26-1.1l-.3-.18-4.58 1.04 1.06-4.47-.2-.32a9.9 9.9 0 0 1-1.53-5.27c0-5.46 4.45-9.9 9.91-9.9 2.65 0 5.14 1.03 7 2.9a9.85 9.85 0 0 1 2.9 7c0 5.47-4.45 9.9-9.9 9.9zm5.44-7.42c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.65.07-.3-.15-1.24-.46-2.36-1.46-.87-.78-1.46-1.73-1.63-2.03-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.07-.15-.67-1.6-.91-2.2-.24-.58-.48-.5-.67-.5h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.87 1.22 3.06.15.2 2.1 3.2 5.1 4.49.71.31 1.27.49 1.7.63.72.23 1.37.2 1.88.12.57-.09 1.76-.72 2.01-1.41.25-.7.25-1.3.17-1.42-.07-.13-.27-.2-.57-.35z"/></svg>',
		'instagram' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1.1" fill="currentColor" stroke="none"/></svg>',
		'linkedin' => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.94 5a1.94 1.94 0 1 1-3.88 0 1.94 1.94 0 0 1 3.88 0zM3.4 8.4h3.06V21H3.4V8.4zm5.2 0h2.93v1.72h.04c.41-.77 1.4-1.58 2.89-1.58 3.09 0 3.66 2.03 3.66 4.67V21h-3.05v-5.6c0-1.34-.02-3.06-1.86-3.06-1.87 0-2.16 1.46-2.16 2.96V21H8.6V8.4z"/></svg>',
		'menu'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>',
		'close'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="5" y1="5" x2="19" y2="19"/><line x1="19" y1="5" x2="5" y2="19"/></svg>',
		'arrow'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>',
		'print'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>',
		'compass'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>',
		'target'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1"/></svg>',
		'trend'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="3 17 9 11 13 15 21 6"/><polyline points="14 6 21 6 21 13"/></svg>',
		'shield'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 3l7 3v6c0 5-3.5 8-7 9-3.5-1-7-4-7-9V6z"/></svg>',
		'user'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 3.6-7 8-7s8 3 8 7"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * SVG decorativo (o "rabisco" da marca Nozes) usado em seções de destaque.
 */
function nozes_scribble( $color = 'var(--nz-lime)', $class = '' ) {
	printf(
		'<svg class="%s" width="220" height="260" viewBox="0 0 220 260" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M20 40C40 10 90 10 100 40C110 70 60 80 70 110C80 140 150 120 160 150C170 180 110 190 120 220C128 244 160 250 190 240" stroke="%s" stroke-width="6" stroke-linecap="round"/></svg>',
		esc_attr( $class ),
		esc_attr( $color )
	);
}

/**
 * Verifica se a página atual foi montada com o Elementor — nesses casos o
 * tema não deve aplicar seu próprio container/padding, deixando o Elementor
 * controlar 100% do layout da página.
 */
function nozes_is_built_with_elementor() {
	if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Plugin' ) ) {
		return false;
	}
	$document = \Elementor\Plugin::$instance->documents->get( get_the_ID() );
	return $document && $document->is_built_with_elementor();
}

/**
 * Menu de rodapé de reserva: se nenhum menu foi atribuído ao local "Menu do
 * Rodapé", mostra o menu principal (ou as páginas principais), para a coluna
 * "Navegação" nunca ficar vazia.
 */
function nozes_footer_menu_fallback() {
	if ( has_nav_menu( 'primary' ) ) {
		wp_nav_menu( array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => '',
			'depth'          => 1,
			'fallback_cb'    => false,
		) );
		return;
	}
	echo '<ul>';
	wp_list_pages( array(
		'title_li'    => '',
		'depth'       => 1,
		'sort_column' => 'menu_order,post_title',
		'number'      => 6,
	) );
	echo '</ul>';
}

/**
 * Paginação padrão (arquivo do blog).
 */
function nozes_pagination() {
	the_posts_pagination( array(
		'mid_size'  => 1,
		'prev_text' => __( '← Anteriores', 'nozes' ),
		'next_text' => __( 'Próximos →', 'nozes' ),
	) );
}

/**
 * Pequenos shortcodes para manter dados de contato editáveis num único lugar
 * (Personalizador) mesmo quando usados dentro do texto das páginas.
 */
add_shortcode( 'nozes_telefone', function() {
	return esc_html( get_theme_mod( 'nozes_phone_display', '' ) );
} );

add_shortcode( 'nozes_email', function() {
	return esc_html( get_theme_mod( 'nozes_email', '' ) );
} );

add_shortcode( 'nozes_endereco', function() {
	return esc_html( get_theme_mod( 'nozes_address', '' ) );
} );

add_shortcode( 'nozes_whatsapp_link', function( $atts ) {
	$atts = shortcode_atts( array( 'texto' => 'Falar no WhatsApp' ), $atts, 'nozes_whatsapp_link' );
	return sprintf(
		'<a class="nz-btn nz-btn--primary" href="%s" target="_blank" rel="noopener noreferrer">%s %s</a>',
		esc_url( nozes_get_whatsapp_link() ),
		esc_html( $atts['texto'] ),
		nozes_icon( 'whatsapp' )
	);
} );

/**
 * Lista os últimos posts do blog no layout de cards — usado na Home.
 * Uso: [nozes_latest_posts quantidade="3"]
 */
add_shortcode( 'nozes_latest_posts', function( $atts ) {
	$atts = shortcode_atts( array( 'quantidade' => 3 ), $atts, 'nozes_latest_posts' );

	$query = new WP_Query( array(
		'post_type'      => 'post',
		'posts_per_page' => (int) $atts['quantidade'],
		'ignore_sticky_posts' => true,
	) );

	if ( ! $query->have_posts() ) {
		return '';
	}

	ob_start();
	echo '<div class="nz-grid nz-grid--3">';
	while ( $query->have_posts() ) {
		$query->the_post();
		?>
		<a class="nz-post-card" href="<?php the_permalink(); ?>">
			<div class="nz-post-card__media">
				<?php if ( has_post_thumbnail() ) : ?>
					<?php the_post_thumbnail( 'nozes-card' ); ?>
				<?php endif; ?>
			</div>
			<div class="nz-post-card__body">
				<?php $cats = get_the_category(); ?>
				<?php if ( ! empty( $cats ) ) : ?>
					<span class="nz-post-card__cat"><?php echo esc_html( $cats[0]->name ); ?></span>
				<?php endif; ?>
				<h3><?php the_title(); ?></h3>
				<div class="nz-post-card__meta"><?php echo esc_html( get_the_date() ); ?></div>
			</div>
		</a>
		<?php
	}
	echo '</div>';
	wp_reset_postdata();
	return ob_get_clean();
} );
