# Checklist de baseline manual - Web/PWA atual

Este checklist registra a baseline funcional da versão Web/PWA atual do Ardetho ERP antes de correções ou refatorações.

Status permitido para cada teste: `Não testado`, `Passou`, `Falhou`, `Não aplicável`.

## Ambiente da baseline

| Item | Valor |
| --- | --- |
| Branch | `pwa-review` |
| Commit inicial | `da19dcb2c3def40981d1cda72e20024b89205fc2` |
| Navegador e versão | A preencher na execução |
| Servidor/local URL usado | A preencher na execução |
| Resolução desktop | A preencher na execução |
| Resolução mobile | A preencher na execução |
| Data da execução | A preencher na execução |

## Pré-condições gerais

- Executar os testes sem alterar o código funcional.
- Iniciar com os dados atuais da aplicação, salvo quando o próprio teste criar dados.
- Registrar evidências e observações sempre que o resultado for `Falhou` ou `Não aplicável`.
- Para testes de PWA, servir a aplicação por `localhost` ou outro contexto compatível com Service Worker. Não executar os testes de PWA abrindo os arquivos diretamente via `file://`.

## Páginas públicas

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| PUB-01 | Página inicial pública | Abrir a URL raiz ou `index.html`. Verificar hero, chamada principal, seções e navegação. | A página carrega sem erro visual crítico, sem exigir login, com links principais acessíveis. | P0 | Não testado |  |
| PUB-02 | Página Sobre | Abrir `about.html` pelo menu público. | Conteúdo institucional e layout público carregam corretamente. | P1 | Não testado |  |
| PUB-03 | Página Módulos | Abrir `modules.html` pelo menu público. | Lista de módulos públicos aparece sem exigir autenticação. | P1 | Não testado |  |
| PUB-04 | Página Contato | Abrir `contact.html` pelo menu público e navegar pelo formulário. | Campos e botões aparecem; validações nativas do formulário podem ser verificadas sem envio real. | P1 | Não testado |  |
| PUB-05 | Link para login | A partir de uma página pública, acionar o link/botão de login. | O navegador abre `login.html`. | P0 | Não testado |  |

## Autenticação e proteção

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| AUTH-01 | Login válido Ardetho | Abrir `login.html`, informar `admin@ardetho.com` e `123456`, enviar o formulário. | Usuário e empresa são salvos no LocalStorage e o sistema redireciona para `dashboard.html`. | P0 | Não testado |  |
| AUTH-02 | Login válido Mecânica XYZ | Sair do sistema, abrir `login.html`, informar `admin@mecanicaxyz.com` e `123456`, enviar o formulário. | Usuário e empresa da Mecânica XYZ são salvos no LocalStorage e o sistema redireciona para `dashboard.html`. | P0 | Não testado |  |
| AUTH-03 | Login inválido | Abrir `login.html`, informar credenciais inexistentes e enviar. | O acesso é recusado e a aplicação mostra feedback de erro. | P0 | Não testado |  |
| AUTH-04 | Campos obrigatórios do login | Abrir `login.html`, tentar enviar sem preencher email e/ou senha. | O formulário impede envio ou apresenta validação apropriada. | P1 | Não testado |  |
| AUTH-05 | Redirecionamento de usuário já autenticado | Com usuário autenticado, abrir `login.html` diretamente. | A aplicação redireciona o usuário para `dashboard.html`. | P1 | Não testado |  |
| AUTH-06 | Proteção de página interna | Remover/limpar sessão do usuário, abrir diretamente `dashboard.html` ou outra página interna. | A página interna não fica acessível e o usuário é redirecionado para login. | P0 | Não testado |  |
| AUTH-07 | Logout | Com usuário autenticado, acionar logout no layout interno. | Dados de sessão são removidos e o usuário retorna ao fluxo público/login. | P0 | Não testado |  |

## Dashboard

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| DASH-01 | Renderização inicial | Entrar no sistema e abrir `dashboard.html`. | Cards, gráficos/listas e informações de resumo carregam a partir dos dados locais. | P0 | Não testado |  |
| DASH-02 | Navegação pelos atalhos | Acionar atalhos ou links visíveis no dashboard para outros módulos. | Os links levam para as páginas internas correspondentes. | P1 | Não testado |  |
| DASH-03 | Impacto das configurações do dashboard | Alterar configurações relacionadas ao dashboard em `settings.html`, voltar ao dashboard e recarregar. | O dashboard reflete as configurações persistidas quando esse comportamento estiver implementado. | P1 | Não testado |  |

