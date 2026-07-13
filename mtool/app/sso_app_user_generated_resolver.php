<?php

declare(strict_types=1);

require_once __DIR__ . '/generated_name.php';
require_once __DIR__ . '/sso_app_user_project_policy.php';

/**
 * Build the fail-closed generation contract after canonical schema validation.
 *
 * @return array{ok:bool,status:string,errors:list<string>,operations:array<string,array<string,string>>,artifact_text:string}
 */
function app_sso_app_user_generated_resolver_contract(
    array $policyInput,
    array $schemaValidation,
    array $dbAccessClasses,
): array {
    $policyResult = app_sso_app_user_project_policy_normalize($policyInput);
    if (!$policyResult['ok']) {
        return app_sso_app_user_generated_resolver_contract_result('invalid_policy', $policyResult['errors']);
    }
    if (($schemaValidation['ready_for_generation'] ?? false) !== true
        || ($schemaValidation['status'] ?? '') !== 'generation_ready'
    ) {
        return app_sso_app_user_generated_resolver_contract_result(
            'schema_not_ready',
            ['canonical SSO app-user schema must be generation_ready before resolver emission.'],
        );
    }

    $roles = $policyResult['policy']['schema_roles'];
    $operations = app_sso_app_user_generated_resolver_required_operations($roles);
    $classCatalog = [];
    foreach ($dbAccessClasses as $class) {
        if (!is_array($class)) {
            continue;
        }
        $sourceName = trim((string) ($class['source_name'] ?? ''));
        if ($sourceName === '') {
            continue;
        }
        $functions = [];
        foreach (is_array($class['functions'] ?? null) ? $class['functions'] : [] as $function) {
            if (!is_array($function)) {
                continue;
            }
            $name = strtolower(trim((string) ($function['function_name'] ?? '')));
            if ($name !== '') {
                $functions[$name][] = app_sso_app_user_generated_resolver_action_family((string) ($function['action_type'] ?? ''));
            }
        }
        $classCatalog[strtolower($sourceName)] = $functions;
    }

    $errors = [];
    foreach ($operations as $operation => $contract) {
        $physicalClass = strtolower($contract['table_name']);
        $generatedClass = strtolower($contract['class_name']);
        $functions = $classCatalog[$generatedClass] ?? $classCatalog[$physicalClass] ?? null;
        $functionKey = strtolower($contract['function_name']);
        if (!is_array($functions) || !isset($functions[$functionKey])) {
            $errors[] = 'missing generated SSO DBAccess operation ' . $operation . ': '
                . $contract['class_name'] . 'DBAccess::' . $contract['function_name'] . '.';
            continue;
        }
        if (count($functions[$functionKey]) !== 1) {
            $errors[] = 'ambiguous generated SSO DBAccess operation ' . $operation . ': '
                . $contract['function_name'] . '.';
            continue;
        }
        if ($functions[$functionKey][0] !== $contract['action_type']) {
            $errors[] = 'wrong action_type for generated SSO DBAccess operation ' . $operation . ': expected '
                . strtoupper($contract['action_type']) . '.';
        }
    }
    if ($errors !== []) {
        return app_sso_app_user_generated_resolver_contract_result('operation_contract_gap', $errors, $operations);
    }

    return [
        'ok' => true,
        'status' => 'artifact_ready',
        'errors' => [],
        'operations' => $operations,
        'artifact_text' => app_sso_app_user_generated_resolver_contract_artifact($policyResult['policy'], $operations),
    ];
}

function app_sso_app_user_generated_resolver_action_family(string $actionType): string
{
    return match (strtoupper(trim($actionType))) {
        'SELECT', 'SELECTLIST', 'SELECTSINGLE' => 'select',
        'INSERT' => 'insert',
        'UPDATE' => 'update',
        default => strtolower(trim($actionType)),
    };
}

