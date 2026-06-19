<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/db_access_endpoint_policy.php';

use PHPUnit\Framework\TestCase;

final class AuthPolicyContractTest extends TestCase
{
    public function testLegacyBlankAuthRemainsProjectTokenCompatible(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy('', '');

        self::assertTrue($policy['is_valid']);
        self::assertSame('legacy-default', $policy['resolution_source']);
        self::assertSame('project-token', $policy['strategy_key']);
    }

    public function testV2BlankAuthPolicyIsInvalid(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy('ProjectToken', '', 2, '');

        self::assertFalse($policy['is_valid']);
        self::assertSame('auth-policy-v2-invalid', $policy['resolution_source']);
        self::assertSame('invalid', $policy['strategy_key']);
    }

    public function testV2UnknownPolicyTypeIsInvalid(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy(
            'ProjectToken',
            '',
            2,
            '{"type":"made-up-auth","secret_env":"DEGODB_PROXY_BEARER_TOKEN"}',
        );

        self::assertFalse($policy['is_valid']);
        self::assertStringContainsString('未知の auth_policy_json.type', implode("\n", $policy['notes']));
    }

    public function testV2StaticBearerRequiresSecretReference(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy(
            'ProjectToken',
            '',
            2,
            '{"type":"static-bearer"}',
        );

        self::assertFalse($policy['is_valid']);
        self::assertStringContainsString('secret_env', implode("\n", $policy['notes']));
    }

    public function testV2StaticBearerRejectsStoredSecretValues(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy(
            'ProjectToken',
            '',
            2,
            '{"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN","token":"do-not-store"}',
        );

        self::assertFalse($policy['is_valid']);
        self::assertStringContainsString('secret 値を保存できません', implode("\n", $policy['notes']));
    }

    public function testV2StaticBearerWithSecretEnvIsValid(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy(
            'ProjectToken',
            'LegacyGetToken',
            2,
            '{"type":"static-bearer","secret_env":"DEGODB_PROXY_BEARER_TOKEN"}',
        );

        self::assertTrue($policy['is_valid']);
        self::assertSame('auth-policy-v2', $policy['resolution_source']);
        self::assertSame('static-bearer', $policy['strategy_key']);
        self::assertSame('DEGODB_PROXY_BEARER_TOKEN', $policy['secret_env']);
        self::assertStringContainsString('legacy get function', implode("\n", $policy['notes']));
    }
}