## Clientes

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| CLI-01 | Listagem de clientes | Abrir `clients.html` autenticado. | Clientes do LocalStorage aparecem na listagem. | P0 | Não testado |  |
| CLI-02 | Pesquisa/filtro de clientes | Usar o campo de busca/filtros da página de clientes. | A lista é filtrada de acordo com o termo ou critério informado. | P1 | Não testado |  |
| CLI-03 | Cadastro de cliente | Acessar o fluxo de novo cliente, preencher dados válidos e salvar. | Novo cliente aparece na listagem e é persistido nos dados locais. | P0 | Não testado |  |
| CLI-04 | Validação de cliente | Tentar salvar cliente sem campos obrigatórios. | O formulário impede ou sinaliza o cadastro inválido. | P0 | Não testado |  |
| CLI-05 | Edição de cliente | Abrir um cliente existente, alterar dados e salvar. | Alterações aparecem na listagem/detalhe e permanecem após recarregar. | P0 | Não testado |  |
| CLI-06 | Exclusão de cliente | Excluir um cliente criado para teste e confirmar quando aplicável. | Cliente removido deixa de aparecer na listagem. | P1 | Não testado |  |

## Produtos

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| PROD-01 | Listagem de produtos | Abrir `products.html`. | Produtos cadastrados nos dados locais aparecem na tela. | P0 | Não testado |  |
| PROD-02 | Pesquisa/filtro de produtos | Usar busca ou filtros disponíveis na página. | A listagem responde ao critério informado. | P1 | Não testado |  |
| PROD-03 | Cadastro de produto | Acessar o formulário de novo produto, preencher dados válidos e salvar. | Produto é criado, listado e persistido localmente. | P0 | Não testado |  |
| PROD-04 | Validação de produto | Tentar salvar produto com campos obrigatórios vazios ou valores inválidos. | O formulário impede ou sinaliza o cadastro inválido. | P0 | Não testado |  |
| PROD-05 | Edição/status de produto | Alterar dados e status de um produto existente. | Alterações são refletidas na listagem e persistidas após recarregar. | P1 | Não testado |  |

## Vendas

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| SALES-01 | Listagem de vendas | Abrir `sales.html`. | Vendas dos dados locais aparecem na listagem. | P0 | Não testado |  |
| SALES-02 | Pesquisa/filtro de vendas | Usar busca, filtros de status ou período quando disponíveis. | A listagem exibe somente vendas correspondentes aos critérios. | P1 | Não testado |  |
| SALES-03 | Cadastro de venda | Criar uma venda com cliente, itens e valores válidos. | Venda é salva, listada e persistida localmente. | P0 | Não testado |  |
| SALES-04 | Validação de venda | Tentar salvar venda incompleta ou sem itens obrigatórios. | O formulário impede ou sinaliza o cadastro inválido. | P0 | Não testado |  |
| SALES-05 | Integração venda/financeiro | Criar uma venda e verificar o financeiro. | Quando implementado, a receita correspondente aparece em `financial.html`. | P1 | Não testado |  |

## Financeiro

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| FIN-01 | Listagem financeira | Abrir `financial.html`. | Lançamentos financeiros dos dados locais aparecem na página. | P0 | Não testado |  |
| FIN-02 | Filtros financeiros | Aplicar filtros de tipo, status ou período quando disponíveis. | Os lançamentos são filtrados corretamente. | P1 | Não testado |  |
| FIN-03 | Cadastro de receita | Criar uma receita manual com dados válidos. | Receita é salva, listada e persistida localmente. | P0 | Não testado |  |
| FIN-04 | Cadastro de despesa | Criar uma despesa manual com dados válidos. | Despesa é salva, listada e persistida localmente. | P0 | Não testado |  |
| FIN-05 | Validação financeira | Tentar salvar lançamento sem campos obrigatórios. | O formulário impede ou sinaliza o cadastro inválido. | P0 | Não testado |  |

