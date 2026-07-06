<?php
/**
 * Conteúdo inicial criado automaticamente quando o tema é ativado:
 * páginas (Início, Sobre, Serviços, Contato, Área do Cliente), menu principal
 * e configuração de "página inicial estática". Só cria o que ainda não existe,
 * então é seguro reativar o tema sem duplicar nada.
 *
 * IMPORTANTE: os textos abaixo são um ponto de partida com a voz da Nozes.
 * Alguns trechos (marcados com "AJUSTE") são placeholders porque não foi
 * possível ler o conteúdo do site atual (somosnozes.com.br) para migrá-lo
 * automaticamente — edite-os pelo WordPress ou pelo Elementor à vontade.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nozes_theme_activation() {
	nozes_register_relatorio_cpt();
	nozes_register_client_role();
	nozes_llms_rewrite_rule();
	flush_rewrite_rules();

	$home_id     = nozes_create_page_if_missing( 'inicio', 'Início', nozes_home_content() );
	$about_id    = nozes_create_page_if_missing( 'sobre', 'Sobre', nozes_about_content() );
	$services_id = nozes_create_page_if_missing( 'servicos', 'Serviços', nozes_services_content() );
	$contact_id  = nozes_create_page_if_missing( 'contato', 'Contato', nozes_contact_content() );
	$client_id   = nozes_create_page_if_missing( 'area-do-cliente', 'Área do Cliente', '' );

	if ( $client_id ) {
		update_post_meta( $client_id, '_wp_page_template', 'template-area-cliente.php' );
	}

	// Página de posts (Blog) — só cria se ainda não existir nenhuma página de posts configurada.
	$blog_id = get_option( 'page_for_posts' );
	if ( ! $blog_id ) {
		$blog_id = nozes_create_page_if_missing( 'blog', 'Blog', '' );
	}

	if ( $home_id && get_option( 'show_on_front' ) !== 'page' ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $home_id );
		if ( $blog_id ) {
			update_option( 'page_for_posts', $blog_id );
		}
	}

	nozes_create_default_menu();
}
add_action( 'after_switch_theme', 'nozes_theme_activation' );

function nozes_create_page_if_missing( $slug, $title, $content ) {
	$existing = get_page_by_path( $slug );
	if ( $existing ) {
		return $existing->ID;
	}
	return wp_insert_post( array(
		'post_title'   => $title,
		'post_name'    => $slug,
		'post_content' => $content,
		'post_status'  => 'publish',
		'post_type'    => 'page',
	) );
}

function nozes_create_default_menu() {
	if ( wp_get_nav_menu_object( 'Menu Principal' ) ) {
		return;
	}

	$menu_id = wp_create_nav_menu( 'Menu Principal' );

	$items = array(
		'inicio'   => 'Início',
		'sobre'    => 'Sobre',
		'servicos' => 'Serviços',
		'blog'     => 'Blog',
		'contato'  => 'Contato',
	);

	foreach ( $items as $slug => $label ) {
		$page = get_page_by_path( $slug );
		if ( $slug === 'blog' && ! $page ) {
			$blog_id = get_option( 'page_for_posts' );
			if ( $blog_id ) {
				$page = get_post( $blog_id );
			}
		}
		if ( $page ) {
			wp_update_nav_menu_item( $menu_id, 0, array(
				'menu-item-title'     => $label,
				'menu-item-object'    => 'page',
				'menu-item-object-id' => $page->ID,
				'menu-item-type'      => 'post_type',
				'menu-item-status'    => 'publish',
			) );
		}
	}

	$locations              = get_theme_mod( 'nav_menu_locations', array() );
	$locations['primary']   = $menu_id;
	$locations['footer']    = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );
}

/* ---------------------------------------------------------------------
 * Conteúdo das páginas
 * ------------------------------------------------------------------- */

