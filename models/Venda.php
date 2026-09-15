<?php
declare(strict_types=1);

class Venda
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
                'v.codigo',
                'c.nome',
                'c.razao_social',
                'c.nome_fantasia',
            ];
            $partesBusca = [];
            $termoBusca = '%' . $filtros['busca'] . '%';

            foreach ($camposBusca as $indice => $campo) {
                $placeholder = 'busca_' . $indice;
                $partesBusca[] = "{$campo} LIKE :{$placeholder}";
                $params[$placeholder] = $termoBusca;
            }

            $partesBusca[] = 'EXISTS (
                SELECT 1
                  FROM venda_itens vi_busca
                  JOIN produtos p_busca ON p_busca.id_produto = vi_busca.id_produto
                 WHERE vi_busca.id_venda = v.id_venda
                   AND (p_busca.nome LIKE :busca_produto OR p_busca.codigo LIKE :busca_codigo_produto)
            )';
            $params['busca_produto'] = $termoBusca;
            $params['busca_codigo_produto'] = $termoBusca;

            if (ctype_digit($filtros['busca'])) {
                $partesBusca[] = 'v.id_venda = :busca_id';
                $params['busca_id'] = (int) $filtros['busca'];
            }

            $where[] = '(' . implode(' OR ', $partesBusca) . ')';
        }

        if (($filtros['status'] ?? '') !== '') {
            $where[] = 'v.status = :status';
            $params['status'] = $filtros['status'];
        }

        if (($filtros['id_cliente'] ?? '') !== '') {
            $where[] = 'v.id_cliente = :id_cliente';
            $params['id_cliente'] = (int) $filtros['id_cliente'];
        }

        $sql = 'SELECT v.*,
                       c.tipo_pessoa AS cliente_tipo_pessoa,
                       c.nome AS cliente_nome,
                       c.razao_social AS cliente_razao_social,
                       c.nome_fantasia AS cliente_nome_fantasia,
                       COUNT(vi.id_item) AS total_itens,
                       (
                           SELECT p_nome.nome
                             FROM venda_itens vi_nome
                             JOIN produtos p_nome ON p_nome.id_produto = vi_nome.id_produto
                            WHERE vi_nome.id_venda = v.id_venda
                            ORDER BY vi_nome.id_item
                            LIMIT 1
                       ) AS primeiro_item_nome,
                       (
                           SELECT p_tipo.tipo_item
                             FROM venda_itens vi_tipo
                             JOIN produtos p_tipo ON p_tipo.id_produto = vi_tipo.id_produto
                            WHERE vi_tipo.id_venda = v.id_venda
                            ORDER BY vi_tipo.id_item
                            LIMIT 1
                       ) AS primeiro_item_tipo
                  FROM vendas v
                  JOIN clientes c ON c.id_cliente = v.id_cliente
             LEFT JOIN venda_itens vi ON vi.id_venda = v.id_venda
             LEFT JOIN produtos p ON p.id_produto = vi.id_produto';

        if ($where) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY v.id_venda
                  ORDER BY v.data_venda DESC, v.id_venda DESC';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function obterResumo(): array
    {
        $total = (int) $this->pdo
            ->query('SELECT COUNT(*) FROM vendas')
            ->fetchColumn();

        $emAnalise = (int) $this->pdo
            ->query("SELECT COUNT(*) FROM vendas WHERE status = 'Em análise'")
            ->fetchColumn();

        $faturamento = (string) $this->pdo
            ->query("SELECT COALESCE(SUM(valor_total), 0) FROM vendas WHERE status <> 'Cancelado'")
            ->fetchColumn();

        return [
            'total' => $total,
            'em_analise' => $emAnalise,
            'faturamento' => $faturamento,
        ];
    }

    public function listarClientesFiltro(): array
    {
        $consulta = $this->pdo->query(
            'SELECT DISTINCT c.id_cliente,
                    c.tipo_pessoa,
                    c.nome,
                    c.razao_social,
                    c.nome_fantasia
               FROM vendas v
               JOIN clientes c ON c.id_cliente = v.id_cliente
              ORDER BY c.nome, c.nome_fantasia, c.razao_social'
        );

        return $consulta->fetchAll();
    }

    public function listarClientesDisponiveis(?int $idClienteAtual = null): array
    {
        $sql = "SELECT id_cliente, tipo_pessoa, nome, razao_social, nome_fantasia, status
                  FROM clientes
                 WHERE status = 'Ativo'";
        $params = [];

        if ($idClienteAtual !== null) {
            $sql .= ' OR id_cliente = :id_cliente_atual';
            $params['id_cliente_atual'] = $idClienteAtual;
        }

        $sql .= ' ORDER BY nome, nome_fantasia, razao_social';

        $consulta = $this->pdo->prepare($sql);
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function listarProdutosDisponiveis(array $idsAtuais = []): array
    {
        $where = ["status = 'Ativo'"];
        $params = [];

        $idsAtuais = array_values(array_unique(array_filter($idsAtuais, static fn ($id) => $id > 0)));

        if ($idsAtuais) {
            $placeholders = [];
            foreach ($idsAtuais as $indice => $idProduto) {
                $placeholder = 'id_atual_' . $indice;
                $placeholders[] = ':' . $placeholder;
                $params[$placeholder] = $idProduto;
            }

            $where[] = 'id_produto IN (' . implode(', ', $placeholders) . ')';
        }

        $consulta = $this->pdo->prepare(
            'SELECT id_produto, tipo_item, codigo, nome, preco, status
               FROM produtos
              WHERE ' . implode(' OR ', $where) . '
              ORDER BY nome, codigo'
        );
        $consulta->execute($params);

        return $consulta->fetchAll();
    }

    public function buscarClientePorId(int $idCliente): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT id_cliente, tipo_pessoa, nome, razao_social, nome_fantasia, status
               FROM clientes
              WHERE id_cliente = :id_cliente
              LIMIT 1'
        );
        $consulta->execute(['id_cliente' => $idCliente]);
        $cliente = $consulta->fetch();

        return $cliente ?: null;
    }

    public function buscarProdutosPorIds(array $idsProdutos): array
    {
        $idsProdutos = array_values(array_unique(array_filter($idsProdutos, static fn ($id) => $id > 0)));

        if (!$idsProdutos) {
            return [];
        }

        $params = [];
        $placeholders = [];

        foreach ($idsProdutos as $indice => $idProduto) {
            $placeholder = 'id_produto_' . $indice;
            $placeholders[] = ':' . $placeholder;
            $params[$placeholder] = $idProduto;
        }

        $consulta = $this->pdo->prepare(
            'SELECT id_produto, tipo_item, codigo, nome, preco, status
               FROM produtos
              WHERE id_produto IN (' . implode(', ', $placeholders) . ')'
        );
        $consulta->execute($params);

        $produtos = [];
        foreach ($consulta->fetchAll() as $produto) {
            $produtos[(int) $produto['id_produto']] = $produto;
        }

        return $produtos;
    }

    public function buscarPorId(int $idVenda): ?array
    {
        $consulta = $this->pdo->prepare(
            'SELECT v.*,
                    c.tipo_pessoa AS cliente_tipo_pessoa,
                    c.nome AS cliente_nome,
                    c.razao_social AS cliente_razao_social,
                    c.nome_fantasia AS cliente_nome_fantasia
               FROM vendas v
               JOIN clientes c ON c.id_cliente = v.id_cliente
              WHERE v.id_venda = :id_venda
              LIMIT 1'
        );
        $consulta->execute(['id_venda' => $idVenda]);
        $venda = $consulta->fetch();

        return $venda ?: null;
    }

    public function buscarItens(int $idVenda): array
    {
        $consulta = $this->pdo->prepare(
            'SELECT vi.*,
                    p.codigo,
                    p.nome,
                    p.tipo_item,
                    p.status AS produto_status
               FROM venda_itens vi
               JOIN produtos p ON p.id_produto = vi.id_produto
              WHERE vi.id_venda = :id_venda
              ORDER BY vi.id_item'
        );
        $consulta->execute(['id_venda' => $idVenda]);

        return $consulta->fetchAll();
    }

    public function criar(array $venda, array $itens): int
    {
        $this->pdo->beginTransaction();

        try {
            $insercaoVenda = $this->pdo->prepare(
                'INSERT INTO vendas (
                    codigo, id_cliente, data_venda, status, forma_pagamento,
                    condicao_pagamento, valor_total, observacoes
                ) VALUES (
                    :codigo, :id_cliente, :data_venda, :status, :forma_pagamento,
                    :condicao_pagamento, :valor_total, :observacoes
                )'
            );
            $insercaoVenda->execute($venda);

            $idVenda = (int) $this->pdo->lastInsertId();
            $this->inserirItens($idVenda, $itens);
            $this->pdo->commit();

            return $idVenda;
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function atualizar(int $idVenda, array $venda, array $itens): void
    {
        $this->pdo->beginTransaction();

        try {
            $venda['id_venda'] = $idVenda;

            $atualizacaoVenda = $this->pdo->prepare(
                'UPDATE vendas
                    SET codigo = :codigo,
                        id_cliente = :id_cliente,
                        data_venda = :data_venda,
                        status = :status,
                        forma_pagamento = :forma_pagamento,
                        condicao_pagamento = :condicao_pagamento,
                        valor_total = :valor_total,
                        observacoes = :observacoes
                  WHERE id_venda = :id_venda'
            );
            $atualizacaoVenda->execute($venda);

            $remocaoItens = $this->pdo->prepare('DELETE FROM venda_itens WHERE id_venda = :id_venda');
            $remocaoItens->execute(['id_venda' => $idVenda]);

            $this->inserirItens($idVenda, $itens);
            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }

    public function alterarStatus(int $idVenda, string $status): void
    {
        $consulta = $this->pdo->prepare(
            'UPDATE vendas
                SET status = :status
              WHERE id_venda = :id_venda'
        );
        $consulta->execute([
            'id_venda' => $idVenda,
            'status' => $status,
        ]);
    }

    private function inserirItens(int $idVenda, array $itens): void
    {
        $insercaoItem = $this->pdo->prepare(
            'INSERT INTO venda_itens (
                id_venda, id_produto, quantidade, valor_unitario, subtotal
            ) VALUES (
                :id_venda, :id_produto, :quantidade, :valor_unitario, :subtotal
            )'
        );

        foreach ($itens as $item) {
            $item['id_venda'] = $idVenda;
            $insercaoItem->execute($item);
        }
    }
}
