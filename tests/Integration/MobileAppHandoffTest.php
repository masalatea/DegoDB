<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__, 2) . '/mtool/app/mobile_app_handoff.php';

final class MobileAppHandoffTest extends TestCase
{
    public function testRepresentativePacketIsReadyForAppCreatorAndMobileBuilder(): void
    {
        $result = app_mobile_app_handoff_validate($this->packet());

        self::assertTrue($result['ready'], json_encode($result['blockers'], JSON_THROW_ON_ERROR));
        self::assertFalse($result['mutation_performed']);
        self::assertSame('mobile-app-handoff-v1-validation-v1', $result['validation_version']);
        self::assertSame([], $result['blockers']);
    }

    public function testMissingCoreBehaviorFailsClosed(): void
    {
        $packet = $this->packet();
        $packet['screens'] = [
            ['screen_key' => 'cases_list', 'screen_type' => 'list', 'title' => 'Cases', 'states' => ['loading', 'empty', 'error']],
        ];
        $packet['actions'] = [];
        $packet['error_states'] = [
            ['state_key' => 'success', 'user_message' => 'Saved.'],
        ];

        $result = app_mobile_app_handoff_validate($packet);
        $codes = array_column($result['blockers'], 'code');

        self::assertFalse($result['ready']);
        self::assertContains('detail_or_form_screen_required', $codes);
        self::assertContains('actions_required', $codes);
        self::assertContains('required_error_state_missing', $codes);
    }

    public function testFirstPlatformTargetMustBeReactWebCapacitor(): void
    {
        $packet = $this->packet();
        $packet['platform_targets'] = [
            ['target_key' => 'flutter_input_packet', 'required_now' => true],
        ];

        $result = app_mobile_app_handoff_validate($packet);

        self::assertFalse($result['ready']);
        self::assertContains('first_platform_target_must_be_react_web_capacitor', array_column($result['blockers'], 'code'));
    }

    public function testNativeCapabilityDeclarationIsRequiredEvenWhenNone(): void
    {
        $packet = $this->packet();
        $packet['native_capabilities'] = [];

        $result = app_mobile_app_handoff_validate($packet);

        self::assertFalse($result['ready']);
        self::assertContains('native_capability_declaration_required', array_column($result['blockers'], 'code'));
    }

    public function testSecretBearingPacketIsRejectedAndNotReady(): void
    {
        $packet = $this->packet();
        $packet['auth']['access_token'] = 'do-not-copy';

        $result = app_mobile_app_handoff_validate($packet);

        self::assertFalse($result['ready']);
        self::assertContains('secret_in_packet', array_column($result['blockers'], 'code'));
    }

    public function testOfflineSyncRequiresExplicitContractReference(): void
    {
        $packet = $this->packet();
        $packet['offline_and_local_storage']['offline_sync'] = true;

        $result = app_mobile_app_handoff_validate($packet);

        self::assertFalse($result['ready']);
        self::assertContains('offline_sync_contract_required', array_column($result['blockers'], 'code'));
    }