/** @return array<string,array<string,string>> */
function app_sso_app_user_generated_resolver_required_operations(array $roles): array
{
    $appUser = app_generated_name_pascal_case((string) ($roles['application_user_table'] ?? 'app_user'));
    $identity = app_generated_name_pascal_case((string) ($roles['external_identity_table'] ?? 'app_user_external_identity'));
    $profile = app_generated_name_pascal_case((string) ($roles['profile_table'] ?? 'app_user_profile'));
    return [
        'external_identity_lookup' => ['table_name' => (string) $roles['external_identity_table'], 'class_name' => $identity, 'function_name' => 'Select' . $identity . 'ByIssuerSubject', 'action_type' => 'select'],
        'application_user_read' => ['table_name' => (string) $roles['application_user_table'], 'class_name' => $appUser, 'function_name' => 'Select' . $appUser . 'ByAppUserId', 'action_type' => 'select'],
        'application_user_create' => ['table_name' => (string) $roles['application_user_table'], 'class_name' => $appUser, 'function_name' => 'Insert' . $appUser, 'action_type' => 'insert'],
        'external_identity_create' => ['table_name' => (string) $roles['external_identity_table'], 'class_name' => $identity, 'function_name' => 'Insert' . $identity, 'action_type' => 'insert'],
        'profile_write' => ['table_name' => (string) $roles['profile_table'], 'class_name' => $profile, 'function_name' => 'Upsert' . $profile, 'action_type' => 'update'],
        'external_identity_touch' => ['table_name' => (string) $roles['external_identity_table'], 'class_name' => $identity, 'function_name' => 'Update' . $identity . 'LastAuthenticatedAt', 'action_type' => 'update'],
    ];
}

/** @return array{ok:bool,status:string,errors:list<string>,operations:array,artifact_text:string} */
function app_sso_app_user_generated_resolver_contract_result(string $status, array $errors, array $operations = []): array
{
    return ['ok' => false, 'status' => $status, 'errors' => array_values($errors), 'operations' => $operations, 'artifact_text' => ''];
}

function app_sso_app_user_generated_resolver_contract_artifact(array $policy, array $operations): string
{
    $payload = json_encode(
        ['contract_version' => 'mtool-generated-sso-app-user-resolver-v1', 'policy' => $policy, 'operations' => $operations],
        JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR,
    );
    return "<?php\n\ndeclare(strict_types=1);\n\n// Generated only after canonical SSO schema and DBAccess operation gates pass.\nfinal class MtoolGeneratedSsoAppUserResolverContract\n{\n    public const CONTRACT_JSON = "
        . var_export($payload, true)
        . ";\n}\n";
}

/** @return array{ok:bool,status:string,operations:array<string,callable>,errors:list<string>} */
function app_sso_app_user_generated_resolver_bind(array $contract, array $dbAccessInstances): array
{
    if (($contract['ok'] ?? false) !== true || ($contract['status'] ?? '') !== 'artifact_ready') {
        return ['ok' => false, 'status' => 'contract_not_ready', 'operations' => [], 'errors' => ['resolver contract is not artifact_ready.']];
    }
    $bound = [];
    $errors = [];
    foreach ($contract['operations'] as $operation => $definition) {
        $className = (string) ($definition['class_name'] ?? '');
        $methodName = (string) ($definition['function_name'] ?? '');
        $instance = $dbAccessInstances[$className] ?? null;
        if (!is_object($instance) || !is_callable([$instance, $methodName])) {
            $errors[] = 'generated DBAccess binding is missing: ' . $className . 'DBAccess::' . $methodName . '.';
            continue;
        }
        $bound[$operation] = [$instance, $methodName];
    }
    return ['ok' => $errors === [], 'status' => $errors === [] ? 'bound' : 'binding_gap', 'operations' => $errors === [] ? $bound : [], 'errors' => $errors];
}

/**
 * Resolve a server-verified principal through bound generated DBAccess operations.
 * Each operation accepts one associative payload and returns a row/null for reads,
 * or true / ['ok' => true] for writes.
 *
 * @return array<string,mixed>
 */
