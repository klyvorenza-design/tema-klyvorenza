# Klyvorenza (tema WordPress de blocos)

Tema rápido e leve para o blog https://klyvorenza.com/ (moda feminina, acessórios, maquiagem, pele, cabelos, unhas e perfumaria). Requer WordPress 6.5 ou superior e PHP 7.4 ou superior.

## Instalação

1. Baixe `klyvorenza.zip` (ou gere com `bin/empacotar.sh`).
2. Aparência > Temas > Adicionar > Enviar tema > ativar.
3. Na ativação, se a página `/blog/` existir e nenhuma "Página de posts" estiver definida, o tema a define sozinho. Nada é apagado. Confira em Configurações > Leitura.
4. Aparência > Opções Klyvorenza: preencha redes sociais e, se quiser, AdSense e newsletter.

## O que é automático

- **Menu:** Iniciar, Blog, Glossário, Web Stories, Especiais e Datas Festivas. Os submenus vêm das **categorias filhas** de `especiais` e `datas-festivas` (slugs mudam no painel) e só mostram categorias com artigo publicado. Se forem páginas, usa as páginas filhas.
- **Home:** slide com os 4 artigos mais recentes, últimos artigos e uma seção para cada categoria com artigos, mais Glossário e Web Stories. Nenhum artigo aparece duas vezes. Categoria nova entra sozinha com o primeiro artigo. A seção só aparece com 2 ou mais artigos ainda não exibidos (filtro `klyvorenza_home_min_posts`).
- **`/categorias/`:** crie uma página com o slug `categorias`. O mesmo vale para `glossario`.
- **Artigo:** sumário, migalhas, autor, data de atualização, relacionados, FAQPage em JSON-LD para títulos em forma de pergunta.

## Imagem principal e imagem de destaque

No editor do artigo, painel **Imagem principal do artigo**. Ela aparece grande abaixo do título. A imagem de destaque continua nos cartões, no slide e no compartilhamento. Sem imagem principal, o topo usa a de destaque; sem as duas, a primeira imagem do conteúdo. Artigos antigos funcionam sem edição. Para migrar: abra o artigo, escolha a imagem no painel e atualize.

Tamanhos registrados: principal 1200x675, cartão 600x400, social 1200x630, story 1080x1440. Depois de ativar, regenere as miniaturas (plugin de sua preferência).

## Padrões de blocos

Em "Klyvorenza": Resumo rápido (resposta direta para leitores e IAs), Perguntas e respostas, quadro com degradê, chamada para redes.

## AdSense

Cole só o trecho `<ins class="adsbygoogle">` (e o `push`) de cada bloco. O tema carrega a biblioteca depois da primeira interação. Para respeitar um plugin de consentimento, devolva `false` no filtro `klyvorenza_ads_consent` ou defina `window.klyvAdsConsent = false` até a permissão e dispare `window.dispatchEvent(new Event('klyvorenza:consent'))` depois. Não há anúncios em páginas, só em artigos.

## Newsletter

Contatos ficam na tabela `wp_klyv_subscribers`, com exportação em CSV no painel. Há confirmação por e-mail (opcional). Integrações: `add_action( 'klyvorenza_newsletter_subscribed', fn( $nome, $email, $id ) => ... )`. Para não gravar no WordPress, use o filtro `klyvorenza_newsletter_pre_store`.

Regras do pop-up: uma vez por visita, volta em 24 h se fechado, volta em 1 ano se inscrito. Não abre em política, termos e contato.

## Modo escuro e logos

`bin/gerar-logo-escura.py` recria `logo-site-dark.png` a partir de `logo-site.png` (só a palavra vira branca). Requer Pillow.

## GTranslate e Web Stories

As bandeiras aparecem na barra do topo (shortcode oficial). Scripts externos do plugin carregam após a interação. As páginas de story não recebem CSS, JS nem metas do tema.

## SEO

Sem plugin de SEO ativo, o tema gera descrição, Open Graph, Twitter Card, canonical e JSON-LD. Com Yoast, Rank Math, AIOSEO ou SEOPress ativos, ele deixa tudo com o plugin. `/llms.txt` e `robots.txt` liberam rastreadores de busca e de IA.

## Decisões de arquitetura

Cabeçalho, rodapé e seções automáticas são blocos dinâmicos em PHP (sem build de JS). O bloco de navegação do WordPress não monta submenus a partir de categorias novas sem edição manual, por isso o menu é próprio e usa o mesmo conceito de gaveta no mobile.

## Estrutura

`theme.json`, `templates/`, `parts/`, `patterns/`, `styles/escuro.json`, `inc/` (opções, menu, blocos, slide, home, imagem principal, sumário, compartilhamento, anúncios, newsletter, GTranslate, SEO, llms), `assets/`, `bin/`, `languages/klyvorenza.pot`, `docs/`.

Licença: GPL-2.0-or-later. Fontes: Playfair Display e Inter (SIL OFL 1.1).
