<?php

namespace Hubert\MysqlDriver\Tests;

use Avocado\AvocadoORM\Attributes\JoinDirection;
use Avocado\MysqlDriver\MySQLQueryBuilder;
use PHPUnit\Framework\TestCase;

class MySQLQueryBuilderTest extends TestCase {

    public function testFind() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString("SELECT * FROM test", $builder->find("test", [])->get());
    }

    public function testFindWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString("SELECT * FROM test WHERE  a = 2 AND  b LIKE \"asd\" AND c = null",
            $builder->find("test", ["a" => 2, "b" => "asd", "c" => NULL])->get());
    }

    public function testUpdate() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString("UPDATE test SET  a = 24,  b = 'null' ",
            $builder->update("test", ["a" => 24, "b" => "null"])->get());
    }

    public function testUpdateWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString("UPDATE test SET  a = 2,  b = 'asd'  WHERE  test = 12 AND  test2 = null",
            $builder->update("test", ["a" => 2, "b" => "asd"], ["test" => 12, "test2" => null])->get());
    }

    public function testDelete() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString('DELETE FROM test ', $builder->delete("test", [])->get());
    }

    public function testDeleteWithCriteria() {
        $builder = new MySQLQueryBuilder();

        self::assertStringContainsString('DELETE FROM test  WHERE  a = 2 AND  b LIKE "asd"',
            $builder->delete("test", ["a" => 2, "b" => "asd"])->get());
    }

    public function testSingleJoin() {
        $builder = new MySQLQueryBuilder();
        $expected = "SELECT * FROM test LEFT JOIN test2 test2 ON test.id = test2.t_id  ORDER BY test.id ASC, test2.id ASC";

        $given = $builder::find("test")
                         ->join("test2", "id", "t_id")
                         ->orderBy("test.id")
                         ->orderBy("test2.id");

        self::assertSame($expected, $given->get());
    }

    public function testDoubleJoin() {
        $builder = new MySQLQueryBuilder();
        $expected = "SELECT * FROM test LEFT JOIN test2 test2 ON test.id = test2.t_id  LEFT JOIN test3 test3 ON test.id = test3.t2_id  ORDER BY test.id ASC, test2.id ASC, test3.id ASC";

        $given = $builder::find("test")
                         ->join("test2", "id", "t_id")
                         ->join("test3", "id", "t2_id")
                         ->orderBy("test.id")
                         ->orderBy("test2.id")
                         ->orderBy("test3.id");

        self::assertSame($expected, $given->get());
    }
}
