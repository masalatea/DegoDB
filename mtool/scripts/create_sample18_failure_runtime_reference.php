<?php

declare(strict_types=1);

function usage(): never
{
    fwrite(STDERR, "Usage: php mtool/scripts/create_sample18_failure_runtime_reference.php SOURCE_DIR OUTPUT_DIR\n");
    exit(2);
}

if ($argc !== 3) {
    usage();
}

$sourceDir = rtrim((string) $argv[1], DIRECTORY_SEPARATOR);
$outputDir = rtrim((string) $argv[2], DIRECTORY_SEPARATOR);
if ($sourceDir === '' || !is_dir($sourceDir)) {
    throw new RuntimeException('Sample18 runtime reference source directory was not found: ' . $sourceDir);
}
if ($outputDir === '' || $outputDir === $sourceDir || str_starts_with($outputDir . DIRECTORY_SEPARATOR, $sourceDir . DIRECTORY_SEPARATOR)) {
    throw new RuntimeException('Output directory must be separate from the source runtime reference.');
}
if (file_exists($outputDir)) {
    throw new RuntimeException('Output directory already exists: ' . $outputDir);
}

/** @param string $source @param string $destination */
$copyTree = static function (string $source, string $destination) use (&$copyTree): void {
    if (is_dir($source)) {
        if (!mkdir($destination, 0777, true) && !is_dir($destination)) {
            throw new RuntimeException('Could not create fixture directory: ' . $destination);
        }
        $entries = scandir($source);
        if ($entries === false) {
            throw new RuntimeException('Could not read fixture source directory: ' . $source);
        }
        foreach ($entries as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $copyTree($source . DIRECTORY_SEPARATOR . $entry, $destination . DIRECTORY_SEPARATOR . $entry);
        }
        return;
    }
    if (!copy($source, $destination)) {
        throw new RuntimeException('Could not copy fixture file: ' . $source);
    }
};

$required = [
    'DBACCESS-PHP/_support/mtool_runtime_db.php',
    'DATACLASS-PHP/base/data-TaskCardBase.php',
    'DATACLASS-PHP/data-TaskCard.php',
    'DBACCESS-PHP/base/dbaccess-TaskCardBase.php',
    'DBACCESS-PHP/dbaccess-TaskCard.php',
];
foreach ($required as $relativePath) {
    if (!is_file($sourceDir . DIRECTORY_SEPARATOR . $relativePath)) {
        throw new RuntimeException('Required generated runtime file is missing: ' . $relativePath);
    }
}

$copyTree($sourceDir, $outputDir);
$wrapperPath = $outputDir . '/DBACCESS-PHP/dbaccess-TaskCard.php';
$wrapper = <<<'PHP'
<?php

// Smoke-only generated wrapper. Never use this runtime reference in production.

require_once __DIR__ . '/base/dbaccess-TaskCardBase.php';

class TaskCardDBAccess extends TaskCardDBAccessBase
{
    public function InsertTaskCard($TaskCardObj)
    {
        parent::InsertTaskCard($TaskCardObj);

        return (object) [
            'errno' => 1,
            'error' => 'sample18 smoke forced failure after SQL',
        ];
    }
}

?>
PHP;
if (file_put_contents($wrapperPath, $wrapper . "\n") === false) {
    throw new RuntimeException('Could not write failure runtime wrapper: ' . $wrapperPath);
}

$manifest = [
    'fixture_version' => 'sample18-failure-after-sql-v1',
    'smoke_only' => true,
    'source_dir' => realpath($sourceDir) ?: $sourceDir,
    'failure_point' => 'TaskCardDBAccess.InsertTaskCard.after_parent_sql',
    'expected_transaction_outcome' => 'rolled_back',
];
file_put_contents(
    $outputDir . '/smoke-fixture.json',
    json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n",
);

fwrite(STDOUT, json_encode([
    'ok' => true,
    'output_dir' => $outputDir,
    'wrapper_path' => $wrapperPath,
    'manifest' => $manifest,
], JSON_UNESCAPED_SLASHES) . "\n");