## Relatórios

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| REP-01 | Abertura de relatórios | Abrir `reports.html`. | Relatórios/resumos aparecem com base nos dados locais. | P0 | Não testado |  |
| REP-02 | Filtro por período | Alterar período ou critérios disponíveis. | Os dados exibidos mudam de acordo com o filtro. | P1 | Não testado |  |
| REP-03 | Exportação CSV | Acionar exportação quando disponível. | Um arquivo CSV é gerado/baixado com dados compatíveis com o relatório exibido. | P1 | Não testado |  |

## Módulos ERP

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| MOD-01 | Listagem/estado dos módulos | Abrir área de configuração ou página que apresenta módulos ativos. | Módulos ativos/inativos refletem os dados persistidos. | P1 | Não testado |  |
| MOD-02 | Ativação/desativação de módulo | Alterar o estado de um módulo quando a interface permitir e recarregar. | O estado escolhido permanece no LocalStorage. | P1 | Não testado |  |
| MOD-03 | Acesso a módulo inativo | Tentar acessar página ou item de menu de módulo inativo. | Registrar o comportamento atual observado: bloqueio, ocultação, alerta, acesso livre ou erro. | P1 | Não testado |  |
| MOD-04 | Módulos com páginas ausentes | Tentar navegar para módulos apresentados pela configuração, especialmente quando não houver página correspondente. | Registrar o comportamento atual observado sem corrigir links ou arquivos ausentes. | P2 | Não testado |  |

## Configurações

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| SET-01 | Abertura de configurações | Abrir `settings.html`. | Página carrega configurações atuais sem erro crítico. | P0 | Não testado |  |
| SET-02 | Salvamento de configurações | Alterar campos de configuração e salvar. | Configurações são persistidas em `ardetho_settings` e refletidas onde aplicável. | P1 | Não testado |  |
| SET-03 | Restauração/redefinição de configurações | Usar ação de reset/restauração de configurações, se disponível. | Configurações retornam ao estado esperado pela aplicação atual. | P2 | Não testado |  |

## Perfil e branding

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| PROF-01 | Abertura do perfil | Abrir `profile.html`. | Dados do usuário e da empresa atual aparecem corretamente. | P0 | Não testado |  |
| PROF-02 | Edição de perfil | Alterar dados editáveis do perfil e salvar. | Dados alterados são persistidos e exibidos após recarregar. | P1 | Não testado |  |
| BRAND-01 | Branding Ardetho | Entrar como `admin@ardetho.com` e observar logo, nome da empresa e cores aplicadas no layout interno. | Branding da Ardetho aparece de forma consistente nas áreas que usam perfil da empresa. | P1 | Não testado |  |
| BRAND-02 | Branding Mecânica XYZ | Entrar como `admin@mecanicaxyz.com` e observar logo, nome da empresa e cores aplicadas no layout interno. | Branding da Mecânica XYZ aparece de forma consistente nas áreas que usam perfil da empresa. | P1 | Não testado |  |

## RH

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| HR-01 | Listagem de RH | Abrir `hr.html`. | Registros de colaboradores/RH aparecem a partir dos dados locais. | P0 | Não testado |  |
| HR-02 | Pesquisa/filtro de RH | Usar busca ou filtros disponíveis em `hr.html`. | Lista de RH responde aos critérios informados. | P1 | Não testado |  |
| HR-03 | Cadastro de registro de RH | Criar um novo registro com dados válidos. | Registro é salvo, listado e persistido localmente. | P1 | Não testado |  |
| HR-04 | Edição/exclusão de RH | Editar e excluir um registro criado para teste. | Alterações são refletidas na listagem e persistidas após recarregar. | P1 | Não testado |  |

## Persistência no LocalStorage

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| LS-01 | Persistência após recarregar a página | Criar ou alterar um registro, recarregar a página atual com o navegador aberto. | O dado criado/alterado permanece disponível após o reload. | P0 | Não testado |  |
| LS-02 | Persistência após fechar e reabrir o navegador | Criar ou alterar um registro, fechar o navegador, reabrir o navegador e acessar a aplicação novamente. | O dado criado/alterado permanece disponível no LocalStorage do mesmo navegador/perfil. | P0 | Não testado |  |
| LS-03 | Chaves principais do LocalStorage | Abrir DevTools/Application/LocalStorage e verificar chaves usadas pela aplicação. | Chaves esperadas incluem `ardetho_app_data`, `ardetho_current_user`, `ardetho_current_company_profile`, `ardetho_active_modules` e, quando configurações forem salvas, `ardetho_settings`. | P1 | Não testado |  |
| LS-04 | Sessão persistida | Fazer login, recarregar e reabrir página interna. | Usuário permanece autenticado enquanto `ardetho_current_user` estiver salvo. | P1 | Não testado |  |

