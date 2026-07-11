<?php
/**
 * Template Name: Área do Cliente
 *
 * Página protegida por login: cada cliente só vê os próprios relatórios
 * mensais (cadastrados pela Nozes como o tipo de conteúdo "Relatório").
 *
 * Ao abrir um relatório (?relatorio=ID), ele é exibido em tela cheia, com o
 * HTML renderizado como nas páginas HTML personalizadas — sempre com
 * verificação de permissão, então um cliente nunca acessa o de outro.
 */

$nozes_login_error = null;
if ( ! is_user_logged_in() && ! empty( $_POST['nozes_client_login_nonce'] ) ) {
	$result = nozes_process_client_login();
	if ( is_wp_error( $result ) ) {
		$nozes_login_error = $result->get_error_message();
	}
}

// Qual relatório abrir em página própria (se houver) e se o usuário pode vê-lo.
$nozes_report_id     = isset( $_GET['relatorio'] ) ? absint( $_GET['relatorio'] ) : 0;
$nozes_single_report = $nozes_report_id ? get_post( $nozes_report_id ) : null;
$nozes_can_view      = is_user_logged_in() && $nozes_single_report && nozes_user_can_view_report( get_current_user_id(), $nozes_single_report );

/**
 * Relatório aberto em página própria: viewer limpo em tela cheia (sem o menu do
 * site), com o HTML do relatório renderizado como nas páginas HTML livres.
 */
if ( $nozes_report_id && $nozes_can_view ) :
	?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'nz-report-viewer' ); ?>>
<?php wp_body_open(); ?>
	<div class="nz-report-viewer__bar">
		<a class="nz-report-viewer__logo" href="<?php echo esc_url( nozes_get_client_area_url() ); ?>">
			<img src="<?php echo esc_url( NOZES_URI . '/assets/img/logo-wordmark.png' ); ?>" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
		</a>
		<div class="nz-report-viewer__actions">
			<a class="nz-btn nz-btn--outline" href="<?php echo esc_url( nozes_get_client_area_url() ); ?>">&larr; <?php esc_html_e( 'Voltar aos relatórios', 'nozes' ); ?></a>
			<button type="button" class="nz-btn nz-btn--lime" data-nz-print>
				<?php echo nozes_icon( 'print' ); ?>
				<span><?php esc_html_e( 'Imprimir / PDF', 'nozes' ); ?></span>
			</button>
			<a class="nz-btn nz-btn--outline" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sair', 'nozes' ); ?></a>
		</div>
	</div>
	<main class="nz-html-livre nz-report-viewer__content">
		<?php echo apply_filters( 'the_content', $nozes_single_report->post_content ); ?>
	</main>
<?php wp_footer(); ?>
</body>
</html>
	<?php
	return;
endif;

get_header();
?>

