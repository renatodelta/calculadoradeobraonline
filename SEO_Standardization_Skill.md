# Skill: Padronização de URLs e SEO para Microsites Estáticos

Esta skill descreve como resolver "Erros de Redirecionamento" e inconsistências técnicas em sites HTML estáticos hospedados com Cloudflare ou servidores que removem extensões automaticamente.

## 1. Unificação de Estrutura de URLs
Para evitar loops de redirecionamento e erros de indexação, **Sitemap**, **Links Internos** e **Tags Canonical** devem ser idênticos.

### Checklist:
- [ ] **Remover .html de links internos:** Substitua `<a href="página.html">` por `<a href="página">`.
- [ ] **Sitemap Limpo:** Todas as entradas `<loc>` devem omitir o `.html`.
- [ ] **Tag Canonical:** Deve sempre apontar para a versão sem extensão: 
  `<link rel="canonical" href="https://dominio.com/página" />`.

## 2. Alinhamento com Cloudflare / Servidor
Se o servidor remove extensões `.html` automaticamente, o código **não deve** tentar forçá-las. Isso evita o "301 Redirect" desnecessário que o Google reporta como erro.

- Se a página é `contato.html`, o link deve ser apenas `contato`.
- Se a página é `index.html` em uma pasta, use apenas `./`.

## 3. Integridade de Codificação (UTF-8)
Ao realizar edições em massa via scripts (PowerShell/Python), garanta que o arquivo seja salvo em **UTF-8 sem BOM**.
- Erros comuns: `Ã¡`, `Ã§`, `Ã£` aparecendo no lugar de `á`, `ç`, `ã`.
- **Correção:** Use editores que forcem a codificação UTF-8 e revise as páginas após rodar scripts de "Search & Replace".

## 4. Sintaxe HTML e SEO
Parsers de SEO (Googlebot) podem se confundir com tags malformadas. 
- **Erro comum:** `<img class="img" / fetchpriority="high">`. A barra `/` no meio dos atributos quebra a leitura.
- **Correção:** Remova barras desnecessárias e use atributos limpos: `<img class="img" fetchpriority="high">`.

## 5. Fluxo de Validação no Google Search Console
Após aplicar as mudanças acima:
1. Reenvie o sitemap no painel do GSC.
2. Clique em **"Validar Correção"** no relatório de Erros de Redirecionamento.
