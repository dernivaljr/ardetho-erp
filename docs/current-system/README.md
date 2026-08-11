# Documentacao da versao Web/PWA atual

Esta pasta documenta o estado atual do Ardetho ERP como projeto Web/PWA estatico, baseado em HTML, CSS, JavaScript puro, LocalStorage, manifest e service worker.

O objetivo desta documentacao e registrar como o sistema funciona hoje. Ela nao define alternativas tecnologicas, nao propoe mudanca de tecnologia e nao substitui os documentos historicos da pasta `documents/`.

## Escopo

Esta documentacao cobre:

- paginas publicas e internas existentes;
- estrutura de arquivos atual;
- arquitetura multi-page estatica;
- ordem de carregamento dos scripts;
- dados simulados em `assets/js/data.js`;
- persistencia em `localStorage` via `assets/js/storage.js`;
- autenticacao simulada;
- fluxo dos modulos;
- layout compartilhado;
- CSS e responsividade;
- PWA, `manifest.json` e `service-worker.js`;
- limitacoes conhecidas e divida tecnica da versao atual.

## Documentos

- [01 - Visao geral](01-visao-geral.md)
- [02 - Estrutura do projeto](02-estrutura-do-projeto.md)
- [03 - Arquitetura atual](03-arquitetura-atual.md)
- [04 - JavaScript](04-javascript.md)
- [05 - Dados e storage](05-dados-e-storage.md)
- [06 - Autenticacao](06-autenticacao.md)
- [07 - Modulos](07-modulos.md)
- [08 - Layout, CSS e responsividade](08-layout-css-responsividade.md)
- [09 - PWA](09-pwa.md)
- [10 - Limitacoes e divida tecnica](10-limitacoes-divida-tecnica.md)

## Fontes verificadas

As informacoes foram verificadas diretamente nos arquivos da versao atual:

- paginas HTML da raiz;
- `assets/js/*.js`;
- `assets/css/*.css`;
- `manifest.json`;
- `service-worker.js`;
- imagens em `assets/images/`;
- `README.MD`;
- `.gitignore`.

## Notas de leitura

Os documentos diferenciam:

- comportamento atual: o que o sistema faz hoje;
- limitacao conhecida: restricao funcional ou tecnica existente;
- divida tecnica: ponto de organizacao, duplicacao ou manutencao que dificulta evolucao.
