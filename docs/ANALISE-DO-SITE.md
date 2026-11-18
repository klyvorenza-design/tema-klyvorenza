# Análise do site

**Aviso importante:** durante a construção do tema, o ambiente de desenvolvimento não conseguiu abrir https://klyvorenza.com/ (a rede bloqueou o acesso). Esta análise parte, portanto, do levantamento descrito no briefing e **não foi confirmada no site ao vivo**. Os itens marcados com "Confirmar" precisam de uma conferência rápida antes de publicar.

## O que o tema assume

| Item | Assumido | Situação |
| --- | --- | --- |
| Menu | Iniciar, Blog, Glossário, Web Stories, Especiais, Datas Festivas | Implementado |
| Especiais | Categoria-mãe com slug `especiais`; filhas: Moda Gestante, Perfumes e Fragrâncias, História da Moda, Praias do Brasil, Moda por Estado | Confirmar slug (muda no painel) |
| Datas Festivas | Categoria-mãe com slug `datas-festivas`; filhas: Natal, Ano Novo. O tema também aceita o slug antigo `datas-comemorativas` | Confirmar |
| Se forem páginas | O menu aceita páginas-mãe e usa as páginas filhas como submenu | Implementado |
| Categorias de moda | Roupas, Vestidos, Blusas, Saias, Plus Size, Casacos e Jaqueta, Maiôs, Biquínis, Calças, Shorts e Macacão | Entram sozinhas |
| Categorias de cuidados | Beleza & Cuidados, Cuidados com a Pele, Maquiagem, Cabelo, Unhas | Entram sozinhas |
| Hub | `/categorias/` (página com o slug `categorias`) | Modelo `page-categorias` |
| Blog | `/blog/` como "Página de posts" | Ativado na ativação do tema, se o campo estiver vazio |
| Glossário | `/glossario/` (página, com páginas filhas, ou tipo de conteúdo próprio) | Confirmar o tipo |
| Web Stories | `/fws-story/` do plugin Fast Web Stories | O tipo de conteúdo é detectado pelo slug de reescrita, nunca presumido |
| Institucionais | Sobre, Galeria, Contato, Links na Bio, Mapa do Site, Termos de Uso, Política de Privacidade | Links do rodapé usam estes slugs |
| Redes | Facebook, Instagram, Pinterest | Configuráveis no painel |

## Pontos a conferir no site

1. Há categorias, tags ou conteúdo de acessórios e perfumaria fora do menu atual? O tema mostra **toda** categoria com pelo menos um artigo, na home e em `/categorias/`. Uma seção própria de Acessórios aparece sozinha assim que existir a categoria.
2. Os slugs reais de "Especiais" e "Datas Festivas" (campos em Aparência > Opções Klyvorenza > Categorias).
3. O nome do tipo de conteúdo do glossário e se os termos têm resumo (campo "resumo" do artigo).
4. Como o artigo atual monta a imagem principal. O tema usa, nesta ordem: o campo "Imagem principal", a imagem de destaque e a primeira imagem do conteúdo.
5. Permalinks: o tema **não altera** nenhuma estrutura de URL.
6. O viewport do tema é `width=device-width, initial-scale=1`, sem `user-scalable=no`.
