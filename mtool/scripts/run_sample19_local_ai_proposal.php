<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/schema_proposal_request.php';
require_once dirname(__DIR__) . '/app/task_packet_ollama_adapter.php';
require_once dirname(__DIR__) . '/app/task_packet_local_fallback.php';

$options = getopt('', ['task:', 'execute-local-fallback', 'ollama-endpoint:', 'ollama-model:', 'timeout-seconds:', 'num-ctx:', 'temperature:']);
$taskPath = (string) ($options['task'] ?? '');
if (!array_key_exists('execute-local-fallback', $options) || $taskPath === '' || !is_file($taskPath)) {
    fwrite(STDERR, "This optional local fallback never auto-runs.\nUsage: php mtool/scripts/run_sample19_local_ai_proposal.php --task=<task.json> --execute-local-fallback [--ollama-endpoint=http://127.0.0.1:11434/api/generate] [--ollama-model=qwen2.5-coder:7b]\n");
    exit(64);
}
$ollamaConfig = [
    'endpoint' => (string) ($options['ollama-endpoint'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_ENDPOINT),
    'model' => (string) ($options['ollama-model'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_MODEL),
    'timeout_seconds' => (string) ($options['timeout-seconds'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_TIMEOUT_SECONDS),
    'num_ctx' => (string) ($options['num-ctx'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_NUM_CTX),
    'temperature' => (string) ($options['temperature'] ?? APP_TASK_PACKET_OLLAMA_DEFAULT_TEMPERATURE),
];
$result = app_task_packet_local_fallback_run(
    $taskPath,
    static function (array $task, array $inputs) use ($ollamaConfig): array {
        $root = dirname(__DIR__, 2);
        $sample = $root . '/sample/tutorials/sample19-json-first-content-model-demo';
        $templateBytes = (string) file_get_contents($sample . '/proposal/prompt/schema-proposal-v0.txt');
        $request = app_schema_proposal_request_build($templateBytes, (string) $inputs['output_shape'], (string) $inputs['source'], (string) $inputs['canonical']);
        if (!$request['ok']) throw new RuntimeException('schema_proposal_request_failed');
        $generatedAt = gmdate('Y-m-d\TH:i:s\Z');
        $prompt = (string) $request['envelope']['prompt']['content']
            . "\nFor this run set created_at exactly to \"{$generatedAt}\" and canonical_diff to an empty JSON list. Mtool derives the diff.\n";
        $candidate = app_task_packet_ollama_generate_candidate($prompt, $ollamaConfig);
        if (!$candidate['ok']) throw new RuntimeException((string) $candidate['stage'] . ':' . implode(',', $candidate['errors']));
        return ['candidate_json' => (string) $candidate['candidate_json'], 'provider' => (string) $candidate['provider'], 'model' => (string) $candidate['model']];
    },
);
echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
exit($result['ok'] ? 0 : 2);
