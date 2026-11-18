# Desempenho

## Medido neste ambiente (Chromium, servidor local PHP, sem cache)

| Métrica | Resultado |
| --- | --- |
| CSS do tema | 25 KB, **6,1 KB** com gzip |
| JS no carregamento (theme.js) | **2,2 KB** com gzip |
| JS extra na home (slider.js) | 1,0 KB com gzip |
| JS da newsletter | 1,5 KB com gzip |
| CLS na home (390 px) | **0** |
| Rolagem horizontal | nenhuma, em 13 larguras (320 a 1920 px) e 7 modelos |

Total de CSS e JS fica bem abaixo das metas (30 KB e 15 KB).

## Pendente (depende de um servidor real)

O **Lighthouse (mobile e desktop) e o LCP/INP não foram medidos**, pois o ambiente não tinha acesso à internet nem ao Lighthouse. Rode `npx lighthouse https://klyvorenza.com --preset=perf --form-factor=mobile` após publicar e anote aqui.

## O que já foi feito para ajudar

- Imagem do slide (LCP da home) com `fetchpriority="high"`, sem lazy, `srcset` e `sizes`; proporção fixa.
- Imagem principal do artigo com `fetchpriority="high"`.
- Logo do cabeçalho com `width`, `height`, `srcset` WebP e `fetchpriority="high"`; a versão escura só é baixada ao ser exibida.
- Demais imagens com `loading="lazy"` e `decoding="async"`.
- Sem jQuery, sem bibliotecas, sem emojis, sem `wp-embed`, sem `wlwmanifest` e `rsd_link`.
- AdSense e GTranslate carregam depois da primeira interação ou do evento `load`.
- Consultas da home em cache por `transient`, com versão que muda ao salvar posts e categorias.
- Alturas reservadas para barra de idiomas, cabeçalho, slide, anúncios e pop-up.
