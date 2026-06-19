<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/bootstrap.php';
require_once dirname(__DIR__, 2) . '/mtool/app/compare_output_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database.php';
require_once dirname(__DIR__, 2) . '/mtool/app/database_source_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/data_class_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/db_access_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_membership_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_metadata_bundle.php';
require_once dirname(__DIR__, 2) . '/mtool/app/project_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/source_output_repository.php';
require_once dirname(__DIR__, 2) . '/mtool/app/table_metadata_repository.php';

use PHPUnit\Framework\TestCase;

final class ProjectMetadataBundleContractTest extends TestCase
{
    /** @var list<string> */
    private array $cleanupProjectKeys = [];

    /** @var list<string> */
    private array $cleanupDatabaseSourceKeys = [];

    /** @var list<string> */
    private array $cleanupPaths = [];

    /** @var array<string,string|null> */
    private array $savedEnv = [];

    protected function tearDown(): void
    {
        foreach ($this->savedEnv as $key => $value) {
            if ($value === null) {
                putenv($key);
                continue;
            }

            putenv($key . '=' . $value);
        }
        $this->savedEnv = [];

        $app = app_bootstrap();
        $pdo = app_create_config_pdo($app);

        foreach (array_reverse($this->cleanupProjectKeys) as $projectKey) {
            try {
                $statement = $pdo->prepare('DELETE FROM projects WHERE project_key = :project_key');
                $statement->execute([
                    ':project_key' => $projectKey,
                ]);
            } catch (Throwable) {
            }
        }
        $this->cleanupProjectKeys = [];

        foreach (array_reverse($this->cleanupDatabaseSourceKeys) as $sourceKey) {
            try {
                $statement = $pdo->prepare('DELETE FROM database_sources WHERE source_key = :source_key');
                $statement->execute([
                    ':source_key' => $sourceKey,
                ]);
            } catch (Throwable) {
            }
        }
        $this->cleanupDatabaseSourceKeys = [];

        foreach (array_reverse($this->cleanupPaths) as $path) {
            try {
                app_project_metadata_bundle_delete_tree($path);
            } catch (Throwable) {
            }
        }
        $this->cleanupPaths = [];

        parent::tearDown();
    }

