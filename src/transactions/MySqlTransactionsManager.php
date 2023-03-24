<?php

namespace Hubert\MysqlDriver\transactions;

use Avocado\DataSource\Transactions\TransactionManager;
use PDO;

class MySqlTransactionsManager implements TransactionManager {

    public function __construct(private PDO $db) {}

    public function begin(): bool {
        return $this->db->beginTransaction();
    }

    public function commit(): bool {
        if ($this->db->commit()) {
            return true;
        } else {
            $this->rollback();

            return false;
        }
    }

    public function rollback(): bool {
        return $this->db->rollBack();
    }
}