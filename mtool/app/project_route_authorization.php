<?php

declare(strict_types=1);

require_once __DIR__ . '/project_permission.php';

/**
 * @return array<string,array{
 *     capability:string,
 *     enforcement_status:string,
 *     audit_required:bool,
 *     notes:string
 * }>
 */
function app_project_route_authorization_contract(): array
{
    return [
        'project_detail' => [
            'capability' => 'project.read',
            'enforcement_status' => 'next',
            'audit_required' => false,
            'notes' => 'low-risk read route first candidate',
        ],
        'project_settings:GET' => [
            'capability' => 'project.read',
            'enforcement_status' => 'next',
            'audit_required' => false,
            'notes' => 'settings form display',
        ],
        'project_settings:POST' => [
            'capability' => 'project.edit',
            'enforcement_status' => 'next',
            'audit_required' => true,
            'notes' => 'project metadata mutation',
        ],
        'project_shared_contracts:GET' => [
            'capability' => 'project.read',
            'enforcement_status' => 'next',
            'audit_required' => false,
            'notes' => 'shared contract metadata display for no-code usage intent',
        ],
        'project_shared_contracts:POST' => [
            'capability' => 'project.edit',
            'enforcement_status' => 'next',
            'audit_required' => true,
            'notes' => 'contract-level usage intent mutation',
        ],
        'project_source_output_download' => [
            'capability' => 'source_output.download',
            'enforcement_status' => 'done',
            'audit_required' => true,
            'notes' => 'already enforced with audited permission decision',
        ],
        'project_source_output_artifact_detail' => [
            'capability' => 'source_output.download',
            'enforcement_status' => 'done',
            'audit_required' => true,
            'notes' => 'read-only artifact detail uses the same audited boundary as archive download',
        ],
        'project_sync_outbox_detail' => [
            'capability' => 'source_output.download',
            'enforcement_status' => 'done',
            'audit_required' => true,
            'notes' => 'read-only sync outbox item detail uses the same audited boundary as source output inspection',
        ],
        'project_sync_outbox_status_json' => [
            'capability' => 'source_output.download',
            'enforcement_status' => 'done',
            'audit_required' => true,
            'notes' => 'read-only sync outbox status JSON uses the same audited boundary as source output inspection',
        ],
        'project_source_output_new:GET' => [
            'capability' => 'project.read',
            'enforcement_status' => 'next',
            'audit_required' => false,
            'notes' => 'source output create form display',
        ],
        'project_source_output_new:POST' => [
            'capability' => 'source_output.publish',
            'enforcement_status' => 'next',
            'audit_required' => true,
            'notes' => 'source output creation can affect generated artifacts',
        ],
        'database_sources:GET' => [
            'capability' => 'db_source.manage',
            'enforcement_status' => 'next',
            'audit_required' => false,
            'notes' => 'global configuration route; read/write split can be relaxed later if needed',
        ],
        'database_sources:POST' => [
            'capability' => 'db_source.manage',
            'enforcement_status' => 'next',
            'audit_required' => true,
            'notes' => 'database source create/update/delete',
        ],
    ];
}

/**
 * @return array{
 *     ok:bool,
 *     route_name:string,
 *     method:string,
 *     capability:string,
 *     required_role:string,
 *     enforcement_status:string,
 *     audit_required:bool,
 *     notes:string,
 *     error:string
 * }
 */
function app_project_route_authorization_requirement(string $routeName, string $method = 'GET'): array
{
    $normalizedRouteName = trim($routeName);
    $normalizedMethod = strtoupper(trim($method));
    if ($normalizedMethod === '') {
        $normalizedMethod = 'GET';
    }

    $contract = app_project_route_authorization_contract();
    $key = $normalizedRouteName . ':' . $normalizedMethod;
    $item = $contract[$key] ?? ($contract[$normalizedRouteName] ?? null);
    if (!is_array($item)) {
        return [
            'ok' => false,
            'route_name' => $normalizedRouteName,
            'method' => $normalizedMethod,
            'capability' => '',
            'required_role' => '',
            'enforcement_status' => 'unknown',
            'audit_required' => false,
            'notes' => '',
            'error' => 'unknown route authorization requirement: ' . $normalizedRouteName,
        ];
    }

    $capability = (string) ($item['capability'] ?? '');
    $requiredRole = APP_PROJECT_PERMISSION_REQUIREMENTS[$capability] ?? '';
    if ($requiredRole === '') {
        return [
            'ok' => false,
            'route_name' => $normalizedRouteName,
            'method' => $normalizedMethod,
            'capability' => $capability,
            'required_role' => '',
            'enforcement_status' => (string) ($item['enforcement_status'] ?? 'unknown'),
            'audit_required' => (bool) ($item['audit_required'] ?? false),
            'notes' => (string) ($item['notes'] ?? ''),
            'error' => 'unknown project capability in route contract: ' . $capability,
        ];
    }

    return [
        'ok' => true,
        'route_name' => $normalizedRouteName,
        'method' => $normalizedMethod,
        'capability' => $capability,
        'required_role' => $requiredRole,
        'enforcement_status' => (string) ($item['enforcement_status'] ?? 'unknown'),
        'audit_required' => (bool) ($item['audit_required'] ?? false),
        'notes' => (string) ($item['notes'] ?? ''),
        'error' => '',
    ];
}
