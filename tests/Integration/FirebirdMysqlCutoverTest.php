<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/firebird_mysql_cutover.php';

final class FirebirdMysqlCutoverTest extends TestCase
{
    public function testBuildsDeterministicApprovalBoundFirebirdMysqlCutoverContract(): void
    {
        $first = app_firebird_mysql_cutover_plan($this->manifest(), $this->verification(), $this->cutover(), $this->rollback(), APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS);
        $second = app_firebird_mysql_cutover_plan($this->manifest(), $this->verification(), $this->cutover(), $this->rollback(), array_reverse(APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS));

        self::assertTrue($first['cutover_allowed'], implode(',', $first['errors']));
        self::assertSame(APP_FIREBIRD_MYSQL_CUTOVER_PLAN_VERSION, $first['cutover_plan_version']);
        self::assertSame('firebird_mysql_cutover_contract_ready', $first['stage']);
        self::assertFalse($first['mutation_performed']);
        self::assertTrue($first['requires_explicit_approval']);
        self::assertSame(APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS, $first['required_approvals']);
        self::assertSame(APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS, $first['approvals']);
        self::assertSame($first, $second);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['cutover_contract_sha256']);
    }

    public function testFirebirdMysqlCutoverFailsClosedUntilVerificationFreezeRollbackAndApprovalsArePresent(): void
    {
        $notReadyVerification = $this->verification();
        $notReadyVerification['cutover_ready'] = false;
        $cutover = $this->cutover();
        $cutover['writes_frozen'] = false;
        $rollback = $this->rollback();
        $rollback['retain_source'] = false;

        $plan = app_firebird_mysql_cutover_plan($this->manifest(), $notReadyVerification, $cutover, $rollback, ['freeze_confirmed']);

        self::assertFalse($plan['cutover_allowed']);
        self::assertContains('verification_not_ready', $plan['errors']);
        self::assertContains('writes_not_frozen', $plan['errors']);
        self::assertContains('source_retention_required', $plan['errors']);
        self::assertContains('missing_approval:switch_approved', $plan['errors']);
        self::assertContains('missing_approval:rollback_acknowledged', $plan['errors']);
    }

    public function testBuildsDeterministicSideEffectFreeFirebirdMysqlOperatorPackage(): void
    {
        $plan = $this->readyCutoverPlan();
        $first = app_firebird_mysql_cutover_operator_package($plan, $this->switchPackage(), $this->rehearsal(), APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);
        $second = app_firebird_mysql_cutover_operator_package($plan, $this->switchPackage(), $this->rehearsal(), array_reverse(APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS));

        self::assertTrue($first['operator_package_ready'], implode(',', $first['errors']));
        self::assertSame(APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_PACKAGE_VERSION, $first['operator_package_version']);
        self::assertSame('firebird_mysql_operator_package_ready', $first['stage']);
        self::assertFalse($first['mutation_performed']);
        self::assertSame($plan['cutover_contract_sha256'], $first['cutover_contract_sha256']);
        self::assertSame(APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS, $first['required_approvals']);
        self::assertSame(APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS, $first['approvals']);
        self::assertSame('mysql', $first['switch']['switch_target_driver']);
        self::assertFalse($first['switch']['automatic_apply']);
        self::assertFalse($first['switch']['source_delete']);
        self::assertTrue($first['rehearsal']['switch_dry_run_passed']);
        self::assertTrue($first['rehearsal']['rollback_rehearsal_passed']);
        self::assertSame($first, $second);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['operator_package_sha256']);
    }

    public function testFirebirdMysqlOperatorPackageRejectsInlineExecutionSecretsAndSourceDeletion(): void
    {
        $switch = $this->switchPackage();
        $switch['switch_command_ref'] = 'https://example.invalid/run.sh';
        $switch['inline_switch_command'] = 'php switch.php';
        $switch['password'] = 'do-not-copy';
        $switch['source_delete'] = true;
        $rehearsal = $this->rehearsal();
        $rehearsal['sql'] = 'DROP TABLE source';

        $package = app_firebird_mysql_cutover_operator_package($this->readyCutoverPlan(), $switch, $rehearsal, APP_FIREBIRD_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);

        self::assertFalse($package['operator_package_ready']);
        self::assertContains('secret_in_operator_package', $package['errors']);
        self::assertContains('invalid_switch_command_ref', $package['errors']);
        self::assertContains('inline_execution_forbidden:inline_switch_command', $package['errors']);
        self::assertContains('inline_execution_forbidden:sql', $package['errors']);
        self::assertContains('source_delete_forbidden', $package['errors']);
        self::assertStringNotContainsString('do-not-copy', json_encode($package, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('DROP TABLE', json_encode($package, JSON_THROW_ON_ERROR));
        self::assertSame('', $package['switch']['switch_command_ref']);
    }

    /** @return array<string,mixed> */
    private function manifest(): array
    {
        return [
            'manifest_version' => APP_FIREBIRD_MYSQL_PROMOTION_MANIFEST_VERSION,
            'ok' => true,
            'stage' => 'preflight',
            'mutation_performed' => false,
            'source' => ['driver' => 'firebird', 'identity' => 'fixture.fdb', 'snapshot_sha256' => str_repeat('1', 64), 'requires_source_backup' => true],
            'target' => ['driver' => 'mysql', 'identity' => 'target', 'must_be_empty' => true],
            'canonical_sha256' => str_repeat('2', 64),
            'blockers' => [],
            'warnings' => [],
            'required_approvals' => [],
            'required_verification' => ['firebird_backup_restore_smoke'],
            'non_goals' => ['firebird_to_sqlite', 'bidirectional_sync', 'zero_downtime_cdc', 'automatic_cutover'],
            'tables' => [[
                'name' => 'record',
                'row_count' => 1,
                'primary_key' => ['id'],
                'keys' => [['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']]],
                'foreign_keys' => [],
                'columns' => [['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false]],
            ]],
        ];
    }

    /** @return array<string,mixed> */
    private function verification(): array
    {
        return [
            'verification_version' => APP_FIREBIRD_MYSQL_VERIFICATION_VERSION,
            'cutover_ready' => true,
            'mutation_performed' => false,
            'context' => [
                'promotion_manifest_sha256' => app_sqlite_mysql_promotion_digest($this->manifest()),
                'target_schema_sha256' => str_repeat('3', 64),
                'import_checkpoint_sha256' => str_repeat('4', 64),
            ],
            'checks' => array_map(static fn (string $key): array => ['check_key' => $key, 'required' => true, 'status' => 'passed'], APP_SQLITE_MYSQL_VERIFICATION_REQUIRED),
            'blockers' => [],
        ];
    }

    /** @return array<string,mixed> */
    private function cutover(): array
    {
        return [
            'freeze_window_id' => 'firebird-freeze-20260714T120000Z',
            'writes_frozen' => true,
            'final_source_snapshot_sha256' => str_repeat('5', 64),
            'final_verification_sha256' => str_repeat('6', 64),
            'target_config_ref' => 'config/database/mysql-target',
            'post_cutover_smoke_ref' => 'validation/post-cutover-smoke',
            'post_cutover_smoke_passed' => true,
            'automatic_source_delete' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function rollback(): array
    {
        return [
            'retain_source' => true,
            'source_retention_ref' => 'rollback/firebird/frozen-source',
            'rollback_procedure_ref' => 'runbooks/firebird-restore',
            'rollback_window_until' => '2026-07-21T12:00:00Z',
            'post_window_source_disposition' => 'archive',
        ];
    }

    /** @return array<string,mixed> */
    private function readyCutoverPlan(): array
    {
        return app_firebird_mysql_cutover_plan($this->manifest(), $this->verification(), $this->cutover(), $this->rollback(), APP_FIREBIRD_MYSQL_CUTOVER_REQUIRED_APPROVALS);
    }

    /** @return array<string,mixed> */
    private function switchPackage(): array
    {
        return [
            'package_id' => 'firebird-switch-20260714T130000Z',
            'switch_target_driver' => 'mysql',
            'switch_config_ref' => 'config/database/mysql-target',
            'switch_command_ref' => 'runbooks/switch-firebird-to-mysql',
            'pre_switch_backup_ref' => 'backups/pre-switch-firebird',
            'post_switch_smoke_ref' => 'validation/post-switch-smoke',
            'rollback_command_ref' => 'runbooks/rollback-to-firebird',
            'automatic_apply' => false,
            'source_delete' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function rehearsal(): array
    {
        return [
            'rehearsal_report_ref' => 'validation/firebird-mysql-cutover-rehearsal',
            'switch_dry_run_passed' => true,
            'rollback_rehearsal_passed' => true,
            'post_switch_smoke_rehearsed' => true,
            'mutation_performed' => false,
        ];
    }
}
