# Migratool: Simple PHP tool to execute database migrations

## Install via Composer

```php
composer require kulizh/migratool
```

## How it works

The logic of the library is based on obtaining the contents of the migration catalog (`.sql files`).

After receiving the list, the script tries to execute the SQL commands specified in the files. The results are placed in a JSON file `result.json` in the library directory. If execution succeeds, SQL-script will no longer be executed.

Note: for every file Migrator starts its own transaction. If you need to execute sensitive commands, put them in one file.

## Usage: create PHP-runner

Example code is above. You can start such runner manually or when deploy starts.

```php
require_once './vendor/autoload.php';

use Migratool\Migrator;

// Define migrations dir
// default is <./migration>
$migrations_dir = '/var/www/migrations/';

try {
    // Create instance of Migratool
    $migrator = new Migrator($pdo, $migrations_dir);

    // Start script
    $migrator->run();

    // Echo result, you can also save it to file
    // execution result is available via method ->result().
    // If you pass Migratool\Result::RETURN_ERR_ONLY to the method
    // it returns only failed transactions

    echo "Result:\n";
    foreach($migrator->result() as $file => $result)
    {
        echo "\t$file: $result\n";
    }
} catch(\Exception $exception)
{
    die($exception->getMessage() . "\n");
}
```
