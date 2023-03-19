<?php

namespace Hubert\MysqlDriver\Tests;

use Avocado\MysqlDriver\MySQLMapper;
use Avocado\ORM\AvocadoModel;
use PHPUnit\Framework\TestCase;
use stdClass;

class MySQLMapperTest extends TestCase {

    public function testEntityToObject() {
        $mapper = new MySQLMapper();
        $std = new stdClass();

        $std->id = 4;
        $std->field = "test";

        $model = new AvocadoModel(SampleTable::class);
        $expected = new SampleTable(4, "test");
        $instance = $mapper->entityToObject($model, $std);

        self::assertEquals($expected, $instance);
    }
}
