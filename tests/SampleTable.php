<?php

namespace Hubert\MysqlDriver\Tests;

use Avocado\ORM\Attributes\Field;
use Avocado\ORM\Attributes\Id;
use Avocado\ORM\Attributes\Table;

#[Table('table')]
class SampleTable {
    public function __construct(#[Id] private int $id, #[Field] private string $field) {}
}
