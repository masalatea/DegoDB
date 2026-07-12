<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/app/schema_proposal_task_packet.php';

$root = dirname(__DIR__, 2);
$sample = $root . '/sample/tutorials/sample19-json-first-content-model-demo';
$packet = app_schema_proposal_sample19_task_packet(
    (string) file_get_contents($sample . '/proposal/source/article.json'),
    (string) file_get_contents($sample . '/golden/canonical-schema-snapshot.json'),
    (string) file_get_contents($sample . '/proposal/prompt/schema-proposal-v1-shape.json'),
);
$taskRoot = $argv[1] ?? ($root . '/work/ai-tasks/' . $packet['task']['task_id']);
app_schema_proposal_task_packet_write($packet, $taskRoot);
echo json_encode(['ok' => true, 'task_id' => $packet['task']['task_id'], 'task_root' => $taskRoot, 'state' => 'pending_user_confirmation', 'ai_executed' => false], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
