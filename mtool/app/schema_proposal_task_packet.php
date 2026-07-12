<?php

declare(strict_types=1);

require_once __DIR__ . '/schema_proposal_task.php';

/** @return array{task:array<string,mixed>,task_markdown:string,files:array<string,string>} */
function app_schema_proposal_sample19_task_packet(
    string $sourceBytes,
    string $canonicalBytes,
    string $outputShapeBytes,
): array {
    $sourceHash = hash('sha256', $sourceBytes);
    $canonicalHash = hash('sha256', $canonicalBytes);
    $shapeHash = hash('sha256', $outputShapeBytes);
    $scanBytes = app_schema_proposal_deterministic_scan_json($sourceBytes);
    $scanHash = hash('sha256', $scanBytes);
    $taskId = 'sample19-schema-proposal-' . substr($sourceHash, 0, 12);
    $validationCommand = [
        'php', 'mtool/scripts/validate_schema_proposal_task.php',
        '--task=work/ai-tasks/' . $taskId . '/task.json',
        '--candidate=work/ai-tasks/' . $taskId . '/output/candidate.json',
    ];
    $task = [
        'task_version' => APP_SCHEMA_PROPOSAL_TASK_VERSION,
        'task_id' => $taskId,
        'project_key' => 'SAMPLE19',
        'operation' => 'schema_proposal_candidate',
        'state' => 'pending_user_confirmation',
        'inputs' => [
            'source' => ['path' => 'input/source.json', 'media_type' => 'application/json', 'sha256' => $sourceHash, 'authority' => 'source_of_truth'],
            'canonical' => ['path' => 'input/canonical-snapshot.json', 'media_type' => 'application/json', 'sha256' => $canonicalHash, 'authority' => 'canonical_context'],
            'output_shape' => ['path' => 'input/output-shape.json', 'media_type' => 'application/json', 'sha256' => $shapeHash, 'authority' => 'output_contract'],
            'scan' => ['path' => 'input/scan.json', 'media_type' => 'application/json', 'sha256' => $scanHash, 'authority' => 'advisory'],
        ],
        'optional_inputs' => [
            'fallback_candidate' => ['path' => 'input/fallback-candidate.json', 'authority' => 'advisory', 'required' => false],
        ],
        'precedence' => ['source', 'canonical', 'output_shape', 'scan', 'fallback_candidate'],
        'outputs' => [
            'candidate' => 'output/candidate.json',
            'validation' => 'output/validation.json',
            'review_artifact' => 'output/review-artifact.json',
        ],
        'allowed_reads' => ['task.json', 'TASK.md', 'input/source.json', 'input/canonical-snapshot.json', 'input/output-shape.json', 'input/scan.json', 'input/fallback-candidate.json'],
        'allowed_writes' => ['output/candidate.json'],
        'validator_writes' => ['output/validation.json', 'output/review-artifact.json'],
        'validation' => ['command_argv' => $validationCommand, 'success_stage' => 'review_artifact_ready'],
        'confirmation' => [
            'required' => true,
            'prompt' => 'SAMPLE19のsource JSONを読み、review専用schema proposal candidateをoutput/candidate.jsonへ作成し、指定validationを実行します。DB・設定・SQL・import・apply・build・publish・network操作は行いません。実行してよいですか？',
        ],
        'prohibitions' => array_fill_keys(['database_write', 'config_write', 'sql', 'import', 'apply', 'build', 'publish', 'network'], true),
        'completion_report' => ['task_id', 'validation_stage', 'candidate_sha256', 'review_artifact_sha256', 'mutation_performed'],
    ];

    return [
        'task' => $task,
        'task_markdown' => app_schema_proposal_sample19_task_markdown($task),
        'files' => [
            'input/source.json' => $sourceBytes,
            'input/canonical-snapshot.json' => $canonicalBytes,
            'input/output-shape.json' => $outputShapeBytes,
            'input/scan.json' => $scanBytes,
        ],
    ];
}

