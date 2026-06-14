<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/sample20_dacustomproxy_method_and_enum_output_check.php';

function app_cli_sample20_usage(): string
{
    return <<<TEXT
Usage:
  php mtool/scripts/check_sample20_dacustomproxy_method_and_enum_outputs.php [--requested-by=NAME] [--reference=PATH] [--no-reference-check]

Options:
  --requested-by=NAME   実行者名 (default: sample20-output-check)
  --reference=PATH      reference root (default: sample/internal-patterns/pattern12-method-and-enum-no-top-level/reference)
  --no-reference-check  reference compare を行わず disposable output だけ更新する
  --help                この help を表示する
TEXT;
}

$requestedBy = 'sample20-output-check';
$referenceRoot = app_sample20_dacustomproxy_default_reference_root();
$compareReference = true;

foreach (array_slice($argv, 1) as $argument) {
    if ($argument === '--help') {
        fwrite(STDOUT, app_cli_sample20_usage() . PHP_EOL);
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
    fwrite(STDERR, app_cli_sample20_usage() . PHP_EOL);
    exit(2);
}

$result = app_sample20_dacustomproxy_run(
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
