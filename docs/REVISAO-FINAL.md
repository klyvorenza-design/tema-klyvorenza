# Revisão final

## Verificado

- `php -l` em todos os arquivos PHP, `node --check` nos JS, JSON do `theme.json` válido.
- Ativação em um WordPress de teste (núcleo 7.1.4 com SQLite). Abriram sem erro: home, blog, categorias, glossário, artigo, categoria (inclusive filha), busca, 404, `/llms.txt`, `/robots.txt`. O log de erros do PHP ficou vazio.
- Home: slide com 4 artigos e **nenhum artigo repetido** nas seções seguintes.
- Artigo: 1 `h1`, sumário, imagem principal, 3 anúncios no texto e 2 nas laterais, JSON-LD com `WebSite`, `SearchAction`, `Organization`, `BreadcrumbList` e `BlogPosting`.
- Logo escura: palavra branca, emblema sem alteração (só pixels fora do círculo mudam).
- Busca de travessões e jargões proibidos em código e textos: nada encontrado.

## Corrigido durante o teste

- Conteúdo da home limitado a 760 px pelo layout do bloco: agora usa 1240 px.
- Botão da newsletter ocupando a linha toda no desktop.

## Ainda depende de você ou de um ambiente com internet

1. **Site ao vivo:** não foi possível analisar klyvorenza.com. Confirme os slugs de Especiais e Datas Festivas e o tipo do glossário (veja `ANALISE-DO-SITE.md`).
2. **Plugins GTranslate e Fast Web Stories** não estavam instalados no teste. O tema detecta o tipo de Web Stories pelo slug `fws-story` e adia os scripts do GTranslate, mas isso precisa ser visto com os plugins ativos.
3. **Lighthouse, Rich Results Test, validador W3C, axe e Theme Check:** não executados aqui.
4. **PHPCS (WordPress Coding Standards):** não executado. O código escapa toda saída e sanitiza toda entrada, mas rode `phpcs` para confirmar.
5. **Fallback de logo por filtro SVG:** não foi incluído. Um filtro CSS não consegue isolar só a palavra em um PNG sem alterar as cores do símbolo. A solução entregue é o arquivo `logo-site-dark.png`, gerado pelo script em `bin/`.
6. **Pesquisa de referências de design e skills do Claude Code** (`frontend-design`): indisponíveis neste ambiente.
7. **Código do AdSense**, serviço de e-mail da newsletter e consentimento de cookies (veja o README).
8. **Repositório:** proteger a `main` e criar o release `v1.0.0` é feito no GitHub, após o merge.
9. **Traduções do núcleo:** os textos em inglês do formulário de comentários desaparecem quando o pacote de idioma pt_BR do WordPress está instalado.
