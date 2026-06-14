<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/runtime_reference_promotion.php';

use PHPUnit\Framework\TestCase;

final class RuntimeReferencePromotionTest extends TestCase
{
    private string $fixtureRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureRoot = sys_get_temp_dir() . '/mtool-runtime-reference-promotion-' . bin2hex(random_bytes(6));
        mkdir($this->fixtureRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->fixtureRoot);
        parent::tearDown();
    }

    public function testPromoteTreeReplacesExistingReferenceRoot(): void
    {
        $sourceRoot = $this->fixtureRoot . '/source';
        $targetRoot = $this->fixtureRoot . '/target';

        mkdir($sourceRoot . '/base', 0777, true);
        mkdir($sourceRoot . '/_support', 0777, true);
        mkdir($targetRoot, 0777, true);

        file_put_contents($sourceRoot . '/dbaccess-Project.php', "<?php\n");
        file_put_contents($sourceRoot . '/base/dbaccess-ProjectBase.php', "<?php\n");
        file_put_contents(
            $sourceRoot . '/_support/runtime-generation-manifest.json',
            "{\"mode\":\"canonical-dbaccess-partial-sql-regenerated\"}\n",
        );
        file_put_contents($targetRoot . '/stale.php', "<?php echo 'old';\n");

        $result = app_runtime_reference_promote_tree($sourceRoot, $targetRoot);

        self::assertTrue($result['ok'], (string) ($result['error'] ?? ''));
        self::assertIsArray($result['promoted']);
        self::assertSame($sourceRoot, $result['promoted']['source_root']);
        self::assertSame($targetRoot, $result['promoted']['target_root']);
        self::assertSame(3, $result['promoted']['file_count']);
        self::assertFileExists($targetRoot . '/dbaccess-Project.php');
        self::assertFileExists($targetRoot . '/base/dbaccess-ProjectBase.php');
        self::assertFileExists($targetRoot . '/_support/runtime-generation-manifest.json');
        self::assertFileDoesNotExist($targetRoot . '/stale.php');
    }

    public function testConfigDetectsSelfGeneratedReferenceModeFromManifest(): void
    {
        $dbclassesRoot = $this->fixtureRoot . '/dbclasses';
        mkdir($dbclassesRoot . '/_support', 0777, true);
        file_put_contents(
            $dbclassesRoot . '/_support/runtime-generation-manifest.json',
            json_encode(
                [
                    'generated_at' => '2026-05-19T01:07:20+00:00',
                    'generation_summary' => [
                        'mode' => 'canonical-dbaccess-partial-sql-regenerated',
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ),
        );

        self::assertSame(
            'self-generated-reference:canonical-dbaccess-partial-sql-regenerated',
            app_config_detect_generated_dbclasses_mode($dbclassesRoot),
        );
    }

    public function testRuntimeGenerationManifestArtifactKeyCanBeStamped(): void
    {
        $runtimeRoot = $this->fixtureRoot . '/runtime';
        mkdir($runtimeRoot . '/_support', 0777, true);
        file_put_contents(
            $runtimeRoot . '/_support/runtime-generation-manifest.json',
            json_encode(
                [
                    'generated_at' => '2026-05-20T02:03:36+00:00',
                    'generation_summary' => [
                        'mode' => 'canonical-dbaccess-partial-sql-regenerated',
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ),
        );

        app_project_output_set_runtime_generation_manifest_artifact_key(
            $runtimeRoot,
            '20260520-020336-f9270c9c',
        );

        $manifest = json_decode(
            (string) file_get_contents($runtimeRoot . '/_support/runtime-generation-manifest.json'),
            true,
        );

        self::assertIsArray($manifest);
        self::assertSame('20260520-020336-f9270c9c', $manifest['artifact_key'] ?? '');
    }

    public function testPromoteArtifactAlsoCapturesDurableSnapshot(): void
    {
        $artifactKey = '20260520-022959-3e593819';
        $bundleRoot = $this->fixtureRoot . '/artifacts/bundle/mtool-source-output-runtime-dbclasses-' . $artifactKey;
        $runtimeRoot = $bundleRoot . '/mtool/dbclasses';
        $targetRoot = $this->fixtureRoot . '/reference/dbclasses';
        $snapshotRoot = $this->fixtureRoot . '/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/' . $artifactKey;

        $this->writeRuntimeTree($runtimeRoot, $artifactKey, [
            'dbaccess-Project.php' => "<?php\n",
            'base/dbaccess-ProjectBase.php' => "<?php\n",
        ]);

        $result = app_runtime_reference_promote_artifact(
            [
                'project_key' => 'MTOOL',
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'artifact_key' => $artifactKey,
                'bundle_root' => $bundleRoot,
                'runtime_source_relative_path' => 'mtool/dbclasses',
            ],
            $targetRoot,
            'phpunit',
            $snapshotRoot,
        );

        self::assertTrue($result['ok'], (string) ($result['error'] ?? ''));
        self::assertIsArray($result['promoted']);
        self::assertSame($snapshotRoot, $result['promoted']['snapshot_root']);
        self::assertFileExists($targetRoot . '/dbaccess-Project.php');
        self::assertFileExists($snapshotRoot . '/dbaccess-Project.php');
        self::assertFileExists($snapshotRoot . '/_support/runtime-reference-snapshot.json');

        $snapshotManifest = json_decode(
            (string) file_get_contents($snapshotRoot . '/_support/runtime-reference-snapshot.json'),
            true,
        );

        self::assertIsArray($snapshotManifest);
        self::assertSame('MTOOL', $snapshotManifest['project_key'] ?? '');
        self::assertSame('RUNTIME-DBCLASSES', $snapshotManifest['source_output_key'] ?? '');
        self::assertSame($artifactKey, $snapshotManifest['artifact_key'] ?? '');
        self::assertSame('phpunit', $snapshotManifest['requested_by'] ?? '');
    }

    public function testRestoreSnapshotRebuildsReferenceWithoutArtifactHistory(): void
    {
        $artifactKey = '20260520-022959-3e593819';
        $sourceRoot = $this->fixtureRoot . '/source/dbclasses';
        $targetRoot = $this->fixtureRoot . '/reference/dbclasses';
        $snapshotRoot = $this->fixtureRoot . '/reference/runtime-reference-snapshots/MTOOL/RUNTIME-DBCLASSES/' . $artifactKey;

        $this->writeRuntimeTree($sourceRoot, $artifactKey, [
            'dbaccess-Project.php' => "<?php\n",
            'base/dbaccess-ProjectBase.php' => "<?php\n",
        ]);
        mkdir($targetRoot, 0777, true);
        file_put_contents($targetRoot . '/stale.php', "<?php echo 'old';\n");

        $capture = app_runtime_reference_capture_snapshot_from_root(
            $sourceRoot,
            'MTOOL',
            'RUNTIME-DBCLASSES',
            $artifactKey,
            'phpunit',
            $snapshotRoot,
        );

        self::assertTrue($capture['ok'], (string) ($capture['error'] ?? ''));

        $restore = app_runtime_reference_restore_snapshot(
            'MTOOL',
            'RUNTIME-DBCLASSES',
            $artifactKey,
            $targetRoot,
            'phpunit',
            $snapshotRoot,
        );

        self::assertTrue($restore['ok'], (string) ($restore['error'] ?? ''));
        self::assertIsArray($restore['restored']);
        self::assertSame($targetRoot, $restore['restored']['target_root']);
        self::assertFileExists($targetRoot . '/dbaccess-Project.php');
        self::assertFileDoesNotExist($targetRoot . '/stale.php');

        $manifest = json_decode(
            (string) file_get_contents($targetRoot . '/_support/runtime-generation-manifest.json'),
            true,
        );

        self::assertIsArray($manifest);
        self::assertSame($artifactKey, $manifest['artifact_key'] ?? '');
    }

    /**
     * @param array<string,string> $files
     */
    private function writeRuntimeTree(string $runtimeRoot, string $artifactKey, array $files): void
    {
        mkdir($runtimeRoot . '/_support', 0777, true);
        foreach ($files as $relativePath => $contents) {
            $path = $runtimeRoot . '/' . $relativePath;
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0777, true);
            }
            file_put_contents($path, $contents);
        }
        file_put_contents(
            $runtimeRoot . '/_support/runtime-generation-manifest.json',
            json_encode(
                [
                    'generated_at' => '2026-05-20T02:29:59+00:00',
                    'project_key' => 'MTOOL',
                    'source_output_key' => 'RUNTIME-DBCLASSES',
                    'runtime_source_relative_path' => 'mtool/dbclasses',
                    'artifact_key' => $artifactKey,
                    'generation_summary' => [
                        'mode' => 'canonical-dbaccess-partial-sql-regenerated',
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ),
        );
    }

    /**
     * @param string $path
     */
    private function removeTree(string $path): void
    {
        if (!file_exists($path)) {
            return;
        }

        if (is_file($path) || is_link($path)) {
            @unlink($path);
            return;
        }

        $entries = scandir($path);
        if ($entries !== false) {
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }

                $this->removeTree($path . '/' . $entry);
            }
        }

        @rmdir($path);
    }
}
