<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_runtime.php';
require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_promotion_rehearsal.php';

final class SsoAppUserPromotionTest extends TestCase
{
    public function testSsoAppUserSqliteFixtureBuildsPromotionRehearsalPackage(): void
    {
        [$pdo, $appUserId] = $this->sqliteSsoFixture();
        $manifest = $this->manifest($pdo);
        $schema = app_sqlite_mysql_target_schema_plan($manifest);
        $export = app_sqlite_mysql_export($pdo, $manifest, 1);
        $checkpoint = $this->checkpointFromExport($export);
        $verification = app_sqlite_mysql_verification_artifact([
            'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($manifest),
            'target_schema_sha256' => (string) $schema['schema_sha256'],
            'import_checkpoint_sha256' => app_sqlite_mysql_promotion_digest($checkpoint),
        ], array_map(static fn (string $key): array => ['check_key' => $key, 'status' => 'passed'], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED));
        $cutover = app_sqlite_mysql_cutover_plan($manifest, $verification, $this->cutover(), $this->rollback(), APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS);
        $operator = app_sqlite_mysql_cutover_operator_package($cutover, $this->switchPackage(), $this->operatorRehearsal(), APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);
        $package = app_sqlite_mysql_promotion_rehearsal_package($manifest, $schema, $export, [
            'ok' => true,
            'stage' => 'chunk_committed',
            'error' => '',
            'checkpoint' => $checkpoint,
            'mutation_performed' => true,
        ], $verification, $cutover, $operator);

        self::assertTrue($schema['ok'], implode(',', $schema['errors']));
        self::assertTrue($export['ok'], implode(',', $export['errors']));
        self::assertSame(4, $package['export_summary']['table_count']);
        self::assertSame(4, $package['export_summary']['row_count']);
        self::assertTrue($verification['cutover_ready'], json_encode($verification['blockers'], JSON_THROW_ON_ERROR));
        self::assertTrue($cutover['cutover_allowed'], implode(',', $cutover['errors']));
        self::assertTrue($operator['operator_package_ready'], implode(',', $operator['errors']));
        self::assertTrue($package['rehearsal_ready'], implode(',', $package['errors']));
        self::assertSame('promotion_rehearsal_ready', $package['stage']);
        self::assertFalse($package['mutation_performed']);

        $encodedExport = json_encode($export, JSON_THROW_ON_ERROR);
        self::assertStringContainsString($appUserId, $encodedExport);
        self::assertStringContainsString('changed@example.test', $encodedExport);
        self::assertStringContainsString('SSO-owned item', $encodedExport);
        self::assertStringNotContainsString('must-not-persist', $encodedExport);
    }

    public function testPromotedMysqlTargetCanRestoreAndCreateSsoAppUsers(): void
    {
        $database = trim((string) getenv('PROMOTION_MYSQL_TEST_DB'));
        if ($database === '') self::markTestSkipped('dedicated promotion MySQL schema is not configured');
        self::assertMatchesRegularExpression('/^mtool_promotion_test_[a-z0-9_]+$/', $database);

        [$sqlite, $appUserId] = $this->sqliteSsoFixture();
        $manifest = $this->manifest($sqlite);
        $mysql = $this->mysql($database);
        $this->dropMysqlSsoTables($mysql);

        try {
            $schema = app_sqlite_mysql_target_schema_plan($manifest);
            self::assertTrue($schema['ok'], implode(',', $schema['errors']));
            $applied = app_sqlite_mysql_target_schema_apply($mysql, $schema, true);
            self::assertTrue($applied['ok'], $applied['error']);

            $checkpoint = [];
            $export = app_sqlite_mysql_export($sqlite, $manifest, 1);
            self::assertTrue($export['ok'], implode(',', $export['errors']));
            foreach ($export['chunks'] as $chunk) {
                $import = app_sqlite_mysql_import_chunk($mysql, $manifest, $chunk, $checkpoint, true);
                self::assertTrue($import['ok'], $import['error']);
                $checkpoint = $import['checkpoint'];
            }

            $restorePrincipal = $this->principal('mysql-restored@example.test', 'subject-1');
            $restorePrincipal['display_name'] = 'MySQL Restored Name';
            $restored = app_sso_app_user_resolve_verified_principal($mysql, $restorePrincipal, $this->jitPolicy());
            self::assertTrue($restored['ok'], $restored['error']);
            self::assertSame('restored', $restored['status']);
            self::assertSame($appUserId, $restored['app_user_id']);
            self::assertSame('SSO-owned item', $mysql->query('SELECT title FROM saved_item WHERE app_user_id = ' . $mysql->quote($appUserId))->fetchColumn());
            self::assertSame('mysql-restored@example.test', $mysql->query('SELECT email FROM app_user_profile WHERE app_user_id = ' . $mysql->quote($appUserId))->fetchColumn());

            $created = app_sso_app_user_resolve_verified_principal($mysql, $this->principal('mysql-new@example.test', 'subject-2'), $this->jitPolicy());
            self::assertTrue($created['ok'], $created['error']);
            self::assertSame('created', $created['status']);
            self::assertNotSame($appUserId, $created['app_user_id']);
            self::assertSame(2, (int) $mysql->query('SELECT COUNT(*) FROM app_user')->fetchColumn());
            self::assertSame(2, (int) $mysql->query('SELECT COUNT(*) FROM app_user_external_identity')->fetchColumn());
        } finally {
            $this->dropMysqlSsoTables($mysql);
        }
    }

