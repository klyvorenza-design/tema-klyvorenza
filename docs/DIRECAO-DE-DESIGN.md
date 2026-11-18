# Direção de design

Referência de partida: revistas digitais de moda e beleza, com grade editorial, fotografia grande, bastante respiro e títulos em serifa. Nenhum site foi copiado. A pesquisa online não foi possível neste ambiente, então a direção segue princípios editoriais conhecidos.

## Paleta (extraída de `simbolo.png` por `bin/extrair-paleta.py`)

| Uso | Cor |
| --- | --- |
| Verde claro | `#B8F888` |
| Verde | `#78D878` |
| Menta | `#18B898` |
| Turquesa | `#08C8D8` |
| Azul | `#0878C8` |
| Fundo claro | `#F6FBF7` |
| Texto | `#10241A` (verde quase preto) |
| Link em texto | `#066099` (o azul da logo escurecido para contraste AA) |

Degradê da marca: verde claro, menta, azul. Aparece em linhas finas, botões, selos ao passar o mouse, cabeçalhos de seção e rodapé. Os quadros usam uma versão com transparência, bem suave.

## Tipografia

- Títulos: Playfair Display 700 (serifa elegante que conversa com a letra cursiva da logo).
- Texto: Inter 400 e 600.
- Três arquivos WOFF2 locais, `font-display: swap`, preload só da fonte do título.

## Sistema

Tamanhos fluidos (`theme.json`), espaçamentos de `0.5rem` a `5rem` fluidos, raio de 14 px nos cartões e pílulas nos botões e selos, sombra leve. Componentes: botão, cartão, selo de categoria, campo de formulário, quadro de resumo, sumário, aviso de anúncio.

## Modo escuro

Fundo `#0D1612`, superfície `#14211A`, texto `#E8F3EA`, links em verde claro. A palavra da logo troca para a versão branca (`logo-site-dark.png`). O símbolo fica sempre dentro de um disco claro nos cartões, na newsletter e no rodapé, para o anel preto continuar legível.
