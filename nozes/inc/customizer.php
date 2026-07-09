<?php
/**
 * Personalizador do WordPress — tudo que a Nozes precisa trocar sem mexer em código:
 * WhatsApp, redes sociais, dados de contato/endereço (usados no rodapé e nos dados
 * estruturados de SEO) e o texto de apresentação da empresa (usado no llms.txt).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function nozes_customize_register( $wp_customize ) {

	/* ---------------------------------------------------------------
	 * Painel geral da Nozes
	 * ------------------------------------------------------------- */
	$wp_customize->add_panel( 'nozes_options', array(
		'title'    => __( 'Configurações da Nozes', 'nozes' ),
		'priority' => 1,
	) );

	/* -- Seção: WhatsApp -- */
	$wp_customize->add_section( 'nozes_whatsapp', array(
		'title' => __( 'WhatsApp (ação principal do site)', 'nozes' ),
		'panel' => 'nozes_options',
	) );

	$wp_customize->add_setting( 'nozes_whatsapp_number', array(
		'default'           => '554830507538',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'nozes_whatsapp_number', array(
		'label'       => __( 'Número do WhatsApp (com código do país e DDD, só números)', 'nozes' ),
		'description' => __( 'Exemplo: 554830507538 (55 + DDD + número, só dígitos). Confirme que este é o número cadastrado no WhatsApp/WhatsApp Business.', 'nozes' ),
		'section'     => 'nozes_whatsapp',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'nozes_whatsapp_message', array(
		'default'           => 'Olá! Vim pelo site da Nozes e quero saber mais sobre a Assessoria em Decisões de Negócio e Valorização de Marca.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'nozes_whatsapp_message', array(
		'label'   => __( 'Mensagem inicial sugerida', 'nozes' ),
		'section' => 'nozes_whatsapp',
		'type'    => 'textarea',
	) );

	$wp_customize->add_setting( 'nozes_whatsapp_float_show', array(
		'default'           => true,
		'sanitize_callback' => 'nozes_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'nozes_whatsapp_float_show', array(
		'label'   => __( 'Mostrar botão flutuante de WhatsApp em todo o site', 'nozes' ),
		'section' => 'nozes_whatsapp',
		'type'    => 'checkbox',
	) );

	/* -- Seção: Contato / NAP (usado no rodapé e no SEO local) -- */
	$wp_customize->add_section( 'nozes_contact', array(
		'title' => __( 'Contato e Endereço', 'nozes' ),
		'panel' => 'nozes_options',
	) );

	$contact_fields = array(
		'nozes_phone_display' => array( '(48) 3050-7538', __( 'Telefone (formato de exibição)', 'nozes' ) ),
		'nozes_email'         => array( 'contato@somosnozes.com.br', __( 'E-mail de contato (usado no envio de e-mails e no SEO)', 'nozes' ) ),
		'nozes_footer_email'  => array( 'olá@somosnozes.com.br', __( 'E-mail exibido no rodapé', 'nozes' ) ),
		'nozes_address'       => array( 'Florianópolis, Santa Catarina, Brasil', __( 'Cidade/Endereço', 'nozes' ) ),
	);
	foreach ( $contact_fields as $key => $data ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $data[0],
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => $data[1],
			'section' => 'nozes_contact',
			'type'    => 'text',
		) );
	}

	/* -- Seção: Redes sociais -- */
	$wp_customize->add_section( 'nozes_social', array(
		'title' => __( 'Redes Sociais', 'nozes' ),
		'panel' => 'nozes_options',
	) );

	$socials = array(
		'nozes_social_instagram' => array( 'Instagram (URL completa)', 'https://www.instagram.com/somosnozes/' ),
		'nozes_social_linkedin'  => array( 'LinkedIn (URL completa)', '' ),
	);
	foreach ( $socials as $key => $data ) {
		$wp_customize->add_setting( $key, array(
			'default'           => $data[1],
			'sanitize_callback' => 'esc_url_raw',
		) );
		$wp_customize->add_control( $key, array(
			'label'   => __( $data[0], 'nozes' ),
			'section' => 'nozes_social',
			'type'    => 'url',
		) );
	}

	/* -- Seção: Blog (chamada no fim de cada artigo) -- */
	$wp_customize->add_section( 'nozes_blog', array(
		'title' => __( 'Blog', 'nozes' ),
		'panel' => 'nozes_options',
	) );

	$wp_customize->add_setting( 'nozes_blog_cta_title', array(
		'default'           => 'Esse assunto apareceu na sua empresa?',
		'sanitize_callback' => 'sanitize_text_field',
	) );
	$wp_customize->add_control( 'nozes_blog_cta_title', array(
		'label'       => __( 'Título da chamada no fim dos artigos', 'nozes' ),
		'section'     => 'nozes_blog',
		'type'        => 'text',
	) );

	$wp_customize->add_setting( 'nozes_blog_cta_text', array(
		'default'           => 'Se quiser destrinchar isso pro seu negócio, me escreve. A primeira conversa é sem compromisso.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'nozes_blog_cta_text', array(
		'label'       => __( 'Texto da chamada no fim dos artigos', 'nozes' ),
		'section'     => 'nozes_blog',
		'type'        => 'textarea',
	) );

	/* -- Seção: SEO / GEO -- */
	$wp_customize->add_section( 'nozes_seo', array(
		'title' => __( 'SEO e Dados Estruturados', 'nozes' ),
		'panel' => 'nozes_options',
	) );

	$wp_customize->add_setting( 'nozes_company_summary', array(
		'default'           => 'A Nozes é especialista em branding e gestão de marca. Ajudamos empresas a alinhar o que o mercado percebe com o que a operação já entrega.',
		'sanitize_callback' => 'sanitize_textarea_field',
	) );
	$wp_customize->add_control( 'nozes_company_summary', array(
		'label'       => __( 'Resumo da empresa (usado nos dados estruturados e no arquivo para IAs/llms.txt)', 'nozes' ),
		'section'     => 'nozes_seo',
		'type'        => 'textarea',
	) );

	$wp_customize->add_setting( 'nozes_disable_schema', array(
		'default'           => false,
		'sanitize_callback' => 'nozes_sanitize_checkbox',
	) );
	$wp_customize->add_control( 'nozes_disable_schema', array(
		'label'       => __( 'Desativar dados estruturados (JSON-LD) do tema', 'nozes' ),
		'description' => __( 'Marque isso apenas se você instalar um plugin de SEO (ex: Rank Math) e ativar os dados estruturados por lá, para não duplicar informações para o Google.', 'nozes' ),
		'section'     => 'nozes_seo',
		'type'        => 'checkbox',
	) );
}
add_action( 'customize_register', 'nozes_customize_register' );

function nozes_sanitize_checkbox( $checked ) {
	return ( isset( $checked ) && true === $checked ) ? true : false;
}

/**
 * Monta o link wa.me a partir das opções do Personalizador.
 */
function nozes_get_whatsapp_link() {
	$number  = preg_replace( '/\D/', '', get_theme_mod( 'nozes_whatsapp_number', '554830507538' ) );
	$message = get_theme_mod( 'nozes_whatsapp_message', '' );

	$url = 'https://wa.me/' . $number;
	if ( ! empty( $message ) ) {
		$url .= '?text=' . rawurlencode( $message );
	}
	return $url;
}
