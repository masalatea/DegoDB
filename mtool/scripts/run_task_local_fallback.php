<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/task_packet_local_fallback.php';

$options = getopt('', ['task:', 'candidate-json:', 'execute-local-fallback']);
$taskPath = (string) ($options['task'] ?? '');
$candidateJsonPath = (string) ($options['candidate-json'] ?? '');

if (!array_key_exists('execute-local-fallback', $options) || $taskPath === '' || $candidateJsonPath === '') {
    fwrite(STDERR, "This optional local fallback never auto-runs.\nUsage: php mtool/scripts/run_task_local_fallback.php --task=<task.json> --candidate-json=<candidate.json> --execute-local-fallback\n");
    exit(64);
}
if (!is_file($candidateJsonPath)) {
    fwrite(STDERR, "candidate_json_required\n");
    exit(64);
}

$candidateReal = realpath($candidateJsonPath);
if (!is_string($candidateReal)) {
    fwrite(STDERR, "candidate_json_required\n");
    exit(64);
}
$result = app_task_packet_local_fallback_run(
    $taskPath,
    static function (array $task, array $inputs) use ($candidateReal): array {
        return [
            'candidate_json' => (string) file_get_contents($candidateReal),
            'provider' => 'local-candidate-file',
            'model' => basename($candidateReal),
        ];
    },
);

echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
exit($result['ok'] ? 0 : 2);
