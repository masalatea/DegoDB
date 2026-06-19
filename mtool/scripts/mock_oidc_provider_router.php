<?php

declare(strict_types=1);

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

use Firebase\JWT\JWT;

function mock_oidc_base64url(string $value): string
{
    return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
}

function mock_oidc_json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}

function mock_oidc_private_key_path(): string
{
    $path = getenv('MOCK_OIDC_PRIVATE_KEY_PATH') ?: '';
    if ($path === '' || !is_file($path)) {
        throw new RuntimeException('MOCK_OIDC_PRIVATE_KEY_PATH is missing.');
    }

    return $path;
}

function mock_oidc_public_jwk(): array
{
    $publicKey = file_get_contents(getenv('MOCK_OIDC_PUBLIC_KEY_PATH') ?: '');
    if (!is_string($publicKey) || $publicKey === '') {
        throw new RuntimeException('MOCK_OIDC_PUBLIC_KEY_PATH is missing.');
    }

    $resource = openssl_pkey_get_public($publicKey);
    if ($resource === false) {
        throw new RuntimeException('mock public key is invalid.');
    }

    $details = openssl_pkey_get_details($resource);
    if (!is_array($details) || !isset($details['rsa']['n'], $details['rsa']['e'])) {
        throw new RuntimeException('mock public key details are invalid.');
    }

    return [
        'kty' => 'RSA',
        'use' => 'sig',
        'kid' => getenv('MOCK_OIDC_KEY_ID') ?: 'mock-key',
        'alg' => 'RS256',
        'n' => mock_oidc_base64url($details['rsa']['n']),
        'e' => mock_oidc_base64url($details['rsa']['e']),
    ];
}

function mock_oidc_claim_groups(): array
{
    $raw = getenv('MOCK_OIDC_GROUPS') ?: 'dego-config,dego:project:CLAIM-FIRST:publisher';
    $groups = [];
    foreach (explode(',', $raw) as $group) {
        $group = trim($group);
        if ($group !== '') {
            $groups[] = $group;
        }
    }

    return array_values(array_unique($groups));
}

function mock_oidc_authorize(): void
{
    $redirectUri = trim((string) ($_GET['redirect_uri'] ?? ''));
    $state = trim((string) ($_GET['state'] ?? ''));
    $nonce = trim((string) ($_GET['nonce'] ?? ''));
    $clientId = trim((string) ($_GET['client_id'] ?? ''));
    if ($redirectUri === '' || $state === '' || $nonce === '' || $clientId === '') {
        mock_oidc_json_response(['error' => 'invalid_request'], 400);
        return;
    }

    $codePayload = mock_oidc_base64url(json_encode([
        'nonce' => $nonce,
        'client_id' => $clientId,
    ], JSON_THROW_ON_ERROR));
    $location = $redirectUri
        . (str_contains($redirectUri, '?') ? '&' : '?')
        . http_build_query([
            'code' => 'mock.' . $codePayload,
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);

    header('Location: ' . $location, true, 302);
}

function mock_oidc_token(): void
{
    $code = trim((string) ($_POST['code'] ?? ''));
    $clientId = trim((string) ($_POST['client_id'] ?? ''));
    if (!str_starts_with($code, 'mock.') || $clientId === '') {
        mock_oidc_json_response(['error' => 'invalid_grant'], 400);
        return;
    }

    $json = base64_decode(strtr(substr($code, strlen('mock.')), '-_', '+/'), true);
    $codePayload = is_string($json) ? json_decode($json, true) : null;
    if (!is_array($codePayload) || (string) ($codePayload['client_id'] ?? '') !== $clientId) {
        mock_oidc_json_response(['error' => 'invalid_grant'], 400);
        return;
    }

    $now = time();
    $issuer = rtrim(getenv('MOCK_OIDC_ISSUER') ?: '', '/');
    $claims = [
        'iss' => $issuer,
        'sub' => getenv('MOCK_OIDC_SUBJECT') ?: 'oidc-smoke-user',
        'aud' => $clientId,
        'iat' => $now,
        'exp' => $now + 300,
        'nonce' => (string) ($codePayload['nonce'] ?? ''),
        'name' => getenv('MOCK_OIDC_NAME') ?: 'OIDC Smoke User',
        'groups' => mock_oidc_claim_groups(),
    ];

    $privateKey = file_get_contents(mock_oidc_private_key_path());
    if (!is_string($privateKey) || $privateKey === '') {
        throw new RuntimeException('mock private key is empty.');
    }

    mock_oidc_json_response([
        'access_token' => 'mock-access-token',
        'token_type' => 'Bearer',
        'expires_in' => 300,
        'id_token' => JWT::encode($claims, $privateKey, 'RS256', getenv('MOCK_OIDC_KEY_ID') ?: 'mock-key'),
    ]);
}

try {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $issuer = rtrim(getenv('MOCK_OIDC_ISSUER') ?: '', '/');

    if ($path === '/.well-known/openid-configuration') {
        mock_oidc_json_response([
            'issuer' => $issuer,
            'authorization_endpoint' => $issuer . '/authorize',
            'token_endpoint' => $issuer . '/token',
            'jwks_uri' => $issuer . '/jwks',
        ]);
        return;
    }

    if ($path === '/authorize') {
        mock_oidc_authorize();
        return;
    }

    if ($path === '/token') {
        mock_oidc_token();
        return;
    }

    if ($path === '/jwks') {
        mock_oidc_json_response(['keys' => [mock_oidc_public_jwk()]]);
        return;
    }

    mock_oidc_json_response(['error' => 'not_found'], 404);
} catch (Throwable $throwable) {
    mock_oidc_json_response(['error' => $throwable->getMessage()], 500);
}