<section class="nz-section">
	<div class="nz-container nz-client">

		<?php if ( ! is_user_logged_in() ) : ?>

			<div class="u-center" style="margin-bottom:2.5rem;">
				<span class="u-eyebrow" style="justify-content:center;"><?php esc_html_e( 'Área do Cliente', 'nozes' ); ?></span>
				<h1><?php esc_html_e( 'Acesse seus relatórios', 'nozes' ); ?></h1>
				<p class="u-lede u-center"><?php esc_html_e( 'Entre com o usuário e senha enviados pela Nozes para ver seus relatórios mensais.', 'nozes' ); ?></p>
			</div>

			<form class="nz-client-login" method="post">
				<?php if ( $nozes_login_error ) : ?>
					<div class="nz-client-error"><?php echo esc_html( $nozes_login_error ); ?></div>
				<?php endif; ?>

				<label for="nozes_username"><?php esc_html_e( 'Usuário ou e-mail', 'nozes' ); ?></label>
				<input type="text" id="nozes_username" name="nozes_username" autocomplete="username" required>

				<label for="nozes_password"><?php esc_html_e( 'Senha', 'nozes' ); ?></label>
				<input type="password" id="nozes_password" name="nozes_password" autocomplete="current-password" required>

				<label class="nz-show-password">
					<input type="checkbox" id="nozes_show_password" data-nz-toggle-password="nozes_password">
					<?php esc_html_e( 'Mostrar senha', 'nozes' ); ?>
				</label>

				<label class="nz-remember-me">
					<input type="checkbox" id="nozes_remember" name="nozes_remember" value="1" checked>
					<?php esc_html_e( 'Manter conectado neste dispositivo', 'nozes' ); ?>
				</label>

				<?php wp_nonce_field( 'nozes_client_login', 'nozes_client_login_nonce' ); ?>
				<button type="submit" class="nz-btn nz-btn--primary"><?php esc_html_e( 'Entrar', 'nozes' ); ?></button>

				<p style="margin-top:1.2rem; text-align:center;">
					<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Esqueci minha senha', 'nozes' ); ?></a>
				</p>
			</form>

		<?php elseif ( $nozes_report_id && ! $nozes_can_view ) : ?>

			<?php /* Pediu um relatório que não é dele (ou não existe) */ ?>
			<div class="nz-client-topbar">
				<div>
					<span class="u-eyebrow"><?php esc_html_e( 'Área do Cliente', 'nozes' ); ?></span>
					<h1 style="margin-bottom:0;"><?php esc_html_e( 'Relatório indisponível', 'nozes' ); ?></h1>
				</div>
				<a class="nz-btn nz-btn--outline" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sair', 'nozes' ); ?></a>
			</div>
			<div class="nz-client-error"><?php esc_html_e( 'Este relatório não foi encontrado ou não está disponível para o seu usuário.', 'nozes' ); ?></div>
			<p><a href="<?php echo esc_url( nozes_get_client_area_url() ); ?>">&larr; <?php esc_html_e( 'Voltar aos meus relatórios', 'nozes' ); ?></a></p>

		<?php else : ?>

			<?php $current_user = wp_get_current_user(); ?>

			<div class="nz-client-topbar">
				<div>
					<span class="u-eyebrow"><?php esc_html_e( 'Área do Cliente', 'nozes' ); ?></span>
					<h1 style="margin-bottom:0;">
						<?php
						printf(
							/* translators: %s: nome do cliente */
							esc_html__( 'Olá, %s', 'nozes' ),
							esc_html( $current_user->display_name )
						);
						?>
					</h1>
				</div>
				<a class="nz-btn nz-btn--outline" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Sair', 'nozes' ); ?></a>
			</div>

			<?php $reports = nozes_get_client_reports( $current_user->ID ); ?>

			<?php if ( empty( $reports ) ) : ?>
				<p><?php esc_html_e( 'Ainda não há relatórios publicados para o seu usuário. Assim que a Nozes publicar, eles aparecem aqui.', 'nozes' ); ?></p>
			<?php else : ?>
				<?php
				$nz_groups     = nozes_group_reports_by_category( $reports );
				$nz_area_url   = nozes_get_client_area_url();

				/**
				 * Renderiza a lista de cards de um conjunto de relatórios.
				 */
				$nz_render_report_list = function ( $list ) use ( $nz_area_url ) {
					echo '<div class="nz-report-list">';
					foreach ( $list as $report ) {
						$title = get_the_title( $report );
						$date  = get_the_date( '', $report );
						printf(
							'<a class="nz-report nz-report--link" href="%1$s" target="_blank" rel="noopener" data-nz-report-item data-nz-report-search-text="%2$s">'
								. '<div class="nz-report__head"><h3>%3$s</h3><span class="nz-report__date">%4$s</span></div>'
								. '<span class="nz-report__open">%5$s &rarr;</span>'
							. '</a>',
							esc_url( add_query_arg( 'relatorio', $report->ID, $nz_area_url ) ),
							esc_attr( $title . ' ' . $date ),
							esc_html( $title ),
							esc_html( $date ),
							esc_html__( 'Abrir relatório', 'nozes' )
						);
					}
					echo '</div>';
				};
				?>

				<div class="nz-client-toolbar">
					<p class="nz-client-hint"><?php esc_html_e( 'Clique em um relatório para abri-lo em uma nova aba.', 'nozes' ); ?></p>
					<div class="nz-report-search">
						<label class="u-visually-hidden" for="nz-report-search"><?php esc_html_e( 'Buscar relatório', 'nozes' ); ?></label>
						<input type="search" id="nz-report-search" placeholder="<?php esc_attr_e( 'Buscar relatório por nome ou data…', 'nozes' ); ?>" data-nz-report-search autocomplete="off">
					</div>
				</div>

				<div class="nz-report-groups" data-nz-report-scope>
					<?php if ( $nz_groups['has_categories'] ) : ?>
						<?php foreach ( $nz_groups['tree'] as $group ) : ?>
							<section class="nz-report-group" data-nz-report-group>
								<h2 class="nz-report-group__title"><?php echo esc_html( $group['term']->name ); ?></h2>

								<?php if ( ! empty( $group['reports'] ) ) : ?>
									<?php $nz_render_report_list( $group['reports'] ); ?>
								<?php endif; ?>

								<?php foreach ( $group['subs'] as $sub ) : ?>
									<div class="nz-report-subgroup" data-nz-report-group>
										<h3 class="nz-report-subgroup__title"><?php echo esc_html( $sub['term']->name ); ?></h3>
										<?php $nz_render_report_list( $sub['reports'] ); ?>
									</div>
								<?php endforeach; ?>
							</section>
						<?php endforeach; ?>

						<?php if ( ! empty( $nz_groups['uncategorized'] ) ) : ?>
							<section class="nz-report-group" data-nz-report-group>
								<h2 class="nz-report-group__title"><?php esc_html_e( 'Outros relatórios', 'nozes' ); ?></h2>
								<?php $nz_render_report_list( $nz_groups['uncategorized'] ); ?>
							</section>
						<?php endif; ?>
					<?php else : ?>
						<?php $nz_render_report_list( $reports ); ?>
					<?php endif; ?>
				</div>

				<p class="nz-report-empty" data-nz-report-empty hidden><?php esc_html_e( 'Nenhum relatório encontrado para a sua busca.', 'nozes' ); ?></p>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>

<?php get_footer(); ?>
