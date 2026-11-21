# Hub de conteúdo Klyvorenza: instruções (v1.1)

Arquivos desta pasta (nada do tema foi alterado; esta pasta não é carregada pelo WordPress):

| Arquivo | O que é |
|---|---|
| `pagina-hub.html` | Bloco único (HTML + CSS + JS) da página. Já inclui o pop-up. |
| `popup-global.html` | Só o pop-up, para colar em um local global do site. |

## 1. Onde colar

**Hub:** na página inicial (Elementor), logo abaixo de "Posts Recentes", adicione o widget **HTML** (ou um bloco **HTML personalizado**, se a página for do editor de blocos) e cole o conteúdo inteiro de `pagina-hub.html`.
Evite shortcode e editor clássico: o WordPress pode inserir `<p>` e `<br>` dentro do código e quebrar o script.

**Pop-up global:** cole `popup-global.html` em um local que carregue em todas as páginas e artigos (rodapé do site). Opções: plugin WPCode ou "Insert Headers and Footers" na área *Footer*, ou o código personalizado do Elementor Pro (*Custom Code*, local `</body>`).
Se o hub e o bloco global estiverem na mesma página, só um pop-up abre (trava `window.__kvzPopupInit`).

Depois de colar: limpe o cache do LiteSpeed. Se a otimização de JS do LiteSpeed combinar ou adiar os scripts e algo quebrar, exclua `kvz-script` e `kvz-pop-script` da combinação/adiamento.

## Novidades da v1.1

- **Menu com 7 itens:** Especial, Moda Feminina, Cuidados Femininos, Web Stories, Datas Festivas, **Glossário** (rola até a seção que já existe) e **Inscreva-se**.
- **Inscreva-se:** um clique abre a newsletter na hora, em qualquer ponto da página, e abre de novo a cada clique. Ele ignora as regras do pop-up automático (fechado hoje, inscrito há menos de 1 ano) e, se fechado, não grava "fechado hoje". Sem JavaScript, o link leva ao formulário do fim da página (`#newsletter`). Isso vale para o bloco do hub; o bloco global sozinho não tem o botão.
- **Celular:** a newsletter fica centralizada na tela (antes era uma folha colada na base). O hub ocupa a largura inteira da tela, mesmo dentro de containers com padding do tema, e os cards ficam de borda a borda. Se o seu tema/Elementor cortar o conteúdo que passa do container (`overflow: hidden`), me avise para ajustar.

## 2. Variáveis que você pode configurar

Ficam no começo do `<script>`. **Os valores de `CONFIG` precisam ser iguais nos dois blocos** (hub e pop-up).

| Variável | O que faz |
|---|---|
| `CONFIG.LOGO_URL` | Logo da newsletter e do pop-up. Já aponta para a logo do cabeçalho (`.../2026/11/Logo-Klyvorenza-1.png`). Para usar outra, envie em *Mídia > Adicionar* e cole a URL. |
| `CONFIG.NEWSLETTER_ENDPOINT` | **Única** URL de envio (Brevo, Mailchimp, MailerLite, plugin). Vazia = o formulário mostra "newsletter ainda não está ativa" e não finge sucesso. |
| `CONFIG.NEWSLETTER_FIELDS` | Nomes dos campos que o serviço espera. Padrão `name`/`email`. Mailchimp: `{ name: 'FNAME', email: 'EMAIL' }`. |
| `CONFIG.NEWSLETTER_EXTRA` | Campos fixos do serviço (lista, token). |
| `CONFIG.NEWSLETTER_NO_CORS` | `true` para serviços sem CORS (Mailchimp e formulários HTML simples). O envio acontece, mas o navegador não lê a resposta, então o site assume sucesso. |
| `CONFIG.PRIVACY_URL` | Política de privacidade (LGPD). Já aponta para `/politica-de-privacidade/`. |
| `CONFIG.DARK_BODY_CLASS` / `DARK_STORAGE_KEY` | Classe do `<body>` e chave do localStorage do modo escuro do tema. Confirmado no site: `ashe-dark-mode` e `asheDarkMode`. |
| `CONFIG.FOLLOW_OS_DARK` | `true` segue `prefers-color-scheme` quando a pessoa nunca usou o botão de lua do tema. `false` só segue o botão do tema. |
| `CONFIG.POPUP_DELAY_MS` | Atraso do pop-up (3000). |
| `HUB.SECTIONS` | **Mapa seção → categorias** (por slug; a ordem da lista é a ordem dos cards). `parents` = slugs de categorias-pai cujas filhas entram sozinhas na seção. |
| `HUB.LABELS` | Nome exibido diferente do nome no WordPress (hoje: "Cabelos" e "Casacos e Jaquetas"). |
| `HUB.FIXED_HEADER_SELECTORS` | Barras fixas do tema que ficam acima do menu do hub (`#wpadminbar`, `#main-nav`). |
| `HUB.STORIES_*`, `HUB.GLOSSARY_*` | Endereços das Web Stories e do glossário. |

Exemplo (Mailchimp):

```js
NEWSLETTER_ENDPOINT: 'https://SEU-SITE.usXX.list-manage.com/subscribe/post?u=XXXX&id=XXXX',
NEWSLETTER_FIELDS: { name: 'FNAME', email: 'EMAIL' },
NEWSLETTER_NO_CORS: true,
```

### Categoria nova aparece sozinha?

Hoje **todas** as categorias do site são de nível 0 (sem pai). Por isso o agrupamento é por slug:

