<?php

namespace App\Swoole\Services;

use SwooleTW\Http\Coroutine\PDO;

class PdoCoroutine
{
    private $pdo;

    public function select(string $query, array $parameters = []): array
    {
        return $this
            ->query($query, $parameters)
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function selectRow(string $query, array $parameters = []): ?array
    {
        $result = $this
            ->query($query, $parameters)
            ->fetch(PDO::FETCH_ASSOC);
        return ($result !== false) ? $result : null;
    }

    public function insert(string $query, array $parameters): int
    {
        $this->query($query, $parameters);
        return $this->pdo->lastInsertId();
    }

    public function update(string $query, array $parameters): int
    {
        return $this
            ->query($query, $parameters)
            ->rowCount();
    }

    public function delete(string $query, array $parameters = []): int
    {
        return $this
            ->query($query, $parameters)
            ->rowCount();
    }

    public function buildArrayQueryParameters(array $data, string $keyPrefix = 'array_params'): array
    {
        $response = [
            'conditions' => [],
            'parameters' => []
        ];

        foreach ($data as $key => $item) {
            $conditionKey = "{$keyPrefix}_{$key}";
            $response['conditions'][] = ":{$conditionKey}";
            $response['parameters'][$conditionKey] = trim($item);
        }

        $response['conditions'] = implode(', ', $response['conditions']);
        return $response;
    }

    public function __destruct()
    {
        if ($this->pdo instanceof PDO) {
            $this->pdo->__destruct();
        }
    }

    private function query(string $query, array $parameters)
    {
        $this->connect();

        $sth = $this->pdo->prepare($query);
        foreach ($parameters as $key => $parameter) {
            $sth->bindValue(":{$key}", $parameter);
        }

        $sth->execute();
        return $sth;
    }

    private function connect(): void
    {
        if ($this->pdo instanceof PDO) {
            return;
        }

        $this->pdo = new PDO(
            config('database.connections.mysql.driver') . ':dbname=' . config('database.connections.mysql.database') . ';host=' . config('database.connections.mysql.host'),
            config('database.connections.mysql.username'),
            config('database.connections.mysql.password'),
            [
                'strict_type' => false,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
            ]
        );
    }
}
