<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/schema_proposal_task.php';

$options = getopt('', ['task:', 'candidate:']);
$taskPath = (string) ($options['task'] ?? '');
$candidatePath = (string) ($options['candidate'] ?? '');
if ($taskPath === '' || $candidatePath === '' || !is_file($taskPath)) {
    fwrite(STDERR, "usage: php mtool/scripts/validate_schema_proposal_task.php --task=<task.json> --candidate=<candidate.json>\n");
    exit(64);
}
$taskRoot = realpath(dirname($taskPath));
$taskReal = realpath($taskPath);
$candidateReal = realpath($candidatePath);
if (!is_string($taskRoot) || !is_string($taskReal) || !is_string($candidateReal)
    || !str_starts_with($taskReal, $taskRoot . DIRECTORY_SEPARATOR)
    || !str_starts_with($candidateReal, $taskRoot . DIRECTORY_SEPARATOR)) {
    fwrite(STDERR, "task_or_candidate_outside_task_root\n");
    exit(65);
}
try {
    $task = json_decode((string) file_get_contents($taskReal), true, 512, JSON_THROW_ON_ERROR);
} catch (JsonException) {
    fwrite(STDERR, "invalid_task_json\n");
    exit(66);
}
if (!is_array($task) || array_is_list($task)) {
    fwrite(STDERR, "task_must_be_object\n");
    exit(66);
}
$declaredCandidate = $taskRoot . DIRECTORY_SEPARATOR . (string) ($task['outputs']['candidate'] ?? '');
if (realpath($declaredCandidate) !== $candidateReal) {
    fwrite(STDERR, "candidate_path_not_declared\n");
    exit(65);
}
$sourcePath = $taskRoot . DIRECTORY_SEPARATOR . (string) ($task['inputs']['source']['path'] ?? '');
$canonicalPath = $taskRoot . DIRECTORY_SEPARATOR . (string) ($task['inputs']['canonical']['path'] ?? '');
$sourceReal = realpath($sourcePath);
$canonicalReal = realpath($canonicalPath);
if (!is_string($sourceReal) || !is_string($canonicalReal)
    || !str_starts_with($sourceReal, $taskRoot . DIRECTORY_SEPARATOR)
    || !str_starts_with($canonicalReal, $taskRoot . DIRECTORY_SEPARATOR)) {
    fwrite(STDERR, "input_path_outside_task_root\n");
    exit(65);
}
$supportErrors = [];
foreach (['output_shape', 'scan'] as $inputKey) {
    $declared = is_array($task['inputs'][$inputKey] ?? null) ? $task['inputs'][$inputKey] : [];
    $supportReal = realpath($taskRoot . DIRECTORY_SEPARATOR . (string) ($declared['path'] ?? ''));
    if (!is_string($supportReal) || !str_starts_with($supportReal, $taskRoot . DIRECTORY_SEPARATOR)) {
        $supportErrors[] = 'input_path_outside_task_root:' . $inputKey;
        continue;
    }
    if (!hash_equals((string) ($declared['sha256'] ?? ''), hash_file('sha256', $supportReal))) $supportErrors[] = 'task_' . $inputKey . '_sha256_mismatch';
}
if ($supportErrors !== []) {
    $result = app_schema_proposal_task_result(false, 'input_integrity', $supportErrors, hash_file('sha256', $candidateReal));
} else {
$result = app_schema_proposal_task_validate(
    $task,
    (string) file_get_contents($candidateReal),
    (string) file_get_contents($sourceReal),
    (string) file_get_contents($canonicalReal),
);
}
$validationPath = $taskRoot . DIRECTORY_SEPARATOR . (string) ($task['outputs']['validation'] ?? '');
$validationDir = dirname($validationPath);
if (!is_dir($validationDir)) mkdir($validationDir, 0775, true);
file_put_contents($validationPath, json_encode(array_diff_key($result, ['review_artifact_json' => true]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
if ($result['ok']) {
    $reviewPath = $taskRoot . DIRECTORY_SEPARATOR . (string) $task['outputs']['review_artifact'];
    $reviewDir = dirname($reviewPath);
    if (!is_dir($reviewDir)) mkdir($reviewDir, 0775, true);
    file_put_contents($reviewPath, $result['review_artifact_json']);
}
echo json_encode(array_diff_key($result, ['review_artifact_json' => true, 'derived_diff' => true]), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
exit($result['ok'] ? 0 : 2);
