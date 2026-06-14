<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';

/**
 * @param array{
 *     auth_type_field_name?:string,
 *     single_get_field_name?:string
 * } $options
 * @return array{
 *     raw_auth_type:string,
 *     raw_auth_type_caption:string,
 *     resolved_auth_type:string,
 *     resolved_auth_type_caption:string,
 *     strategy_key:string,
 *     strategy_caption:string,
 *     resolution_source:string,
 *     requires_get_function:bool,
 *     single_get_function_name:string,
 *     security_mode:string,
 *     summary:string,
 *     notes:list<string>,
 *     is_valid:bool
 * }
 */
function app_resolve_proxy_auth_policy(
    string $rawAuthType,
    string $singleGetFunctionName,
    array $options = [],
): array {
    $rawAuthType = trim($rawAuthType);
    $singleGetFunctionName = trim($singleGetFunctionName);
    $authTypeFieldName = trim((string) ($options['auth_type_field_name'] ?? 'AuthType'));
    $singleGetFieldName = trim((string) ($options['single_get_field_name'] ?? 'SingleGetFunc 相当名'));
    if ($authTypeFieldName === '') {
        $authTypeFieldName = 'AuthType';
    }
    if ($singleGetFieldName === '') {
        $singleGetFieldName = 'SingleGetFunc 相当名';
    }

    $rawAuthTypeCaption = app_proxy_auth_type_caption($rawAuthType);
    $resolvedAuthType = $rawAuthType;
    $resolvedAuthTypeCaption = $rawAuthTypeCaption;
    $resolutionSource = 'explicit';
    $notes = [];

    if ($rawAuthType === '') {
        $resolvedAuthType = 'ProjectToken';
        $resolvedAuthTypeCaption = app_proxy_auth_type_caption($resolvedAuthType);
        $resolutionSource = 'legacy-default';
        $notes[] = $authTypeFieldName . ' が空欄のときは legacy 互換で ProjectToken として扱います。';
    } elseif (!in_array($rawAuthType, app_allowed_proxy_auth_types(), true)) {
        $resolutionSource = 'invalid';
        $notes[] = $authTypeFieldName . ' が許可値に含まれていません。';
    }

    $requiresGetFunction = $resolutionSource !== 'invalid'
        && app_proxy_auth_type_requires_get_function($resolvedAuthType);
    $strategyKey = 'invalid';
    $strategyCaption = 'Invalid';
    $securityMode = 'invalid';
    $summary = '未知の auth type です。';

    if ($resolutionSource !== 'invalid') {
        switch ($resolvedAuthType) {
            case 'ProjectToken':
                $strategyKey = 'project-token';
                $strategyCaption = 'Project Token';
                $securityMode = 'project-token';
                $summary = $resolutionSource === 'legacy-default'
                    ? $authTypeFieldName . ' が未指定なので、legacy 互換で project token 認証として扱います。'
                    : 'project token 認証です。';
                break;
            case 'GetFunc':
                $strategyKey = 'get-function';
                $strategyCaption = 'Get Function';
                $securityMode = 'get-function';
                $summary = $singleGetFunctionName === ''
                    ? 'get function 認証ですが、参照 function 名が未設定です。'
                    : 'get function 認証です。';
                break;
            case 'ProjectTokenOrGetFunc':
                $strategyKey = 'project-token-or-get-function';
                $strategyCaption = 'Project Token or Get Function';
                $securityMode = 'project-token-or-get-function';
                $summary = $singleGetFunctionName === ''
                    ? 'project token と get function の併用設定ですが、参照 function 名が未設定です。'
                    : 'project token を優先し、必要時は get function も使う設定です。';
                break;
            case 'NoSecurity':
                $strategyKey = 'no-security';
                $strategyCaption = 'No Security';
                $securityMode = 'no-security';
                $summary = '認証を掛けません。';
                break;
            case 'Manual':
                $strategyKey = 'manual';
                $strategyCaption = 'Manual';
                $securityMode = 'manual';
                $summary = '認証処理は手動実装前提です。';
                break;
            case 'LoginCookieToken':
                $strategyKey = 'login-cookie-token';
                $strategyCaption = 'Login Cookie Token';
                $securityMode = 'login-cookie-token';
                $summary = 'login cookie token 認証です。';
                break;
        }
    }

    if ($requiresGetFunction && $singleGetFunctionName === '') {
        $notes[] = 'GetFunc 系のため、' . $singleGetFieldName . ' が必要です。';
    }

    if (!$requiresGetFunction && $singleGetFunctionName !== '') {
        $notes[] = 'この auth type では ' . $singleGetFieldName . ' は使いません。';
    }

    return [
        'raw_auth_type' => $rawAuthType,
        'raw_auth_type_caption' => $rawAuthTypeCaption,
        'resolved_auth_type' => $resolvedAuthType,
        'resolved_auth_type_caption' => $resolvedAuthTypeCaption,
        'strategy_key' => $strategyKey,
        'strategy_caption' => $strategyCaption,
        'resolution_source' => $resolutionSource,
        'requires_get_function' => $requiresGetFunction,
        'single_get_function_name' => $singleGetFunctionName,
        'security_mode' => $securityMode,
        'summary' => $summary,
        'notes' => $notes,
        'is_valid' => $resolutionSource !== 'invalid'
            && (!$requiresGetFunction || $singleGetFunctionName !== ''),
    ];
}

/**
 * @return array{
 *     raw_auth_type:string,
 *     raw_auth_type_caption:string,
 *     resolved_auth_type:string,
 *     resolved_auth_type_caption:string,
 *     strategy_key:string,
 *     strategy_caption:string,
 *     resolution_source:string,
 *     requires_get_function:bool,
 *     single_get_function_name:string,
 *     security_mode:string,
 *     summary:string,
 *     notes:list<string>,
 *     is_valid:bool
 * }
 */
function app_resolve_db_access_single_proxy_auth_policy(
    string $rawAuthType,
    string $singleGetFunctionName,
): array {
    return app_resolve_proxy_auth_policy($rawAuthType, $singleGetFunctionName, [
        'auth_type_field_name' => 'SingleProxy_AuthType',
        'single_get_field_name' => 'SingleProxy_SingleGetFuncPID 相当名',
    ]);
}

/**
 * @return array{
 *     raw_auth_type:string,
 *     raw_auth_type_caption:string,
 *     resolved_auth_type:string,
 *     resolved_auth_type_caption:string,
 *     strategy_key:string,
 *     strategy_caption:string,
 *     resolution_source:string,
 *     requires_get_function:bool,
 *     single_get_function_name:string,
 *     security_mode:string,
 *     summary:string,
 *     notes:list<string>,
 *     is_valid:bool
 * }
 */
function app_resolve_custom_proxy_auth_policy(
    string $rawAuthType,
    string $singleGetFunctionName,
): array {
    return app_resolve_proxy_auth_policy($rawAuthType, $singleGetFunctionName, [
        'auth_type_field_name' => 'AuthType',
        'single_get_field_name' => 'SingleGetFunc 相当名',
    ]);
}
