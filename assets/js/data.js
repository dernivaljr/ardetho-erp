const appData = {
  currentUser: {
    id: "USR-001",
    name: "Admin User",
    email: "admin@ardetho.com",
    password: "123456",
    role: "Administrador",
    department: "Gestão",
    avatar: "AD",
    lastAccess: "04/04/2026 às 09:25",
    permissions: {
      fullAccess: true,
      canEditSettings: true,
      canExportReports: true
    },
    preferences: {
      receiveDashboardAlerts: true,
      showDailySummary: false,
      theme: "light",
      language: "pt-BR",
      startupView: "Resumo executivo",
      priorityModule: "Financeiro"
    }
  },

  modules: [
    {
      id: "MOD-001",
      name: "Clientes",
      slug: "clients",
      description: "Gestão de cadastros, status e relacionamento comercial.",
      active: true,
      category: "principal"
    },
    {
      id: "MOD-002",
      name: "Produtos e Serviços",
      slug: "products",
      description: "Controle de itens, categorias, valores e disponibilidade.",
      active: true,
      category: "principal"
    },
    {
      id: "MOD-003",
      name: "Vendas",
      slug: "sales",
      description: "Acompanhamento de pedidos, propostas e status comerciais.",
      active: true,
      category: "principal"
    },
    {
      id: "MOD-004",
      name: "Financeiro",
      slug: "financial",
      description: "Receitas, despesas, vencimentos e visão consolidada.",
      active: true,
      category: "principal"
    },
    {
      id: "MOD-005",
      name: "Relatórios",
      slug: "reports",
      description: "Indicadores operacionais e gerenciais do ambiente.",
      active: true,
      category: "complementar"
    },
    {
      id: "MOD-006",
      name: "Estoque Avançado",
      slug: "advanced-stock",
      description: "Alertas, níveis de estoque e controle ampliado.",
      active: true,
      category: "complementar"
    },
    {
      id: "MOD-007",
      name: "RH",
      slug: "hr",
      description: "Gestão de colaboradores, cargos e registros internos.",
      active: false,
      category: "complementar"
    },
    {
      id: "MOD-008",
      name: "Agenda",
      slug: "schedule",
      description: "Controle de compromissos, visitas e tarefas programadas.",
      active: false,
      category: "complementar"
    },
    {
      id: "MOD-009",
      name: "Relatórios Avançados",
      slug: "advanced-reports",
      description: "Visualizações analíticas expandidas para gestão.",
      active: false,
      category: "complementar"
    }
  ],

clients: [
  {
    id: "CLI-001",
    personType: "PJ",
    status: "Ativo",

    fullName: "",
    cpf: "",
    rg: "",
    birthDate: "",

    companyName: "NovaLab Ltda",
    tradeName: "NovaLab",
    cnpj: "12.345.678/0001-90",
    stateRegistration: "123456789",

    contactName: "Juliana Alves",
    mainEmail: "juliana@novalab.com",
    invoiceEmail: "fiscal@novalab.com",
    phone: "(11) 99876-1234",
    whatsapp: "(11) 99876-1234",

    zipCode: "09000-000",
    street: "Rua das Indústrias",
    number: "123",
    complement: "Sala 4",
    district: "Centro",
    city: "Santo André",
    state: "SP",

    notes: "Cliente com prioridade comercial.",
    createdAt: "2026-04-07"
  },
  {
    id: "CLI-002",
    personType: "PJ",
    status: "Em análise",

    fullName: "",
    cpf: "",
    rg: "",
    birthDate: "",

    companyName: "BioAnalytica Comércio e Serviços Ltda",
    tradeName: "BioAnalytica",
    cnpj: "98.765.432/0001-10",
    stateRegistration: "987654321",

    contactName: "Lucas Mendes",
    mainEmail: "lucas@bioanalytica.com",
    invoiceEmail: "financeiro@bioanalytica.com",
    phone: "(11) 99711-4422",
    whatsapp: "(11) 99711-4422",

    zipCode: "01000-000",
    street: "Avenida Paulista",
    number: "1500",
    complement: "Conjunto 12",
    district: "Bela Vista",
    city: "São Paulo",
    state: "SP",

    notes: "Aguardando validação comercial.",
    createdAt: "2026-04-05"
  },
  {
    id: "CLI-003",
    personType: "PJ",
    status: "Pendente",

    fullName: "",
    cpf: "",
    rg: "",
    birthDate: "",

    companyName: "QualiTech Instrumentação Ltda",
    tradeName: "QualiTech",
    cnpj: "45.321.678/0001-55",
    stateRegistration: "456123789",

    contactName: "Marina Costa",
    mainEmail: "marina@qualitech.com",
    invoiceEmail: "fiscal@qualitech.com",
    phone: "(11) 99620-8710",
    whatsapp: "(11) 99620-8710",

    zipCode: "09700-000",
    street: "Rua Alfa",
    number: "88",
    complement: "",
    district: "Centro",
    city: "São Bernardo",
    state: "SP",

    notes: "Cadastro pendente de aprovação.",
    createdAt: "2026-04-03"
  },
  {
    id: "CLI-004",
    personType: "PF",
    status: "Inativo",

    fullName: "Rafael Souza",
    cpf: "123.456.789-00",
    rg: "45.678.901-2",
    birthDate: "1990-08-15",

    companyName: "",
    tradeName: "",
    cnpj: "",
    stateRegistration: "",

    contactName: "Rafael Souza",
    mainEmail: "rafael@labcore.com",
    invoiceEmail: "",
    phone: "(11) 99455-3399",
    whatsapp: "(11) 99455-3399",

    zipCode: "09090-000",
    street: "Rua Beta",
    number: "45",
    complement: "Casa",
    district: "Vila Assunção",
    city: "Santo André",
    state: "SP",

    notes: "Cliente PF sem movimentação recente.",
    createdAt: "2026-03-29"
  },
  {
    id: "CLI-005",
    personType: "PJ",
    status: "Ativo",

    fullName: "",
    cpf: "",
    rg: "",
    birthDate: "",

    companyName: "TechMed Soluções em Laboratório Ltda",
    tradeName: "TechMed",
    cnpj: "67.890.123/0001-44",
    stateRegistration: "741258963",

    contactName: "Ana Ribeiro",
    mainEmail: "ana@techmed.com",
    invoiceEmail: "nf@techmed.com",
    phone: "(11) 99112-7854",
    whatsapp: "(11) 99112-7854",

    zipCode: "01310-100",
    street: "Rua da Consolação",
    number: "500",
    complement: "8º andar",
    district: "Consolação",
    city: "São Paulo",
    state: "SP",

    notes: "Cliente recorrente com bom histórico.",
    createdAt: "2026-04-01"
  }
],

  products: [
    {
      id: "PRD-001",
      name: "pHmetro Digital",
      category: "Equipamentos",
      type: "Produto",
      price: 2450,
      stock: 18,
      status: "Disponível"
    },
    {
      id: "SRV-004",
      name: "Calibração de pHmetro",
      category: "Serviços",
      type: "Serviço",
      price: 380,
      stock: null,
      status: "Ativo"
    },
    {
      id: "PRD-012",
      name: "Condutivímetro Portátil",
      category: "Equipamentos",
      type: "Produto",
      price: 1980,
      stock: 4,
      status: "Baixo estoque"
    },
    {
      id: "PRD-020",
      name: "Eletrodo combinado",
      category: "Acessórios",
      type: "Produto",
      price: 620,
      stock: 0,
      status: "Indisponível"
    },
    {
      id: "SRV-009",
      name: "Manutenção preventiva",
      category: "Serviços",
      type: "Serviço",
      price: 590,
      stock: null,
      status: "Disponível"
    }
  ],

  sales: [
    {
      id: "PED-2034",
      client: "NovaLab",
      date: "12/04/2026",
      owner: "Ana Lima",
      value: 4800,
      status: "Concluído"
    },
    {
      id: "PED-2035",
      client: "BioAnalytica",
      date: "12/04/2026",
      owner: "Lucas Rocha",
      value: 2150,
      status: "Pendente"
    },
    {
      id: "PED-2036",
      client: "QualiTech",
      date: "13/04/2026",
      owner: "Marina Costa",
      value: 6920,
      status: "Em análise"
    },
    {
      id: "PED-2037",
      client: "LabCore",
      date: "13/04/2026",
      owner: "Rafael Mendes",
      value: 1740,
      status: "Cancelado"
    },
    {
      id: "PED-2038",
      client: "TechMed",
      date: "14/04/2026",
      owner: "Juliana Alves",
      value: 3980,
      status: "Aprovado"
    }
  ],

  financial: [
    {
      id: "FIN-001",
      description: "Recebimento - NovaLab",
      type: "Receita",
      category: "Venda",
      dueDate: "15/04/2026",
      value: 4800,
      status: "Recebido"
    },
    {
      id: "FIN-002",
      description: "Fornecedor Alpha",
      type: "Despesa",
      category: "Compra",
      dueDate: "16/04/2026",
      value: 2150,
      status: "Pendente"
    },
    {
      id: "FIN-003",
      description: "Serviço técnico - QualiTech",
      type: "Receita",
      category: "Serviço",
      dueDate: "18/04/2026",
      value: 1980,
      status: "Agendado"
    },
    {
      id: "FIN-004",
      description: "Internet corporativa",
      type: "Despesa",
      category: "Infraestrutura",
      dueDate: "10/04/2026",
      value: 320,
      status: "Vencido"
    },
    {
      id: "FIN-005",
      description: "Recebimento - TechMed",
      type: "Receita",
      category: "Venda",
      dueDate: "20/04/2026",
      value: 3980,
      status: "Pendente"
    }
  ],

  reports: [
    {
      id: "REP-001",
      name: "Resumo comercial mensal",
      category: "Vendas",
      period: "Março/2026",
      generatedBy: "Admin User",
      date: "12/04/2026",
      status: "Exportado"
    },
    {
      id: "REP-002",
      name: "Financeiro consolidado",
      category: "Financeiro",
      period: "Últimos 30 dias",
      generatedBy: "Ana Lima",
      date: "11/04/2026",
      status: "Gerado"
    },
    {
      id: "REP-003",
      name: "Produtos com baixo estoque",
      category: "Produtos",
      period: "Abril/2026",
      generatedBy: "Lucas Rocha",
      date: "10/04/2026",
      status: "Pendente"
    },
    {
      id: "REP-004",
      name: "Clientes ativos",
      category: "Clientes",
      period: "Últimos 90 dias",
      generatedBy: "Marina Costa",
      date: "09/04/2026",
      status: "Exportado"
    }
  ],

  dashboard: {
    metrics: {
      activeClients: 128,
      registeredProducts: 342,
      monthlySales: 42800,
      pendingBills: 19
    },
    financialPerformance: [48, 66, 54, 82, 72, 92, 78],
    financialLabels: ["Jan", "Fev", "Mar", "Abr", "Mai", "Jun", "Jul"],
    summary: {
      activeModules: 6,
      openOrders: 24,
      expectedIncome: 18400,
      scheduledPayments: 7250
    },
    activities: [
      {
        id: "ACT-001",
        type: "info",
        title: "Novo cliente cadastrado",
        description: "Empresa NovaLab adicionada ao sistema.",
        time: "09:30"
      },
      {
        id: "ACT-002",
        type: "success",
        title: "Pedido aprovado",
        description: "Pedido #2034 aprovado pelo financeiro.",
        time: "10:15"
      },
      {
        id: "ACT-003",
        type: "warning",
        title: "Conta próxima do vencimento",
        description: "Fatura do fornecedor Alpha vence amanhã.",
        time: "11:05"
      },
      {
        id: "ACT-004",
        type: "neutral",
        title: "Relatório exportado",
        description: "Resumo mensal enviado pelo módulo de relatórios.",
        time: "11:42"
      }
    ],
    moduleStatus: [
      { name: "CRM / Clientes", status: "Ativo" },
      { name: "Financeiro", status: "Ativo" },
      { name: "Relatórios", status: "Sincronizado" },
      { name: "Estoque avançado", status: "Parcial" },
      { name: "RH", status: "Inativo" }
    ]
  }
};