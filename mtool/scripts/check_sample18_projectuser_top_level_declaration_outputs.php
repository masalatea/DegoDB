<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample18_projectuser_top_level_declaration_output_check.php';

function app_cli_sample18_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample18_projectuser_top_level_declaration_outputs.php [--requested-by=NAME] [--reference=PATH] [--no-reference-check]

Options:
  --requested-by=NAME   実行者名 (default: sample18-output-check)
  --reference=PATH      reference root (default: sample/internal-patterns/pattern10-top-level-declaration-multiclass/reference)
  --no-reference-check  reference compare を行わず disposable output だけ更新する
  --help                この help を表示する
TEXT;
}

$requestedBy = 'sample18-output-check';
$referenceRoot = app_sample18_projectuser_default_reference_root();
$compareReference = true;

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help') {
        fwrite(STDOUT, app_cli_sample18_usage() . PHP_EOL);
        exit(0);
    }
    if ($argument === '--no-reference-check') {
        $compareReference = false;
        continue;
    }
    if (str_starts_with($argument, '--requested-by=')) {
        $requestedBy = substr($argument, strlen('--requested-by='));
        continue;
    }
    if (str_starts_with($argument, '--reference=')) {
        $referenceRoot = substr($argument, strlen('--reference='));
        continue;
    }

    fwrite(STDERR, 'Unknown option: ' . $argument . PHP_EOL);
    fwrite(STDERR, app_cli_sample18_usage() . PHP_EOL);
    exit(2);
}

$result = app_sample18_projectuser_run(
    $requestedBy,
    $referenceRoot,
    $compareReference,
);

$encoded = json_encode(
    $result,
    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
);
if (!is_string($encoded) || $encoded === '') {
    fwrite(STDERR, "failed to encode result\n");
    exit(1);
}

fwrite(STDOUT, $encoded . PHP_EOL);

exit($result['ok'] ? 0 : 1);