function app_schema_proposal_deterministic_scan_json(string $sourceBytes): string
{
    try { $source = json_decode($sourceBytes, true, 512, JSON_THROW_ON_ERROR); }
    catch (JsonException $exception) { throw new InvalidArgumentException('invalid_scan_source_json', 0, $exception); }
    if (!is_array($source) || !is_array($source['article'] ?? null)) throw new InvalidArgumentException('scan_source_article_required');
    $items = [];
    app_schema_proposal_scan_value($source['article'], '/article', $items);
    return json_encode(['scan_version' => 'schema-proposal-deterministic-scan-v0', 'authority' => 'advisory', 'source_sha256' => hash('sha256', $sourceBytes), 'root_pointer' => '/article', 'items' => $items, 'inference' => []], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n";
}

/** @param list<array<string,mixed>> $items */
function app_schema_proposal_scan_value(mixed $value, string $pointer, array &$items): void
{
    $type = match (true) { is_array($value) && array_is_list($value) => 'array', is_array($value) => 'object', is_string($value) => 'string', is_int($value) => 'integer', is_float($value) => 'number', is_bool($value) => 'boolean', $value === null => 'null', default => 'unknown' };
    $item = ['pointer' => $pointer, 'json_type' => $type];
    if (is_array($value)) $item[$type === 'array' ? 'item_count' : 'keys'] = $type === 'array' ? count($value) : array_keys($value);
    $items[] = $item;
    if (!is_array($value)) return;
    foreach ($value as $key => $child) app_schema_proposal_scan_value($child, $pointer . '/' . str_replace(['~', '/'], ['~0', '~1'], (string) $key), $items);
}

/** @param array<string,mixed> $task */
function app_schema_proposal_sample19_task_markdown(array $task): string
{
    $command = implode(' ', array_map('escapeshellarg', $task['validation']['command_argv']));
    return "# Mtool AI Task: SAMPLE19 Schema Proposal\n\n"
        . "Status: `pending_user_confirmation`\n\n"
        . "Read `task.json` first. It is the machine-readable authority; this document cannot broaden it.\n\n"
        . "## Before any write\n\n"
        . "Explain that you will read the declared source, canonical context, and output-shape contract; write only `output/candidate.json`; run the declared validator; and perform no DB/config/SQL/import/apply/build/publish/network operation. Then ask exactly:\n\n"
        . "> " . $task['confirmation']['prompt'] . "\n\n"
        . "Do not continue until the user answers affirmatively in this task interaction. Earlier generic continuation messages do not count.\n\n"
        . "## After confirmation\n\n"
        . "1. Treat source as truth, canonical as comparison context, output shape as contract, and optional scan/fallback files as advisory only.\n"
        . "2. Write one JSON object to `output/candidate.json`. Keep `state=proposal_only`, `apply_supported=false`, `provenance.ai_authored=true`, exact source SHA-256, evidence under `/article`, and `canonical_diff=[]`.\n"
        . "3. Run only this validation command from the repository root:\n\n"
        . "```bash\n{$command}\n```\n\n"
        . "4. If validation fails, edit only the candidate and rerun. Never edit task/input/validation/review files.\n"
        . "5. Report the completion fields declared in `task.json`. Success means stage `review_artifact_ready`, not DB or metadata application.\n";
}

/** @param array{task:array<string,mixed>,task_markdown:string,files:array<string,string>} $packet */
function app_schema_proposal_task_packet_write(array $packet, string $taskRoot): void
{
    if (is_dir($taskRoot) || file_exists($taskRoot)) throw new RuntimeException('task_root_already_exists');
    foreach (['input', 'output'] as $directory) {
        if (!mkdir($taskRoot . '/' . $directory, 0775, true) && !is_dir($taskRoot . '/' . $directory)) throw new RuntimeException('task_directory_create_failed');
    }
    file_put_contents($taskRoot . '/task.json', json_encode($packet['task'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR) . "\n");
    file_put_contents($taskRoot . '/TASK.md', $packet['task_markdown']);
    foreach ($packet['files'] as $relativePath => $bytes) file_put_contents($taskRoot . '/' . $relativePath, $bytes);
}