    /** @return array{0:PDO,1:string} */
    private function sqliteSsoFixture(): array
    {
        $pdo = new PDO('sqlite::memory:');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA foreign_keys = ON');
        $policy = ['provider_key' => 'primary-oidc', 'provisioning_mode' => 'jit', 'sso_profile_fields' => ['display_name', 'email']];
        $principal = ['issuer' => 'https://idp.example.test/', 'subject' => 'subject-1', 'display_name' => 'Example User', 'email' => 'first@example.test'];
        $created = app_sso_app_user_resolve_verified_principal($pdo, $principal, $policy);
        self::assertTrue($created['ok']);
        $changed = $principal;
        $changed['display_name'] = 'Changed Name';
        $changed['email'] = 'changed@example.test';
        $changed['access_token'] = 'must-not-persist';
        $changed['raw_claims'] = ['secret' => 'must-not-persist'];
        $restored = app_sso_app_user_resolve_verified_principal($pdo, $changed, $policy);
        self::assertTrue($restored['ok']);
        self::assertSame($created['app_user_id'], $restored['app_user_id']);
        $pdo->exec(
            'CREATE TABLE saved_item (
                saved_item_id INTEGER PRIMARY KEY AUTOINCREMENT,
                app_user_id TEXT NOT NULL,
                title TEXT NOT NULL,
                FOREIGN KEY (app_user_id) REFERENCES app_user (app_user_id)
            )',
        );
        $statement = $pdo->prepare('INSERT INTO saved_item (app_user_id, title) VALUES (:app_user_id, :title)');
        $statement->execute([':app_user_id' => $restored['app_user_id'], ':title' => 'SSO-owned item']);
        return [$pdo, (string) $restored['app_user_id']];
    }

    /** @return array<string,mixed> */
    private function manifest(PDO $pdo): array
    {
        return [
            'manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION,
            'ok' => true,
            'stage' => 'preflight',
            'mutation_performed' => false,
            'source' => ['driver' => 'sqlite', 'identity' => 'sso-app-user.sqlite', 'snapshot_sha256' => str_repeat('a', 64)],
            'target' => ['driver' => 'mysql', 'identity' => 'sso-app-user-mysql', 'must_be_empty' => true],
            'canonical_sha256' => str_repeat('b', 64),
            'blockers' => [],
            'warnings' => [],
            'required_approvals' => ['target_schema_prepare', 'data_import', 'cutover'],
            'required_verification' => APP_SQLITE_MYSQL_VERIFICATION_REQUIRED,
            'non_goals' => ['mysql_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover'],
            'tables' => [
                ['name' => 'app_user', 'row_count' => $this->rowCount($pdo, 'app_user'), 'primary_key' => ['app_user_id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_app_user', 'columns' => ['app_user_id']]], 'foreign_keys' => [], 'columns' => [
                    ['name' => 'app_user_id', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                    ['name' => 'status', 'target_type' => 'VARCHAR(20)', 'nullable' => false],
                    ['name' => 'created_at', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                    ['name' => 'updated_at', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                ]],
                ['name' => 'app_user_external_identity', 'row_count' => $this->rowCount($pdo, 'app_user_external_identity'), 'primary_key' => ['issuer', 'subject'], 'keys' => [
                    ['kind' => 'primary', 'name' => 'pk_app_user_external_identity', 'columns' => ['issuer', 'subject']],
                ], 'foreign_keys' => [['name' => 'fk_external_identity_app_user', 'columns' => ['app_user_id'], 'referenced_table' => 'app_user', 'referenced_columns' => ['app_user_id']]], 'columns' => [
                    ['name' => 'app_user_id', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                    ['name' => 'provider_key', 'target_type' => 'VARCHAR(80)', 'nullable' => false],
                    ['name' => 'issuer', 'target_type' => 'VARCHAR(255)', 'nullable' => false],
                    ['name' => 'subject', 'target_type' => 'VARCHAR(255)', 'nullable' => false],
                    ['name' => 'first_authenticated_at', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                    ['name' => 'last_authenticated_at', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                ]],
                ['name' => 'app_user_profile', 'row_count' => $this->rowCount($pdo, 'app_user_profile'), 'primary_key' => ['app_user_id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_app_user_profile', 'columns' => ['app_user_id']]], 'foreign_keys' => [['name' => 'fk_profile_app_user', 'columns' => ['app_user_id'], 'referenced_table' => 'app_user', 'referenced_columns' => ['app_user_id']]], 'columns' => [
                    ['name' => 'app_user_id', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                    ['name' => 'display_name', 'target_type' => 'VARCHAR(255)', 'nullable' => false],
                    ['name' => 'email', 'target_type' => 'VARCHAR(255)', 'nullable' => false],
                    ['name' => 'profile_json', 'target_type' => 'JSON', 'nullable' => false],
                    ['name' => 'updated_at', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                ]],
                ['name' => 'saved_item', 'row_count' => $this->rowCount($pdo, 'saved_item'), 'primary_key' => ['saved_item_id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_saved_item', 'columns' => ['saved_item_id']]], 'foreign_keys' => [['name' => 'fk_saved_item_app_user', 'columns' => ['app_user_id'], 'referenced_table' => 'app_user', 'referenced_columns' => ['app_user_id']]], 'columns' => [
                    ['name' => 'saved_item_id', 'target_type' => 'BIGINT', 'nullable' => false],
                    ['name' => 'app_user_id', 'target_type' => 'VARCHAR(40)', 'nullable' => false],
                    ['name' => 'title', 'target_type' => 'VARCHAR(255)', 'nullable' => false],
                ]],
            ],
        ];
    }

    private function rowCount(PDO $pdo, string $table): int
    {
        return (int) $pdo->query('SELECT COUNT(*) FROM ' . app_sqlite_mysql_export_quote_identifier($table))->fetchColumn();
    }

    /** @return array<string,mixed> */
    private function principal(string $email, string $subject = 'subject-1'): array
    {
        return [
            'issuer' => 'https://idp.example.test/',
            'subject' => $subject,
            'display_name' => 'Example User',
            'email' => $email,
        ];
    }

    /** @return array<string,mixed> */
    private function jitPolicy(): array
    {
        return ['provider_key' => 'primary-oidc', 'provisioning_mode' => 'jit', 'sso_profile_fields' => ['display_name', 'email']];
    }

    private function mysql(string $database): PDO
    {
        return new PDO(
            'mysql:host=' . getenv('APP_LAB_DB_HOST') . ';port=' . (getenv('APP_LAB_DB_PORT') ?: '3306') . ';dbname=' . $database . ';charset=utf8mb4',
            (string) getenv('APP_LAB_DB_USER'),
            (string) getenv('APP_LAB_DB_PASSWORD'),
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
        );
    }

    private function dropMysqlSsoTables(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS `saved_item`');
        $pdo->exec('DROP TABLE IF EXISTS `app_user_profile`');
        $pdo->exec('DROP TABLE IF EXISTS `app_user_external_identity`');
        $pdo->exec('DROP TABLE IF EXISTS `app_user`');
    }

    /** @param array<string,mixed> $export @return array<string,mixed> */
    private function checkpointFromExport(array $export): array
    {
        $completed = [];
        foreach ($export['chunks'] as $chunk) {
            $completed[(string) $chunk['table'] . ':' . (int) $chunk['chunk_index']] = (string) $chunk['rows_sha256'];
        }
        ksort($completed, SORT_STRING);
        return ['checkpoint_version' => APP_SQLITE_MYSQL_IMPORT_CHECKPOINT_VERSION, 'completed' => $completed, 'last_table' => 'saved_item', 'last_chunk_index' => 0, 'resume_after_primary_key' => ['saved_item_id' => '1']];
    }

    /** @return array<string,mixed> */
    private function cutover(): array
    {
        return ['freeze_window_id' => 'freeze-20260713T180000Z', 'writes_frozen' => true, 'final_source_snapshot_sha256' => str_repeat('4', 64), 'final_verification_sha256' => str_repeat('5', 64), 'target_config_ref' => 'config/database/mysql-target', 'post_cutover_smoke_ref' => 'validation/sso-app-user-post-cutover-smoke', 'post_cutover_smoke_passed' => true, 'automatic_source_delete' => false];
    }

    /** @return array<string,mixed> */
    private function rollback(): array
    {
        return ['retain_source' => true, 'source_retention_ref' => 'rollback/sqlite/sso-app-user-source', 'rollback_procedure_ref' => 'runbooks/sso-app-user-sqlite-restore', 'rollback_window_until' => '2026-07-20T12:00:00Z', 'post_window_source_disposition' => 'archive'];
    }

    /** @return array<string,mixed> */
    private function switchPackage(): array
    {
        return ['package_id' => 'switch-20260713T190000Z', 'switch_target_driver' => 'mysql', 'switch_config_ref' => 'config/database/mysql-target', 'switch_command_ref' => 'runbooks/switch-sso-app-user-to-mysql', 'pre_switch_backup_ref' => 'backups/sso-app-user-pre-switch-config', 'post_switch_smoke_ref' => 'validation/sso-app-user-post-switch-smoke', 'rollback_command_ref' => 'runbooks/rollback-sso-app-user-to-sqlite', 'automatic_apply' => false, 'source_delete' => false];
    }

    /** @return array<string,mixed> */
    private function operatorRehearsal(): array
    {
        return ['rehearsal_report_ref' => 'validation/sso-app-user-cutover-rehearsal', 'switch_dry_run_passed' => true, 'rollback_rehearsal_passed' => true, 'post_switch_smoke_rehearsed' => true, 'mutation_performed' => false];
    }
}
