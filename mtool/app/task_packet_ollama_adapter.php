<?php

declare(strict_types=1);

const APP_TASK_PACKET_OLLAMA_ADAPTER_VERSION = 'mtool-local-ollama-adapter-v1';
const APP_TASK_PACKET_OLLAMA_DEFAULT_ENDPOINT = 'http://127.0.0.1:11434/api/generate';
const APP_TASK_PACKET_OLLAMA_DEFAULT_MODEL = 'qwen2.5-coder:7b';
const APP_TASK_PACKET_OLLAMA_DEFAULT_TIMEOUT_SECONDS = 600;
const APP_TASK_PACKET_OLLAMA_DEFAULT_NUM_CTX = 32768;
const APP_TASK_PACKET_OLLAMA_DEFAULT_TEMPERATURE = 0.0;

/**
 * @param array<string,mixed> $input
 * @return array<string,mixed>
 */
function app_task_packet_ollama_config_normalize(array $input = []): array
{
    $endpoint = (string) ($input['endpoint'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_ENDPOINT);
    $model = (string) ($input['model'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_MODEL);
    $timeout = app_task_packet_ollama_int_option($input['timeout_seconds'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_TIMEOUT_SECONDS);
    $numCtx = app_task_packet_ollama_int_option($input['num_ctx'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_NUM_CTX);
    $temperature = app_task_packet_ollama_float_option($input['temperature'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_TEMPERATURE);

    $errors = [];
    if ($model === '') $errors[] = 'model_required';
    if ($timeout === null || $timeout < 1 || $timeout > 3600) $errors[] = 'invalid_timeout_seconds';
    if ($numCtx === null || $numCtx < 1024 || $numCtx > 262144) $errors[] = 'invalid_num_ctx';
    if ($temperature === null || $temperature < 0 || $temperature > 2) $errors[] = 'invalid_temperature';
    foreach (['api_key', 'token', 'authorization', 'headers', 'credential', 'credentials'] as $credentialKey) {
        if (array_key_exists($credentialKey, $input)) $errors[] = 'credential_not_supported:' . $credentialKey;
    }
    foreach (app_task_packet_ollama_local_endpoint_errors($endpoint) as $error) $errors[] = $error;

    return [
        'ok' => $errors === [],
        'stage' => $errors === [] ? 'config_ready' : 'config_validation',
        'errors' => array_values(array_unique($errors)),
        'config' => [
            'adapter_version' => APP_TASK_PACKET_OLLAMA_ADAPTER_VERSION,
            'endpoint' => $endpoint,
            'model' => $model,
            'timeout_seconds' => $timeout ?? APP_TASK_PACKET_OLLAMA_DEFAULT_TIMEOUT_SECONDS,
            'num_ctx' => $numCtx ?? APP_TASK_PACKET_OLLAMA_DEFAULT_NUM_CTX,
            'temperature' => $temperature ?? APP_TASK_PACKET_OLLAMA_DEFAULT_TEMPERATURE,
            'local_only' => true,
            'credential_required' => false,
        ],
    ];
}

/**
 * @param array<string,mixed> $configInput
 * @param null|callable(string,string,int):string $transport
 * @return array<string,mixed>
 */
function app_task_packet_ollama_generate_candidate(string $prompt, array $configInput = [], ?callable $transport = null): array
{
    if ($prompt === '') return app_task_packet_ollama_result(false, 'prompt_validation', ['prompt_required']);
    $normalized = app_task_packet_ollama_config_normalize($configInput);
    if (!$normalized['ok']) return app_task_packet_ollama_result(false, (string) $normalized['stage'], $normalized['errors'], $normalized['config']);

    $config = $normalized['config'];
    $payload = app_task_packet_ollama_request_payload($prompt, $config);
    try {
        $apiBytes = ($transport ?? 'app_task_packet_ollama_http_post')((string) $config['endpoint'], $payload, (int) $config['timeout_seconds']);
        $api = json_decode($apiBytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (Throwable $throwable) {
        return app_task_packet_ollama_result(false, 'provider_request', ['ollama_request_failed:' . $throwable->getMessage()], $config);
    }

    $candidateJson = is_array($api) && is_string($api['response'] ?? null) ? trim($api['response']) : '';
    if ($candidateJson === '') return app_task_packet_ollama_result(false, 'provider_response', ['ollama_empty_response'], $config);

    return array_merge(app_task_packet_ollama_result(true, 'candidate_ready', [], $config), [
        'candidate_json' => $candidateJson,
        'provider' => 'ollama-local',
        'model' => (string) $config['model'],
        'request_sha256' => hash('sha256', $payload),
        'candidate_sha256' => hash('sha256', $candidateJson),
    ]);
}

/**
 * @param array<string,mixed> $config
 */
function app_task_packet_ollama_request_payload(string $prompt, array $config): string
{
    return json_encode([
        'model' => (string) $config['model'],
        'prompt' => $prompt,
        'format' => 'json',
        'stream' => false,
        'options' => [
            'temperature' => $config['temperature'],
            'num_ctx' => $config['num_ctx'],
        ],
    ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
}

function app_task_packet_ollama_http_post(string $endpoint, string $payload, int $timeoutSeconds): string
{
    $context = stream_context_create(['http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => $timeoutSeconds,
        'ignore_errors' => true,
    ]]);
    $apiBytes = @file_get_contents($endpoint, false, $context);
    if (!is_string($apiBytes)) throw new RuntimeException('http_post_failed');
    return $apiBytes;
}

/** @return list<string> */
function app_task_packet_ollama_local_endpoint_errors(string $endpoint): array
{
    if ($endpoint === '') return ['endpoint_required'];
    $parts = parse_url($endpoint);
    if (!is_array($parts)) return ['invalid_endpoint'];
    $errors = [];
    if (($parts['scheme'] ?? '') !== 'http') $errors[] = 'endpoint_must_use_http';
    if (array_key_exists('user', $parts) || array_key_exists('pass', $parts)) $errors[] = 'endpoint_must_not_embed_credentials';
    $host = strtolower(trim((string) ($parts['host'] ?? ''), '[]'));
    if (!in_array($host, ['127.0.0.1', 'localhost', '::1'], true)) $errors[] = 'endpoint_must_be_local';
    return array_values(array_unique($errors));
}

function app_task_packet_ollama_int_option(mixed $value): ?int
{
    if (is_int($value)) return $value;
    if (is_string($value) && preg_match('/^-?\d+$/', $value) === 1) return (int) $value;
    return null;
}

function app_task_packet_ollama_float_option(mixed $value): ?float
{
    if (is_int($value) || is_float($value)) return (float) $value;
    if (is_string($value) && is_numeric($value)) return (float) $value;
    return null;
}

/**
 * @param list<string> $errors
 * @param array<string,mixed> $config
 * @return array<string,mixed>
 */
function app_task_packet_ollama_result(bool $ok, string $stage, array $errors, array $config = []): array
{
    return [
        'ok' => $ok,
        'stage' => $stage,
        'errors' => array_values(array_unique($errors)),
        'candidate_json' => '',
        'provider' => '',
        'model' => '',
        'request_sha256' => '',
        'candidate_sha256' => '',
        'config' => $config,
        'mutation_performed' => false,
    ];
}
