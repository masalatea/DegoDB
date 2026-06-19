<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/mtool/app/db_access_endpoint_policy.php';
require_once dirname(__DIR__, 2) . '/mtool/app/generated_runtime_auth_policy.php';

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

    public function testGeneratedRuntimeOidcJwtBearerContractIsInterfaceOnly(): void
    {
        $policy = app_generated_runtime_auth_policy_validate_json(
            2,
            json_encode([
                'type' => 'oidc-jwt-bearer',
                'issuer' => 'https://idp.example.test/realms/dego',
                'audience' => 'dego-generated-api',
                'discovery_url' => 'https://idp.example.test/realms/dego/.well-known/openid-configuration',
                'required_claims' => [
                    'scope' => 'dego.read',
                ],
            ], JSON_THROW_ON_ERROR),
        );

        self::assertTrue($policy['is_valid']);
        self::assertSame('oidc-jwt-bearer', $policy['strategy_key']);
        self::assertSame('interface-only', $policy['implementation_status']);
        self::assertSame([], $policy['secret_refs']);
    }

    public function testGeneratedProxyResolverDoesNotExecuteOidcJwtBearerYet(): void
    {
        $policy = app_resolve_db_access_single_proxy_auth_policy(
            'ProjectToken',
            '',
            2,
            json_encode([
                'type' => 'oidc-jwt-bearer',
                'issuer' => 'https://idp.example.test/realms/dego',
                'audience' => 'dego-generated-api',
                'jwks_uri' => 'https://idp.example.test/realms/dego/protocol/openid-connect/certs',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertFalse($policy['is_valid']);
        self::assertSame('invalid', $policy['strategy_key']);
        self::assertStringContainsString('未知の auth_policy_json.type', implode("\n", $policy['notes']));
    }
}
