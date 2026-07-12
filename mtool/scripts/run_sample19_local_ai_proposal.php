<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/schema_proposal_request.php';
require_once dirname(__DIR__) . '/app/schema_proposal_response.php';

const SAMPLE19_LOCAL_AI_MODEL = 'qwen2.5-coder:7b';
const SAMPLE19_LOCAL_AI_ENDPOINT = 'http://127.0.0.1:11434/api/generate';

$root = dirname(__DIR__, 2);
$sampleRoot = $root . '/sample/tutorials/sample19-json-first-content-model-demo';
$retryPath = $argv[1] ?? '';
$attemptNumber = $retryPath === '' ? 1 : 2;
$outputPath = $argv[2] ?? ($root . '/work/tmp/sample19-local-ai-proposal-v1-attempt-' . $attemptNumber . '.json');
$templateBytes = (string) file_get_contents($sampleRoot . '/proposal/prompt/schema-proposal-v0.txt');
$shapeBytes = (string) file_get_contents($sampleRoot . '/proposal/prompt/schema-proposal-v1-shape.json');
$sourceBytes = (string) file_get_contents($sampleRoot . '/proposal/source/article.json');
$canonicalBytes = (string) file_get_contents($sampleRoot . '/golden/canonical-schema-snapshot.json');
$request = app_schema_proposal_request_build($templateBytes, $shapeBytes, $sourceBytes, $canonicalBytes);
if (!$request['ok']) {
    fwrite(STDERR, json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n");
    exit(2);
}

$generatedAt = gmdate('Y-m-d\TH:i:s\Z');
$prompt = (string) $request['envelope']['prompt']['content']
    . "\nFor this run, set top-level created_at exactly to \"{$generatedAt}\".\n";
if ($attemptNumber === 2) {
    $previousBytes = @file_get_contents($retryPath);
    $previous = is_string($previousBytes) ? json_decode($previousBytes, true) : null;
    $previousResponse = is_array($previous) ? (string) ($previous['response_bytes'] ?? '') : '';
    $previousErrors = is_array($previous) && is_array($previous['acceptance']['errors'] ?? null)
        ? $previous['acceptance']['errors']
        : [];
    if ($previousResponse === '' || $previousErrors === []) {
        fwrite(STDERR, "invalid_retry_bundle\n");
        exit(2);
    }
    $prompt .= "\nThis is the one permitted corrective retry. The previous response was rejected."
        . " Return a complete replacement JSON object, not a patch. Preserve the safe proposal-only boundary."
        . " Correct every validator error below without copying canonical values blindly and ensure every required"
        . " entity, field, relationship, lifecycle, and diff evidence entry has a /article JSON Pointer.\n"
        . "VALIDATOR ERRORS:\n" . json_encode($previousErrors, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
        . "\nPREVIOUS REJECTED RESPONSE:\n" . $previousResponse . "\n";
}
$payload = json_encode([
    'model' => SAMPLE19_LOCAL_AI_MODEL,
    'prompt' => $prompt,
    'format' => 'json',
    'stream' => false,
    'options' => ['temperature' => 0, 'num_ctx' => 32768],
], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
$context = stream_context_create([
    'http' => [
        'method' => 'POST',
        'header' => "Content-Type: application/json\r\n",
        'content' => $payload,
        'timeout' => 600,
        'ignore_errors' => true,
    ],
]);
$apiBytes = @file_get_contents(SAMPLE19_LOCAL_AI_ENDPOINT, false, $context);
if (!is_string($apiBytes)) {
    fwrite(STDERR, "local_ollama_request_failed\n");
    exit(3);
}
$api = json_decode($apiBytes, true, 512, JSON_THROW_ON_ERROR);
$responseBytes = is_string($api['response'] ?? null) ? $api['response'] : '';
if ($responseBytes === '') {
    fwrite(STDERR, "local_ollama_empty_response\n");
    exit(4);
}

$run = [
    'provider' => 'ollama-local',
    'model' => SAMPLE19_LOCAL_AI_MODEL,
    'prompt_template_sha256' => hash('sha256', $templateBytes),
    'prompt_final_sha256' => hash('sha256', $prompt),
    'attempt_number' => $attemptNumber,
    'generated_at' => $generatedAt,
    'provider_request_id' => '',
];
$acceptance = app_schema_proposal_response_accept($responseBytes, $run, $sourceBytes, $canonicalBytes);
$bundle = [
    'bundle_version' => 'sample19-local-ai-proof-v0',
    'request' => [
        'provider' => 'ollama-local',
        'endpoint' => SAMPLE19_LOCAL_AI_ENDPOINT,
        'model' => SAMPLE19_LOCAL_AI_MODEL,
        'generated_at' => $generatedAt,
        'prompt_template_sha256' => $run['prompt_template_sha256'],
        'prompt_final_sha256' => $run['prompt_final_sha256'],
        'source_sha256' => hash('sha256', $sourceBytes),
        'canonical_sha256' => hash('sha256', $canonicalBytes),
        'external_transmission' => false,
        'credential_used' => false,
    ],
    'provider_result' => [
        'done' => ($api['done'] ?? false) === true,
        'done_reason' => (string) ($api['done_reason'] ?? ''),
        'total_duration' => $api['total_duration'] ?? null,
        'eval_count' => $api['eval_count'] ?? null,
    ],
    'acceptance' => $acceptance,
    'response_bytes' => $responseBytes,
];
$outputDir = dirname($outputPath);
if (!is_dir($outputDir) && !mkdir($outputDir, 0775, true) && !is_dir($outputDir)) {
    fwrite(STDERR, "output_directory_create_failed\n");
    exit(5);
}
file_put_contents($outputPath, json_encode($bundle, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");

echo json_encode([
    'ok' => $acceptance['ok'],
    'status' => $acceptance['status'],
    'errors' => $acceptance['errors'],
    'attempt' => $acceptance['attempt'],
    'provider_result' => $bundle['provider_result'],
    'output_path' => $outputPath,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
exit($acceptance['ok'] ? 0 : 6);
