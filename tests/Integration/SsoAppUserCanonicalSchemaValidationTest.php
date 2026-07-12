<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_canonical_schema_validation.php';

final class SsoAppUserCanonicalSchemaValidationTest extends TestCase
{
    public function testDisabledPolicyDoesNotRequireSchema(): void
    {
        $result = app_sso_app_user_validate_canonical_schema(['enabled' => false], [], [], []);
        self::assertTrue($result['metadata_valid']);
        self::assertFalse($result['ready_for_generation']);
        self::assertSame('not_applicable', $result['status']);
    }

    public function testMissingCanonicalMetadataFailsClosed(): void
    {
        $result = app_sso_app_user_validate_canonical_schema($this->policy(), [], [], []);
        self::assertFalse($result['metadata_valid']);
        self::assertSame('metadata_invalid', $result['status']);
        self::assertCount(9, $result['errors']);
        self::assertFalse($result['ready_for_generation']);
    }

    public function testExpressibleMetadataPassesButConstraintGapBlocksGeneration(): void
    {
        $tables = [
            $this->table('app_user', [['app_user_id', 'PRI'], ['status', '']]),
            $this->table('app_user_external_identity', [['app_user_id', ''], ['issuer', ''], ['subject', '']]),
            $this->table('app_user_profile', [['app_user_id', '']]),
        ];
        $dataClasses = [
            $this->dataClass('app_user', [['app_user_id', '']]),
            $this->dataClass('app_user_external_identity', [['app_user_id', 'app_user'], ['issuer', ''], ['subject', '']]),
            $this->dataClass('app_user_profile', [['app_user_id', 'app_user']]),
        ];
        $dbAccess = [
            $this->dbAccess('app_user', ['insert']),
            $this->dbAccess('app_user_external_identity', ['select', 'insert']),
            $this->dbAccess('app_user_profile', ['insert', 'update']),
        ];

        $result = app_sso_app_user_validate_canonical_schema($this->policy(), $tables, $dataClasses, $dbAccess);
        self::assertTrue($result['metadata_valid']);
        self::assertFalse($result['ready_for_generation']);
        self::assertSame('metadata_valid_constraint_gap', $result['status']);
        self::assertSame([], $result['errors']);
        self::assertCount(2, $result['blocking_gaps']);
        self::assertStringContainsString('UNIQUE (issuer, subject)', $result['blocking_gaps'][0]);
    }

    private function policy(): array
    {
        return [
            'enabled' => true,
            'auth_mode' => 'oidc',
            'provisioning_mode' => 'jit',
            'provider_key' => 'primary-oidc',
            'sso_profile_fields' => ['display_name', 'email'],
            'application_profile_fields' => ['nickname'],
            'user_owned_data' => ['saved_item'],
            'lifecycle_custom_boundary' => [],
        ];
    }

    private function table(string $name, array $columns): array
    {
        return [
            'physical_name' => $name,
            'columns' => array_map(
                static fn (array $column): array => ['physical_name' => $column[0], 'is_key' => $column[1]],
                $columns,
            ),
        ];
    }

    private function dataClass(string $name, array $fields): array
    {
        return [
            'physical_name' => $name,
            'fields' => array_map(
                static fn (array $field): array => [
                    'physical_name' => $field[0],
                    'ref_data_class_name' => $field[1],
                ],
                $fields,
            ),
        ];
    }

    private function dbAccess(string $name, array $actions): array
    {
        return [
            'source_name' => $name,
            'functions' => array_map(static fn (string $action): array => ['action_type' => $action], $actions),
        ];
    }
}
