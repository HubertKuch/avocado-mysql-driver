# MySQL driver for Avocado
Driver based on PDO. Will works with MySQL and MariaDB.

## Used example

```php
#[Configuration]
class DatasourceConfiguration {
    
    #[Leaf]
    public function datasource(): DataSource {
        return (new DataSourceBuilder())
            ->username("...")
            ->password("...")
            ->server("...")
            ->port(3306)
            ->driver(MySQLDriver::class) // or FQN -> "\\Avocado\\MysqlDriver\\MySQLDriver"
            ->databaseName("")
            ->build();
    }

```