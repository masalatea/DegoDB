<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/user_db_contract.php';

function app_user_db_contract_usage(): string
{
    return <<<'TXT'
usage:
  php mtool/scripts/user_db_contract.php manifest --root=PATH --output=PATH --dialect=NAME --sample=KEY [--pretty]
  php mtool/scripts/user_db_contract.php compare --left=PATH --right=PATH --output=PATH [--pretty]

TXT;
}

/**
 * @return array<string,mixed>
 */
function app_user_db_contract_parse_options(array $argv): array
{
    $options = [
        'command' => (string) ($argv[1] ?? ''),
        'pretty' => false,
    ];

    foreach (array_slice($argv, 2) as $argument) {
        if ($argument === '--pretty') {
            $options['pretty'] = true;
            continue;
        }

        if (str_starts_with($argument, '--') && str_contains($argument, '=')) {
            [$key, $value] = explode('=', substr($argument, 2), 2);
            $options[$key] = $value;
            continue;
        }

        throw new InvalidArgumentException('unsupported argument: ' . $argument);
    }

    return $options;
}

try {
    $options = app_user_db_contract_parse_options($argv);
    $command = (string) ($options['command'] ?? '');
    $pretty = (bool) ($options['pretty'] ?? false);

    if ($command === 'manifest') {
        $root = (string) ($options['root'] ?? '');
        $output = (string) ($options['output'] ?? '');
        $dialect = (string) ($options['dialect'] ?? '');
        $sample = (string) ($options['sample'] ?? '');
        if ($root === '' || $output === '' || $dialect === '' || $sample === '') {
            throw new InvalidArgumentException(app_user_db_contract_usage());
        }

        app_user_db_contract_write_json(
            $output,
            app_user_db_contract_manifest($root, $dialect, $sample),
            $pretty,
        );
        exit(0);
    }

    if ($command === 'compare') {
        $leftPath = (string) ($options['left'] ?? '');
        $rightPath = (string) ($options['right'] ?? '');
        $output = (string) ($options['output'] ?? '');
        if ($leftPath === '' || $rightPath === '' || $output === '') {
            throw new InvalidArgumentException(app_user_db_contract_usage());
        }

        $left = json_decode((string) file_get_contents($leftPath), true);
        $right = json_decode((string) file_get_contents($rightPath), true);
        if (!is_array($left) || !is_array($right)) {
            throw new RuntimeException('manifest JSON parse failed');
        }

        $result = app_user_db_contract_compare_manifests($left, $right);
        app_user_db_contract_write_json($output, $result, $pretty);
        fwrite(STDOUT, ($result['ok'] ? 'user DB contract OK' : 'user DB contract failed') . PHP_EOL);
        exit($result['ok'] ? 0 : 1);
    }

    throw new InvalidArgumentException(app_user_db_contract_usage());
} catch (Throwable $e) {
    fwrite(STDERR, $e->getMessage() . PHP_EOL);
    exit(1);
}
