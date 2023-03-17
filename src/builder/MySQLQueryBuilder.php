<?php

namespace Avocado\MysqlDriver;

use Avocado\AvocadoORM\Attributes\JoinDirection;
use Avocado\AvocadoORM\Order;
use Avocado\DataSource\Builder\Builder;
use Avocado\DataSource\Builder\SQLBuilder;
use Avocado\Utils\ReflectionUtils;

class MySQLQueryBuilder implements SQLBuilder {

    private string $sql;
    private string $tableName;

    public function __construct(string $sql = "", string $tableName = "") {
        $this->sql = $sql;
        $this->tableName = $tableName;
    }

    public function get(): string {
        return $this->sql;
    }

    public static function find(string $tableName, array $criteria = [], ?array $special = []): Builder {
        $base = "SELECT * FROM $tableName";

        if (!empty($criteria)) {
            $base .= " WHERE ".self::buildCriteria($criteria);
        }

        return new MySQLQueryBuilder($base, $tableName);
    }

    public function join(string $table, string $columnName, string $referencedColumnName, JoinDirection $joinDirection = JoinDirection::LEFT): Builder {
        $joinAlias = $table;
        $base = " {$joinDirection->value} JOIN $table {$joinAlias} ON {$this->tableName}.{$columnName} = {$joinAlias}.{$referencedColumnName} ";

        $this->sql .= $base;

        return $this;
    }

    public static function update(string $tableName, array $updateCriteria, array $findCriteria = []): Builder {
        $base = "UPDATE $tableName SET ";

        $base .= self::buildUpdateCriteria($updateCriteria);
        $base .= empty($findCriteria) ? "" : " WHERE " . self::buildCriteria($findCriteria);

        return new MySQLQueryBuilder($base, $tableName);
    }

    public static function delete(string $tableName, array $criteria): Builder {
        $base = "DELETE FROM $tableName ";

        if (!empty($criteria)) {
            $base .= " WHERE ".self::buildCriteria($criteria);
        }

        return new MySQLQueryBuilder($base, $tableName);
    }

    public static function save(string $tableName, object $object): Builder {
        $fields = ReflectionUtils::modelFieldsToArray($object);

        $insertColumns = self::fieldsArrayToColumnString($fields);
        $saveValues = self::fieldsToSaveString($fields);

        $base = "INSERT INTO $tableName ($insertColumns) VALUE ($saveValues)";

        return new MySQLQueryBuilder($base, $tableName);
    }

    private static function buildCriteria(array $criteria): string {
        $sql = "";

        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if (is_object($value)) {
                $valueType = gettype($value->value) ?? NULL;
                $value = $value->value;
            }

            if ($valueType === "integer" || $valueType === "double" || $valueType === "boolean") $sql.=" $key = $value AND ";
            else if ($valueType == "NULL") $sql .= " $key = null AND";
            else if ($valueType === "string") $sql.= " $key LIKE \"$value\" AND";
        }

        return substr($sql, 0,-4);
    }

    private static function buildUpdateCriteria(array $criteria): string {
        $sql = "";

        foreach ($criteria as $key => $value) {
            $valueType = gettype($value);

            if (is_object($value)) {
                $valueType = $value->value ?? NULL;
            }

            if ($valueType === "integer" || $valueType === "double" || $valueType === "boolean") $sql.=" $key = $value, ";
            else if ($valueType == "NULL") $sql .= " $key = null ";
            else if ($valueType === "string") {
                $value = str_replace("'", "\\'", $value);
                $sql.= " $key = '$value' , ";
            }
        }

        return substr($sql, 0, -2);
    }

    private static function fieldsArrayToColumnString(array $arr): string {
        return ltrim(array_reduce(array_keys($arr), fn ($prev, $key) => $prev . ", $key"), ", \s\t\n\r\0\x0B");
    }

    private static function fieldsToSaveString(array $arr): string {
        $subject = array_reduce(array_keys($arr), function ($prev, $key) use ($arr) {
            $val = $arr[$key];

            if (is_object($val)) {
                $val = $val->value;
            }

            if (is_null($val)) {
                $prev .= ", NULL";
            } else if (is_string($val) == "string") {
                $val = str_replace("'", "\\'", $val);
                $prev .= " , '$val'";
            } else {
                $prev .= " , $val";
            }

            return $prev;
        });

        return ltrim($subject, ", ");
    }

    public function limit(int $limit): Builder {
        $this->sql .= " LIMIT $limit ";

        return $this;
    }

    public function offset(int $offset): Builder {
        $this->sql .= " OFFSET $offset ";

        return $this;
    }

    public function orderBy(string $field, Order $order = Order::ASCENDING): Builder {
        if (str_contains($this->sql, "ORDER BY")) {
            $this->sql .= ", $field {$order->value}";
        } else {
            $this->sql .= " ORDER BY $field {$order->value}";
        }

        return $this;
    }
}
