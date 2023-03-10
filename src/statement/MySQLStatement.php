<?php

namespace Avocado\MysqlDriver;

use Avocado\DataSource\Database\Statement\Statement;
use PDO;

class MySQLStatement implements Statement {
    private string $sql;
    private PDO $pdo;

    public function __construct(PDO $pdo, string $sql) {
        $this->pdo = $pdo;
        $this->sql = $sql;
    }

    public function execute(): array {
        $stmt = $this->pdo->query($this->sql);

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }
}