    public function testProjectCoreBundleRoundTripRestoresCoreMetadataAndPreservesExcludedRows(): void
    {
        $app = app_project_metadata_bundle_repository_app(app_bootstrap());
        $projectKey = 'BNDL' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $databaseSourceKey = 'bundle_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $this->cleanupProjectKeys[] = $projectKey;
        $this->cleanupDatabaseSourceKeys[] = $databaseSourceKey;

        $this->seedProjectCoreFixture($app, $projectKey);
        $this->seedDatabaseSourceFixture($app, $databaseSourceKey, 'bundle-initial-password');

        $compareOutputResult = app_create_project_compare_output($app, [
            'project_key' => $projectKey,
            'compare_output_key' => 'COMPARE-BUNDLE',
            'name' => 'Bundle Compare Output',
            'storage_base_path' => 'work/compare-output/' . strtolower($projectKey),
            'output_file_path' => 'result.txt',
            'output_file_type' => 'Text',
            'compare_path' => 'expected',
            'compare_tool_file_path' => '',
            'compare_output_list_order' => '10',
            'notes' => 'excluded fixture row',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($compareOutputResult['ok'], $compareOutputResult['error']);

        $bundleRoot = sys_get_temp_dir() . '/mtool-project-metadata-bundle-' . bin2hex(random_bytes(6));
        $roundTripRoot = sys_get_temp_dir() . '/mtool-project-metadata-bundle-' . bin2hex(random_bytes(6));
        $this->cleanupPaths[] = $bundleRoot;
        $this->cleanupPaths[] = $roundTripRoot;

        $exportResult = app_project_metadata_bundle_export($app, $projectKey, [
            'output_dir' => $bundleRoot,
            'database_source_keys' => [$databaseSourceKey],
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($exportResult['ok'], $exportResult['error']);
        self::assertSame('project-core', $exportResult['manifest']['scope'] ?? '');
        self::assertSame('exclude-all', $exportResult['manifest']['secrets_policy'] ?? '');
        self::assertSame($projectKey, $exportResult['manifest']['source_project_key'] ?? '');
        self::assertSame(1, $exportResult['summary']['database_source_count'] ?? 0);
        self::assertSame(1, $exportResult['summary']['database_source_with_password_count'] ?? 0);
        self::assertSame(
            [
                [
                    'source_key' => $databaseSourceKey,
                    'label' => 'Bundle External Source',
                    'description' => 'bundle metadata source',
                    'host' => 'bundle-db-host',
                    'port' => '3306',
                    'database_name' => 'bundle_schema',
                    'user_name' => 'bundle_user',
                    'supports_live_schema_import' => true,
                    'supports_proxy_runtime_read' => true,
                    'proxy_runtime_priority' => 150,
                    'source_of_truth' => 'manual',
                    'has_password' => true,
                ],
            ],
            $exportResult['sections']['database_sources']['database_sources'] ?? [],
        );
        self::assertSame(
            'database-source-secrets.template.json',
            $exportResult['manifest']['supplemental_files']['database_source_secrets_template']['path'] ?? '',
        );
        $databaseSourceSecretsTemplatePath = $bundleRoot . '/database-source-secrets.template.json';
        self::assertFileExists($databaseSourceSecretsTemplatePath);
        $databaseSourceSecretsTemplate = json_decode((string) file_get_contents($databaseSourceSecretsTemplatePath), true);
        self::assertIsArray($databaseSourceSecretsTemplate);
        self::assertSame(
            [$databaseSourceKey => ''],
            $databaseSourceSecretsTemplate['database_source_passwords'] ?? null,
        );
        self::assertStringNotContainsString(
            'bundle-initial-password',
            (string) file_get_contents($databaseSourceSecretsTemplatePath),
        );

        $previewResult = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
            'target_project_key' => '',
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($previewResult['ok'], $previewResult['error']);
        self::assertSame('replace-core', $previewResult['summary']['target_action'] ?? '');
        self::assertSame('1', $previewResult['summary']['target_exists'] ?? '');
        self::assertSame(1, $previewResult['summary']['excluded_compare_outputs'] ?? 0);
        self::assertSame(1, $previewResult['summary']['database_source_existing_count'] ?? 0);
        self::assertSame(0, $previewResult['summary']['database_source_create_count'] ?? 0);
        self::assertSame(1, $previewResult['summary']['database_source_preserve_password_count'] ?? 0);
        self::assertSame(0, $previewResult['summary']['database_source_missing_secret_count'] ?? 0);
        self::assertContains(
            'target project には bundle scope 外 row があり、そのまま preserve されます。',
            $previewResult['warnings'],
        );

        $this->mutateProjectCoreFixture($app, $projectKey);
        $this->updateDatabaseSourceFixture($app, $databaseSourceKey, [
            'label' => 'Bundle External Source Mutated',
            'host' => 'mutated-db-host',
            'password' => 'bundle-mutated-password',
        ]);

        $applyResult = app_project_metadata_bundle_import_apply($app, $bundleRoot, [
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($applyResult['ok'], $applyResult['error']);
        self::assertSame('replace-core', $applyResult['summary']['target_action'] ?? '');
        self::assertSame(1, $applyResult['summary']['excluded_compare_outputs'] ?? 0);

        $compareOutputCatalog = app_fetch_project_compare_output_catalog($app, $projectKey);
        self::assertTrue($compareOutputCatalog['ok'], $compareOutputCatalog['error']);
        self::assertCount(1, $compareOutputCatalog['items']);
        self::assertSame('COMPARE-BUNDLE', $compareOutputCatalog['items'][0]['compare_output_key'] ?? '');

        $databaseSource = $this->fetchDatabaseSourceFixture($app, $databaseSourceKey);
        self::assertIsArray($databaseSource);
        self::assertSame('Bundle External Source', $databaseSource['label'] ?? '');
        self::assertSame('bundle-db-host', $databaseSource['host'] ?? '');
        self::assertSame('bundle-mutated-password', $databaseSource['password'] ?? '');

        $roundTripExport = app_project_metadata_bundle_export($app, $projectKey, [
            'output_dir' => $roundTripRoot,
            'database_source_keys' => [$databaseSourceKey],
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($roundTripExport['ok'], $roundTripExport['error']);

        $loadedOriginal = app_project_metadata_bundle_load($bundleRoot);
        $loadedRoundTrip = app_project_metadata_bundle_load($roundTripRoot);
        self::assertTrue($loadedOriginal['ok'], $loadedOriginal['error']);
        self::assertTrue($loadedRoundTrip['ok'], $loadedRoundTrip['error']);
        self::assertSame($loadedOriginal['sections'], $loadedRoundTrip['sections']);
    }

    public function testProjectCoreBundleDatabaseSourcesFailClosedForNewSecretBackedRowsWithoutSecretsFile(): void
    {
        $app = app_project_metadata_bundle_repository_app(app_bootstrap());
        $projectKey = 'BNDL' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $databaseSourceKey = 'bundle_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $this->cleanupProjectKeys[] = $projectKey;
        $this->cleanupDatabaseSourceKeys[] = $databaseSourceKey;

        $this->seedProjectCoreFixture($app, $projectKey);
        $this->seedDatabaseSourceFixture($app, $databaseSourceKey, 'bundle-export-password');

        $bundleRoot = sys_get_temp_dir() . '/mtool-project-metadata-bundle-' . bin2hex(random_bytes(6));
        $secretsPath = sys_get_temp_dir() . '/mtool-project-metadata-secrets-' . bin2hex(random_bytes(6)) . '.json';
        $this->cleanupPaths[] = $bundleRoot;
        $this->cleanupPaths[] = $secretsPath;

        $exportResult = app_project_metadata_bundle_export($app, $projectKey, [
            'output_dir' => $bundleRoot,
            'database_source_keys' => [$databaseSourceKey],
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($exportResult['ok'], $exportResult['error']);
        self::assertSame(
            'database-source-secrets.template.json',
            $exportResult['manifest']['supplemental_files']['database_source_secrets_template']['path'] ?? '',
        );

        $this->deleteDatabaseSourceFixture($app, $databaseSourceKey);

        $previewWithoutSecrets = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($previewWithoutSecrets['ok'], $previewWithoutSecrets['error']);
        self::assertSame(0, $previewWithoutSecrets['summary']['database_source_existing_count'] ?? 0);
        self::assertSame(1, $previewWithoutSecrets['summary']['database_source_create_count'] ?? 0);
        self::assertSame(1, $previewWithoutSecrets['summary']['database_source_missing_secret_count'] ?? 0);
        self::assertContains(
            'database_sources の新規 row に必要な password secret が不足しています: ' . $databaseSourceKey,
            $previewWithoutSecrets['warnings'],
        );

        $applyWithoutSecrets = app_project_metadata_bundle_import_apply($app, $bundleRoot, [
            'requested_by' => 'phpunit',
        ]);
        self::assertFalse($applyWithoutSecrets['ok']);
        self::assertStringContainsString(
            'database_sources の新規 row に必要な password secret が不足しています: ' . $databaseSourceKey,
            $applyWithoutSecrets['error'],
        );

        file_put_contents(
            $secretsPath,
            json_encode(
                [
                    'database_source_passwords' => [
                        $databaseSourceKey => 'bundle-restored-password',
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            ),
        );

        $previewWithSecrets = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
            'database_source_secrets_path' => $secretsPath,
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($previewWithSecrets['ok'], $previewWithSecrets['error']);
        self::assertSame(1, $previewWithSecrets['summary']['database_source_secret_supplied_count'] ?? 0);
        self::assertSame(0, $previewWithSecrets['summary']['database_source_missing_secret_count'] ?? 0);

        $applyWithSecrets = app_project_metadata_bundle_import_apply($app, $bundleRoot, [
            'database_source_secrets_path' => $secretsPath,
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($applyWithSecrets['ok'], $applyWithSecrets['error']);

        $databaseSource = $this->fetchDatabaseSourceFixture($app, $databaseSourceKey);
        self::assertIsArray($databaseSource);
        self::assertSame('bundle-restored-password', $databaseSource['password'] ?? '');
        self::assertSame('Bundle External Source', $databaseSource['label'] ?? '');
    }

    public function testProjectCoreBundleDatabaseSourceSecretsCanReferenceEnvironmentVariables(): void
    {
        $app = app_project_metadata_bundle_repository_app(app_bootstrap());
        $projectKey = 'BNDL' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $databaseSourceKey = 'bundle_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $envName = 'MTOOL_BUNDLE_SECRET_' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $this->cleanupProjectKeys[] = $projectKey;
        $this->cleanupDatabaseSourceKeys[] = $databaseSourceKey;

        $this->seedProjectCoreFixture($app, $projectKey);
        $this->seedDatabaseSourceFixture($app, $databaseSourceKey, 'bundle-export-password');

        $bundleRoot = sys_get_temp_dir() . '/mtool-project-metadata-bundle-' . bin2hex(random_bytes(6));
        $secretsPath = sys_get_temp_dir() . '/mtool-project-metadata-secrets-' . bin2hex(random_bytes(6)) . '.json';
        $this->cleanupPaths[] = $bundleRoot;
        $this->cleanupPaths[] = $secretsPath;

        $exportResult = app_project_metadata_bundle_export($app, $projectKey, [
            'output_dir' => $bundleRoot,
            'database_source_keys' => [$databaseSourceKey],
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($exportResult['ok'], $exportResult['error']);

        $this->deleteDatabaseSourceFixture($app, $databaseSourceKey);

        file_put_contents(
            $secretsPath,
            json_encode(
                [
                    'database_source_passwords' => [
                        $databaseSourceKey => [
                            'password_env' => $envName,
                        ],
                    ],
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT,
            ),
        );

        $previewWithoutEnv = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
            'database_source_secrets_path' => $secretsPath,
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($previewWithoutEnv['ok'], $previewWithoutEnv['error']);
        self::assertSame('1', $previewWithoutEnv['summary']['database_source_secrets_file_provided'] ?? '');
        self::assertSame(1, $previewWithoutEnv['summary']['database_source_secret_env_ref_count'] ?? 0);
        self::assertSame(1, $previewWithoutEnv['summary']['database_source_secret_missing_env_count'] ?? 0);
        self::assertContains(
            'database source secret env が未設定です: ' . $databaseSourceKey . ' -> ' . $envName,
            $previewWithoutEnv['warnings'],
        );

        $this->setEnv($envName, 'bundle-env-ref-password');

        $previewWithEnv = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
            'database_source_secrets_path' => $secretsPath,
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($previewWithEnv['ok'], $previewWithEnv['error']);
        self::assertSame(1, $previewWithEnv['summary']['database_source_secret_env_ref_count'] ?? 0);
        self::assertSame(0, $previewWithEnv['summary']['database_source_secret_missing_env_count'] ?? 0);
        self::assertSame(1, $previewWithEnv['summary']['database_source_secret_supplied_count'] ?? 0);

        $applyWithEnv = app_project_metadata_bundle_import_apply($app, $bundleRoot, [
            'database_source_secrets_path' => $secretsPath,
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($applyWithEnv['ok'], $applyWithEnv['error']);

        $databaseSource = $this->fetchDatabaseSourceFixture($app, $databaseSourceKey);
        self::assertIsArray($databaseSource);
        self::assertSame('bundle-env-ref-password', $databaseSource['password'] ?? '');
    }

    public function testProjectCoreBundlePreservesGeneratedAuthPolicyReferencesAndRejectsSecretValues(): void
    {
        $app = app_project_metadata_bundle_repository_app(app_bootstrap());
        $projectKey = 'BNDL' . strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
        $this->cleanupProjectKeys[] = $projectKey;

        $this->seedProjectCoreFixture($app, $projectKey);
        $this->setDbAccessFunctionAuthPolicy(
            $app,
            $projectKey,
            'bundle_articles',
            'Insertbundle_articles',
            'StaticBearer',
            2,
            '{"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN"}',
        );

        $bundleRoot = sys_get_temp_dir() . '/mtool-project-metadata-auth-policy-bundle-' . bin2hex(random_bytes(6));
        $this->cleanupPaths[] = $bundleRoot;

        $exportResult = app_project_metadata_bundle_export($app, $projectKey, [
            'output_dir' => $bundleRoot,
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($exportResult['ok'], $exportResult['error']);

        $insertFunction = $this->bundleFunctionByName($exportResult['sections'], 'bundle_articles', 'Insertbundle_articles');
        self::assertSame('StaticBearer', $insertFunction['single_proxy_auth_type'] ?? '');
        self::assertSame('2', $insertFunction['auth_policy_version'] ?? '');
        self::assertSame(
            '{"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN"}',
            $insertFunction['auth_policy_json'] ?? '',
        );
        self::assertStringNotContainsString('sample-secret-value', app_project_metadata_bundle_json_encode($exportResult['sections']));

        $this->setDbAccessFunctionAuthPolicy(
            $app,
            $projectKey,
            'bundle_articles',
            'Insertbundle_articles',
            'ProjectToken',
            1,
            '',
        );

        $applyResult = app_project_metadata_bundle_import_apply($app, $bundleRoot, [
            'requested_by' => 'phpunit',
        ]);
        self::assertTrue($applyResult['ok'], $applyResult['error']);

        $importedFunction = app_fetch_db_access_function_metadata(
            $app,
            $projectKey,
            'bundle_articles',
            'Insertbundle_articles',
        );
        self::assertTrue($importedFunction['ok'], $importedFunction['error']);
        self::assertSame('2', (string) ($importedFunction['item']['auth_policy_version'] ?? ''));
        self::assertSame(
            '{"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN"}',
            (string) ($importedFunction['item']['auth_policy_json'] ?? ''),
        );

        $dbAccessPath = $bundleRoot . '/db-access.json';
        $dbAccessJson = json_decode((string) file_get_contents($dbAccessPath), true);
        self::assertIsArray($dbAccessJson);
        $dbAccessJson['classes'][0]['functions'][1]['auth_policy_json'] =
            '{"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN","token":"sample-secret-value"}';
        $dbAccessContents = app_project_metadata_bundle_json_encode($dbAccessJson);
        app_project_metadata_bundle_write_text($dbAccessPath, $dbAccessContents);
        $manifestPath = $bundleRoot . '/manifest.json';
        $manifestJson = json_decode((string) file_get_contents($manifestPath), true);
        self::assertIsArray($manifestJson);
        $manifestJson['files']['db_access']['sha256'] = hash('sha256', $dbAccessContents);
        $manifestJson['files']['db_access']['bytes'] = strlen($dbAccessContents);
        app_project_metadata_bundle_write_text($manifestPath, app_project_metadata_bundle_json_encode($manifestJson));

        $previewResult = app_project_metadata_bundle_import_preview($app, $bundleRoot, [
            'requested_by' => 'phpunit',
        ]);
        self::assertFalse($previewResult['ok']);
        self::assertStringContainsString('secret 値を保存できません', $previewResult['error']);
    }

    private function seedProjectCoreFixture(array $app, string $projectKey): void
    {
        $insertProject = app_insert_project($app, [
            'project_key' => $projectKey,
            'name' => 'Bundle Fixture Project',
            'slug' => 'bundle-fixture-' . strtolower($projectKey),
            'lifecycle_status' => 'draft',
            'owner_login_id' => 'owner_' . strtolower($projectKey),
            'description' => 'project metadata bundle contract fixture',
        ]);
        self::assertTrue($insertProject['ok'], $insertProject['error']);

        $replaceMemberships = app_replace_project_memberships($app, $projectKey, [
            [
                'login_id' => 'admin_' . strtolower($projectKey),
                'role_code' => 'admin',
            ],
            [
                'login_id' => 'member_' . strtolower($projectKey),
                'role_code' => 'member',
            ],
        ]);
        self::assertTrue($replaceMemberships['ok'], $replaceMemberships['error']);

        $tableResult = app_create_table_metadata_item($app, $projectKey, 'bundle_articles');
        self::assertTrue($tableResult['ok'], $tableResult['error']);
        self::assertIsArray($tableResult['item']);
        $tablePid = (string) ($tableResult['item']['pid'] ?? '');
        self::assertNotSame('', $tablePid);

        foreach (
            [
                [
                    'name' => 'article_id',
                    'datatype' => 'int',
                    'is_null' => 'NO',
                    'is_key' => 'PRI',
                    'is_default' => '',
                    'extra' => 'auto_increment',
                    'memo' => '',
                ],
                [
                    'name' => 'title',
                    'datatype' => 'varchar',
                    'is_null' => 'NO',
                    'is_key' => '',
                    'is_default' => '',
                    'extra' => '',
                    'memo' => '',
                ],
                [
                    'name' => 'score',
                    'datatype' => 'int',
                    'is_null' => 'NO',
                    'is_key' => '',
                    'is_default' => '0',
                    'extra' => '',
                    'memo' => '',
                ],
            ] as $columnInput
        ) {
            $columnResult = app_create_table_metadata_column($app, $projectKey, $tablePid, $columnInput);
            self::assertTrue($columnResult['ok'], $columnResult['error']);
        }

        $dataClassResult = app_create_data_class_metadata_item($app, $projectKey, [
            'name' => 'BundleArticle',
            'store_base_path' => 'mtool/dbclasses',
            'is_autoload' => '1',
            'inherit_parent_data_class_name' => '',
        ]);
        self::assertTrue($dataClassResult['ok'], $dataClassResult['error']);
        self::assertIsArray($dataClassResult['item']);
        $dataClassPid = (string) ($dataClassResult['item']['pid'] ?? '');
        self::assertNotSame('', $dataClassPid);

        foreach (
            [
                [
                    'name' => 'article_id',
                    'datatype' => 'int',
                    'ref_data_class_name' => '',
                    'ref_data_class_field_name' => '',
                ],
                [
                    'name' => 'title',
                    'datatype' => 'varchar',
                    'ref_data_class_name' => '',
                    'ref_data_class_field_name' => '',
                ],
                [
                    'name' => 'score',
                    'datatype' => 'int',
                    'ref_data_class_name' => '',
                    'ref_data_class_field_name' => '',
                ],
            ] as $fieldInput
        ) {
            $fieldResult = app_create_data_class_metadata_field($app, $projectKey, $dataClassPid, $fieldInput);
            self::assertTrue($fieldResult['ok'], $fieldResult['error']);
        }

        foreach ($this->sourceOutputFixtureRows($projectKey) as $sourceOutputInput) {
            $sourceOutputResult = app_create_project_source_output($app, $sourceOutputInput);
            self::assertTrue($sourceOutputResult['ok'], $sourceOutputResult['error']);
        }

        $classResult = app_upsert_db_access_class_metadata($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'store_base_path' => 'mtool/dbclasses',
            'is_autoload' => '1',
            'notes' => 'bundle fixture class',
            'source_of_truth' => 'manual',
            'last_detected_dbaccess_file' => '',
            'last_detected_data_file' => '',
        ]);
        self::assertTrue($classResult['ok'], $classResult['error']);

        $getListResult = app_upsert_db_access_function_metadata($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Getbundle_articlesList',
            'function_list_order' => '10',
            'function_suffix' => '',
            'action_type' => 'SELECTLIST',
            'data_class_base_name' => 'BundleArticle',
            'target_table_name' => 'bundle_articles',
            'parameter_type' => 'argument',
            'select_by_distinct' => '0',
            'sort_order_columns' => 'bundle_articles.article_id ASC',
            'memo' => 'select list fixture',
            'limit_parameter_type' => '',
            'limit_fixed_parameter' => '',
            'or_group_type' => '',
            'single_proxy_auth_type' => 'NoSecurity',
            'single_proxy_single_get_function_name' => '',
            'is_blob_target' => '0',
            'detected_signature' => '',
            'detected_line' => '10',
            'source_of_truth' => 'manual',
            'last_detected_dbaccess_file' => '',
            'last_detected_data_file' => '',
        ]);
        self::assertTrue($getListResult['ok'], $getListResult['error']);

        $replaceTargets = app_replace_db_access_function_source_output_target_keys(
            $app,
            $projectKey,
            'bundle_articles',
            'Getbundle_articlesList',
            ['OPENAPI-BUNDLE', 'PROXY-BUNDLE'],
        );
        self::assertTrue($replaceTargets['ok'], $replaceTargets['error']);

        $selectWhereResult = app_create_db_access_function_select_where($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Getbundle_articlesList',
            'target_table_name' => 'bundle_articles',
            'target_table_alias_name' => '',
            'target_table_column_name' => 'article_id',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'int',
            'fixed_parameter' => '',
            'another_table_name' => '',
            'another_table_alias_name' => '',
            'another_field_name' => '',
            'join_type' => '',
            'or_group' => '',
            'relational_operator' => '=',
            'where_order' => '10',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($selectWhereResult['ok'], $selectWhereResult['error']);

        $selectTargetFieldA = app_create_db_access_function_select_target_field($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Getbundle_articlesList',
            'target_table_name' => 'bundle_articles',
            'target_table_alias_name' => '',
            'target_table_column_name' => 'article_id',
            'target_table_column_prefix' => '',
            'target_table_column_suffix' => '',
            'store_class_field_name' => 'article_id',
            'group_by_target' => '0',
            'field_list_order' => '10',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($selectTargetFieldA['ok'], $selectTargetFieldA['error']);

        $selectTargetFieldB = app_create_db_access_function_select_target_field($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Getbundle_articlesList',
            'target_table_name' => 'bundle_articles',
            'target_table_alias_name' => '',
            'target_table_column_name' => 'score',
            'target_table_column_prefix' => 'COUNT(',
            'target_table_column_suffix' => ')',
            'store_class_field_name' => 'score',
            'group_by_target' => '0',
            'field_list_order' => '20',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($selectTargetFieldB['ok'], $selectTargetFieldB['error']);

        $selectHavingResult = app_create_db_access_function_select_having($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Getbundle_articlesList',
            'left_target_prefix' => '',
            'left_target_field_id' => $selectTargetFieldB['item_id'],
            'left_target_suffix' => '',
            'relational_operator' => '>',
            'right_target_prefix' => '',
            'right_parameter_type' => 'fixed',
            'right_parameter_data_type' => 'int',
            'right_fixed_parameter' => '0',
            'right_target_field_id' => '0',
            'right_target_suffix' => '',
            'having_order' => '10',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($selectHavingResult['ok'], $selectHavingResult['error']);

        $insertResult = app_upsert_db_access_function_metadata($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Insertbundle_articles',
            'function_list_order' => '20',
            'function_suffix' => '',
            'action_type' => 'INSERT',
            'data_class_base_name' => 'BundleArticle',
            'target_table_name' => 'bundle_articles',
            'parameter_type' => 'argument',
            'select_by_distinct' => '0',
            'sort_order_columns' => '',
            'memo' => 'insert fixture',
            'limit_parameter_type' => '',
            'limit_fixed_parameter' => '',
            'or_group_type' => '',
            'single_proxy_auth_type' => 'ProjectToken',
            'single_proxy_single_get_function_name' => '',
            'is_blob_target' => '0',
            'detected_signature' => '',
            'detected_line' => '20',
            'source_of_truth' => 'manual',
            'last_detected_dbaccess_file' => '',
            'last_detected_data_file' => '',
        ]);
        self::assertTrue($insertResult['ok'], $insertResult['error']);
        $insertTargetFieldResult = app_create_db_access_function_insert_target_field($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Insertbundle_articles',
            'target_table_column_name' => 'title',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'varchar',
            'fixed_parameter' => '',
            'field_list_order' => '10',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($insertTargetFieldResult['ok'], $insertTargetFieldResult['error']);

        $updateResult = app_upsert_db_access_function_metadata($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Updatebundle_articles',
            'function_list_order' => '30',
            'function_suffix' => '',
            'action_type' => 'UPDATE',
            'data_class_base_name' => 'BundleArticle',
            'target_table_name' => 'bundle_articles',
            'parameter_type' => 'argument',
            'select_by_distinct' => '0',
            'sort_order_columns' => '',
            'memo' => 'update fixture',
            'limit_parameter_type' => '',
            'limit_fixed_parameter' => '',
            'or_group_type' => '',
            'single_proxy_auth_type' => 'ProjectTokenOrGetFunc',
            'single_proxy_single_get_function_name' => 'Getbundle_articlesList',
            'is_blob_target' => '0',
            'detected_signature' => '',
            'detected_line' => '30',
            'source_of_truth' => 'manual',
            'last_detected_dbaccess_file' => '',
            'last_detected_data_file' => '',
        ]);
        self::assertTrue($updateResult['ok'], $updateResult['error']);

        $updateTargetFieldResult = app_create_db_access_function_update_target_field($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Updatebundle_articles',
            'target_table_column_name' => 'title',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'varchar',
            'fixed_parameter' => '',
            'field_list_order' => '10',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($updateTargetFieldResult['ok'], $updateTargetFieldResult['error']);

        $updateWhereResult = app_create_db_access_function_update_delete_where($app, [
            'project_key' => $projectKey,
            'source_name' => 'bundle_articles',
            'function_name' => 'Updatebundle_articles',
            'target_table_column_name' => 'article_id',
            'parameter_type' => 'argument',
            'parameter_data_type' => 'int',
            'fixed_parameter' => '',
            'or_group' => '',
            'relational_operator' => '=',
            'where_order' => '10',
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($updateWhereResult['ok'], $updateWhereResult['error']);
    }

    private function mutateProjectCoreFixture(array $app, string $projectKey): void
    {
        $updateProject = app_update_project($app, [
            'project_key' => $projectKey,
            'name' => 'Bundle Fixture Project Mutated',
            'slug' => 'bundle-fixture-' . strtolower($projectKey),
            'lifecycle_status' => 'active',
            'description' => 'mutated before import apply',
        ]);
        self::assertTrue($updateProject['ok'], $updateProject['error']);

        $replaceMemberships = app_replace_project_memberships($app, $projectKey, [
            [
                'login_id' => 'mutated_' . strtolower($projectKey),
                'role_code' => 'member',
            ],
        ]);
        self::assertTrue($replaceMemberships['ok'], $replaceMemberships['error']);

        $pdo = app_create_config_pdo($app);
        $projectId = app_project_metadata_bundle_find_project_id($pdo, $projectKey);
        self::assertIsInt($projectId);
        self::assertGreaterThan(0, $projectId);

        foreach (
            [
                'project_db_access_classes' => 'project_id',
                'project_source_outputs' => 'project_id',
                'dataclass' => 'ProjectPID',
                'dbtable' => 'ProjectPID',
            ] as $tableName => $projectColumn
        ) {
            $statement = $pdo->prepare(
                'DELETE FROM ' . $tableName . '
                 WHERE ' . $projectColumn . ' = :project_id'
            );
            $statement->execute([
                ':project_id' => $projectId,
            ]);
        }
    }

    /**
     * @return list<array<string,string>>
     */
    private function sourceOutputFixtureRows(string $projectKey): array
    {
        return [
            [
                'project_key' => $projectKey,
                'source_output_key' => 'OPENAPI-BUNDLE',
                'name' => 'OpenAPI Bundle',
                'program_language' => 'json',
                'class_type' => 'OpenAPI',
                'release_target_type' => 'Release',
                'source_template_dir' => '',
                'source_output_dir' => 'work/source-outputs/' . $projectKey . '/OPENAPI-BUNDLE',
                'source_temp_output_dir' => 'work/source-outputs/' . $projectKey . '/OPENAPI-BUNDLE/tmp',
                'proxy_base_url' => 'http://127.0.0.1:18092/runs/proxy/' . $projectKey . '/PROXY-BUNDLE',
                'autoload_filename_suffix' => '',
                'source_text_char_code' => 'UTF-8',
                'runtime_source_relative_path' => 'mtool/openapi/' . $projectKey . '/OPENAPI-BUNDLE',
                'artifact_strategy' => 'openapi-json',
                'target_binding_type' => 'single-function-proxy',
                'spec_visibility' => 'internal-only',
                'output_archive_format' => 'tar.gz',
                'source_output_list_order' => '10',
                'notes' => 'bundle fixture openapi',
                'source_of_truth' => 'manual',
            ],
            [
                'project_key' => $projectKey,
                'source_output_key' => 'PROXY-BUNDLE',
                'name' => 'Proxy Bundle',
                'program_language' => 'php',
                'class_type' => 'DBAccess',
                'release_target_type' => 'Release',
                'source_template_dir' => '',
                'source_output_dir' => 'work/source-outputs/' . $projectKey . '/PROXY-BUNDLE',
                'source_temp_output_dir' => 'work/source-outputs/' . $projectKey . '/PROXY-BUNDLE/tmp',
                'proxy_base_url' => 'http://127.0.0.1:18092/runs/proxy/' . $projectKey . '/PROXY-BUNDLE',
                'autoload_filename_suffix' => '',
                'source_text_char_code' => 'UTF-8',
                'runtime_source_relative_path' => 'mtool/proxy/' . $projectKey . '/PROXY-BUNDLE',
                'artifact_strategy' => 'single-function-proxy-server',
                'target_binding_type' => 'single-function-proxy',
                'spec_visibility' => 'internal-only',
                'output_archive_format' => 'tar.gz',
                'source_output_list_order' => '20',
                'notes' => 'bundle fixture proxy',
                'source_of_truth' => 'manual',
            ],
        ];
    }

    private function seedDatabaseSourceFixture(array $app, string $sourceKey, string $password): void
    {
        $createResult = app_create_database_source($app, [
            'source_key' => $sourceKey,
            'label' => 'Bundle External Source',
            'description' => 'bundle metadata source',
            'host' => 'bundle-db-host',
            'port' => '3306',
            'database_name' => 'bundle_schema',
            'user_name' => 'bundle_user',
            'password' => $password,
            'supports_live_schema_import' => true,
            'supports_proxy_runtime_read' => true,
            'proxy_runtime_priority' => 150,
            'source_of_truth' => 'manual',
        ]);
        self::assertTrue($createResult['ok'], $createResult['error']);
    }

    /**
     * @param array<string,mixed> $overrides
     */
    private function updateDatabaseSourceFixture(array $app, string $sourceKey, array $overrides): void
    {
        $item = $this->fetchDatabaseSourceFixture($app, $sourceKey);
        self::assertIsArray($item);

        $updateResult = app_update_database_source($app, (int) ($item['id'] ?? 0), [
            'source_key' => $overrides['source_key'] ?? (string) ($item['source_key'] ?? ''),
            'label' => $overrides['label'] ?? (string) ($item['label'] ?? ''),
            'description' => $overrides['description'] ?? (string) ($item['description'] ?? ''),
            'host' => $overrides['host'] ?? (string) ($item['host'] ?? ''),
            'port' => $overrides['port'] ?? (string) ($item['port'] ?? ''),
            'database_name' => $overrides['database_name'] ?? (string) ($item['name'] ?? ''),
            'user_name' => $overrides['user_name'] ?? (string) ($item['user'] ?? ''),
            'password' => $overrides['password'] ?? (string) ($item['password'] ?? ''),
            'supports_live_schema_import' => $overrides['supports_live_schema_import']
                ?? (bool) ($item['supports_live_schema_import'] ?? false),
            'supports_proxy_runtime_read' => $overrides['supports_proxy_runtime_read']
                ?? (bool) ($item['supports_proxy_runtime_read'] ?? false),
            'proxy_runtime_priority' => $overrides['proxy_runtime_priority']
                ?? (int) ($item['proxy_runtime_priority'] ?? 1000),
            'source_of_truth' => $overrides['source_of_truth'] ?? (string) ($item['source_of_truth'] ?? 'manual'),
        ]);
        self::assertTrue($updateResult['ok'], $updateResult['error']);
    }

    private function deleteDatabaseSourceFixture(array $app, string $sourceKey): void
    {
        $item = $this->fetchDatabaseSourceFixture($app, $sourceKey);
        self::assertIsArray($item);

        $deleteResult = app_delete_database_source($app, (int) ($item['id'] ?? 0));
        self::assertTrue($deleteResult['ok'], $deleteResult['error']);
    }

    /**
     * @return array<string,mixed>|null
     */
    private function fetchDatabaseSourceFixture(array $app, string $sourceKey): ?array
    {
        $catalogResult = app_fetch_database_sources($app);
        self::assertTrue($catalogResult['ok'], $catalogResult['error']);

        foreach ($catalogResult['items'] as $item) {
            if (!is_array($item)) {
                continue;
            }

            if ((string) ($item['source_key'] ?? '') === $sourceKey) {
                return $item;
            }
        }

        return null;
    }

    private function setDbAccessFunctionAuthPolicy(
        array $app,
        string $projectKey,
        string $sourceName,
        string $functionName,
        string $authType,
        int $authPolicyVersion,
        string $authPolicyJson,
    ): void {
        $pdo = app_create_config_pdo($app);
        $select = $pdo->prepare(
            'SELECT f.id
             FROM project_db_access_functions AS f
             INNER JOIN project_db_access_classes AS c ON c.id = f.db_access_class_id
             INNER JOIN projects AS p ON p.id = c.project_id
             WHERE p.project_key = :project_key
               AND c.source_name = :source_name
               AND f.function_name = :function_name
             LIMIT 1'
        );
        $select->execute([
            ':project_key' => $projectKey,
            ':source_name' => $sourceName,
            ':function_name' => $functionName,
        ]);
        $row = $select->fetch(PDO::FETCH_ASSOC);
        self::assertIsArray($row);

        $update = $pdo->prepare(
            'UPDATE project_db_access_functions
             SET single_proxy_auth_type = :auth_type,
                 auth_policy_version = :auth_policy_version,
                 auth_policy_json = :auth_policy_json
             WHERE id = :id'
        );
        $update->execute([
            ':auth_type' => $authType,
            ':auth_policy_version' => $authPolicyVersion,
            ':auth_policy_json' => $authPolicyJson,
            ':id' => (int) ($row['id'] ?? 0),
        ]);

        self::assertSame(1, $update->rowCount());
    }

    /**
     * @param array<string,mixed> $sections
     * @return array<string,mixed>
     */
    private function bundleFunctionByName(array $sections, string $sourceName, string $functionName): array
    {
        $classes = $sections['db_access']['classes'] ?? [];
        self::assertIsArray($classes);
        foreach ($classes as $class) {
            if (!is_array($class) || (string) ($class['source_name'] ?? '') !== $sourceName) {
                continue;
            }

            $functions = $class['functions'] ?? [];
            self::assertIsArray($functions);
            foreach ($functions as $function) {
                if (is_array($function) && (string) ($function['function_name'] ?? '') === $functionName) {
                    return $function;
                }
            }
        }

        self::fail('bundle function not found: ' . $sourceName . '.' . $functionName);
    }

    private function setEnv(string $key, string $value): void
    {
        if (!array_key_exists($key, $this->savedEnv)) {
            $current = getenv($key);
            $this->savedEnv[$key] = $current === false ? null : $current;
        }

        putenv($key . '=' . $value);
    }
}
