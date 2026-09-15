<?php
declare(strict_types=1);

class Produto
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function listar(array $filtros = []): array
    {
        $where = [];
        $params = [];

        if (($filtros['busca'] ?? '') !== '') {
            $camposBusca = [
                'codigo',
                'nome',
                'categoria',
                'descricao',
                'marca',
                'fornecedor',
                'departamento',
            ];
            $partesBusca = [];
            $termoBusca = '%' . $filtros['busca'] . '%';

            foreach ($camposBusca as $indice => $campo) {
                $placeholder = 'busca_' . $indice;
                $partesBusca[] = "{$campo} LIKE :{$placeholder}";
                $params[$placeholder] = $termoBusca;
            }

            $where[] = '(' . implode(' OR ', $partesBusca) . ')';
        }

        if (($filtros['categoria'] ?? '') !== '') {
            $where[] = 'categoria = :categoria';
            $params['categoria'] = $filtros['categoria'];
        }

        $this->aplicarFiltroStatus($where, $params, (string) ($filtros['status'] ?? ''));

        $sql = 'SELECT *
                  FROM produtos';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY data_cadastro DESC, id_produto DESC';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function obterResumo(): array
    {
        $total = (int) $this->pdo
            ->query('SELECT COUNT(*) FROM produtos')
            ->fetchColumn();

        $estoqueCritico = (int) $this->pdo
            ->query(
                "SELECT COUNT(*)
                   FROM produtos
                  WHERE tipo_item = 'Produto'
                    AND status <> 'Inativo'
                    AND estoque > 0
                    AND estoque <= estoque_minimo"
            )
            ->fetchColumn();

        $categoriasAtivas = (int) $this->pdo
            ->query(
                "SELECT COUNT(DISTINCT categoria)
                   FROM produtos
                  WHERE categoria IS NOT NULL
                    AND categoria <> ''"
            )
            ->fetchColumn();

        return [
            'total' => $total,
            'estoque_critico' => $estoqueCritico,
            'categorias_ativas' => $categoriasAtivas,
        ];
    }

    public function listarCategorias(): array
    {
        $consulta = $this->pdo->query(
            "SELECT DISTINCT categoria
               FROM produtos
              WHERE categoria IS NOT NULL
                AND categoria <> ''
              ORDER BY categoria"
        );

        return array_column($consulta->fetchAll(), 'categoria');
    }

    public function buscarPorId(int $idProduto): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT *
               FROM produtos
              WHERE id_produto = :id_produto
              LIMIT 1'
        );
        $consulta->execute(['id_produto' => $idProduto]);
        $produto = $consulta->fetch();

        return $produto ?: null;
    }

    public function codigoExiste(string $codigo, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*)
                  FROM produtos
                 WHERE codigo = :codigo';
        $params = ['codigo' => $codigo];

        if ($ignorarId !== null) {
            $sql .= ' AND id_produto <> :id_produto';
            $params['id_produto'] = $ignorarId;
        }

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return (int) $consulta->fetchColumn() > 0;
    }

    public function criar(array $dados): int
    {
        $consulta = $this->pdo->prepare(
            'INSERT INTO produtos (
                tipo_item, codigo, nome, categoria, descricao, preco, unidade,
                status, estoque, estoque_minimo, marca, fornecedor, ncm,
                prazo_estimado, departamento
            ) VALUES (
                :tipo_item, :codigo, :nome, :categoria, :descricao, :preco,
                :unidade, :status, :estoque, :estoque_minimo, :marca,
                :fornecedor, :ncm, :prazo_estimado, :departamento
            )'
        );
        $consulta->execute($dados);

        return (int) $this->pdo->lastInsertId();
    }

    public function atualizar(int $idProduto, array $dados): void
    {
        $dados['id_produto'] = $idProduto;

        $consulta = $this->pdo->prepare(
            'UPDATE produtos
                SET tipo_item = :tipo_item,
                    codigo = :codigo,
                    nome = :nome,
                    categoria = :categoria,
                    descricao = :descricao,
                    preco = :preco,
                    unidade = :unidade,
                    status = :status,
                    estoque = :estoque,
                    estoque_minimo = :estoque_minimo,
                    marca = :marca,
                    fornecedor = :fornecedor,
                    ncm = :ncm,
                    prazo_estimado = :prazo_estimado,
                    departamento = :departamento
              WHERE id_produto = :id_produto'
        );
        $consulta->execute($dados);
    }

    public function alterarStatus(int $idProduto, string $status): void
    {
        $consulta = $this->pdo->prepare(
            'UPDATE produtos
                SET status = :status
              WHERE id_produto = :id_produto'
        );
        $consulta->execute([
            'id_produto' => $idProduto,
            'status' => $status,
        ]);
    }

    private function aplicarFiltroStatus(array &$where, array &$params, string $status): void
    {
        if ($status === '') {
            return;
        }

        if ($status === 'Disponível') {
            $where[] = "(tipo_item = 'Produto' AND status <> 'Inativo' AND estoque > estoque_minimo)";
            return;
        }

        if ($status === 'Baixo estoque') {
            $where[] = "(tipo_item = 'Produto' AND status <> 'Inativo' AND estoque > 0 AND estoque <= estoque_minimo)";
            return;
        }

        if ($status === 'Indisponível') {
            $where[] = "(tipo_item = 'Produto' AND status <> 'Inativo' AND estoque <= 0)";
            return;
        }

        if ($status === 'Ativo' || $status === 'Em análise') {
            $where[] = "(tipo_item = 'Serviço' AND status = :status_filtro)";
            $params['status_filtro'] = $status;
            return;
        }

        if ($status === 'Inativo') {
            $where[] = 'status = :status_filtro';
            $params['status_filtro'] = $status;
        }
    }
}
