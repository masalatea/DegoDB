<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/config.php';
require_once dirname(__DIR__, 2) . '/mtool/app/config_db_bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_dbaccess.php';
require_once dirname(__DIR__, 2) . '/mtool/app/app_local_sqlite_schema.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/table_metadata_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/data_class_repository_pdo.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_output_service.php';
require_once dirname(__DIR__, 2) . '/mtool/app/shared_contract_manifest.php';

use PHPUnit\Framework\TestCase;

final class SharedDataClassContractFoundationTest extends TestCase
{
    public function testBuildsManifestV0FromTableAndDataClassMetadata(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $result = app_shared_contract_manifest_from_project($app, 'CONTRACT-FOUNDATION-TEST');

        self::assertTrue($result['ok'], json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        self::assertTrue($result['validation']['ok'], json_encode($result['validation']['errors'], JSON_PRETTY_PRINT));
        self::assertTrue($result['compare']['ok'], json_encode($result['compare'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $manifest = $result['manifest'];
        self::assertSame('shared-contract-manifest-v0', $manifest['manifest_version']);
        self::assertSame('CONTRACT-FOUNDATION-TEST', $manifest['project_key']);

        $contract = $manifest['contracts'][0] ?? null;
        self::assertIsArray($contract);
        self::assertSame('task', $contract['contract_key']);
        self::assertSame([
            'logical_name' => 'Task',
            'physical_name' => 'task',
            'generated_name' => 'Task',
        ], $contract['entity']);
        self::assertSame(
            ['id', 'title', 'status', 'sort_order', 'is_pinned', 'published_at', 'note'],
            array_column($contract['fields'], 'physical_name'),
        );
        self::assertSame(
            ['id', 'title', 'status', 'sortOrder', 'isPinned', 'publishedAt', 'note'],
            array_column($contract['fields'], 'generated_name'),
        );

        $fieldsByPhysicalName = [];
        foreach ($contract['fields'] as $field) {
            self::assertIsArray($field);
            $fieldsByPhysicalName[(string) $field['physical_name']] = $field;
        }

        self::assertSame('integer', $fieldsByPhysicalName['id']['type']);
        self::assertTrue($fieldsByPhysicalName['id']['is_key']);
        self::assertFalse($fieldsByPhysicalName['id']['nullable']);
        self::assertSame('draft', $fieldsByPhysicalName['status']['default']);
        self::assertSame(0, $fieldsByPhysicalName['sort_order']['default']);
        self::assertFalse($fieldsByPhysicalName['is_pinned']['default']);
        self::assertSame('datetime', $fieldsByPhysicalName['published_at']['type']);
        self::assertTrue($fieldsByPhysicalName['published_at']['nullable']);
        self::assertSame('text', $fieldsByPhysicalName['note']['type']);
        self::assertTrue($fieldsByPhysicalName['note']['nullable']);

        self::assertSame(1, $result['compare']['contract_count']);
        self::assertSame(0, $result['compare']['mismatch_count']);
        self::assertSame(
            ['id', 'title', 'status', 'sortOrder', 'isPinned', 'publishedAt', 'note'],
            $result['compare']['contracts'][0]['contract_fields'] ?? [],
        );
    }

    public function testSharedContractJsonSourceOutputBuildsManifestArtifact(): void
    {
        self::assertContains('SharedContract', app_allowed_source_output_class_types());
        self::assertContains('shared-contract-json', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('shared-contract-json'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('shared-contract-json'));
        self::assertSame(
            'Shared Contract JSON Artifact',
            app_source_output_artifact_strategy_caption('shared-contract-json'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'SHARED-CONTRACT-JSON',
            'name' => 'Contract Foundation Shared Contract',
            'program_language' => 'json',
            'class_type' => 'SharedContract',
            'artifact_strategy' => 'shared-contract-json',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $result = app_project_output_prepare_shared_contract_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(
            'mtool/shared-contract-source-outputs/CONTRACT-FOUNDATION-TEST/SHARED-CONTRACT-JSON',
            $result['runtime_source_relative_path'],
        );
        self::assertSame(
            ['README.md', 'shared-contract-report.json', 'shared-contract.json'],
            array_column($result['scan_result']['files'] ?? [], 'relative_path'),
        );

        $manifestPath = $result['runtime_source_root'] . '/shared-contract.json';
        self::assertFileExists($manifestPath);
        $manifest = json_decode((string) file_get_contents($manifestPath), true);
        self::assertIsArray($manifest);
        self::assertSame('shared-contract-manifest-v0', $manifest['manifest_version']);
        self::assertSame('CONTRACT-FOUNDATION-TEST', $manifest['project_key']);
        self::assertSame('task', $manifest['contracts'][0]['contract_key'] ?? '');

        $reportPath = $result['runtime_source_root'] . '/shared-contract-report.json';
        $report = json_decode((string) file_get_contents($reportPath), true);
        self::assertIsArray($report);
        self::assertTrue($report['ok']);
        self::assertTrue($report['validation']['ok']);
        self::assertTrue($report['compare']['ok']);
    }

    public function testExplicitSharedContractMetadataIsSeparateAndMergedIntoManifest(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $contract = app_pdo_upsert_shared_contract_metadata($app, 'CONTRACT-FOUNDATION-TEST', [
            'contract_key' => 'task',
            'data_class_physical_name' => 'task',
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'notes' => 'explicit contract metadata fixture',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($contract['ok'], $contract['error']);
        self::assertSame('task', $contract['item']['contract_key'] ?? '');
        self::assertSame('task', $contract['item']['data_class_physical_name'] ?? '');

        $field = app_pdo_upsert_shared_contract_field_metadata($app, 'CONTRACT-FOUNDATION-TEST', 'task', [
            'field_physical_name' => 'status',
            'sync_role' => 'server-copy',
            'operation_role' => 'editable',
            'no_code_role' => 'filterable',
            'app_persistence_role' => 'local-copy',
            'notes' => 'status drives task workflow',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($field['ok'], $field['error']);

        $snapshot = app_pdo_fetch_shared_contract_metadata_snapshot($app, 'CONTRACT-FOUNDATION-TEST');
        self::assertTrue($snapshot['ok'], $snapshot['error']);
        self::assertSame(1, count($snapshot['items']));
        self::assertSame('status', $snapshot['items'][0]['fields'][0]['field_physical_name'] ?? '');

        $result = app_shared_contract_manifest_from_project($app, 'CONTRACT-FOUNDATION-TEST');
        self::assertTrue($result['ok'], json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $contractManifest = $result['manifest']['contracts'][0] ?? [];
        self::assertSame([
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'notes' => 'explicit contract metadata fixture',
            'source_of_truth' => 'test',
        ], $contractManifest['contract_metadata'] ?? []);

        $fieldsByPhysicalName = [];
        foreach (($contractManifest['fields'] ?? []) as $manifestField) {
            self::assertIsArray($manifestField);
            $fieldsByPhysicalName[(string) ($manifestField['physical_name'] ?? '')] = $manifestField;
        }
        self::assertSame([
            'sync_role' => 'server-copy',
            'operation_role' => 'editable',
            'no_code_role' => 'filterable',
            'app_persistence_role' => 'local-copy',
            'notes' => 'status drives task workflow',
            'source_of_truth' => 'test',
        ], $fieldsByPhysicalName['status']['contract_metadata'] ?? []);
    }

    public function testSharedContractTypeScriptSourceOutputBuildsDtoArtifact(): void
    {
        self::assertContains('ts', app_allowed_source_output_program_languages());
        self::assertContains('TypeScriptDTO', app_allowed_source_output_class_types());
        self::assertContains('shared-contract-typescript', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('shared-contract-typescript'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('shared-contract-typescript'));
        self::assertSame(
            'Shared Contract TypeScript DTO Artifact',
            app_source_output_artifact_strategy_caption('shared-contract-typescript'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'TYPESCRIPT-DTO',
            'name' => 'Contract Foundation TypeScript DTO',
            'program_language' => 'ts',
            'class_type' => 'TypeScriptDTO',
            'artifact_strategy' => 'shared-contract-typescript',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $result = app_project_output_prepare_typescript_dto_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($result['ok'], $result['error']);
        self::assertSame(
            'mtool/typescript-dto-source-outputs/CONTRACT-FOUNDATION-TEST/TYPESCRIPT-DTO',
            $result['runtime_source_relative_path'],
        );
        self::assertSame(
            ['README.md', 'dto.ts'],
            array_column($result['scan_result']['files'] ?? [], 'relative_path'),
        );

        $dtoPath = $result['runtime_source_root'] . '/dto.ts';
        self::assertFileExists($dtoPath);
        $dtoText = (string) file_get_contents($dtoPath);
        self::assertStringContainsString('export interface TaskDto {', $dtoText);
        self::assertStringContainsString('  id: number;', $dtoText);
        self::assertStringContainsString('  title: string;', $dtoText);
        self::assertStringContainsString('  status: string;', $dtoText);
        self::assertStringContainsString('  sortOrder: number;', $dtoText);
        self::assertStringContainsString('  isPinned: boolean;', $dtoText);
        self::assertStringContainsString('  publishedAt: string | null;', $dtoText);
        self::assertStringContainsString('  note: string | null;', $dtoText);
    }

    public function testAppLocalPersistenceSourceOutputBuildsExecutableArtifact(): void
    {
        self::assertContains('AppLocalPersistence', app_allowed_source_output_class_types());
        self::assertContains('app-local-persistence-php', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('app-local-persistence-php'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('app-local-persistence-php'));
        self::assertSame(
            'App-local Persistence PHP Artifact',
            app_source_output_artifact_strategy_caption('app-local-persistence-php'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'APP-LOCAL-PERSISTENCE',
            'name' => 'Contract Foundation App-local Persistence',
            'program_language' => 'php',
            'class_type' => 'AppLocalPersistence',
            'artifact_strategy' => 'app-local-persistence-php',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $treeResult = app_project_output_prepare_app_local_persistence_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($treeResult['ok'], $treeResult['error']);
        self::assertSame(
            'mtool/app-local-persistence-source-outputs/CONTRACT-FOUNDATION-TEST/APP-LOCAL-PERSISTENCE',
            $treeResult['runtime_source_relative_path'],
        );
        self::assertSame(
            [
                'AppLocalPersistence.php',
                'README.md',
                'app-local-contract.json',
                'app-local-summary.json',
                'schema.sql',
            ],
            array_column($treeResult['scan_result']['files'] ?? [], 'relative_path'),
        );

        $contractPath = $treeResult['runtime_source_root'] . '/app-local-contract.json';
        $contract = json_decode((string) file_get_contents($contractPath), true);
        self::assertIsArray($contract);
        self::assertSame('shared-contract-manifest-v0', $contract['manifest_version']);
        self::assertSame('task', $contract['contracts'][0]['contract_key'] ?? '');

        $summaryPath = $treeResult['runtime_source_root'] . '/app-local-summary.json';
        $summary = json_decode((string) file_get_contents($summaryPath), true);
        self::assertIsArray($summary);
        self::assertTrue($summary['ok']);
        self::assertSame(1, $summary['schema']['table_count'] ?? null);

        $schemaText = (string) file_get_contents($treeResult['runtime_source_root'] . '/schema.sql');
        self::assertStringContainsString('CREATE TABLE IF NOT EXISTS "task"', $schemaText);
        self::assertStringContainsString('"sync_status"', $schemaText);

        $wrapperPath = $treeResult['runtime_source_root'] . '/AppLocalPersistence.php';
        $wrapperText = (string) file_get_contents($wrapperPath);
        self::assertStringContainsString('final class TaskAppLocalPersistence', $wrapperText);
        self::assertStringContainsString('public static function save(PDO $pdo, array $dto, array $localMetadata = []): array', $wrapperText);
        require_once $wrapperPath;

        $localPdo = new PDO('sqlite::memory:');
        $apply = TaskAppLocalPersistence::applySchema($localPdo);
        self::assertTrue($apply['ok'], $apply['error']);

        $dto = [
            'id' => 1001,
            'title' => 'App-local artifact task',
            'status' => 'draft',
            'sortOrder' => 10,
            'isPinned' => false,
            'publishedAt' => null,
            'note' => 'artifact wrapper round trip',
        ];
        $save = TaskAppLocalPersistence::save($localPdo, $dto, [
            'dirty' => 1,
            'sync_status' => 'dirty',
        ]);
        self::assertTrue($save['ok'], $save['error']);

        $read = TaskAppLocalPersistence::read($localPdo, ['id' => 1001]);
        self::assertTrue($read['ok'], $read['error']);
        self::assertSame($dto, $read['dto']);
        self::assertSame(1, $read['local_metadata']['dirty'] ?? null);

        $artifactResult = app_project_output_create_from_definition(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
            'phpunit',
        );
        self::assertTrue($artifactResult['ok'], $artifactResult['error']);
        self::assertSame(5, $artifactResult['artifact']['source_file_count'] ?? null);
        self::assertSame(
            'app-local-persistence-php',
            $artifactResult['artifact']['artifact_strategy'] ?? '',
        );

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $definition,
        );
        self::assertTrue($publishResult['ok'], $publishResult['error']);
        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        self::assertFileExists($publishedRoot . '/AppLocalPersistence.php');
        self::assertFileExists($publishedRoot . '/schema.sql');
    }

    public function testCompareDetectsDataClassShapeMismatch(): void
    {
        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $result = app_shared_contract_manifest_from_project($app, 'CONTRACT-FOUNDATION-TEST');
        self::assertTrue($result['ok'], json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $manifest = $result['manifest'];
        array_pop($manifest['contracts'][0]['fields']);
        $dataClassSnapshot = app_pdo_fetch_data_class_metadata_snapshot($app, 'CONTRACT-FOUNDATION-TEST');
        self::assertTrue($dataClassSnapshot['ok'], $dataClassSnapshot['error']);

        $compare = app_shared_contract_manifest_compare_dataclass_shape($manifest, $dataClassSnapshot['items']);

        self::assertFalse($compare['ok']);
        self::assertSame(['field shape mismatch for contract: task'], $compare['mismatches']);
    }

    /**
     * @return array<string,mixed>
     */
    private function createBootstrappedSqliteApp(): array
    {
        $storeDir = sys_get_temp_dir() . '/dego-shared-contract-test-' . getmypid() . '-' . bin2hex(random_bytes(4));
        $configDb = app_config_store_config(
            'sqlite',
            'db-config',
            '3306',
            'config_app',
            'config_app',
            'secret',
            '/var/www/work',
            $storeDir,
        );
        $app = [
            'site' => 'admin',
            'db' => $configDb,
            'config_db' => $configDb,
            'work' => [
                'root' => $storeDir . '/work',
            ],
        ];

        $bootstrap = app_config_db_bootstrap_apply($app);
        self::assertTrue($bootstrap['ok'], $bootstrap['error']);

        return $app;
    }

    /**
     */
    private function seedTaskProject(array $app): void
    {
        $project = app_pdo_insert_project($app, [
            'project_key' => 'CONTRACT-FOUNDATION-TEST',
            'name' => 'Contract Foundation Test',
            'slug' => 'contract-foundation-test',
            'lifecycle_status' => 'active',
            'owner_login_id' => 'owner@example.test',
            'description' => 'shared contract foundation fixture',
        ]);
        self::assertTrue($project['ok'], $project['error']);

        $table = app_pdo_create_table_metadata_item($app, 'CONTRACT-FOUNDATION-TEST', 'task');
        self::assertTrue($table['ok'], $table['error']);
        $tablePid = (string) ($table['item']['pid'] ?? '');

        foreach ($this->taskColumns() as $column) {
            $result = app_pdo_create_table_metadata_column($app, 'CONTRACT-FOUNDATION-TEST', $tablePid, $column);
            self::assertTrue($result['ok'], $result['error']);
        }

        $dataClass = app_pdo_create_data_class_metadata_item($app, 'CONTRACT-FOUNDATION-TEST', [
            'name' => 'Task',
            'physical_name' => 'task',
            'store_base_path' => '',
            'is_autoload' => '1',
            'inherit_parent_data_class_name' => '',
        ]);
        self::assertTrue($dataClass['ok'], $dataClass['error']);
        $dataClassPid = (string) ($dataClass['item']['pid'] ?? '');

        foreach ($this->taskFields() as $field) {
            $result = app_pdo_create_data_class_metadata_field(
                $app,
                'CONTRACT-FOUNDATION-TEST',
                $dataClassPid,
                $field,
            );
            self::assertTrue($result['ok'], $result['error']);
        }
    }

    /**
     * @return list<array<string,string>>
     */
    private function taskColumns(): array
    {
        return [
            ['name' => 'id', 'physical_name' => 'id', 'datatype' => 'bigint unsigned', 'is_null' => 'NO', 'is_key' => 'PRI', 'is_default' => '', 'extra' => 'auto_increment', 'memo' => ''],
            ['name' => 'title', 'physical_name' => 'title', 'datatype' => 'varchar(255)', 'is_null' => 'NO', 'is_key' => '', 'is_default' => '', 'extra' => '', 'memo' => ''],
            ['name' => 'status', 'physical_name' => 'status', 'datatype' => 'varchar(20)', 'is_null' => 'NO', 'is_key' => '', 'is_default' => 'draft', 'extra' => '', 'memo' => ''],
            ['name' => 'sortOrder', 'physical_name' => 'sort_order', 'datatype' => 'int', 'is_null' => 'NO', 'is_key' => '', 'is_default' => '0', 'extra' => '', 'memo' => ''],
            ['name' => 'isPinned', 'physical_name' => 'is_pinned', 'datatype' => 'tinyint(1)', 'is_null' => 'NO', 'is_key' => '', 'is_default' => '0', 'extra' => '', 'memo' => ''],
            ['name' => 'publishedAt', 'physical_name' => 'published_at', 'datatype' => 'datetime', 'is_null' => 'YES', 'is_key' => '', 'is_default' => '', 'extra' => '', 'memo' => ''],
            ['name' => 'note', 'physical_name' => 'note', 'datatype' => 'text', 'is_null' => 'YES', 'is_key' => '', 'is_default' => '', 'extra' => '', 'memo' => ''],
        ];
    }

    /**
     * @return list<array<string,string>>
     */
    private function taskFields(): array
    {
        $fields = [];
        foreach ($this->taskColumns() as $column) {
            $fields[] = [
                'name' => $column['name'],
                'physical_name' => $column['physical_name'],
                'datatype' => $column['datatype'],
                'ref_data_class_name' => '',
                'ref_data_class_field_name' => '',
            ];
        }

        return $fields;
    }
}
