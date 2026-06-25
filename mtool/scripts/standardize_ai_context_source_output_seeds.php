#!/usr/bin/env php
<?php

declare(strict_types=1);

/**
 * Add the standard AI-CONTEXT-MD source output definition to tutorial sample seeds.
 *
 * This is intentionally mechanical: the generated Markdown/JSON output is authored
 * by Mtool generator code, while AI only reads and reviews the result.
 */

$repoRoot = dirname(__DIR__, 2);
$paths = glob($repoRoot . '/sample/tutorials/*/seed/*source_output_seed.sql') ?: [];
sort($paths, SORT_STRING);

$changed = [];
foreach ($paths as $path) {
    $text = file_get_contents($path);
    if (!is_string($text)) {
        fwrite(STDERR, 'failed to read: ' . $path . PHP_EOL);
        exit(1);
    }

    if (str_contains($text, 'AI-CONTEXT-MD')) {
        continue;
    }

    if (
        preg_match(
            "/SET\\s+@([A-Za-z0-9_]+_project_id)\\s*=\\s*\\(.*?WHERE\\s+project_key\\s*=\\s*'([^']+)'/is",
            $text,
            $matches,
        ) !== 1
    ) {
        fwrite(STDERR, 'project variable not found: ' . $path . PHP_EOL);
        exit(1);
    }

    $projectIdVariable = $matches[1];
    $projectKey = $matches[2];
    $sampleLabel = preg_replace('/^SAMPLE0?([0-9]+)$/', 'Sample$1', $projectKey) ?? $projectKey;

    $insert = <<<SQL

INSERT INTO project_source_outputs (
    project_id,
    source_output_key,
    name,
    program_language,
    class_type,
    release_target_type,
    source_template_dir,
    source_output_dir,
    source_temp_output_dir,
    proxy_base_url,
    autoload_filename_suffix,
    source_text_char_code,
    runtime_source_relative_path,
    artifact_strategy,
    target_binding_type,
    output_archive_format,
    source_output_list_order,
    notes,
    source_of_truth
) VALUES (
    @$projectIdVariable,
    'AI-CONTEXT-MD',
    '$sampleLabel AI Context Markdown',
    'md',
    'AIContext',
    'Release',
    '',
    'work/source-outputs/$projectKey/AI-CONTEXT-MD',
    'work/staging/source-outputs/$projectKey/AI-CONTEXT-MD',
    '',
    '',
    'UTF-8',
    'mtool/ai-context-source-outputs/$projectKey/AI-CONTEXT-MD',
    'ai-context-md',
    'runtime',
    'tar.gz',
    90,
    'Generate AI-readable Markdown and JSON context from canonical project metadata. Authored by DegoDB/Mtool generator code; AI is reader/consumer only.',
    'manual'
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    program_language = VALUES(program_language),
    class_type = VALUES(class_type),
    release_target_type = VALUES(release_target_type),
    source_template_dir = VALUES(source_template_dir),
    source_output_dir = VALUES(source_output_dir),
    source_temp_output_dir = VALUES(source_temp_output_dir),
    proxy_base_url = VALUES(proxy_base_url),
    autoload_filename_suffix = VALUES(autoload_filename_suffix),
    source_text_char_code = VALUES(source_text_char_code),
    runtime_source_relative_path = VALUES(runtime_source_relative_path),
    artifact_strategy = VALUES(artifact_strategy),
    target_binding_type = VALUES(target_binding_type),
    output_archive_format = VALUES(output_archive_format),
    source_output_list_order = VALUES(source_output_list_order),
    notes = VALUES(notes),
    source_of_truth = VALUES(source_of_truth);
SQL;

    $clearVariablePattern = "/\\nSET\\s+@" . preg_quote($projectIdVariable, '/') . "\\s*=\\s*NULL;\\s*$/";
    if (preg_match($clearVariablePattern, $text) === 1) {
        $text = preg_replace($clearVariablePattern, $insert . "\nSET @$projectIdVariable = NULL;\n", $text);
        if (!is_string($text)) {
            fwrite(STDERR, 'failed to rewrite: ' . $path . PHP_EOL);
            exit(1);
        }
    } else {
        $text = rtrim($text) . "\n" . $insert . "\n";
    }

    file_put_contents($path, $text);
    $changed[] = substr($path, strlen($repoRoot) + 1);
}

echo json_encode(
    [
        'changed_count' => count($changed),
        'changed' => $changed,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES,
) . PHP_EOL;
