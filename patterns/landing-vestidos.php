<?php
/**
 * Title: Landing de vestidos (âncoras + Content Egg)
 * Slug: klyvorenza/landing-vestidos
 * Categories: klyvorenza
 * Description: Menu de âncoras, botão "Compre Aqui Shopee" e uma seção por tipo de vestido, cada uma com um shortcode do Content Egg para trocar.
 *
 * @package Klyvorenza
 */

?>
<!-- wp:paragraph -->
<p>Escolha o seu estilo e veja as ofertas selecionadas na Shopee. Preços e disponibilidade podem mudar. Ao comprar pelos links, o blog pode receber uma comissão, sem custo extra para você.</p>
<!-- /wp:paragraph -->

<!-- wp:html -->
<nav class="klyv-chips" aria-label="Tipos de vestido">
<a href="#vestido-longo">Vestido Longo</a>
<a href="#vestido-midi">Vestido Midi</a>
<a href="#vestido-curto">Vestido Curto</a>
<a href="#vestido-festa">Vestido de Festa</a>
<a href="#vestido-casual">Vestido Casual</a>
</nav>
<!-- /wp:html -->

<!-- wp:buttons -->
<div class="wp-block-buttons"><!-- wp:button {"className":"klyv-btn-shopee"} -->
<div class="wp-block-button klyv-btn-shopee"><a class="wp-block-button__link wp-element-button" href="https://shopee.com.br/" target="_blank" rel="sponsored nofollow noopener">Compre Aqui Shopee</a></div>
<!-- /wp:button --></div>
<!-- /wp:buttons -->

<!-- wp:heading {"anchor":"vestido-longo"} -->
<h2 class="wp-block-heading" id="vestido-longo">Vestido Longo</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Escreva aqui duas linhas sobre este estilo: ocasião, tecido e com o que combinar.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[content-egg module=Shopee template=grid limit=6]
<!-- /wp:shortcode -->

<!-- wp:heading {"anchor":"vestido-midi"} -->
<h2 class="wp-block-heading" id="vestido-midi">Vestido Midi</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Escreva aqui duas linhas sobre este estilo: ocasião, tecido e com o que combinar.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[content-egg module=Shopee template=grid limit=6]
<!-- /wp:shortcode -->

<!-- wp:heading {"anchor":"vestido-curto"} -->
<h2 class="wp-block-heading" id="vestido-curto">Vestido Curto</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Escreva aqui duas linhas sobre este estilo: ocasião, tecido e com o que combinar.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[content-egg module=Shopee template=grid limit=6]
<!-- /wp:shortcode -->

<!-- wp:heading {"anchor":"vestido-festa"} -->
<h2 class="wp-block-heading" id="vestido-festa">Vestido de Festa</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Escreva aqui duas linhas sobre este estilo: ocasião, tecido e com o que combinar.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[content-egg module=Shopee template=grid limit=6]
<!-- /wp:shortcode -->

<!-- wp:heading {"anchor":"vestido-casual"} -->
<h2 class="wp-block-heading" id="vestido-casual">Vestido Casual</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>Escreva aqui duas linhas sobre este estilo: ocasião, tecido e com o que combinar.</p>
<!-- /wp:paragraph -->

<!-- wp:shortcode -->
[content-egg module=Shopee template=grid limit=6]
<!-- /wp:shortcode -->
