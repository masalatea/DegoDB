<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sqlite_mysql_cutover.php';

final class SqliteMysqlCutoverTest extends TestCase
{
    public function testBuildsDeterministicApprovalBoundCutoverContract(): void
    {
        $first = app_sqlite_mysql_cutover_plan($this->manifest(), $this->verification(), $this->cutover(), $this->rollback(), APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS);
        $second = app_sqlite_mysql_cutover_plan($this->manifest(), $this->verification(), $this->cutover(), $this->rollback(), array_reverse(APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS));

        self::assertTrue($first['cutover_allowed'], implode(',', $first['errors']));
        self::assertSame('cutover_contract_ready', $first['stage']);
        self::assertFalse($first['mutation_performed']);
        self::assertTrue($first['requires_explicit_approval']);
        self::assertSame(APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS, $first['required_approvals']);
        self::assertSame(APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS, $first['approvals']);
        self::assertSame($first, $second);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['cutover_contract_sha256']);
    }

    public function testFailsClosedUntilFreezeVerificationRollbackAndApprovalsArePresent(): void
    {
        $notReadyVerification = $this->verification();
        $notReadyVerification['cutover_ready'] = false;
        $cutover = $this->cutover();
        $cutover['writes_frozen'] = false;
        $rollback = $this->rollback();
        $rollback['retain_source'] = false;

        $plan = app_sqlite_mysql_cutover_plan($this->manifest(), $notReadyVerification, $cutover, $rollback, ['freeze_confirmed']);

        self::assertFalse($plan['cutover_allowed']);
        self::assertContains('verification_not_ready', $plan['errors']);
        self::assertContains('writes_not_frozen', $plan['errors']);
        self::assertContains('source_retention_required', $plan['errors']);
        self::assertContains('missing_approval:switch_approved', $plan['errors']);
        self::assertContains('missing_approval:rollback_acknowledged', $plan['errors']);
    }

    public function testRejectsAutomaticSourceDeletionSecretsAndUnsafeReferences(): void
    {
        $cutover = $this->cutover();
        $cutover['automatic_source_delete'] = true;
        $cutover['target_config_ref'] = 'mysql://user:pass@db/app';
        $rollback = $this->rollback();
        $rollback['password'] = 'do-not-copy';
        $rollback['post_window_source_disposition'] = 'delete_now';

        $plan = app_sqlite_mysql_cutover_plan($this->manifest(), $this->verification(), $cutover, $rollback, APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS);

        self::assertFalse($plan['cutover_allowed']);
        self::assertContains('automatic_source_delete_forbidden', $plan['errors']);
        self::assertContains('secret_in_cutover_artifact', $plan['errors']);
        self::assertContains('invalid_target_config_ref', $plan['errors']);
        self::assertContains('invalid_post_window_source_disposition', $plan['errors']);
        self::assertStringNotContainsString('do-not-copy', json_encode($plan, JSON_THROW_ON_ERROR));
        self::assertStringNotContainsString('user:pass', json_encode($plan, JSON_THROW_ON_ERROR));
    }

    public function testBuildsDeterministicSideEffectFreeOperatorPackage(): void
    {
        $plan = $this->readyCutoverPlan();
        $first = app_sqlite_mysql_cutover_operator_package($plan, $this->switchPackage(), $this->rehearsal(), APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);
        $second = app_sqlite_mysql_cutover_operator_package($plan, $this->switchPackage(), $this->rehearsal(), array_reverse(APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS));

        self::assertTrue($first['operator_package_ready'], implode(',', $first['errors']));
        self::assertSame('operator_package_ready', $first['stage']);
        self::assertFalse($first['mutation_performed']);
        self::assertSame($plan['cutover_contract_sha256'], $first['cutover_contract_sha256']);
        self::assertSame(APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS, $first['required_approvals']);
        self::assertSame(APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS, $first['approvals']);
        self::assertSame('mysql', $first['switch']['switch_target_driver']);
        self::assertFalse($first['switch']['automatic_apply']);
        self::assertFalse($first['switch']['source_delete']);
        self::assertTrue($first['rehearsal']['switch_dry_run_passed']);
        self::assertTrue($first['rehearsal']['rollback_rehearsal_passed']);
        self::assertSame($first, $second);
        self::assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $first['operator_package_sha256']);
    }

    public function testOperatorPackageFailsClosedWithoutReadyPlanRehearsalAndApprovals(): void
    {
        $plan = $this->readyCutoverPlan();
        $plan['cutover_allowed'] = false;
        $switch = $this->switchPackage();
        $switch['automatic_apply'] = true;
        $rehearsal = $this->rehearsal();
        $rehearsal['rollback_rehearsal_passed'] = false;

        $package = app_sqlite_mysql_cutover_operator_package($plan, $switch, $rehearsal, ['operator_package_approved']);

        self::assertFalse($package['operator_package_ready']);
        self::assertSame('operator_package_blocked', $package['stage']);
        self::assertContains('cutover_plan_not_ready', $package['errors']);
        self::assertContains('automatic_apply_forbidden', $package['errors']);
        self::assertContains('rollback_rehearsal_not_passed', $package['errors']);
        self::assertContains('missing_approval:rollback_rehearsal_acknowledged', $package['errors']);
    }

    public function testOperatorPackageRejectsInlineExecutionSecretsAndUnsafeReferences(): void
    {
        $switch = $this->switchPackage();
        $switch['switch_command_ref'] = 'https://example.invalid/run.sh';
        $switch['inline_switch_command'] = 'php switch.php';
        $switch['password'] = 'do-not-copy';
        $switch['source_delete'] = true;
        $rehearsal = $this->rehearsal();
        $rehearsal['sql'] = 'DROP TABLE source';

        $package = app_sqlite_mysql_cutover_operator_package($this->readyCutoverPlan(), $switch, $rehearsal, APP_SQLITE_MYSQL_CUTOVER_OPERATOR_REQUIRED_APPROVALS);

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
        $manifest = [
            'manifest_version' => APP_SQLITE_MYSQL_PROMOTION_MANIFEST_VERSION,
            'ok' => true,
            'stage' => 'preflight',
            'mutation_performed' => false,
            'source' => ['driver' => 'sqlite', 'identity' => 'fixture.sqlite', 'snapshot_sha256' => str_repeat('1', 64)],
            'target' => ['driver' => 'mysql', 'identity' => 'target', 'must_be_empty' => true],
            'canonical_sha256' => str_repeat('2', 64),
            'blockers' => [],
            'warnings' => [],
            'required_approvals' => [],
            'required_verification' => [],
            'non_goals' => [],
            'tables' => [['name' => 'record', 'row_count' => 1, 'primary_key' => ['id'], 'keys' => [['kind' => 'primary', 'name' => 'pk_record', 'columns' => ['id']]], 'foreign_keys' => [], 'columns' => [['name' => 'id', 'target_type' => 'BIGINT', 'nullable' => false]]]],
        ];
        return $manifest;
    }

    /** @return array<string,mixed> */
    private function verification(): array
    {
        return [
            'verification_version' => APP_SQLITE_MYSQL_VERIFICATION_VERSION,
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
            'freeze_window_id' => 'freeze-20260713T120000Z',
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
            'source_retention_ref' => 'rollback/sqlite/frozen-source',
            'rollback_procedure_ref' => 'runbooks/sqlite-restore',
            'rollback_window_until' => '2026-07-20T12:00:00Z',
            'post_window_source_disposition' => 'archive',
        ];
    }

    /** @return array<string,mixed> */
    private function readyCutoverPlan(): array
    {
        return app_sqlite_mysql_cutover_plan($this->manifest(), $this->verification(), $this->cutover(), $this->rollback(), APP_SQLITE_MYSQL_CUTOVER_REQUIRED_APPROVALS);
    }

    /** @return array<string,mixed> */
    private function switchPackage(): array
    {
        return [
            'package_id' => 'switch-20260713T130000Z',
            'switch_target_driver' => 'mysql',
            'switch_config_ref' => 'config/database/mysql-target',
            'switch_command_ref' => 'runbooks/switch-to-mysql',
            'pre_switch_backup_ref' => 'backups/pre-switch-config',
            'post_switch_smoke_ref' => 'validation/post-switch-smoke',
            'rollback_command_ref' => 'runbooks/rollback-to-sqlite',
            'automatic_apply' => false,
            'source_delete' => false,
        ];
    }

    /** @return array<string,mixed> */
    private function rehearsal(): array
    {
        return [
            'rehearsal_report_ref' => 'validation/cutover-rehearsal',
            'switch_dry_run_passed' => true,
            'rollback_rehearsal_passed' => true,
            'post_switch_smoke_rehearsed' => true,
            'mutation_performed' => false,
        ];
    }
}
