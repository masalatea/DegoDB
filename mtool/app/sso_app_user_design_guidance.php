<?php

declare(strict_types=1);

const APP_SSO_APP_USER_STANDARD_VERSION = 'sso-app-user-standard-v1';

/**
 * Build deterministic, side-effect-free guidance for an application design.
 *
 * @param array<string,mixed> $context
 * @return array<string,mixed>
 */
function app_sso_app_user_design_guidance(array $context): array
{
    $authMode = strtolower(trim((string) ($context['auth_mode'] ?? '')));
    $applicable = in_array($authMode, ['oidc', 'sso'], true);
    if (!$applicable) {
        return [
            'standard_version' => APP_SSO_APP_USER_STANDARD_VERSION,
            'applicable' => false,
            'status' => 'not_applicable',
            'recommendation' => [],
            'decisions' => [],
            'questions' => [],
            'warnings' => [],
        ];
    }

    $provisioningMode = strtolower(trim((string) ($context['provisioning_mode'] ?? '')));
    $ssoProfileFields = app_sso_app_user_string_list($context['sso_profile_fields'] ?? []);
    $applicationProfileFields = app_sso_app_user_string_list($context['application_profile_fields'] ?? []);
    $ownedData = app_sso_app_user_string_list($context['user_owned_data'] ?? []);
    $tenantBoundary = trim((string) ($context['tenant_boundary'] ?? ''));
    $lifecycleRequirements = app_sso_app_user_string_list($context['lifecycle_requirements'] ?? []);

    $decisions = [];
    $questions = [];
    $warnings = [];

    if (!in_array($provisioningMode, ['jit', 'invitation-only'], true)) {
        $questions[] = [
            'key' => 'provisioning_mode',
            'blocking' => true,
            'question' => 'Unknown SSO users should be created by JIT provisioning or restricted to invitation-only enrollment?',
        ];
    } else {
        $decisions['provisioning_mode'] = $provisioningMode;
    }

    if (!array_key_exists('sso_profile_fields', $context)) {
        $questions[] = [
            'key' => 'sso_profile_fields',
            'blocking' => false,
            'question' => 'Which allowlisted profile fields should be stored and refreshed from the verified SSO principal?',
        ];
    } else {
        $decisions['sso_profile_fields'] = $ssoProfileFields;
    }

    if (!array_key_exists('application_profile_fields', $context)) {
        $questions[] = [
            'key' => 'application_profile_fields',
            'blocking' => false,
            'question' => 'Which user profile fields are application-managed and must not be overwritten by SSO refresh?',
        ];
    } else {
        $decisions['application_profile_fields'] = $applicationProfileFields;
    }

    if (!array_key_exists('user_owned_data', $context)) {
        $questions[] = [
            'key' => 'user_owned_data',
            'blocking' => false,
            'question' => 'Which application records are owned, created, or assigned by an app user?',
        ];
    } else {
        $decisions['user_owned_data'] = $ownedData;
    }

    if (($context['has_tenant_boundary'] ?? false) === true && $tenantBoundary === '') {
        $questions[] = [
            'key' => 'tenant_boundary',
            'blocking' => true,
            'question' => 'Which verified claim or application mapping defines the tenant or organization boundary?',
        ];
    } elseif ($tenantBoundary !== '') {
        $decisions['tenant_boundary'] = $tenantBoundary;
    }

    if ($lifecycleRequirements !== []) {
        $decisions['lifecycle_requirements'] = $lifecycleRequirements;
        $warnings[] = 'Custom lifecycle requirements require explicit design outside the standard login and profile-refresh path.';
    }

    $conflicts = array_values(array_intersect($ssoProfileFields, $applicationProfileFields));
    if ($conflicts !== []) {
        $warnings[] = 'Profile field ownership overlaps: ' . implode(', ', $conflicts) . '.';
    }

    $forbidden = app_sso_app_user_forbidden_fields(array_merge($ssoProfileFields, $applicationProfileFields));
    if ($forbidden !== []) {
        $warnings[] = 'Forbidden credential or raw-claim fields must not be persisted: ' . implode(', ', $forbidden) . '.';
    }

    return [
        'standard_version' => APP_SSO_APP_USER_STANDARD_VERSION,
        'applicable' => true,
        'status' => array_filter($questions, static fn (array $item): bool => $item['blocking']) === []
            ? 'ready_for_design'
            : 'needs_decision',
        'recommendation' => [
            'external_identity_key' => ['issuer', 'subject'],
            'application_user_key' => 'app_user_id',
            'email_is_identity_key' => false,
            'storage_owner' => 'generated-user-db',
            'jit_transaction_required' => $provisioningMode === 'jit',
            'server_authorization_required' => true,
            'forbidden_persistence' => ['password', 'access_token', 'refresh_token', 'id_token', 'client_secret', 'raw_claims'],
        ],
        'decisions' => $decisions,
        'questions' => $questions,
        'warnings' => $warnings,
    ];
}

