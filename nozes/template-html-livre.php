<?php
/**
 * Template Name: Página HTML Livre
 *
 * Use este modelo quando quiser colar uma página pronta (por exemplo, uma
 * página inteira gerada aqui pelo Claude). O conteúdo é exibido em largura
 * total, sem o container/padding padrão do tema — mas mantém o cabeçalho e
 * rodapé do site para preservar a navegação e a identidade da marca.
 *
 * Como usar: crie uma página nova, selecione este modelo em "Atributos da
 * página", troque o editor para "Editor de código"/bloco de HTML
 * personalizado e cole o HTML completo fornecido.
 */

get_header();
?>

<div class="nz-html-livre">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</div>

<?php get_footer(); ?>
