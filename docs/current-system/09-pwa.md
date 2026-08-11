# PWA

A versao atual inclui suporte basico a Progressive Web App por meio de `manifest.json`, `service-worker.js` e `assets/js/pwa.js`.

## Arquivos responsaveis

- `manifest.json`;
- `service-worker.js`;
- `assets/js/pwa.js`;
- tags `<link rel="manifest" href="manifest.json">` nos HTML;
- tags `<meta name="theme-color" content="#0F172A">` nos HTML.

## Registro do service worker

`assets/js/pwa.js` verifica:

```js
if ("serviceWorker" in navigator)
```

No evento `load`, registra:

```text
./service-worker.js
```

Em caso de sucesso, escreve no console o escopo registrado. Em caso de erro diferente de `AbortError`, escreve erro no console.

## `manifest.json`

Campos atuais:

- `name`: `Ardetho ERP`;
- `short_name`: `Ardetho`;
- `description`: `Sistema ERP modular para gestão empresarial.`;
- `start_url`: `./index.html`;
- `scope`: `./`;
- `display`: `standalone`;
- `background_color`: `#0F172A`;
- `theme_color`: `#0F172A`;
- `orientation`: `portrait-primary`;
- `categories`: `business`, `productivity`;
- `lang`: `pt-BR`.

Icones declarados:

```text
assets/images/ardetho-icon.png - 211x211 - any
```

Estado verificado: a imagem real `assets/images/ardetho-icon.png` tem 211x211 e o manifest declara esse tamanho real. Ainda faltam assets dedicados 192x192, 512x512 e um icone maskable apropriado.

## `service-worker.js`

Nome do cache atual:

```text
ardetho-erp-v22
```

O service worker usa lista manual `FILES_TO_CACHE`.

### Arquivos base e HTML cacheados

- `./`;
- `./manifest.json`;
- `./index.html`;
- `./about.html`;
- `./modules.html`;
- `./contact.html`;
- `./login.html`;
- `./dashboard.html`;
- `./clients.html`;
- `./client-form.html`;
- `./products.html`;
- `./product-form.html`;
- `./sales.html`;
- `./sale-form.html`;
- `./financial.html`;
- `./financial-form.html`;
- `./reports.html`;
- `./erp-modules.html`;
- `./settings.html`;
- `./profile.html`;
- `./hr.html`;
- `./hr-form.html`.

### CSS cacheado

- `variables.css`;
- `global.css`;
- `layout.css`;
- `components.css`;
- `public.css`;
- `auth.css`;
- `dashboard.css`;
- `pages.css`;
- `responsive.css`.

### JavaScript cacheado

- `data.js`;
- `storage.js`;
- `auth.js`;
- `layout.js`;
- `utils.js`;
- `dashboard.js`;
- `clients.js`;
- `client-form.js`;
- `products.js`;
- `product-form.js`;
- `sales.js`;
- `sale-form.js`;
- `financial.js`;
- `financial-form.js`;
- `reports.js`;
- `modules.js`;
- `settings.js`;
- `profile.js`;
- `hr.js`;
- `hr-form.js`;
- `pwa.js`.

### Imagens cacheadas

- `assets/images/ardetho-logo.png`;
- `assets/images/ardetho-icon.png`;
- `assets/images/mecanica-xyz-logo.png`;
- `assets/images/mecanica-xyz-icon.png`.

## Eventos do service worker

### `install`

- chama `self.skipWaiting()`;
- abre `ardetho-erp-v22`;
- cria requests com `new Request(file, { cache: "reload" })`;
- executa `cache.addAll(freshRequests)`.

O uso de `cache: "reload"` foi adotado para evitar que uma nova versao do Cache Storage seja populada com respostas antigas vindas do HTTP cache do navegador.

### `activate`

- lista caches existentes;
- remove caches com nome diferente de `ardetho-erp-v22`;
- chama `self.clients.claim()`.

### `fetch`

Estrategia atual:

```text
caches.match(event.request)
  se existir resposta em cache, retorna cache
  senao, executa fetch(event.request)
```

Esta e uma estrategia cache-first simples.

## Comportamento atual

A PWA pode funcionar parcialmente offline para arquivos listados no cache. Arquivos nao listados dependem de rede.

## Limitacoes conhecidas

- cache e manual;
- nao ha versionamento automatico por hash;
- `service-worker.js` nao esta em `FILES_TO_CACHE`;
- fontes do Google nao estao em `FILES_TO_CACHE`;
- nao ha runtime caching;
- nao ha fallback offline especifico;
- a estrategia cache-first pode servir arquivos antigos ate a troca de `CACHE_NAME`.

## Divida tecnica

- toda inclusao de nova pagina, CSS, JS ou imagem precisa ser refletida manualmente no service worker;
- faltam icones PWA dedicados 192x192, 512x512 e maskable;
- o cache nao diferencia arquivos publicos, internos ou dados locais;
- mensagens de registro ficam no console em producao.
