<?php
/**
 * Template Name: Página em Branco (HTML completo)
 *
 * Página totalmente em branco: NÃO exibe o cabeçalho (menu) nem o rodapé do
 * tema. Use quando você tiver um HTML completo — que já traz o próprio topo,
 * rodapé, estilos e tudo mais — e quiser que ele ocupe a tela inteira.
 *
 * Como usar:
 * 1. Crie uma página nova (Páginas → Adicionar nova).
 * 2. Em "Atributos da página → Modelo", escolha "Página em Branco (HTML completo)".
 * 3. Troque o editor para o modo "Código" (menu ⋮ no canto → "Editor de código")
 *    ou use um bloco de "HTML personalizado" e cole o HTML completo.
 * 4. Publique.
 *
 * Observação: o botão flutuante de WhatsApp do tema também NÃO aparece aqui,
 * já que a ideia é uma página 100% controlada pelo seu HTML.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'nz-pagina-em-branco' ); ?>>
<?php wp_body_open(); ?>

	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>

<?php wp_footer(); ?>
</body>
</html>