/**
 * Validate a proposed design against the stable standard.
 *
 * @param array<string,mixed> $design
 * @return array{ok:bool,standard_version:string,errors:list<string>,warnings:list<string>}
 */
function app_sso_app_user_validate_design(array $design): array
{
    $errors = [];
    $warnings = [];
    $identityFields = app_sso_app_user_string_list($design['external_identity_fields'] ?? []);
    foreach (['issuer', 'subject'] as $required) {
        if (!in_array($required, $identityFields, true)) {
            $errors[] = 'external identity must include ' . $required . '.';
        }
    }
    if (array_intersect($identityFields, ['email', 'username', 'preferred_username', 'display_name']) !== []) {
        $errors[] = 'mutable profile fields must not be external identity keys.';
    }

    if (trim((string) ($design['application_user_key'] ?? '')) !== 'app_user_id') {
        $errors[] = 'application-owned app_user_id is required.';
    }

    $provisioningMode = strtolower(trim((string) ($design['provisioning_mode'] ?? '')));
    if (!in_array($provisioningMode, ['jit', 'invitation-only'], true)) {
        $errors[] = 'provisioning_mode must be jit or invitation-only.';
    }
    if ($provisioningMode === 'jit' && ($design['jit_transactional'] ?? false) !== true) {
        $errors[] = 'JIT app-user and identity creation must be transactional.';
    }
    if (($design['server_authorization'] ?? false) !== true) {
        $errors[] = 'server authorization from a verified principal is required.';
    }

    $domainReferenceFields = app_sso_app_user_string_list($design['domain_user_reference_fields'] ?? []);
    if ($domainReferenceFields !== [] && array_diff($domainReferenceFields, ['app_user_id']) !== []) {
        $errors[] = 'domain user references must use app_user_id.';
    }

    $persistedFields = app_sso_app_user_string_list($design['persisted_profile_fields'] ?? []);
    $forbidden = app_sso_app_user_forbidden_fields($persistedFields);
    if ($forbidden !== []) {
        $errors[] = 'forbidden fields must not be persisted: ' . implode(', ', $forbidden) . '.';
    }

    $ssoProfileFields = app_sso_app_user_string_list($design['sso_profile_fields'] ?? []);
    $applicationProfileFields = app_sso_app_user_string_list($design['application_profile_fields'] ?? []);
    $overlap = array_values(array_intersect($ssoProfileFields, $applicationProfileFields));
    if ($overlap !== []) {
        $errors[] = 'profile field ownership must not overlap: ' . implode(', ', $overlap) . '.';
    }
    if (($design['identity_link_policy'] ?? '') === 'email-auto-link') {
        $errors[] = 'email-based automatic identity linking is not supported.';
    }
    if (!array_key_exists('lifecycle_custom_boundary', $design)) {
        $warnings[] = 'custom lifecycle boundary is not recorded.';
    }

    return [
        'ok' => $errors === [],
        'standard_version' => APP_SSO_APP_USER_STANDARD_VERSION,
        'errors' => $errors,
        'warnings' => $warnings,
    ];
}

/** @return list<string> */
function app_sso_app_user_string_list(mixed $value): array
{
    if (!is_array($value)) {
        return [];
    }
    $items = [];
    foreach ($value as $item) {
        if (!is_string($item)) {
            continue;
        }
        $normalized = strtolower(trim($item));
        if ($normalized !== '') {
            $items[] = $normalized;
        }
    }
    return array_values(array_unique($items));
}

/** @param list<string> $fields @return list<string> */
function app_sso_app_user_forbidden_fields(array $fields): array
{
    return array_values(array_filter(
        $fields,
        static fn (string $field): bool => preg_match(
            '/(^|_)(password|passwd|secret|token|credential|credentials|raw_claim|raw_claims)($|_)/',
            $field,
        ) === 1,
    ));
}
