# Testes em celulares e telas

## Feito (Chromium automatizado)

- Larguras: 320, 360, 375, 390, 412, 430, 600, 768, 820, 1024, 1280, 1440, 1920 px.
- Modelos: home, blog, artigo, categoria, busca, glossário, hub de categorias. Sem rolagem horizontal em nenhum.
- Mobile: gaveta (abre, sanfona, `Esc` fecha), busca em tela cheia, pop-up como folha inferior, slide com bolinhas, modo escuro.
- Regras do pop-up: uma vez por sessão, 24 h após fechar, 1 ano após inscrever (conferido por script).

## Pendente (precisa de aparelhos reais)

Chrome e Samsung Internet no Android, Safari no iPhone e no iPad, Firefox, e os modos paisagem. Anote os resultados abaixo e corrija o que falhar.

| Navegador | Aparelho | Resultado |
| --- | --- | --- |
| Safari | iPhone | a testar |
| Chrome | Android | a testar |
| Samsung Internet | Android | a testar |
| Safari | iPad | a testar |
| Firefox | desktop | a testar |

O tema respeita `env(safe-area-inset-*)` e usa `dvh` nas telas cheias. Campos de formulário usam fonte de 16 px.
