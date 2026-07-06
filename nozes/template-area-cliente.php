<?php
/**
 * Template Name: Área do Cliente
 *
 * Página protegida por login: cada cliente só vê os próprios relatórios
 * mensais (cadastrados pela Nozes como o tipo de conteúdo "Relatório").
 */

$nozes_login_error = null;
if ( ! is_user_logged_in() && ! empty( $_POST['nozes_client_login_nonce'] ) ) {
	$result = nozes_process_client_login();
	if ( is_wp_error( $result ) ) {
		$nozes_login_error = $result->get_error_message();
	}
}

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

				<?php wp_nonce_field( 'nozes_client_login', 'nozes_client_login_nonce' ); ?>
				<button type="submit" class="nz-btn nz-btn--primary"><?php esc_html_e( 'Entrar', 'nozes' ); ?></button>

				<p style="margin-top:1.2rem; text-align:center;">
					<a href="<?php echo esc_url( wp_lostpassword_url( get_permalink() ) ); ?>"><?php esc_html_e( 'Esqueci minha senha', 'nozes' ); ?></a>
				</p>
			</form>

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
				<?php foreach ( $reports as $report ) : ?>
					<div class="nz-report">
						<div class="nz-report__head" data-nz-toggle-report>
							<h3><?php echo esc_html( get_the_title( $report ) ); ?></h3>
							<span class="nz-report__date"><?php echo esc_html( get_the_date( '', $report ) ); ?></span>
						</div>
						<div class="nz-report__body" hidden>
							<?php echo apply_filters( 'the_content', $report->post_content ); ?>
						</div>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>

		<?php endif; ?>

	</div>
</section>

<?php get_footer(); ?>
