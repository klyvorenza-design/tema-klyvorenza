<?php
/**
 * Title: Grade de cartões (lista própria)
 * Slug: klyvorenza/query-cartoes-lista
 * Categories: klyvorenza
 * Inserter: false
 * Block Types: core/query
 *
 * @package Klyvorenza
 */

?>
<!-- wp:query {"queryId":11,"query":{"perPage":12,"postType":"post","order":"desc","orderBy":"date","inherit":false},"className":"klyv-qgrid"} -->
<div class="wp-block-query klyv-qgrid">
<!-- wp:post-template -->
<!-- wp:group {"className":"klyv-card"} -->
<div class="wp-block-group klyv-card">
<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","sizeSlug":"klyv-card","className":"klyv-card__img"} /-->
<!-- wp:group {"className":"klyv-card__body"} -->
<div class="wp-block-group klyv-card__body">
<!-- wp:post-terms {"term":"category","className":"klyv-chip-terms"} /-->
<!-- wp:post-title {"level":3,"isLink":true,"className":"klyv-card__t"} /-->
<!-- wp:post-date {"className":"klyv-card__d"} /-->
<!-- wp:post-excerpt {"excerptLength":22,"className":"klyv-card__x"} /-->
</div>
<!-- /wp:group -->
</div>
<!-- /wp:group -->
<!-- /wp:post-template -->

<!-- wp:query-pagination {"paginationArrow":"arrow","className":"klyv-pager","layout":{"type":"flex","justifyContent":"center"}} -->
<!-- wp:query-pagination-previous {"label":"Anterior"} /-->
<!-- wp:query-pagination-numbers /-->
<!-- wp:query-pagination-next {"label":"Próxima"} /-->
<!-- /wp:query-pagination -->

<!-- wp:query-no-results -->
<!-- wp:paragraph -->
<p>Nada por aqui ainda. Volte em breve.</p>
<!-- /wp:paragraph -->
<!-- /wp:query-no-results -->
</div>
<!-- /wp:query -->
