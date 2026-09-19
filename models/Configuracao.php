<?php
declare(strict_types=1);

class Configuracao
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    public function obterTodas(array $padroes): array
    {
        if (!$padroes) {
            return [];
        }

        $consulta = $this->pdo->query('SELECT chave, valor FROM configuracoes');
        $configuracoes = $padroes;

        foreach ($consulta->fetchAll() as $linha) {
            $chave = (string) $linha['chave'];

            if (array_key_exists($chave, $padroes)) {
                $configuracoes[$chave] = (string) $linha['valor'];
            }
        }

        return $configuracoes;
    }

    public function salvar(array $configuracoes): void
    {
        $this->pdo->beginTransaction();

        try {
            $consulta = $this->pdo->prepare(
                'INSERT INTO configuracoes (chave, valor)
                 VALUES (:chave, :valor)
                 ON DUPLICATE KEY UPDATE valor = VALUES(valor)'
            );

            foreach ($configuracoes as $chave => $valor) {
                $consulta->execute([
                    'chave' => $chave,
                    'valor' => (string) $valor,
                ]);
            }

            $this->pdo->commit();
        } catch (Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            throw $exception;
        }
    }
}
