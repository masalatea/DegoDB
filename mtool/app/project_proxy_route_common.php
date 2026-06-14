<?php

declare(strict_types=1);

require_once __DIR__ . '/domain_validation.php';

/**
 * @param mixed $rawBridgeErrors
 * @return list<string>
 */
function app_project_proxy_normalize_bridge_errors($rawBridgeErrors): array
{
    if (is_string($rawBridgeErrors) || is_numeric($rawBridgeErrors)) {
        $rawBridgeErrors = [$rawBridgeErrors];
    }

    if (!is_array($rawBridgeErrors)) {
        return [];
    }

    $bridgeErrors = [];
    foreach ($rawBridgeErrors as $rawBridgeError) {
        if (!is_string($rawBridgeError) && !is_numeric($rawBridgeError)) {
            continue;
        }

        $bridgeError = trim((string) $rawBridgeError);
        if ($bridgeError === '') {
            continue;
        }

        $bridgeErrors[$bridgeError] = $bridgeError;
    }

    return array_values($bridgeErrors);
}

/**
 * @return list<string>
 */
function app_project_proxy_bridge_errors_from_post(): array
{
    return app_project_proxy_normalize_bridge_errors($_POST['bridge_errors'] ?? null);
}

/**
 * @return list<string>
 */
function app_project_proxy_bridge_errors_from_request(): array
{
    $bridgeErrors = [];
    foreach ([
        $_POST['bridge_errors'] ?? null,
        $_GET['bridge_errors'] ?? null,
        $_GET['bridge_error'] ?? null,
    ] as $rawBridgeErrors) {
        foreach (app_project_proxy_normalize_bridge_errors($rawBridgeErrors) as $bridgeError) {
            $bridgeErrors[$bridgeError] = $bridgeError;
        }
    }

    return array_values($bridgeErrors);
}

function app_project_proxy_route_with_query(string $path, array $query = []): string
{
    $normalizedQuery = [];
    foreach ($query as $key => $value) {
        if (!is_string($key) || !is_scalar($value)) {
            continue;
        }

        $stringValue = trim((string) $value);
        if ($stringValue === '') {
            continue;
        }

        $normalizedQuery[$key] = $stringValue;
    }

    if ($normalizedQuery === []) {
        return $path;
    }

    return $path . '?' . http_build_query($normalizedQuery, '', '&', PHP_QUERY_RFC3986);
}

function app_project_single_proxy_path(string $projectKey, array $query = []): string
{
    return app_project_proxy_route_with_query(
        '/projects/' . rawurlencode(app_normalize_project_key($projectKey)) . '/proxy/single',
        $query,
    );
}

function app_project_custom_proxy_endpoint_path(
    string $projectKey,
    string $customProxyKey,
    array $query = [],
): string {
    return app_project_proxy_route_with_query(
        '/projects/' . rawurlencode(app_normalize_project_key($projectKey))
        . '/proxy/custom/'
        . rawurlencode(app_normalize_custom_proxy_key($customProxyKey))
        . '/endpoint',
        $query,
    );
}
