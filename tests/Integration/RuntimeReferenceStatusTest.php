<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/runtime_reference_status.php';

use PHPUnit\Framework\TestCase;

final class RuntimeReferenceStatusTest extends TestCase
{
    private string $fixtureRoot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->fixtureRoot = sys_get_temp_dir() . '/mtool-runtime-reference-status-' . bin2hex(random_bytes(6));
        mkdir($this->fixtureRoot, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeTree($this->fixtureRoot);
        parent::tearDown();
    }

    public function testStatusIsUpToDateWhenReferenceMatchesLatestArtifact(): void
    {
        $artifactKey = '20260520-021617-3ca30280';
        $app = $this->fixtureApp();
        $this->writeReferenceManifest($app['generated']['dbclasses_root'], $artifactKey);
        $this->writeArtifact($app, $artifactKey);

        $status = app_runtime_reference_status($app);

        self::assertTrue($status['ok'], (string) ($status['error'] ?? ''));
        self::assertSame('up-to-date', $status['status']);
        self::assertTrue($status['is_latest_promoted']);
        self::assertFalse($status['needs_promote']);
        self::assertIsArray($status['latest_artifact']);
        self::assertSame($artifactKey, $status['latest_artifact']['artifact_key']);
    }

    public function testStatusIsStaleWhenLatestArtifactDiffersFromPromotedReference(): void
    {
        $app = $this->fixtureApp();
        $this->writeReferenceManifest($app['generated']['dbclasses_root'], '20260520-020336-f9270c9c');
        $this->writeArtifact($app, '20260520-021617-3ca30280');

        $status = app_runtime_reference_status($app);

        self::assertTrue($status['ok'], (string) ($status['error'] ?? ''));
        self::assertSame('stale-reference', $status['status']);
        self::assertFalse($status['is_latest_promoted']);
        self::assertTrue($status['needs_promote']);
    }

    public function testStatusDetectsMissingReferenceProvenance(): void
    {
        $app = $this->fixtureApp();
        $this->writeReferenceManifest($app['generated']['dbclasses_root'], '');
        $this->writeArtifact($app, '20260520-021617-3ca30280');

        $status = app_runtime_reference_status($app);

        self::assertTrue($status['ok'], (string) ($status['error'] ?? ''));
        self::assertSame('reference-missing-provenance', $status['status']);
        self::assertTrue($status['needs_promote']);
    }

    public function testStatusFallsBackToArtifactHistoryMissingAfterWorkTreeCleanup(): void
    {
        $artifactKey = '20260520-021617-3ca30280';
        $app = $this->fixtureApp();
        $this->writeReferenceManifest($app['generated']['dbclasses_root'], $artifactKey);

        $status = app_runtime_reference_status($app);

        self::assertTrue($status['ok'], (string) ($status['error'] ?? ''));
        self::assertSame('artifact-history-missing', $status['status']);
        self::assertFalse($status['needs_promote']);
        self::assertFalse($status['durable_recovery_ready']);
        self::assertIsArray($status['reference_snapshot']);
        self::assertFalse($status['reference_snapshot']['exists']);
        self::assertNull($status['latest_artifact']);
    }

    public function testStatusUsesDurableSnapshotWhenArtifactHistoryIsGone(): void
    {
        $artifactKey = '20260520-021617-3ca30280';
        $app = $this->fixtureApp();
        $this->writeReferenceManifest($app['generated']['dbclasses_root'], $artifactKey);
        $this->writeSnapshot($app, $artifactKey);

        $status = app_runtime_reference_status($app);

        self::assertTrue($status['ok'], (string) ($status['error'] ?? ''));
        self::assertSame('reference-snapshot-only', $status['status']);
        self::assertFalse($status['needs_promote']);
        self::assertTrue($status['durable_recovery_ready']);
        self::assertIsArray($status['reference_snapshot']);
        self::assertTrue($status['reference_snapshot']['exists']);
        self::assertSame($artifactKey, $status['reference_snapshot']['artifact_key']);
        self::assertNull($status['latest_artifact']);
    }

    /**
     * @return array{
     *     generated:array{
     *         root:string,
     *         dbclasses_root:string,
     *         dbclasses_loader:string,
     *         dbclasses_mode:string
     *     },
     *     work:array{
     *         root:string
     *     }
     * }
     */
    private function fixtureApp(): array
    {
        $referenceRoot = $this->fixtureRoot . '/reference';
        $dbclassesRoot = $referenceRoot . '/dbclasses';
        mkdir($dbclassesRoot, 0777, true);

        return [
            'generated' => [
                'root' => $referenceRoot,
                'dbclasses_root' => $dbclassesRoot,
                'dbclasses_loader' => $dbclassesRoot . '/autoload_mtool.php',
                'dbclasses_mode' => 'self-generated-reference:canonical-dbaccess-partial-sql-regenerated',
            ],
            'work' => [
                'root' => $this->fixtureRoot . '/work',
            ],
        ];
    }

