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

    public function testManagedOperationDocsSourceOutputBuildsOperationArtifact(): void
    {
        self::assertContains('ManagedOperation', app_allowed_source_output_class_types());
        self::assertContains('managed-operation-docs-md', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('managed-operation-docs-md'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('managed-operation-docs-md'));
        self::assertSame(
            'Managed Operation Markdown Artifact',
            app_source_output_artifact_strategy_caption('managed-operation-docs-md'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $contract = app_pdo_upsert_shared_contract_metadata($app, 'CONTRACT-FOUNDATION-TEST', [
            'contract_key' => 'task',
            'data_class_physical_name' => 'task',
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($contract['ok'], $contract['error']);
        $field = app_pdo_upsert_shared_contract_field_metadata($app, 'CONTRACT-FOUNDATION-TEST', 'task', [
            'field_physical_name' => 'note',
            'operation_role' => 'editable',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($field['ok'], $field['error']);

        $operation = app_pdo_upsert_managed_operation($app, 'CONTRACT-FOUNDATION-TEST', [
            'operation_key' => 'update_note',
            'contract_key' => 'task',
            'name' => 'Update Note',
            'operation_type' => 'update',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.edit',
            'required_roles' => ['editor'],
            'required_scopes' => ['task:write'],
            'required_claims' => ['department' => 'sales'],
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operation['ok'], $operation['error']);
        $operationField = app_pdo_upsert_managed_operation_field($app, 'CONTRACT-FOUNDATION-TEST', 'update_note', [
            'field_physical_name' => 'note',
            'field_role' => 'input',
            'is_required' => true,
            'allow_client_write' => true,
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operationField['ok'], $operationField['error']);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'MANAGED-OPERATION-DOCS',
            'name' => 'Contract Foundation Managed Operations',
            'program_language' => 'md',
            'class_type' => 'ManagedOperation',
            'artifact_strategy' => 'managed-operation-docs-md',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $treeResult = app_project_output_prepare_managed_operation_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($treeResult['ok'], $treeResult['error']);
        self::assertSame(
            'mtool/managed-operation-source-outputs/CONTRACT-FOUNDATION-TEST/MANAGED-OPERATION-DOCS',
            $treeResult['runtime_source_relative_path'],
        );
        self::assertSame(
            ['README.md', 'managed-operations.json', 'managed-operations.md'],
            array_column($treeResult['scan_result']['files'] ?? [], 'relative_path'),
        );

        $payload = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/managed-operations.json'), true);
        self::assertIsArray($payload);
        self::assertTrue($payload['ok']);
        self::assertSame('managed-operation-docs-md', $payload['artifact_type'] ?? '');
        self::assertSame(1, $payload['summary']['operation_count'] ?? null);
        self::assertSame('update_note', $payload['operations'][0]['operation_key'] ?? '');
        self::assertSame('task', $payload['contracts'][0]['contract_key'] ?? '');

        $markdown = (string) file_get_contents($treeResult['runtime_source_root'] . '/managed-operations.md');
        self::assertStringContainsString('| `update_note` | `task` | `update` | `project.edit` | editor | task:write | 1 | `active` |', $markdown);
        self::assertStringContainsString('| `note` | `input` | `true` | `true` |', $markdown);

        $artifactResult = app_project_output_create_from_definition(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
            'phpunit',
        );
        self::assertTrue($artifactResult['ok'], $artifactResult['error']);
        self::assertSame(3, $artifactResult['artifact']['source_file_count'] ?? null);
        self::assertSame(
            'managed-operation-docs-md',
            $artifactResult['artifact']['artifact_strategy'] ?? '',
        );

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $definition,
        );
        self::assertTrue($publishResult['ok'], $publishResult['error']);
        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        self::assertFileExists($publishedRoot . '/managed-operations.json');
        self::assertFileExists($publishedRoot . '/managed-operations.md');
    }

    public function testNoCodeRuntimeSourceOutputBuildsScreenAndPreviewArtifact(): void
    {
        self::assertContains('NoCodeRuntime', app_allowed_source_output_class_types());
        self::assertContains('no-code-runtime-json', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('no-code-runtime-json'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('no-code-runtime-json'));
        self::assertSame(
            'No-Code Runtime JSON Artifact',
            app_source_output_artifact_strategy_caption('no-code-runtime-json'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $contract = app_pdo_upsert_shared_contract_metadata($app, 'CONTRACT-FOUNDATION-TEST', [
            'contract_key' => 'task',
            'data_class_physical_name' => 'task',
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($contract['ok'], $contract['error']);
        $field = app_pdo_upsert_shared_contract_field_metadata($app, 'CONTRACT-FOUNDATION-TEST', 'task', [
            'field_physical_name' => 'note',
            'operation_role' => 'editable',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($field['ok'], $field['error']);

        $operation = app_pdo_upsert_managed_operation($app, 'CONTRACT-FOUNDATION-TEST', [
            'operation_key' => 'update_note',
            'contract_key' => 'task',
            'name' => 'Update Note',
            'operation_type' => 'update',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.edit',
            'required_roles' => ['editor'],
            'required_scopes' => ['task:write'],
            'required_claims' => [],
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operation['ok'], $operation['error']);
        $operationField = app_pdo_upsert_managed_operation_field($app, 'CONTRACT-FOUNDATION-TEST', 'update_note', [
            'field_physical_name' => 'note',
            'field_role' => 'input',
            'is_required' => true,
            'allow_client_write' => true,
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operationField['ok'], $operationField['error']);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'NO-CODE-RUNTIME',
            'name' => 'Contract Foundation No-Code Runtime',
            'program_language' => 'json',
            'class_type' => 'NoCodeRuntime',
            'artifact_strategy' => 'no-code-runtime-json',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $treeResult = app_project_output_prepare_no_code_runtime_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($treeResult['ok'], $treeResult['error']);
        self::assertSame(
            'mtool/no-code-runtime-source-outputs/CONTRACT-FOUNDATION-TEST/NO-CODE-RUNTIME',
            $treeResult['runtime_source_relative_path'],
        );
        self::assertSame(
            ['README.md', 'runtime-preview.html', 'runtime-preview.json', 'screen-definition.json'],
            array_column($treeResult['scan_result']['files'] ?? [], 'relative_path'),
        );

        $screenDefinition = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/screen-definition.json'), true);
        self::assertIsArray($screenDefinition);
        self::assertSame('no-code-screen-definition-v0', $screenDefinition['definition_version'] ?? '');
        self::assertSame('task', $screenDefinition['contracts'][0]['contract_key'] ?? '');
        self::assertSame(['list', 'detail', 'form'], array_column($screenDefinition['contracts'][0]['screens'] ?? [], 'screen_type'));

        $runtimePreview = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/runtime-preview.json'), true);
        self::assertIsArray($runtimePreview);
        self::assertTrue($runtimePreview['ok']);
        self::assertSame('no-code-runtime-v0', $runtimePreview['runtime_version'] ?? '');
        self::assertSame(3, count($runtimePreview['screens'] ?? []));
        self::assertSame('task_list', $runtimePreview['screens'][0]['screen_key'] ?? '');
        self::assertFalse($runtimePreview['screens'][2]['actions'][0]['enabled'] ?? true);

        $runtimePreviewHtml = (string) file_get_contents($treeResult['runtime_source_root'] . '/runtime-preview.html');
        self::assertStringContainsString('<!doctype html>', $runtimePreviewHtml);
        self::assertStringContainsString('task_list', $runtimePreviewHtml);
        self::assertStringContainsString('task_form', $runtimePreviewHtml);

        $artifactResult = app_project_output_create_from_definition(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
            'phpunit',
        );
        self::assertTrue($artifactResult['ok'], $artifactResult['error']);
        self::assertSame(4, $artifactResult['artifact']['source_file_count'] ?? null);
        self::assertSame(
            'no-code-runtime-json',
            $artifactResult['artifact']['artifact_strategy'] ?? '',
        );

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $definition,
        );
        self::assertTrue($publishResult['ok'], $publishResult['error']);
        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        self::assertFileExists($publishedRoot . '/screen-definition.json');
        self::assertFileExists($publishedRoot . '/runtime-preview.json');
        self::assertFileExists($publishedRoot . '/runtime-preview.html');
    }

    public function testNoCodeReactBridgeSourceOutputBuildsFrameworkFacingArtifact(): void
    {
        self::assertContains('NoCodeReactBridge', app_allowed_source_output_class_types());
        self::assertContains('no-code-react-bridge', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('no-code-react-bridge'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('no-code-react-bridge'));
        self::assertSame(
            'No-Code React Bridge Artifact',
            app_source_output_artifact_strategy_caption('no-code-react-bridge'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $contract = app_pdo_upsert_shared_contract_metadata($app, 'CONTRACT-FOUNDATION-TEST', [
            'contract_key' => 'task',
            'data_class_physical_name' => 'task',
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($contract['ok'], $contract['error']);
        $field = app_pdo_upsert_shared_contract_field_metadata($app, 'CONTRACT-FOUNDATION-TEST', 'task', [
            'field_physical_name' => 'note',
            'operation_role' => 'editable',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($field['ok'], $field['error']);

        $operation = app_pdo_upsert_managed_operation($app, 'CONTRACT-FOUNDATION-TEST', [
            'operation_key' => 'update_note',
            'contract_key' => 'task',
            'name' => 'Update Note',
            'operation_type' => 'update',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.edit',
            'required_roles' => ['editor'],
            'required_scopes' => ['task:write'],
            'required_claims' => [],
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operation['ok'], $operation['error']);
        $operationField = app_pdo_upsert_managed_operation_field($app, 'CONTRACT-FOUNDATION-TEST', 'update_note', [
            'field_physical_name' => 'note',
            'field_role' => 'input',
            'is_required' => true,
            'allow_client_write' => true,
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operationField['ok'], $operationField['error']);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'NO-CODE-REACT-BRIDGE',
            'name' => 'Contract Foundation No-Code React Bridge',
            'program_language' => 'typescript',
            'class_type' => 'NoCodeReactBridge',
            'artifact_strategy' => 'no-code-react-bridge',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $treeResult = app_project_output_prepare_no_code_runtime_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($treeResult['ok'], $treeResult['error']);
        self::assertSame(
            'mtool/no-code-react-bridge-source-outputs/CONTRACT-FOUNDATION-TEST/NO-CODE-REACT-BRIDGE',
            $treeResult['runtime_source_relative_path'],
        );
        $filePaths = array_column($treeResult['scan_result']['files'] ?? [], 'relative_path');
        self::assertContains('bridge-contract.json', $filePaths);
        self::assertContains('package.json', $filePaths);
        self::assertContains('src/mtoolNoCodeBridge.ts', $filePaths);
        self::assertContains('src/MtoolNoCodeRuntime.tsx', $filePaths);
        self::assertContains('CONSUMER-NOTES.md', $filePaths);

        $bridgeContract = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/bridge-contract.json'), true);
        self::assertIsArray($bridgeContract);
        self::assertSame('no-code-react-bridge-contract-v0', $bridgeContract['contract_schema_version'] ?? '');
        self::assertSame('no-code-react-bridge-v0', $bridgeContract['bridge_version'] ?? '');
        self::assertSame('react', $bridgeContract['framework']['name'] ?? '');
        self::assertSame('typescript', $bridgeContract['framework']['language'] ?? '');
        self::assertSame('no-code-runtime-action-intent-v0', $bridgeContract['action_intent_version'] ?? '');
        self::assertSame('no-code-screen-definition-v0', $bridgeContract['contract_invariants']['screen_definition_version'] ?? '');
        self::assertSame('no-code-runtime-v0', $bridgeContract['contract_invariants']['runtime_preview_version'] ?? '');
        self::assertSame(
            'no-code-runtime-action-intent-v0',
            $bridgeContract['contract_invariants']['action_intent_version'] ?? '',
        );
        self::assertContains(
            'src/mtoolNoCodeBridge.ts',
            $bridgeContract['contract_invariants']['required_files'] ?? [],
        );
        self::assertContains(
            'CONSUMER-NOTES.md',
            $bridgeContract['contract_invariants']['required_files'] ?? [],
        );
        self::assertStringContainsString(
            'Mtool owns metadata',
            (string) ($bridgeContract['consumer_notes']['contract_boundary'] ?? ''),
        );
        self::assertSame(
            'The React scaffold is a verification and adapter proof. It is not a durable Mtool-owned component library.',
            $bridgeContract['consumer_notes']['generated_scaffold_status'] ?? '',
        );
        self::assertStringContainsString(
            'NO-CODE-JSON-FORMS-PROBE',
            (string) (($bridgeContract['consumer_notes']['artifact_parity_notes'][1] ?? '')),
        );
        self::assertStringContainsString(
            'make sample28-no-code-react-bridge-build-smoke',
            (string) (($bridgeContract['consumer_notes']['adapter_handoff_checklist'][2] ?? '')),
        );
        self::assertStringContainsString(
            'displayRuntimeValue',
            (string) (($bridgeContract['consumer_notes']['adapter_troubleshooting_notes'][1] ?? '')),
        );
        self::assertStringContainsString(
            'Artifact Parity Notes',
            (string) (($bridgeContract['consumer_notes']['adapter_doc_index'][0] ?? '')),
        );
        self::assertContains(
            'display_value',
            $bridgeContract['contract_invariants']['runtime_cell_shape'] ?? [],
        );
        self::assertSame('task', $bridgeContract['screen_definition']['contracts'][0]['contract_key'] ?? '');
        self::assertSame('task_list', $bridgeContract['runtime_preview']['screens'][0]['screen_key'] ?? '');

        $bridgeTypescript = (string) file_get_contents($treeResult['runtime_source_root'] . '/src/mtoolNoCodeBridge.ts');
        self::assertStringContainsString('export type MtoolBridgeContract', $bridgeTypescript);
        self::assertStringContainsString('createActionIntent', $bridgeTypescript);
        self::assertStringContainsString('createActionIntentResult', $bridgeTypescript);
        self::assertStringContainsString('validationMessage', $bridgeTypescript);
        self::assertStringContainsString('displayRuntimeValue', $bridgeTypescript);
        self::assertStringContainsString('editableInputFromItem', $bridgeTypescript);
        self::assertStringContainsString("intent_version: 'no-code-runtime-action-intent-v0'", $bridgeTypescript);
        self::assertStringContainsString('setActionError(result.message)', (string) file_get_contents($treeResult['runtime_source_root'] . '/src/App.tsx'));

        $consumerNotes = (string) file_get_contents($treeResult['runtime_source_root'] . '/CONSUMER-NOTES.md');
        self::assertStringContainsString('# No-Code React Bridge Consumer Notes', $consumerNotes);
        self::assertStringContainsString('## Adapter Documentation Index', $consumerNotes);
        self::assertStringContainsString('Use Adapter Handoff Checklist', $consumerNotes);
        self::assertStringContainsString('## Contract Boundary', $consumerNotes);
        self::assertStringContainsString('## Schema-Form Probe Boundary', $consumerNotes);
        self::assertStringContainsString('## Artifact Parity Notes', $consumerNotes);
        self::assertStringContainsString('Inspect NO-CODE-REACT-BRIDGE', $consumerNotes);
        self::assertStringContainsString('Inspect NO-CODE-JSON-FORMS-PROBE', $consumerNotes);
        self::assertStringContainsString('## Adapter Handoff Checklist', $consumerNotes);
        self::assertStringContainsString('make sample28-no-code-react-bridge-browser-smoke', $consumerNotes);
        self::assertStringContainsString('## Adapter Troubleshooting Notes', $consumerNotes);
        self::assertStringContainsString('displayRuntimeValue helper', $consumerNotes);

        $artifactResult = app_project_output_create_from_definition(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
            'phpunit',
        );
        self::assertTrue($artifactResult['ok'], $artifactResult['error']);
        self::assertSame(
            'no-code-react-bridge',
            $artifactResult['artifact']['artifact_strategy'] ?? '',
        );

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $definition,
        );
        self::assertTrue($publishResult['ok'], $publishResult['error']);
        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        self::assertFileExists($publishedRoot . '/bridge-contract.json');
        self::assertFileExists($publishedRoot . '/CONSUMER-NOTES.md');
        self::assertFileExists($publishedRoot . '/src/mtoolNoCodeBridge.ts');
        self::assertFileExists($publishedRoot . '/src/MtoolNoCodeRuntime.tsx');
    }

    public function testNoCodeJsonFormsProbeSourceOutputBuildsSchemaFormComparisonArtifact(): void
    {
        self::assertContains('NoCodeJsonFormsProbe', app_allowed_source_output_class_types());
        self::assertContains('no-code-json-forms-probe', app_allowed_source_output_artifact_strategies());
        self::assertTrue(app_source_output_artifact_strategy_supports_generation('no-code-json-forms-probe'));
        self::assertTrue(app_source_output_artifact_strategy_requires_runtime_source('no-code-json-forms-probe'));
        self::assertSame(
            'No-Code JSON Forms Probe Artifact',
            app_source_output_artifact_strategy_caption('no-code-json-forms-probe'),
        );

        $app = $this->createBootstrappedSqliteApp();
        $this->seedTaskProject($app);

        $contract = app_pdo_upsert_shared_contract_metadata($app, 'CONTRACT-FOUNDATION-TEST', [
            'contract_key' => 'task',
            'data_class_physical_name' => 'task',
            'status' => 'active',
            'sync_role' => 'server-copy',
            'no_code_role' => 'managed-screen',
            'app_persistence_role' => 'local-copy',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($contract['ok'], $contract['error']);
        $field = app_pdo_upsert_shared_contract_field_metadata($app, 'CONTRACT-FOUNDATION-TEST', 'task', [
            'field_physical_name' => 'title',
            'operation_role' => 'editable',
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($field['ok'], $field['error']);

        $operation = app_pdo_upsert_managed_operation($app, 'CONTRACT-FOUNDATION-TEST', [
            'operation_key' => 'update_note',
            'contract_key' => 'task',
            'name' => 'Update Note',
            'operation_type' => 'update',
            'status' => 'active',
            'storage_policy' => 'business-only',
            'permission_key' => 'project.edit',
            'required_roles' => ['editor'],
            'required_scopes' => ['task:write'],
            'required_claims' => [],
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operation['ok'], $operation['error']);
        $operationField = app_pdo_upsert_managed_operation_field($app, 'CONTRACT-FOUNDATION-TEST', 'update_note', [
            'field_physical_name' => 'title',
            'field_role' => 'input',
            'is_required' => true,
            'allow_client_write' => true,
            'source_of_truth' => 'test',
        ]);
        self::assertTrue($operationField['ok'], $operationField['error']);

        $definition = app_project_output_merge_source_output_definition('CONTRACT-FOUNDATION-TEST', [
            'source_output_key' => 'NO-CODE-JSON-FORMS-PROBE',
            'name' => 'Contract Foundation No-Code JSON Forms Probe',
            'program_language' => 'json',
            'class_type' => 'NoCodeJsonFormsProbe',
            'artifact_strategy' => 'no-code-json-forms-probe',
            'target_binding_type' => 'runtime',
            'runtime_source_relative_path' => '',
        ]);

        $treeResult = app_project_output_prepare_no_code_runtime_source_tree(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
        );

        self::assertTrue($treeResult['ok'], $treeResult['error']);
        self::assertSame(
            'mtool/no-code-json-forms-probe-source-outputs/CONTRACT-FOUNDATION-TEST/NO-CODE-JSON-FORMS-PROBE',
            $treeResult['runtime_source_relative_path'],
        );
        $filePaths = array_column($treeResult['scan_result']['files'] ?? [], 'relative_path');
        self::assertContains('schema-form-contract.json', $filePaths);
        self::assertContains('json-schema.json', $filePaths);
        self::assertContains('ui-schema.json', $filePaths);
        self::assertContains('CONSUMER-NOTES.md', $filePaths);

        $schemaFormContract = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/schema-form-contract.json'), true);
        self::assertIsArray($schemaFormContract);
        self::assertSame('no-code-json-forms-probe-contract-v0', $schemaFormContract['schema_form_contract_version'] ?? '');
        self::assertSame('no-code-json-forms-probe-v0', $schemaFormContract['probe_version'] ?? '');
        self::assertSame('task_form', $schemaFormContract['form_screen_key'] ?? '');
        self::assertSame('update_note', $schemaFormContract['action_key'] ?? '');
        self::assertContains('json-forms', $schemaFormContract['schema_form_targets'] ?? []);
        self::assertContains('rjsf', $schemaFormContract['schema_form_targets'] ?? []);

        $jsonSchema = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/json-schema.json'), true);
        self::assertIsArray($jsonSchema);
        self::assertSame('https://json-schema.org/draft/2020-12/schema', $jsonSchema['$schema'] ?? '');
        self::assertSame('object', $jsonSchema['type'] ?? '');
        self::assertSame(['title'], $jsonSchema['required'] ?? []);
        self::assertSame('string', $jsonSchema['properties']['title']['type'] ?? '');
        self::assertSame('title', $jsonSchema['properties']['title']['x-mtool-field-key'] ?? '');
        self::assertTrue($jsonSchema['properties']['title']['x-mtool-required'] ?? false);
        self::assertSame(1, $jsonSchema['properties']['title']['minLength'] ?? null);
        self::assertSame('\\S', $jsonSchema['properties']['title']['pattern'] ?? '');
        self::assertTrue($jsonSchema['properties']['title']['x-mtool-blank-is-missing'] ?? false);
        self::assertSame('input', $jsonSchema['properties']['title']['x-mtool-action-field-role'] ?? '');
        self::assertTrue($jsonSchema['properties']['title']['x-mtool-client-write'] ?? false);
        self::assertContains(
            'x-mtool-field-key',
            $schemaFormContract['contract_invariants']['mtool_extension_keys'] ?? [],
        );
        self::assertContains(
            'x-mtool-blank-is-missing',
            $schemaFormContract['contract_invariants']['mtool_extension_keys'] ?? [],
        );
        self::assertContains(
            'CONSUMER-NOTES.md',
            $schemaFormContract['contract_invariants']['required_files'] ?? [],
        );
        self::assertStringContainsString(
            'pattern \\S',
            (string) ($schemaFormContract['validation_parity']['required_blank_string_policy'] ?? ''),
        );
        self::assertStringContainsString(
            'comparison probe',
            (string) ($schemaFormContract['consumer_notes']['probe_boundary'] ?? ''),
        );
        self::assertStringContainsString(
            'NO-CODE-REACT-BRIDGE',
            (string) (($schemaFormContract['consumer_notes']['artifact_parity_notes'][1] ?? '')),
        );
        self::assertStringContainsString(
            'make sample28-no-code-schema-form-runtime-smoke',
            (string) (($schemaFormContract['consumer_notes']['adapter_handoff_checklist'][2] ?? '')),
        );
        self::assertStringContainsString(
            'field_mappings',
            (string) (($schemaFormContract['consumer_notes']['adapter_troubleshooting_notes'][1] ?? '')),
        );
        self::assertStringContainsString(
            'blank required values',
            implode(' ', array_map('strval', $schemaFormContract['consumer_notes']['adapter_troubleshooting_notes'] ?? [])),
        );
        self::assertStringContainsString(
            'Generated Files',
            (string) (($schemaFormContract['consumer_notes']['adapter_doc_index'][3] ?? '')),
        );
        self::assertSame(
            ['type', 'title', 'description', 'minLength', 'pattern', 'x-mtool-blank-is-missing'],
            $schemaFormContract['field_mappings'][0]['json_schema_keywords'] ?? [],
        );

        $uiSchema = json_decode((string) file_get_contents($treeResult['runtime_source_root'] . '/ui-schema.json'), true);
        self::assertIsArray($uiSchema);
        self::assertSame('VerticalLayout', $uiSchema['type'] ?? '');
        self::assertSame('#/properties/title', $uiSchema['elements'][0]['scope'] ?? '');
        self::assertSame('title', $uiSchema['elements'][0]['options']['mtoolFieldKey'] ?? '');
        self::assertSame('required', $uiSchema['elements'][0]['options']['mtoolValidationHint'] ?? '');

        $consumerNotes = (string) file_get_contents($treeResult['runtime_source_root'] . '/CONSUMER-NOTES.md');
        self::assertStringContainsString('# No-Code JSON Forms Probe Consumer Notes', $consumerNotes);
        self::assertStringContainsString('## Adapter Documentation Index', $consumerNotes);
        self::assertStringContainsString('Use Adapter Troubleshooting Notes', $consumerNotes);
        self::assertStringContainsString('## Probe Boundary', $consumerNotes);
        self::assertStringContainsString('## Runtime Smoke Boundary', $consumerNotes);
        self::assertStringContainsString('## Validation Parity Boundary', $consumerNotes);
        self::assertStringContainsString('Required string fields include JSON Schema minLength 1 and pattern \\S', $consumerNotes);
        self::assertStringContainsString('## Artifact Parity Notes', $consumerNotes);
        self::assertStringContainsString('Inspect NO-CODE-JSON-FORMS-PROBE', $consumerNotes);
        self::assertStringContainsString('Inspect NO-CODE-REACT-BRIDGE', $consumerNotes);
        self::assertStringContainsString('## Adapter Handoff Checklist', $consumerNotes);
        self::assertStringContainsString('make sample28-no-code-schema-form-runtime-smoke', $consumerNotes);
        self::assertStringContainsString('## Adapter Troubleshooting Notes', $consumerNotes);
        self::assertStringContainsString('field_mappings', $consumerNotes);
        self::assertStringContainsString('x-mtool-blank-is-missing', $consumerNotes);

        $artifactResult = app_project_output_create_from_definition(
            $app,
            'CONTRACT-FOUNDATION-TEST',
            $definition,
            'phpunit',
        );
        self::assertTrue($artifactResult['ok'], $artifactResult['error']);
        self::assertSame(
            'no-code-json-forms-probe',
            $artifactResult['artifact']['artifact_strategy'] ?? '',
        );

        $publishResult = app_project_output_publish_artifact(
            $app,
            $artifactResult['artifact'],
            $definition,
        );
        self::assertTrue($publishResult['ok'], $publishResult['error']);
        $publishedRoot = (string) ($publishResult['published']['published_root'] ?? '');
        self::assertFileExists($publishedRoot . '/schema-form-contract.json');
        self::assertFileExists($publishedRoot . '/json-schema.json');
        self::assertFileExists($publishedRoot . '/ui-schema.json');
        self::assertFileExists($publishedRoot . '/CONSUMER-NOTES.md');
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