- Categoria nova, solta: acrescente o slug na lista `slugs` da seção certa em `HUB.SECTIONS`.
- Sem editar código: crie uma categoria-pai com slug `especial`, `moda-feminina`, `cuidados-femininos` ou `datas-festivas` e crie as novas como filhas dela. Elas entram sozinhas, assim que tiverem 1 artigo publicado. A categoria-pai em si não vira card. Isso muda a hierarquia (e talvez as URLs) das categorias, então decida antes.
- Artigos novos entram na contagem e na imagem da categoria sozinhos.

## 3. Como testar

1. Abra a home e role até o hub: menu fixo logo abaixo do menu do tema, item ativo muda ao rolar, clique rola com suavidade sem ficar sob os menus.
2. Redimensione (ou use o modo celular): 4 colunas ≥1024px, 3 em 768–1023px, 2 abaixo disso, 1 abaixo de 360px; sem rolagem horizontal.
3. Alterne o botão de lua do tema: o hub segue.
4. Pop-up: abre 3 s após carregar. Para testar de novo, no console: `localStorage.removeItem('kvz_news_dismissed'); localStorage.removeItem('kvz_news_subscribed');` e recarregue.
   - Fechar sem enviar: não volta no mesmo dia em nenhuma página (chave `kvz_news_dismissed` = data local).
   - Enviar: fecha e só volta em 1 ano (`kvz_news_subscribed` = timestamp).
   - Sem localStorage (navegação restrita): usa cookie com os mesmos nomes.
5. Preencha o formulário com seu e-mail e confira se chegou ao serviço.
6. Rich Results Test / Search Console: o JSON-LD (`CollectionPage` + `ItemList`) é criado depois que os dados carregam.

## 4. Pontos que dependem do seu site (verificado ao vivo)

| Ponto | Resultado e o que foi feito |
|---|---|
| Tema ativo | **Ashe Pro + tema filho** (como você disse). O repositório `tema-klyvorenza` é outro tema (de blocos), que não está no ar; ele tem pop-up e newsletter próprios (`klyv-news-*`). Se um dia for ativado, haveria dois pop-ups. |
| Modo escuro | Ashe alterna `body.ashe-dark-mode` e grava `localStorage.asheDarkMode` (`on`/`off`). O hub segue a classe; sem escolha do botão, segue `prefers-color-scheme`. Isso pode deixar o hub escuro numa página clara se o sistema do visitante for escuro (desligue com `FOLLOW_OS_DARK: false`). |
| `/wp-json/wp/v2/categories` e `posts` | Expostos. Paginados em paralelo (100 por página) e guardados em sessionStorage por 30 min. |
| Web Stories | O tipo `fws_story` **não** está na REST (404). **Alternativa:** o script lê o HTML de `/fws-story/` (cards `.fws-card`, paginação `.fws-nav`) e monta o carrossel. Hoje existe 1 story. Se o plugin mudar a marcação, ajuste `parseStories`. |
| Glossário | Taxonomia `fastgloss_category` **está** na REST (26 letras). Cada slide mostra a imagem de destaque do termo mais recente da letra, com link para `/glossario-categoria/{letra}/`. Se a REST falhar, monta A–Z pelos endereços e lê a `og:image` da página da letra só quando o slide aparece. |
| Zoom de imagem do tema | Não encontrei no CSS/HTML do Ashe Pro nenhuma regra de zoom nas imagens de lista, e não há como ativar uma por HTML sem mexer no tema. O zoom é próprio do hub (`scale(1.07)`, desligado com `prefers-reduced-motion`). |
| Menu fixo do Ashe | `#main-nav[data-fixed="1"]`. O hub mede essa barra (e a barra de admin) para se posicionar logo abaixo. Se um ancestral (transform/contain do Elementor) quebrar o `position: fixed`, o hub detecta e cai para `sticky`. |
| Pop-up e foco preso | "Foco preso" e "sem bloquear a rolagem" convivem assim: o `Tab` fica dentro do pop-up, mas não há fundo escurecido nem trava de rolagem. Em telas ≤600px ele vira uma folha na base. |
| Outros pop-ups | O CSS da home tem elementos fixos de outros plugins, e a REST mostra o CookieYes (`cky/v1`) ativo. O pop-up usa `z-index: 100000`: confira se não atrapalha o banner de cookies na base da tela. |
| Endereço (www) | localStorage vale por endereço. `www.klyvorenza.com` e `klyvorenza.com` não compartilham o "já fechei". |
| Schema | O Rank Math já imprime schema na página; o hub acrescenta um `CollectionPage` próprio, com `@id` diferente. |

## Testes que foram feitos

63 verificações automáticas em Chromium (Playwright), numa página-simulação com cabeçalho fixo estilo Ashe e CSS agressivo de tema, usando a REST real do site: 8 larguras (320 a 1440px) sem rolagem horizontal, colunas corretas, alvos ≥44px, 23 categorias sem repetição e 23 imagens diferentes, menu fixo/scrollspy/âncoras, carrosséis (setas e teclado), claro/escuro/`prefers-reduced-motion`, validação e envio simulados (sucesso, erro, sem endpoint), todas as regras do pop-up (3 s, dia, 1 ano, cookie, ESC, foco), sem JS e REST fora do ar.

**Não foi testado:** dentro da sua página Elementor real, Safari/Firefox, nem medição de CLS/Lighthouse. Faça o teste do item 3 depois de colar.
