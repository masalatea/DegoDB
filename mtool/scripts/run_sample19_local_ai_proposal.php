<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/schema_proposal_request.php';
require_once dirname(__DIR__) . '/app/schema_proposal_task.php';

const SAMPLE19_LOCAL_AI_MODEL = 'qwen2.5-coder:7b';
const SAMPLE19_LOCAL_AI_ENDPOINT = 'http://127.0.0.1:11434/api/generate';

$options = getopt('', ['task:', 'execute-local-fallback']);
$taskPath = (string) ($options['task'] ?? '');
if (!array_key_exists('execute-local-fallback', $options) || $taskPath === '' || !is_file($taskPath)) {
    fwrite(STDERR, "This optional local fallback never auto-runs.\nUsage: php mtool/scripts/run_sample19_local_ai_proposal.php --task=<task.json> --execute-local-fallback\n");
    exit(64);
}
$taskRoot = realpath(dirname($taskPath));
$taskReal = realpath($taskPath);
if (!is_string($taskRoot) || !is_string($taskReal) || !str_starts_with($taskReal, $taskRoot . DIRECTORY_SEPARATOR)) exit(65);
$task = json_decode((string) file_get_contents($taskReal), true, 512, JSON_THROW_ON_ERROR);
if (!is_array($task) || app_schema_proposal_task_contract_errors($task) !== []) exit(66);
$readInput = static function (string $key) use ($task, $taskRoot): string {
    $path = realpath($taskRoot . '/' . (string) ($task['inputs'][$key]['path'] ?? ''));
    if (!is_string($path) || !str_starts_with($path, $taskRoot . DIRECTORY_SEPARATOR)) throw new RuntimeException('invalid_task_input:' . $key);
    $bytes = (string) file_get_contents($path);
    if (!hash_equals((string) $task['inputs'][$key]['sha256'], hash('sha256', $bytes))) throw new RuntimeException('task_input_hash_mismatch:' . $key);
    return $bytes;
};
$sourceBytes = $readInput('source');
$canonicalBytes = $readInput('canonical');
$shapeBytes = $readInput('output_shape');
$root = dirname(__DIR__, 2);
$sample = $root . '/sample/tutorials/sample19-json-first-content-model-demo';
$templateBytes = (string) file_get_contents($sample . '/proposal/prompt/schema-proposal-v0.txt');
$request = app_schema_proposal_request_build($templateBytes, $shapeBytes, $sourceBytes, $canonicalBytes);
if (!$request['ok']) exit(67);
$generatedAt = gmdate('Y-m-d\TH:i:s\Z');
$prompt = (string) $request['envelope']['prompt']['content']
    . "\nFor this run set created_at exactly to \"{$generatedAt}\" and canonical_diff to an empty JSON list. Mtool derives the diff.\n";
$payload = json_encode(['model' => SAMPLE19_LOCAL_AI_MODEL, 'prompt' => $prompt, 'format' => 'json', 'stream' => false, 'options' => ['temperature' => 0, 'num_ctx' => 32768]], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
$context = stream_context_create(['http' => ['method' => 'POST', 'header' => "Content-Type: application/json\r\n", 'content' => $payload, 'timeout' => 600, 'ignore_errors' => true]]);
$apiBytes = @file_get_contents(SAMPLE19_LOCAL_AI_ENDPOINT, false, $context);
if (!is_string($apiBytes)) exit(68);
$api = json_decode($apiBytes, true, 512, JSON_THROW_ON_ERROR);
$candidateJson = is_string($api['response'] ?? null) ? $api['response'] : '';
if ($candidateJson === '') exit(69);
$candidatePath = $taskRoot . '/input/fallback-candidate.json';
file_put_contents($candidatePath, $candidateJson . (str_ends_with($candidateJson, "\n") ? '' : "\n"));
$result = app_schema_proposal_task_validate($task, $candidateJson, $sourceBytes, $canonicalBytes);
$validationPath = $taskRoot . '/input/fallback-validation.json';
file_put_contents($validationPath, json_encode(array_diff_key($result, ['review_artifact_json' => true, 'derived_diff' => true]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
echo json_encode(['ok' => $result['ok'], 'stage' => $result['stage'], 'errors' => $result['errors'], 'provider' => 'ollama-local', 'model' => SAMPLE19_LOCAL_AI_MODEL, 'advisory' => true, 'candidate_path' => $candidatePath, 'validation_path' => $validationPath, 'mutation_performed' => false], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
exit($result['ok'] ? 0 : 2);