function nozes_home_content() {
	ob_start();
	?>
<section class="nz-hero">
	<?php nozes_scribble( '#FF0066', 'nz-hero__scribble nz-hero__scribble--1' ); ?>
	<?php nozes_scribble( '#ABF705', 'nz-hero__scribble nz-hero__scribble--2' ); ?>
	<div class="nz-container nz-hero__content">
		<span class="u-eyebrow">Nozes · Estratégia de marca</span>
		<h1>Decisões de negócio mais seguras começam com a marca certa.</h1>
		<p class="u-lede">Você conhece o seu negócio como ninguém. A Nozes traz a <mark>visão de marca</mark> que costuma faltar na hora de decidir — traduzindo reputação e percepção em direção clara, para decisões mais seguras.</p>
		<div class="nz-hero__actions">
			[nozes_whatsapp_link texto="Falar no WhatsApp"]
			<a class="nz-btn nz-btn--outline" href="#servicos">Conhecer os serviços</a>
		</div>
	</div>
</section>

<section class="nz-section" id="servicos">
	<div class="nz-container">
		<span class="u-eyebrow">O que fazemos</span>
		<h2>Serviços</h2>
		<p class="u-lede">AJUSTE: confirme os serviços abaixo com o conteúdo atual do site — não conseguimos acessar somosnozes.com.br automaticamente para migrar estes textos.</p>
		<div class="nz-grid nz-grid--3" style="margin-top:2.5rem;">
			<div class="nz-service nz-service--featured" style="--accent:#ABF705;">
				<span class="nz-badge">Novo em 2026</span>
				<div class="nz-service__icon">[icon:target]</div>
				<h3>Assessoria em Decisões de Negócio e Valorização de Marca</h3>
				<p>Nossa entrega é inteligência e direcionamento: você conhece o seu negócio, nós trazemos a visão de marca que falta para a decisão ser mais segura — melhorando reputação e percepção. Este serviço não inclui a execução de um plano de ações imediatas.</p>
			</div>
			<div class="nz-service" style="--accent:#1FBDC6;">
				<div class="nz-service__icon">[icon:compass]</div>
				<h3>Diagnóstico de Marca <em>(AJUSTE)</em></h3>
				<p>Descreva aqui o serviço de diagnóstico/auditoria de marca oferecido hoje pela Nozes.</p>
			</div>
			<div class="nz-service" style="--accent:#7B4FE0;">
				<div class="nz-service__icon">[icon:trend]</div>
				<h3>Direção de Marca <em>(AJUSTE)</em></h3>
				<p>Descreva aqui outro serviço existente (posicionamento, naming, identidade etc.).</p>
			</div>
		</div>
	</div>
</section>

<section class="nz-section nz-section--offwhite">
	<div class="nz-container nz-about">
		<div class="nz-about__media">
			<img src="<?php echo esc_url( NOZES_URI . '/assets/img/founder-portrait.jpg' ); ?>" alt="Fundadora da Nozes" />
		</div>
		<div class="nz-about__content">
			<span class="u-eyebrow">Sobre a Nozes</span>
			<h2>Inteligência de marca a favor das suas decisões.</h2>
			<p>AJUSTE: texto de apresentação da Nozes — quem é, trajetória e forma de trabalho. Substitua por conteúdo real na página <a href="<?php echo esc_url( home_url( '/sobre/' ) ); ?>">Sobre</a>.</p>
			<a class="nz-btn nz-btn--outline" href="<?php echo esc_url( home_url( '/sobre/' ) ); ?>">Conhecer a história</a>
		</div>
	</div>
</section>

<section class="nz-section nz-section--black">
	<div class="nz-container">
		<span class="u-eyebrow">Depoimentos</span>
		<h2>Quem decidiu com a Nozes ao lado</h2>
		<div class="nz-grid nz-grid--3" style="margin-top:2.5rem;">
			<div class="nz-quote"><p>"AJUSTE: adicione aqui um depoimento real de cliente."</p><cite>Nome do cliente, Empresa</cite></div>
			<div class="nz-quote"><p>"AJUSTE: adicione aqui outro depoimento real de cliente."</p><cite>Nome do cliente, Empresa</cite></div>
			<div class="nz-quote"><p>"AJUSTE: adicione aqui outro depoimento real de cliente."</p><cite>Nome do cliente, Empresa</cite></div>
		</div>
	</div>
</section>

<section class="nz-section">
	<div class="nz-container">
		<span class="u-eyebrow">Blog</span>
		<h2>Conteúdo para decidir melhor</h2>
		[nozes_latest_posts quantidade="3"]
	</div>
</section>

<section class="nz-section nz-section--offwhite">
	<div class="nz-container u-max-content u-center">
		<span class="u-eyebrow">Perguntas frequentes</span>
		<h2>Sobre a Assessoria em Decisões de Negócio</h2>
	</div>
	<div class="nz-container u-max-content">
		[nozes_faq]
			[nozes_faq_item pergunta="O que está incluso na Assessoria em Decisões de Negócio e Valorização de Marca?"]A entrega é inteligência e direcionamento: você já conhece o seu negócio, e a Nozes traz a visão de marca que falta para embasar a decisão, com foco em reputação e percepção.[/nozes_faq_item]
			[nozes_faq_item pergunta="A assessoria inclui a execução de um plano de ações?"]Não. O plano de ações imediatas não faz parte deste serviço — o foco é o direcionamento estratégico para a decisão.[/nozes_faq_item]
			[nozes_faq_item pergunta="Como falo com a Nozes?"]O canal principal é o WhatsApp — use o botão no site ou clique no ícone flutuante no canto da tela.[/nozes_faq_item]
		[/nozes_faq]
	</div>
</section>

<section class="nz-section nz-section--black nz-cta">
	<div class="nz-container">
		<span class="u-eyebrow">Vamos conversar?</span>
		<h2>Sua próxima decisão de negócio começa com uma conversa.</h2>
		[nozes_whatsapp_link texto="Chamar no WhatsApp agora"]
	</div>
</section>
	<?php
	return nozes_replace_icon_placeholders( ob_get_clean() );
}

