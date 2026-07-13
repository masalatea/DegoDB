<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/sso_app_user_design_guidance.php';

final class SsoAppUserDesignGuidanceTest extends TestCase
{
    public function testNonSsoProjectIsNotApplicable(): void
    {
        $result = app_sso_app_user_design_guidance(['auth_mode' => 'none']);

        self::assertFalse($result['applicable']);
        self::assertSame('not_applicable', $result['status']);
        self::assertSame([], $result['questions']);
    }

    public function testGuidanceAsksOnlyMissingMaterialDecisions(): void
    {
        $result = app_sso_app_user_design_guidance([
            'auth_mode' => 'oidc',
            'sso_profile_fields' => ['display_name', 'email'],
            'application_profile_fields' => ['nickname'],
            'user_owned_data' => ['documents'],
        ]);

        self::assertTrue($result['applicable']);
        self::assertSame('needs_decision', $result['status']);
        self::assertSame(['provisioning_mode'], array_column($result['questions'], 'key'));
        self::assertSame(['issuer', 'subject'], $result['recommendation']['external_identity_key']);
        self::assertSame('app_user_id', $result['recommendation']['application_user_key']);
        self::assertFalse($result['recommendation']['email_is_identity_key']);
    }

    public function testCompleteStandardContextIsReadyWithoutQuestions(): void
    {
        $result = app_sso_app_user_design_guidance([
            'auth_mode' => 'sso',
            'provisioning_mode' => 'jit',
            'sso_profile_fields' => ['display_name', 'email'],
            'application_profile_fields' => ['nickname', 'theme'],
            'user_owned_data' => ['saved_items'],
            'has_tenant_boundary' => false,
        ]);

        self::assertSame('ready_for_design', $result['status']);
        self::assertSame([], $result['questions']);
        self::assertTrue($result['recommendation']['jit_transaction_required']);
    }

    public function testGuidanceWarnsAboutOwnershipOverlapAndForbiddenFields(): void
    {
        $result = app_sso_app_user_design_guidance([
            'auth_mode' => 'oidc',
            'provisioning_mode' => 'invitation-only',
            'sso_profile_fields' => ['email', 'access_token'],
            'application_profile_fields' => ['email'],
            'user_owned_data' => [],
        ]);

        self::assertCount(2, $result['warnings']);
        self::assertStringContainsString('email', $result['warnings'][0]);
        self::assertStringContainsString('access_token', $result['warnings'][1]);
    }

    public function testValidDesignPassesStandardValidation(): void
    {
        $result = app_sso_app_user_validate_design([
            'external_identity_fields' => ['issuer', 'subject'],
            'application_user_key' => 'app_user_id',
            'provisioning_mode' => 'jit',
            'jit_transactional' => true,
            'server_authorization' => true,
            'domain_user_reference_fields' => ['app_user_id'],
            'persisted_profile_fields' => ['display_name', 'email', 'nickname'],
            'sso_profile_fields' => ['display_name', 'email'],
            'application_profile_fields' => ['nickname'],
            'identity_link_policy' => 'explicit-verified',
            'lifecycle_custom_boundary' => ['identity-link', 'retention'],
        ]);

        self::assertTrue($result['ok']);
        self::assertSame([], $result['errors']);
        self::assertSame([], $result['warnings']);
    }

    public function testUnsafeDesignFailsClosed(): void
    {
        $result = app_sso_app_user_validate_design([
            'external_identity_fields' => ['email'],
            'application_user_key' => 'email',
            'provisioning_mode' => 'jit',
            'jit_transactional' => false,
            'server_authorization' => false,
            'domain_user_reference_fields' => ['email'],
            'persisted_profile_fields' => ['display_name', 'refresh_token', 'raw_claims'],
            'sso_profile_fields' => ['email'],
            'application_profile_fields' => ['email'],
            'identity_link_policy' => 'email-auto-link',
        ]);

        self::assertFalse($result['ok']);
        self::assertGreaterThanOrEqual(9, count($result['errors']));
        self::assertSame(['custom lifecycle boundary is not recorded.'], $result['warnings']);
    }
}