function app_sso_app_user_generated_resolve_verified_principal(
    object $transactionDb,
    array $boundOperations,
    array $principal,
    array $policyInput,
    ?callable $appUserIdFactory = null,
): array {
    foreach (['beginTransaction', 'commit', 'rollBack', 'inTransaction'] as $method) {
        if (!is_callable([$transactionDb, $method])) {
            return app_sso_app_user_generated_resolver_result(false, 'invalid_runtime', '', false, [], 'transaction runtime does not support ' . $method . '.');
        }
    }
    foreach (array_keys(app_sso_app_user_generated_resolver_required_operations([
        'application_user_table' => 'app_user',
        'external_identity_table' => 'app_user_external_identity',
        'profile_table' => 'app_user_profile',
    ])) as $operation) {
        if (!isset($boundOperations[$operation]) || !is_callable($boundOperations[$operation])) {
            return app_sso_app_user_generated_resolver_result(false, 'invalid_runtime', '', false, [], 'bound operation is missing: ' . $operation . '.');
        }
    }

    $policyResult = app_sso_app_user_project_policy_normalize($policyInput);
    if (!$policyResult['ok'] || !$policyResult['policy']['enabled']) {
        return app_sso_app_user_generated_resolver_result(false, 'invalid_policy', '', false, [], implode(' ', $policyResult['errors']) ?: 'SSO app-user policy is disabled.');
    }
    $policy = $policyResult['policy'];
    $issuer = rtrim(trim((string) ($principal['issuer'] ?? $principal['iss'] ?? '')), '/');
    $subject = trim((string) ($principal['subject'] ?? $principal['sub'] ?? ''));
    if ($issuer === '' || $subject === '') {
        return app_sso_app_user_generated_resolver_result(false, 'invalid_principal', '', false, [], 'issuer and subject are required.');
    }
    $profile = [];
    foreach ($policy['sso_profile_fields'] as $field) {
        if (array_key_exists($field, $principal) && (is_scalar($principal[$field]) || $principal[$field] === null)) {
            $profile[$field] = $principal[$field] === null ? '' : (string) $principal[$field];
        }
    }
    ksort($profile);
    $identityInput = ['issuer' => $issuer, 'subject' => $subject];

    try {
        $existingIdentity = ($boundOperations['external_identity_lookup'])($identityInput);
        if ($existingIdentity === null && $policy['provisioning_mode'] === 'invitation-only') {
            return app_sso_app_user_generated_resolver_result(false, 'enrollment_required', '', false, [], 'unknown SSO identity is not invited.');
        }

        $ownsTransaction = !$transactionDb->inTransaction();
        if ($ownsTransaction && $transactionDb->beginTransaction() !== true) {
            throw new RuntimeException('transaction begin failed.');
        }
        try {
            $existingIdentity = ($boundOperations['external_identity_lookup'])($identityInput);
            if (is_array($existingIdentity)) {
                $appUserId = trim((string) ($existingIdentity['app_user_id'] ?? ''));
                $user = ($boundOperations['application_user_read'])(['app_user_id' => $appUserId]);
                if (!is_array($user) || ($user['status'] ?? '') !== 'enabled') {
                    throw new RuntimeException('resolved app user is missing or disabled.');
                }
                app_sso_app_user_generated_resolver_require_write_success(
                    ($boundOperations['profile_write'])(['app_user_id' => $appUserId, 'profile' => $profile]),
                    'profile_write',
                );
                app_sso_app_user_generated_resolver_require_write_success(
                    ($boundOperations['external_identity_touch'])(['issuer' => $issuer, 'subject' => $subject, 'authenticated_at' => gmdate('c')]),
                    'external_identity_touch',
                );
                if ($ownsTransaction && $transactionDb->commit() !== true) {
                    throw new RuntimeException('transaction commit failed.');
                }
                return app_sso_app_user_generated_resolver_result(true, 'restored', $appUserId, false, $profile, '');
            }

            $appUserId = ($appUserIdFactory ?? static fn (): string => 'usr_' . bin2hex(random_bytes(16)))();
            $now = gmdate('c');
            app_sso_app_user_generated_resolver_require_write_success(($boundOperations['application_user_create'])(['app_user_id' => $appUserId, 'status' => 'enabled']), 'application_user_create');
            app_sso_app_user_generated_resolver_require_write_success(($boundOperations['external_identity_create'])(['app_user_id' => $appUserId, 'provider_key' => $policy['provider_key'], 'issuer' => $issuer, 'subject' => $subject, 'first_authenticated_at' => $now, 'last_authenticated_at' => $now]), 'external_identity_create');
            app_sso_app_user_generated_resolver_require_write_success(($boundOperations['profile_write'])(['app_user_id' => $appUserId, 'profile' => $profile]), 'profile_write');
            if ($ownsTransaction && $transactionDb->commit() !== true) {
                throw new RuntimeException('transaction commit failed.');
            }
            return app_sso_app_user_generated_resolver_result(true, 'created', $appUserId, true, $profile, '');
        } catch (Throwable $throwable) {
            if ($ownsTransaction && $transactionDb->inTransaction()) {
                $transactionDb->rollBack();
            }
            throw $throwable;
        }
    } catch (Throwable $throwable) {
        return app_sso_app_user_generated_resolver_result(false, 'persistence_failed', '', false, [], $throwable->getMessage());
    }
}

function app_sso_app_user_generated_resolver_require_write_success(mixed $result, string $operation): void
{
    if ($result === true || (is_array($result) && ($result['ok'] ?? false) === true)) {
        return;
    }
    throw new RuntimeException('generated DBAccess operation failed: ' . $operation . '.');
}

/** @return array<string,mixed> */
function app_sso_app_user_generated_resolver_result(bool $ok, string $status, string $appUserId, bool $created, array $profile, string $error): array
{
    return ['ok' => $ok, 'status' => $status, 'actor' => $ok ? ['app_user_id' => $appUserId] : [], 'app_user_id' => $appUserId, 'created' => $created, 'profile' => $profile, 'error' => $error];
}
