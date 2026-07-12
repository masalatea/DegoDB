<?php

declare(strict_types=1);

const APP_SCHEMA_PROPOSAL_REQUEST_ENVELOPE_VERSION = 'schema-proposal-request-v0';
const APP_SCHEMA_PROPOSAL_PROMPT_VERSION = 'sample19-schema-proposal-v1';

/** @return array{ok:bool,envelope:array<string,mixed>,errors:list<string>} */
function app_schema_proposal_request_build(string $templateBytes, string $shapeBytes, string $sourceBytes, string $canonicalBytes): array
{
    $errors = [];
    foreach (['{{OUTPUT_SHAPE_JSON}}', '{{SOURCE_SHA256}}', '{{SOURCE_JSON}}', '{{CANONICAL_SHA256}}', '{{CANONICAL_JSON}}'] as $placeholder) {
        if (substr_count($templateBytes, $placeholder) < 1) {
            $errors[] = 'missing_prompt_placeholder:' . $placeholder;
        }
    }

    $shape = app_schema_proposal_request_decode_object($shapeBytes, 'shape', $errors);
    $source = app_schema_proposal_request_decode_object($sourceBytes, 'source', $errors);
    $canonical = app_schema_proposal_request_decode_object($canonicalBytes, 'canonical', $errors);
    if ($source !== [] && !is_array($source['article'] ?? null)) {
        $errors[] = 'source_root_article_required';
    }
    if ($shape !== []) {
        $errors = array_merge($errors, app_schema_proposal_request_validate_shape($shape));
    }
    if ($canonical !== [] && (($canonical['snapshot_version'] ?? '') !== 'canonical-schema-snapshot-v0'
        || ($canonical['project_key'] ?? '') !== 'SAMPLE19')) {
        $errors[] = 'unexpected_canonical_snapshot';
    }
    if ($errors !== []) {
        return ['ok' => false, 'envelope' => [], 'errors' => array_values(array_unique($errors))];
    }

    $sourceHash = hash('sha256', $sourceBytes);
    $canonicalHash = hash('sha256', $canonicalBytes);
    $prompt = str_replace(
        ['{{OUTPUT_SHAPE_JSON}}', '{{SOURCE_SHA256}}', '{{SOURCE_JSON}}', '{{CANONICAL_SHA256}}', '{{CANONICAL_JSON}}'],
        [$shapeBytes, $sourceHash, $sourceBytes, $canonicalHash, $canonicalBytes],
        $templateBytes,
    );

    return [
        'ok' => true,
        'envelope' => [
            'envelope_version' => APP_SCHEMA_PROPOSAL_REQUEST_ENVELOPE_VERSION,
            'project_key' => 'SAMPLE19',
            'proposal_version' => 'schema-proposal-v0',
            'prompt' => [
                'template_version' => APP_SCHEMA_PROPOSAL_PROMPT_VERSION,
                'template_sha256' => hash('sha256', $templateBytes),
                'final_sha256' => hash('sha256', $prompt),
                'content' => $prompt,
                'output_shape_sha256' => hash('sha256', $shapeBytes),
            ],
            'source' => [
                'logical_filename' => 'article.json',
                'media_type' => 'application/json',
                'root_pointer' => '/article',
                'byte_length' => strlen($sourceBytes),
                'sha256' => $sourceHash,
            ],
            'canonical_context' => [
                'logical_filename' => 'canonical-schema-snapshot.json',
                'media_type' => 'application/json',
                'byte_length' => strlen($canonicalBytes),
                'sha256' => $canonicalHash,
            ],
            'execution' => [
                'network_allowed' => false,
                'credential_access_allowed' => false,
                'persistence_allowed' => false,
                'mutation_allowed' => false,
                'apply_supported' => false,
            ],
        ],
        'errors' => [],
    ];
}

/** @param array<string,mixed> $shape @return list<string> */
function app_schema_proposal_request_validate_shape(array $shape): array
{
    $requiredPaths = [
        ['entities', 0, 'evidence', 0, 'pointer'],
        ['entities', 0, 'fields', 0, 'field_key'],
        ['entities', 0, 'fields', 0, 'evidence', 0, 'pointer'],
        ['entities', 0, 'keys', 0, 'key_key'],
        ['relationships', 0, 'from_entity'],
        ['relationships', 0, 'from_field'],
        ['relationships', 0, 'to_entity'],
        ['relationships', 0, 'to_field'],
        ['relationships', 0, 'evidence', 0, 'pointer'],
        ['lifecycle', 0, 'evidence', 0, 'pointer'],
        ['canonical_diff', 0, 'object_kind'],
        ['canonical_diff', 0, 'object_key'],
        ['canonical_diff', 0, 'proposal_value', 'field_keys'],
        ['canonical_diff', 0, 'canonical_value', 'field_keys'],
        ['canonical_diff', 0, 'evidence', 0, 'pointer'],
    ];
    $errors = [];
    foreach ($requiredPaths as $path) {
        $value = $shape;
        foreach ($path as $key) {
            if (!is_array($value) || !array_key_exists($key, $value)) {
                $errors[] = 'incomplete_output_shape:' . implode('.', $path);
                continue 2;
            }
            $value = $value[$key];
        }
    }
    return $errors;
}

/** @param list<string> $errors @return array<string,mixed> */
function app_schema_proposal_request_decode_object(string $bytes, string $label, array &$errors): array
{
    try {
        $value = json_decode($bytes, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        $errors[] = 'invalid_' . $label . '_json';
        return [];
    }
    if (!is_array($value) || array_is_list($value)) {
        $errors[] = $label . '_must_be_object';
        return [];
    }
    return $value;
}
