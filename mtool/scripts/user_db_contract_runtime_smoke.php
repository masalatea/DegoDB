<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/user_db_contract_runtime.php';

try {
    $options = app_user_db_contract_runtime_parse_options($argv);
    $dbaccessRoot = (string) ($options['dbaccess-root'] ?? '');
    $dataclassRoot = (string) ($options['dataclass-root'] ?? '');
    $dialect = (string) ($options['dialect'] ?? '');
    $output = (string) ($options['output'] ?? '');
    $sample = (string) ($options['sample'] ?? 'sample10-dbaccess-mini-crud-flow');
    $pretty = (bool) ($options['pretty'] ?? false);

    if ($dbaccessRoot === '' || $dataclassRoot === '' || $dialect === '' || $output === '') {
        throw new InvalidArgumentException(app_user_db_contract_runtime_usage());
    }

    $definition = app_user_db_contract_runtime_sample_definition($sample);

    if ($dialect === 'mysql') {
        putenv('MTOOL_RUNTIME_DB_DSN');
        app_user_db_contract_runtime_prepare_mysql_fixture($definition);
    } elseif ($dialect === 'sqlite') {
        $sqlitePath = (string) ($options['sqlite-path'] ?? '');
        if ($sqlitePath === '') {
            throw new InvalidArgumentException(app_user_db_contract_runtime_usage());
        }
        app_user_db_contract_runtime_prepare_sqlite_fixture($definition, $sqlitePath);
        putenv('MTOOL_RUNTIME_DB_DSN=sqlite:' . $sqlitePath);
        putenv('MTOOL_RUNTIME_DB_USER=');
        putenv('MTOOL_RUNTIME_DB_PASSWORD=');
    } else {
        throw new InvalidArgumentException('unsupported dialect: ' . $dialect);
    }

    $result = app_user_db_contract_runtime_result($definition, $dbaccessRoot, $dataclassRoot, $dialect);
    app_user_db_contract_write_json($output, $result, $pretty);
    fwrite(STDOUT, 'user DB runtime contract OK' . PHP_EOL);
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