function nozes_about_content() {
	ob_start();
	?>
<section class="nz-section">
	<div class="nz-container nz-about">
		<div class="nz-about__media">
			<img src="<?php echo esc_url( NOZES_URI . '/assets/img/founder-square.jpg' ); ?>" alt="Fundadora da Nozes" />
		</div>
		<div class="nz-about__content">
			<span class="u-eyebrow">Quem somos</span>
			<h1>AJUSTE: título de apresentação da Nozes</h1>
			<p class="u-lede">AJUSTE: substitua por um texto real contando a história da Nozes, a experiência da fundadora e a forma de trabalho da assessoria.</p>
			<p>AJUSTE: parágrafo adicional sobre valores, propósito e diferenciais.</p>
		</div>
	</div>
</section>
<section class="nz-section nz-section--offwhite nz-cta">
	<div class="nz-container">
		<h2>Quer trazer a Nozes para uma decisão importante?</h2>
		[nozes_whatsapp_link texto="Falar no WhatsApp"]
	</div>
</section>
	<?php
	return ob_get_clean();
}

function nozes_services_content() {
	ob_start();
	?>
<section class="nz-section">
	<div class="nz-container">
		<span class="u-eyebrow">Serviços</span>
		<h1>Como a Nozes ajuda o seu negócio</h1>
		<p class="u-lede">AJUSTE: confirme esta lista de serviços com o site atual — não foi possível migrar automaticamente o conteúdo de somosnozes.com.br.</p>

		<div class="nz-grid nz-grid--3" style="margin-top:3rem;">
			<div class="nz-service nz-service--featured" style="--accent:#ABF705;">
				<span class="nz-badge">Novo em 2026</span>
				<div class="nz-service__icon">[icon:target]</div>
				<h3>Assessoria em Decisões de Negócio e Valorização de Marca</h3>
				<p>Você conhece o seu negócio. A Nozes entra com a visão de marca que falta para embasar a decisão — entregando inteligência e direcionamento, e melhorando reputação e percepção. Não inclui execução de plano de ações imediatas.</p>
				[nozes_service_schema nome="Assessoria em Decisões de Negócio e Valorização de Marca"]Serviço de assessoria estratégica: diagnóstico e direcionamento de marca para apoiar decisões de negócio, com foco em reputação e percepção. Não inclui execução de plano de ações imediatas.[/nozes_service_schema]
			</div>
			<div class="nz-service" style="--accent:#1FBDC6;">
				<div class="nz-service__icon">[icon:compass]</div>
				<h3>Diagnóstico de Marca <em>(AJUSTE)</em></h3>
				<p>AJUSTE: descreva este serviço existente.</p>
			</div>
			<div class="nz-service" style="--accent:#7B4FE0;">
				<div class="nz-service__icon">[icon:trend]</div>
				<h3>Direção de Marca <em>(AJUSTE)</em></h3>
				<p>AJUSTE: descreva este serviço existente.</p>
			</div>
			<div class="nz-service" style="--accent:#FF6B6B;">
				<div class="nz-service__icon">[icon:shield]</div>
				<h3>AJUSTE: outro serviço</h3>
				<p>AJUSTE: descreva outro serviço existente, se houver.</p>
			</div>
		</div>
	</div>
</section>

<section class="nz-section nz-section--offwhite">
	<div class="nz-container u-max-content">
		<span class="u-eyebrow">Perguntas frequentes</span>
		<h2>Sobre os serviços</h2>
		[nozes_faq]
			[nozes_faq_item pergunta="Qual a diferença entre a Assessoria e um plano de ações?"]A assessoria entrega inteligência e direcionamento estratégico para a decisão. A execução de um plano de ações imediatas é uma etapa separada, não incluída neste serviço.[/nozes_faq_item]
			[nozes_faq_item pergunta="Como escolho o serviço certo para o meu momento?"]Chame no WhatsApp e conte o que está decidindo — a Nozes indica o melhor caminho.[/nozes_faq_item]
		[/nozes_faq]
	</div>
</section>

<section class="nz-section nz-section--black nz-cta">
	<div class="nz-container">
		<h2>Vamos falar sobre a sua próxima decisão?</h2>
		[nozes_whatsapp_link texto="Chamar no WhatsApp"]
	</div>
</section>
	<?php
	return nozes_replace_icon_placeholders( ob_get_clean() );
}

function nozes_contact_content() {
	ob_start();
	?>
<section class="nz-section">
	<div class="nz-container u-center u-max-content">
		<span class="u-eyebrow">Contato</span>
		<h1>Vamos conversar sobre o seu negócio</h1>
		<p class="u-lede">O canal principal da Nozes é o WhatsApp — resposta rápida e direta.</p>
		<div style="margin-top:2rem;">[nozes_whatsapp_link texto="Chamar no WhatsApp"]</div>
		<p style="margin-top:2.5rem;">
			Telefone/WhatsApp: [nozes_telefone]<br />
			E-mail: [nozes_email]<br />
			[nozes_endereco]
		</p>
	</div>
</section>
	<?php
	return ob_get_clean();
}

/**
 * Troca os marcadores [icon:nome] pelo SVG correspondente (mantém o
 * conteúdo legível/editável como texto simples nas páginas).
 */
function nozes_replace_icon_placeholders( $content ) {
	return preg_replace_callback( '/\[icon:([a-z]+)\]/', function( $matches ) {
		return nozes_icon( $matches[1] );
	}, $content );
}