## Responsividade e menu mobile

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| RESP-01 | Layout desktop | Abrir páginas principais na resolução desktop definida no ambiente da baseline. | Layout não apresenta sobreposições críticas e navegação principal permanece utilizável. | P0 | Não testado |  |
| RESP-02 | Layout tablet/mobile | Simular ou abrir na resolução mobile definida no ambiente da baseline. | Conteúdo principal se adapta sem perda crítica de informação. | P0 | Não testado |  |
| RESP-03 | Menu mobile público | Em resolução mobile, abrir e fechar o menu nas páginas públicas. | Menu abre, fecha e permite navegar para os links públicos. | P1 | Não testado |  |
| RESP-04 | Menu mobile interno | Em resolução mobile, abrir e fechar o menu/sidebar em páginas internas. | Navegação interna permanece acessível e não bloqueia indevidamente o conteúdo. | P1 | Não testado |  |
| RESP-05 | Formulários em mobile | Abrir formulários de clientes, produtos, vendas e financeiro em resolução mobile. | Campos e botões permanecem acessíveis, sem corte ou sobreposição crítica. | P1 | Não testado |  |

## PWA, Service Worker e offline

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| PWA-01 | Manifest | Com a aplicação servida por `localhost` ou contexto compatível, abrir DevTools/Application/Manifest. | Manifest é reconhecido pelo navegador com nome, ícones, `start_url`, `scope` e modo de exibição. | P1 | Não testado |  |
| PWA-02 | Registro do Service Worker | Abrir a aplicação em contexto compatível com Service Worker e verificar DevTools/Application/Service Workers. | `service-worker.js` é registrado sem erro bloqueante. | P0 | Não testado |  |
| PWA-03 | Cache inicial | Após carregar a aplicação, verificar Cache Storage. | Cache definido pelo Service Worker é criado com os recursos listados na estratégia atual. | P1 | Não testado |  |
| PWA-04 | Instalação da PWA | Verificar se o navegador oferece instalação da PWA e executar a instalação quando disponível. | Aplicação pode ser instalada quando os critérios do navegador forem atendidos. | P1 | Não testado |  |
| PWA-05 | Abertura instalada | Abrir a PWA instalada. | Aplicação abre no modo definido pelo manifest e carrega a tela inicial esperada. | P1 | Não testado |  |
| PWA-06 | Funcionamento offline geral | Com a aplicação previamente carregada, ativar offline no navegador e acessar páginas cacheadas. | Páginas/recursos presentes no cache continuam acessíveis conforme a estratégia atual. | P0 | Não testado |  |
| PWA-07 | Offline com branding Ardetho | Entrar como Ardetho, carregar páginas internas, ativar offline e recarregar. | Registrar quais recursos de marca carregam offline e quais falham, de acordo com o cache atual. | P1 | Não testado |  |
| PWA-08 | Offline com branding Mecânica XYZ | Entrar como Mecânica XYZ, carregar páginas internas, ativar offline e recarregar. | Registrar o comportamento observado dos logos e recursos visuais, sem assumir antecipadamente sucesso ou falha. | P1 | Não testado |  |

## Teste destrutivo final

Executar somente depois dos testes que dependem dos dados criados durante a baseline.

| ID | Funcionalidade | Passos | Resultado esperado | Prioridade | Resultado | Observações |
| --- | --- | --- | --- | --- | --- | --- |
| DEST-01 | Reset dos dados locais com `resetAppData()` | Abrir o console do navegador, executar `resetAppData()` e recarregar a aplicação. | Dados locais retornam ao estado inicial definido em `assets/js/data.js`. Este teste é destrutivo para os dados criados durante a execução. | P2 | Não testado |  |
