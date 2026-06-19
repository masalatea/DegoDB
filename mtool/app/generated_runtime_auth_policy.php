<?php

declare(strict_types=1);

/**
 * @return list<string>
 */
function app_generated_runtime_auth_policy_contract_types(): array
{
    return [
        'static-bearer',
        'oidc-jwt-bearer',
    ];
}

/**
 * @param array{
 *     allowed_policy_types?:list<string>
 * } $options
 * @return array{
 *     ok:bool,
 *     is_valid:bool,
 *     type:string,
 *     strategy_key:string,
 *     security_mode:string,
 *     implementation_status:string,
 *     summary:string,
 *     notes:list<string>,
 *     secret_refs:array<string,string>,
 *     policy:array<string,mixed>,
 *     json:string
 * }
 */
function app_generated_runtime_auth_policy_validate_json(
    int $authPolicyVersion,
    string $authPolicyJson,
    array $options = [],
): array {
    $trimmedJson = trim($authPolicyJson);
    if ($authPolicyVersion <= 1) {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            'auth policy contract は version 2 以降でのみ有効です。',
        );
    }

    if ($trimmedJson === '') {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            'auth_policy_json が空です。v2 auth policy は明示的な JSON contract が必要です。',
        );
    }

    try {
        $decoded = json_decode($trimmedJson, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            'auth_policy_json が JSON として解釈できません: ' . $exception->getMessage(),
        );
    }

    if (!is_array($decoded)) {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            'auth_policy_json は object である必要があります。',
        );
    }

    $secretValueField = app_generated_runtime_auth_policy_secret_value_field($decoded);
    if ($secretValueField !== '') {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            'auth_policy_json に secret 値を保存できません。参照名だけを保存してください: ' . $secretValueField,
            $decoded,
        );
    }

    $policyType = trim((string) ($decoded['type'] ?? ''));
    if ($policyType === '') {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            'auth_policy_json.type が空です。',
            $decoded,
        );
    }

    $allowedPolicyTypes = $options['allowed_policy_types'] ?? app_generated_runtime_auth_policy_contract_types();
    if (!in_array($policyType, $allowedPolicyTypes, true)) {
        return app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            '未知の auth_policy_json.type です: ' . $policyType,
            $decoded,
            $policyType,
        );
    }

    return match ($policyType) {
        'static-bearer' => app_generated_runtime_auth_policy_validate_static_bearer($trimmedJson, $decoded),
        'oidc-jwt-bearer' => app_generated_runtime_auth_policy_validate_oidc_jwt_bearer($trimmedJson, $decoded),
        default => app_generated_runtime_auth_policy_invalid(
            $trimmedJson,
            '未実装の auth_policy_json.type です: ' . $policyType,
            $decoded,
            $policyType,
        ),
    };
}

/**
 * @param array<string,mixed> $policy
 * @return array{
 *     ok:bool,
 *     is_valid:bool,
 *     type:string,
 *     strategy_key:string,
 *     security_mode:string,
 *     implementation_status:string,
 *     summary:string,
 *     notes:list<string>,
 *     secret_refs:array<string,string>,
 *     policy:array<string,mixed>,
 *     json:string
 * }
 */
function app_generated_runtime_auth_policy_validate_static_bearer(string $json, array $policy): array
{
    $secretEnv = trim((string) ($policy['secret_env'] ?? ''));
    if ($secretEnv === '') {
        return app_generated_runtime_auth_policy_invalid(
            $json,
            'static-bearer policy には secret_env 参照が必要です。secret 値そのものは保存しません。',
            $policy,
            'static-bearer',
        );
    }

    return [
        'ok' => true,
        'is_valid' => true,
        'type' => 'static-bearer',
        'strategy_key' => 'static-bearer',
        'security_mode' => 'static-bearer',
        'implementation_status' => 'implemented',
        'summary' => 'auth policy v2 の static-bearer 認証です。',
        'notes' => [],
        'secret_refs' => [
            'secret_env' => $secretEnv,
        ],
        'policy' => $policy,
        'json' => $json,
    ];
}