    private function writeReferenceManifest(string $dbclassesRoot, string $artifactKey): void
    {
        app_project_output_write_json_file(
            $dbclassesRoot . '/_support/runtime-generation-manifest.json',
            [
                'generated_at' => '2026-05-20T02:16:17+00:00',
                'project_key' => 'MTOOL',
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'runtime_source_relative_path' => 'mtool/dbclasses',
                'artifact_key' => $artifactKey,
                'generation_summary' => [
                    'mode' => 'canonical-dbaccess-partial-sql-regenerated',
                ],
            ],
        );
    }

    private function writeArtifact(array $app, string $artifactKey): void
    {
        $artifactDir = app_runtime_storage_generated_source_outputs_root($app, 'MTOOL', $artifactKey);
        $bundleEntryRoot = 'mtool-source-output-runtime-dbclasses-' . $artifactKey;
        $bundleRoot = $artifactDir . '/bundle/' . $bundleEntryRoot;
        $runtimeRoot = $bundleRoot . '/mtool/dbclasses';

        $item = [
            'project_key' => 'MTOOL',
            'source_output_key' => 'RUNTIME-DBCLASSES',
            'source_output_name' => 'MTOOL Runtime DBClasses',
            'source_output_program_language' => 'PHP',
            'source_output_class_type' => 'dbclasses',
            'source_output_release_target_type' => 'runtime',
            'artifact_strategy' => 'generated-bootstrap-dbclasses',
            'artifact_key' => $artifactKey,
            'created_at' => '2026-05-20T02:16:17+00:00',
            'requested_by' => 'phpunit',
            'archive_format' => 'tar.gz',
            'archive_filename' => $bundleEntryRoot . '.tar.gz',
            'bundle_entry_root' => $bundleEntryRoot,
            'runtime_source_relative_path' => 'mtool/dbclasses',
            'source_file_count' => 499,
            'source_total_bytes' => 1434991,
            'customization_model' => 'generated-runtime-layer',
            'custom_layer_relative_path' => 'mtool/extensions/MTOOL/RUNTIME-DBCLASSES',
            'custom_layer_source' => 'bundle-scaffold',
            'custom_layer_file_count' => 1,
            'custom_layer_total_bytes' => 10,
        ];

        app_project_output_write_json_file(
            $artifactDir . '/manifest.json',
            app_project_output_manifest_from_item($item),
        );
        app_project_output_write_json_file(
            $runtimeRoot . '/_support/runtime-generation-manifest.json',
            [
                'generated_at' => '2026-05-20T02:16:17+00:00',
                'project_key' => 'MTOOL',
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'runtime_source_relative_path' => 'mtool/dbclasses',
                'artifact_key' => $artifactKey,
                'generation_summary' => [
                    'mode' => 'canonical-dbaccess-partial-sql-regenerated',
                ],
            ],
        );
    }

    private function writeSnapshot(array $app, string $artifactKey): void
    {
        $snapshotRoot = app_runtime_storage_runtime_reference_snapshots_root(
            $app,
            'MTOOL',
            'RUNTIME-DBCLASSES',
            $artifactKey,
        );

        app_project_output_write_json_file(
            $snapshotRoot . '/_support/runtime-generation-manifest.json',
            [
                'generated_at' => '2026-05-20T02:16:17+00:00',
                'project_key' => 'MTOOL',
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'runtime_source_relative_path' => 'mtool/dbclasses',
                'artifact_key' => $artifactKey,
                'generation_summary' => [
                    'mode' => 'canonical-dbaccess-partial-sql-regenerated',
                ],
            ],
        );
        app_project_output_write_json_file(
            $snapshotRoot . '/_support/runtime-reference-snapshot.json',
            [
                'project_key' => 'MTOOL',
                'source_output_key' => 'RUNTIME-DBCLASSES',
                'artifact_key' => $artifactKey,
                'runtime_source_relative_path' => 'mtool/dbclasses',
                'captured_at' => '2026-05-20T02:17:00+00:00',
                'requested_by' => 'phpunit',
                'source_root' => '/tmp/source',
            ],
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