    /** @return array<string,mixed> */
    private function packet(): array
    {
        $hash = str_repeat('a', 64);
        return [
            'schema_version' => 'mobile-app-handoff-v1',
            'mutation_performed' => false,
            'project' => [
                'project_key' => 'SAMPLE29',
                'name' => 'Sample29 Support Case',
                'title' => 'Support Case Mobile Wrapper Proof',
            ],
            'source_artifacts' => [
                'openapi' => ['ref' => 'work/artifacts/SAMPLE29/openapi.json', 'sha256' => $hash],
                'no_code_runtime' => ['ref' => 'work/artifacts/SAMPLE29/no-code-runtime.json', 'sha256' => str_repeat('b', 64)],
                'screen_metadata' => ['ref' => 'work/artifacts/SAMPLE29/no-code-screen-definition.json', 'sha256' => str_repeat('c', 64)],
                'auth_policy' => ['ref' => 'work/artifacts/SAMPLE29/auth-policy.json', 'sha256' => str_repeat('d', 64)],
            ],
            'platform_targets' => [
                ['target_key' => 'react_web_capacitor_ios_android', 'required_now' => true, 'role' => 'first proof target'],
                ['target_key' => 'pwa', 'required_now' => false, 'role' => 'optional shared web target'],
                ['target_key' => 'flutter_input_packet', 'required_now' => false, 'role' => 'later input packet'],
                ['target_key' => 'react_native_input_packet', 'required_now' => false, 'role' => 'later input packet'],
                ['target_key' => 'direct_native_generation', 'required_now' => false, 'role' => 'non-goal'],
            ],
            'app_identity' => [
                'display_name' => 'Support Cases',
                'bundle_id_placeholder' => 'com.example.supportcases',
                'package_id_placeholder' => 'com.example.supportcases',
                'environment' => 'local-proof',
            ],
            'auth' => [
                'mode' => 'oidc',
                'login_route' => '/login',
                'logout_route' => '/logout',
                'token_storage_policy' => 'do_not_store_tokens_in_handoff_packet',
                'redirect_or_deep_link_policy' => 'use external app builder deep-link configuration',
            ],
            'api' => [
                'base_url_policy' => 'runtime configurable per environment',
                'error_envelope' => 'standard JSON error envelope',
                'endpoints' => [
                    ['endpoint_key' => 'cases.list', 'method' => 'GET', 'path' => '/api/cases', 'response_ref' => '#/components/schemas/CaseList'],
                    ['endpoint_key' => 'cases.submit', 'method' => 'POST', 'path' => '/api/cases', 'response_ref' => '#/components/schemas/CaseSubmitResult'],
                ],
            ],
            'screens' => [
                [
                    'screen_key' => 'cases_list',
                    'screen_type' => 'list',
                    'title' => 'Cases',
                    'endpoint_key' => 'cases.list',
                    'states' => ['loading', 'empty', 'error'],
                ],
                [
                    'screen_key' => 'case_detail',
                    'screen_type' => 'detail',
                    'title' => 'Case Detail',
                    'endpoint_key' => 'cases.list',
                    'states' => ['loading', 'not_found', 'error'],
                ],
                [
                    'screen_key' => 'case_submit',
                    'screen_type' => 'form',
                    'title' => 'Submit Case',
                    'endpoint_key' => 'cases.submit',
                    'states' => ['draft', 'submitting', 'validation_error', 'submitted'],
                ],
            ],
            'navigation' => [
                ['from' => 'cases_list', 'to' => 'case_detail', 'trigger' => 'select_row'],
                ['from' => 'cases_list', 'to' => 'case_submit', 'trigger' => 'new_case'],
            ],
            'actions' => [
                [
                    'action_key' => 'submit_case',
                    'kind' => 'submit',
                    'endpoint_key' => 'cases.submit',
                    'availability' => 'enabled_after_validation',
                    'safety' => 'safe_submit',
                    'mutates' => true,
                    'idempotency' => 'client_generated_request_key',
                    'success_state' => 'success',
                    'failure_state' => 'validation_failure',
                ],
            ],
            'validation' => [
                'field_rules' => [
                    ['field_key' => 'title', 'required' => true],
                    ['field_key' => 'description', 'required' => true],
                ],
                'action_rules' => [
                    ['action_key' => 'submit_case', 'rule' => 'title_and_description_required'],
                ],
                'enforcement' => 'client displays server-authoritative validation errors',
            ],
            'error_states' => [
                ['state_key' => 'success', 'user_message' => 'Saved.'],
                ['state_key' => 'validation_failure', 'user_message' => 'Please fix the highlighted fields.'],
                ['state_key' => 'auth_failure', 'user_message' => 'Please sign in again.'],
                ['state_key' => 'network_failure', 'user_message' => 'Network unavailable. Try again.'],
                ['state_key' => 'unavailable_action', 'user_message' => 'This action is not available.'],
            ],
            'native_capabilities' => [
                [
                    'capability_key' => 'none',
                    'required' => false,
                    'reason' => 'First wrapper proof uses web/API behavior only.',
                ],
            ],
            'offline_and_local_storage' => [
                'offline_sync' => false,
                'local_draft_policy' => 'browser-local draft allowed for form only',
                'cache_policy' => 'short-lived endpoint cache only',
            ],
            'security_and_privacy' => [
                'secret_policy' => 'no secrets in packet',
                'pii_policy' => 'no production user data in packet',
                'token_persistence_policy' => 'builder must choose secure token storage',
                'logging_policy' => 'do not log tokens or personal data',
            ],
            'build_handoff' => [
                'owned_by' => 'external mobile builder',
                'capacitor_setup_owner' => 'external mobile builder',
                'ios_build_owner' => 'external mobile builder',
                'android_build_owner' => 'external mobile builder',
                'signing_owner' => 'app owner',
                'store_submission_owner' => 'app owner',
            ],
            'verification_checklist' => [
                'source artifact hashes match',
                'login flow understood',
                'list/detail/form routes mapped',
                'submit action validation mapped',
                'native capability declaration reviewed',
                'non-goals accepted',
            ],
            'non_goals' => [
                'direct_native_generation',
                'app_store_signing',
                'offline_sync_by_default',
                'production_user_data_in_packet',
            ],
        ];
    }
}