/**
 * @param array<string,mixed> $policy
 * @return array{
 *     ok:bool,
 *     is_valid:bool,
 *     type:string,
 *     strategy_key:string,
 *     security_mode:string,
 *     implementation_status:string,
 *     summary:string,
 *     notes:list<string>,
 *     secret_refs:array<string,string>,
 *     policy:array<string,mixed>,
 *     json:string
 * }
 */
function app_generated_runtime_auth_policy_validate_oidc_jwt_bearer(string $json, array $policy): array
{
    $issuer = trim((string) ($policy['issuer'] ?? ''));
    $audience = trim((string) ($policy['audience'] ?? ''));
    $discoveryUrl = trim((string) ($policy['discovery_url'] ?? ''));
    $jwksUri = trim((string) ($policy['jwks_uri'] ?? ''));
    $jwksJsonEnv = trim((string) ($policy['jwks_json_env'] ?? ''));

    if ($issuer === '') {
        return app_generated_runtime_auth_policy_invalid($json, 'oidc-jwt-bearer policy には issuer が必要です。', $policy, 'oidc-jwt-bearer');
    }
    if ($audience === '') {
        return app_generated_runtime_auth_policy_invalid($json, 'oidc-jwt-bearer policy には audience が必要です。', $policy, 'oidc-jwt-bearer');
    }
    if ($discoveryUrl === '' && $jwksUri === '' && $jwksJsonEnv === '') {
        return app_generated_runtime_auth_policy_invalid($json, 'oidc-jwt-bearer policy には discovery_url、jwks_uri、jwks_json_env のいずれかが必要です。', $policy, 'oidc-jwt-bearer');
    }
    if (isset($policy['required_claims']) && !is_array($policy['required_claims'])) {
        return app_generated_runtime_auth_policy_invalid($json, 'oidc-jwt-bearer required_claims は object である必要があります。', $policy, 'oidc-jwt-bearer');
    }
    if ($jwksJsonEnv !== '' && preg_match('/^[A-Z_][A-Z0-9_]*$/', $jwksJsonEnv) !== 1) {
        return app_generated_runtime_auth_policy_invalid($json, 'oidc-jwt-bearer jwks_json_env は env 名である必要があります。', $policy, 'oidc-jwt-bearer');
    }

    return [
        'ok' => true,
        'is_valid' => true,
        'type' => 'oidc-jwt-bearer',
        'strategy_key' => 'oidc-jwt-bearer',
        'security_mode' => 'jwt-bearer',
        'implementation_status' => 'implemented',
        'summary' => 'auth policy v2 の OIDC JWT bearer 認証です。',
        'notes' => [],
        'secret_refs' => [],
        'policy' => $policy,
        'json' => $json,
    ];
}

/**
 * @param array<string,mixed> $policy
 */
function app_generated_runtime_auth_policy_secret_value_field(array $policy, string $prefix = ''): string
{
    foreach ($policy as $key => $value) {
        $stringKey = (string) $key;
        $path = $prefix === '' ? $stringKey : $prefix . '.' . $stringKey;
        $normalizedKey = strtolower(trim($stringKey));

        if ($normalizedKey !== 'secret_env'
            && preg_match('/(^|_)(password|passwd|secret|token|credential)($|_)/', $normalizedKey) === 1
        ) {
            return $path;
        }

        if (is_array($value)) {
            $nested = app_generated_runtime_auth_policy_secret_value_field($value, $path);
            if ($nested !== '') {
                return $nested;
            }
        }
    }

    return '';
}

/**
 * @param array<string,mixed> $policy
 * @return array{
 *     ok:bool,
 *     is_valid:bool,
 *     type:string,
 *     strategy_key:string,
 *     security_mode:string,
 *     implementation_status:string,
 *     summary:string,
 *     notes:list<string>,
 *     secret_refs:array<string,string>,
 *     policy:array<string,mixed>,
 *     json:string
 * }
 */
function app_generated_runtime_auth_policy_invalid(
    string $json,
    string $note,
    array $policy = [],
    string $type = '',
): array {
    return [
        'ok' => false,
        'is_valid' => false,
        'type' => $type,
        'strategy_key' => 'invalid',
        'security_mode' => 'invalid',
        'implementation_status' => 'invalid',
        'summary' => 'auth policy v2 contract が無効です。',
        'notes' => [$note],
        'secret_refs' => [],
        'policy' => $policy,
        'json' => $json,
    ];
}
