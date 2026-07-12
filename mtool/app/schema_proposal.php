<?php

declare(strict_types=1);

const APP_SCHEMA_PROPOSAL_VERSION = 'schema-proposal-v0';

/** @return array{ok:bool,proposal:array<string,mixed>,errors:list<string>} */
function app_schema_proposal_decode(string $json): array
{
    try {
        $proposal = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $exception) {
        return ['ok' => false, 'proposal' => [], 'errors' => ['invalid_json: ' . $exception->getMessage()]];
    }
    if (!is_array($proposal)) {
        return ['ok' => false, 'proposal' => [], 'errors' => ['proposal_must_be_object']];
    }

    $validation = app_schema_proposal_validate($proposal);
    return ['ok' => $validation['ok'], 'proposal' => $proposal, 'errors' => $validation['errors']];
}

/** @param array<string,mixed> $proposal @return array{ok:bool,errors:list<string>,warnings:list<string>} */
function app_schema_proposal_validate(array $proposal): array
{
    $errors = [];
    $warnings = [];
    if (($proposal['proposal_version'] ?? '') !== APP_SCHEMA_PROPOSAL_VERSION) {
        $errors[] = 'unsupported_proposal_version';
    }
    foreach (['proposal_id', 'project_key', 'created_at'] as $key) {
        if (trim((string) ($proposal[$key] ?? '')) === '') {
            $errors[] = 'missing_' . $key;
        }
    }
    if (($proposal['state'] ?? '') !== 'proposal_only') {
        $errors[] = 'state_must_be_proposal_only';
    }
    if (($proposal['apply_supported'] ?? null) !== false) {
        $errors[] = 'apply_supported_must_be_false';
    }

    $source = is_array($proposal['source'] ?? null) ? $proposal['source'] : [];
    if (preg_match('/^[a-f0-9]{64}$/', (string) ($source['sha256'] ?? '')) !== 1) {
        $errors[] = 'invalid_source_sha256';
    }
    $sourceRoot = (string) ($source['root_pointer'] ?? '');
    if (!app_schema_proposal_json_pointer_is_valid($sourceRoot)) {
        $errors[] = 'invalid_source_root_pointer';
    }
    $provenance = is_array($proposal['provenance'] ?? null) ? $proposal['provenance'] : [];
    if (trim((string) ($provenance['kind'] ?? '')) === '') {
        $errors[] = 'missing_provenance_kind';
    }

    $entities = is_array($proposal['entities'] ?? null) ? $proposal['entities'] : [];
    if ($entities === []) {
        $errors[] = 'entities_required';
    }
    $entityKeys = [];
    $fieldKeysByEntity = [];
    $dataClasses = array_values(array_filter(
        is_array($proposal['degodb_targets']['data_classes'] ?? null) ? $proposal['degodb_targets']['data_classes'] : [],
        'is_string',
    ));
    foreach ($entities as $entityIndex => $entity) {
        if (!is_array($entity)) {
            $errors[] = 'invalid_entity:' . $entityIndex;
            continue;
        }
        $entityKey = trim((string) ($entity['entity_key'] ?? ''));
        if ($entityKey === '' || isset($entityKeys[$entityKey])) {
            $errors[] = ($entityKey === '' ? 'missing' : 'duplicate') . '_entity_key:' . $entityKey;
            continue;
        }
        $entityKeys[$entityKey] = true;
        app_schema_proposal_validate_evidence($entity['evidence'] ?? null, $sourceRoot, 'entity:' . $entityKey, $errors);
        $fieldKeysByEntity[$entityKey] = [];
        foreach (is_array($entity['fields'] ?? null) ? $entity['fields'] : [] as $fieldIndex => $field) {
            if (!is_array($field)) {
                $errors[] = 'invalid_field:' . $entityKey . ':' . $fieldIndex;
                continue;
            }
            $fieldKey = trim((string) ($field['field_key'] ?? ''));
            if ($fieldKey === '' || isset($fieldKeysByEntity[$entityKey][$fieldKey])) {
                $errors[] = ($fieldKey === '' ? 'missing' : 'duplicate') . '_field_key:' . $entityKey . ':' . $fieldKey;
                continue;
            }
            $fieldKeysByEntity[$entityKey][$fieldKey] = true;
            app_schema_proposal_validate_evidence($field['evidence'] ?? null, $sourceRoot, 'field:' . $entityKey . ':' . $fieldKey, $errors);
        }
        $keyKeys = [];
        foreach (is_array($entity['keys'] ?? null) ? $entity['keys'] : [] as $keyIndex => $key) {
            $keyKey = is_array($key) ? trim((string) ($key['key_key'] ?? '')) : '';
            if ($keyKey === '' || isset($keyKeys[$keyKey])) {
                $errors[] = ($keyKey === '' ? 'missing' : 'duplicate') . '_key_key:' . $entityKey . ':' . $keyKey;
                continue;
            }
            $keyKeys[$keyKey] = true;
            foreach (is_array($key['fields'] ?? null) ? $key['fields'] : [] as $keyField) {
                if (!isset($fieldKeysByEntity[$entityKey][(string) $keyField])) {
                    $errors[] = 'unknown_key_field:' . $entityKey . ':' . $keyKey . ':' . (string) $keyField;
                }
            }
        }
    }

    $relationshipKeys = [];
    foreach (is_array($proposal['relationships'] ?? null) ? $proposal['relationships'] : [] as $relationshipIndex => $relationship) {
        if (!is_array($relationship)) {
            $errors[] = 'invalid_relationship:' . $relationshipIndex;
            continue;
        }
        $relationshipKey = trim((string) ($relationship['relationship_key'] ?? ''));
        if ($relationshipKey === '' || isset($relationshipKeys[$relationshipKey])) {
            $errors[] = ($relationshipKey === '' ? 'missing' : 'duplicate') . '_relationship_key:' . $relationshipKey;
            continue;
        }
        $relationshipKeys[$relationshipKey] = true;
        $fromEntity = (string) ($relationship['from_entity'] ?? '');
        $toEntity = (string) ($relationship['to_entity'] ?? '');
        $fromField = (string) ($relationship['from_field'] ?? '');
        $toField = (string) ($relationship['to_field'] ?? '');
        if (!isset($entityKeys[$fromEntity], $entityKeys[$toEntity])) {
            $errors[] = 'unknown_relationship_entity:' . (string) ($relationship['relationship_key'] ?? $relationshipIndex);
        } elseif (!isset($fieldKeysByEntity[$fromEntity][$fromField], $fieldKeysByEntity[$toEntity][$toField])) {
            $errors[] = 'unknown_relationship_field:' . (string) ($relationship['relationship_key'] ?? $relationshipIndex);
        }
        app_schema_proposal_validate_evidence($relationship['evidence'] ?? null, $sourceRoot, 'relationship:' . $relationshipIndex, $errors);
    }

    foreach (is_array($proposal['lifecycle'] ?? null) ? $proposal['lifecycle'] : [] as $lifecycleIndex => $lifecycle) {
        if (!is_array($lifecycle) || !isset($entityKeys[(string) ($lifecycle['entity_key'] ?? '')])) {
            $errors[] = 'unknown_lifecycle_entity:' . $lifecycleIndex;
            continue;
        }
        app_schema_proposal_validate_evidence($lifecycle['evidence'] ?? null, $sourceRoot, 'lifecycle:' . $lifecycleIndex, $errors);
    }

    foreach (is_array($proposal['degodb_targets']['db_access'] ?? null) ? $proposal['degodb_targets']['db_access'] : [] as $targetIndex => $target) {
        if (!is_array($target)) {
            $errors[] = 'invalid_db_access_target:' . $targetIndex;
            continue;
        }
        if (!isset($entityKeys[(string) ($target['entity_key'] ?? '')])) {
            $errors[] = 'unknown_db_access_entity:' . (string) ($target['function_key'] ?? $targetIndex);
        }
        if (!in_array((string) ($target['output_data_class'] ?? ''), $dataClasses, true)) {
            $errors[] = 'unknown_db_access_data_class:' . (string) ($target['function_key'] ?? $targetIndex);
        }
    }

    $diffCategories = ['add', 'change', 'remove', 'unchanged', 'conflict'];
    foreach (is_array($proposal['canonical_diff'] ?? null) ? $proposal['canonical_diff'] : [] as $diffIndex => $diff) {
        if (!is_array($diff) || !in_array((string) ($diff['category'] ?? ''), $diffCategories, true)) {
            $errors[] = 'invalid_diff_category:' . $diffIndex;
            continue;
        }
        app_schema_proposal_validate_evidence($diff['evidence'] ?? null, $sourceRoot, 'diff:' . $diffIndex, $errors);
    }

    return ['ok' => $errors === [], 'errors' => array_values(array_unique($errors)), 'warnings' => $warnings];
}

function app_schema_proposal_json_pointer_is_valid(string $pointer): bool
{
    return $pointer !== '' && preg_match('/^(?:\/(?:[^~\/]|~[01])*)+$/', $pointer) === 1;
}

/** @param mixed $evidence @param list<string> $errors */
function app_schema_proposal_validate_evidence(mixed $evidence, string $sourceRoot, string $context, array &$errors): void
{
    if (!is_array($evidence) || $evidence === []) {
        $errors[] = 'missing_evidence:' . $context;
        return;
    }
    foreach ($evidence as $index => $item) {
        $pointer = is_array($item) ? (string) ($item['pointer'] ?? '') : '';
        if (!app_schema_proposal_json_pointer_is_valid($pointer)) {
            $errors[] = 'invalid_evidence_pointer:' . $context . ':' . $index;
            continue;
        }
        if ($sourceRoot !== '' && $pointer !== $sourceRoot && !str_starts_with($pointer, $sourceRoot . '/')) {
            $errors[] = 'evidence_outside_source_root:' . $context . ':' . $index;
        }
    }
}

/** @param array<string,mixed> $proposal */
function app_schema_proposal_markdown(array $proposal): string
{
    $validation = app_schema_proposal_validate($proposal);
    if (!$validation['ok']) {
        throw new InvalidArgumentException('Invalid schema proposal: ' . implode(', ', $validation['errors']));
    }
    $lines = [
        '# Schema Proposal: ' . app_schema_proposal_md((string) $proposal['proposal_id']),
        '',
        '> Proposal only. Apply is not supported.',
        '',
        '- Project: `' . app_schema_proposal_md((string) $proposal['project_key']) . '`',
        '- Version: `' . APP_SCHEMA_PROPOSAL_VERSION . '`',
        '- Source SHA-256: `' . app_schema_proposal_md((string) $proposal['source']['sha256']) . '`',
        '- Provenance: `' . app_schema_proposal_md((string) $proposal['provenance']['kind']) . '`',
        '',
        '## Entity Candidates',
        '',
        '| Entity | Purpose | Fields |',
        '| --- | --- | ---: |',
    ];
    foreach ($proposal['entities'] as $entity) {
        $lines[] = '| `' . app_schema_proposal_md((string) $entity['entity_key']) . '` | '
            . app_schema_proposal_md((string) ($entity['purpose'] ?? '')) . ' | '
            . count(is_array($entity['fields'] ?? null) ? $entity['fields'] : []) . ' |';
    }
    $lines = array_merge($lines, ['', '## Canonical Diff', '', '| Category | Kind | Key | Review note |', '| --- | --- | --- | --- |']);
    foreach ($proposal['canonical_diff'] as $diff) {
        $lines[] = '| `' . app_schema_proposal_md((string) $diff['category']) . '` | '
            . app_schema_proposal_md((string) $diff['object_kind']) . ' | `'
            . app_schema_proposal_md((string) $diff['object_key']) . '` | '
            . app_schema_proposal_md((string) ($diff['review_note'] ?? '')) . ' |';
    }
    $lines = array_merge($lines, ['', '## Blocking Questions', '']);
    foreach ($proposal['blocking_questions'] as $question) {
        $lines[] = '- **' . app_schema_proposal_md((string) $question['question_key']) . '**: '
            . app_schema_proposal_md((string) $question['question']);
    }
    $lines = array_merge($lines, ['', '## Non-Blocking Assumptions', '']);
    foreach ($proposal['non_blocking_assumptions'] as $assumption) {
        $lines[] = '- **' . app_schema_proposal_md((string) $assumption['assumption_key']) . '**: '
            . app_schema_proposal_md((string) $assumption['assumption']);
    }

    return implode("\n", $lines) . "\n";
}

function app_schema_proposal_md(string $value): string
{
    return str_replace(["\r", "\n", '|', '`'], [' ', ' ', '\\|', '\\`'], trim($value));
}

/**
 * @param array<string,mixed> $proposal
 * @param array<string,mixed> $snapshot
 * @return array{ok:bool,diff:list<array<string,mixed>>,errors:list<string>}
 */
function app_schema_proposal_derive_canonical_diff(array $proposal, array $snapshot): array
{
    $proposalValidation = app_schema_proposal_validate($proposal);
    if (!$proposalValidation['ok']) {
        return ['ok' => false, 'diff' => [], 'errors' => $proposalValidation['errors']];
    }
    $snapshotErrors = app_schema_proposal_validate_canonical_snapshot($snapshot, (string) $proposal['project_key']);
    if ($snapshotErrors !== []) {
        return ['ok' => false, 'diff' => [], 'errors' => $snapshotErrors];
    }

    $proposalEntities = [];
    foreach ($proposal['entities'] as $entity) {
        $entityKey = (string) $entity['entity_key'];
        $proposalEntities[$entityKey] = [
            'signature' => app_schema_proposal_entity_signature($entity, $proposal['relationships']),
            'evidence' => $entity['evidence'],
        ];
    }
    $canonicalEntities = [];
    foreach ($snapshot['entities'] as $entity) {
        $canonicalEntities[(string) $entity['entity_key']] = [
            'signature' => app_schema_proposal_snapshot_entity_signature($entity),
            'conflict' => ($entity['conflict'] ?? false) === true,
        ];
    }

    $orderedKeys = array_keys($proposalEntities);
    foreach (array_keys($canonicalEntities) as $entityKey) {
        if (!in_array($entityKey, $orderedKeys, true)) {
            $orderedKeys[] = $entityKey;
        }
    }
    $diff = [];
    foreach ($orderedKeys as $entityKey) {
        $proposalItem = $proposalEntities[$entityKey] ?? null;
        $canonicalItem = $canonicalEntities[$entityKey] ?? null;
        if ($proposalItem === null) {
            $category = 'remove';
        } elseif ($canonicalItem === null) {
            $category = 'add';
        } elseif ($canonicalItem['conflict']) {
            $category = 'conflict';
        } else {
            $category = app_schema_proposal_signature_equals(
                $proposalItem['signature'],
                $canonicalItem['signature'],
            ) ? 'unchanged' : 'change';
        }
        $diff[] = [
            'category' => $category,
            'object_kind' => 'entity',
            'object_key' => $entityKey,
            'proposal_value' => $proposalItem['signature'] ?? null,
            'canonical_value' => $canonicalItem['signature'] ?? null,
            'evidence' => $proposalItem['evidence'] ?? [],
            'review_note' => match ($category) {
                'unchanged' => 'Derived proposal and canonical entity signatures match.',
                'add' => 'Proposal entity does not exist in the canonical snapshot.',
                'remove' => 'Canonical entity is absent from the proposal.',
                'conflict' => 'Canonical snapshot reports an unresolved entity conflict.',
                default => 'Derived proposal and canonical entity signatures differ.',
            },
        ];
    }

    return ['ok' => true, 'diff' => $diff, 'errors' => []];
}

/** @param array<string,mixed> $proposal @param array<string,mixed> $snapshot @return array{ok:bool,errors:list<string>,derived_diff:list<array<string,mixed>>} */
function app_schema_proposal_verify_declared_diff(array $proposal, array $snapshot): array
{
    $derived = app_schema_proposal_derive_canonical_diff($proposal, $snapshot);
    if (!$derived['ok']) {
        return ['ok' => false, 'errors' => $derived['errors'], 'derived_diff' => []];
    }
    $declared = is_array($proposal['canonical_diff'] ?? null) ? $proposal['canonical_diff'] : [];
    if ($declared !== $derived['diff']) {
        return ['ok' => false, 'errors' => ['declared_canonical_diff_mismatch'], 'derived_diff' => $derived['diff']];
    }

    return ['ok' => true, 'errors' => [], 'derived_diff' => $derived['diff']];
}

/** @param array<string,mixed> $snapshot @return list<string> */
function app_schema_proposal_validate_canonical_snapshot(array $snapshot, string $projectKey): array
{
    $errors = [];
    if (($snapshot['snapshot_version'] ?? '') !== 'canonical-schema-snapshot-v0') {
        $errors[] = 'unsupported_canonical_snapshot_version';
    }
    if (($snapshot['project_key'] ?? '') !== $projectKey) {
        $errors[] = 'canonical_snapshot_project_mismatch';
    }
    $seen = [];
    foreach (is_array($snapshot['entities'] ?? null) ? $snapshot['entities'] : [] as $index => $entity) {
        $entityKey = is_array($entity) ? trim((string) ($entity['entity_key'] ?? '')) : '';
        if ($entityKey === '') {
            $errors[] = 'missing_canonical_entity_key:' . $index;
            continue;
        }
        if (isset($seen[$entityKey])) {
            $errors[] = 'duplicate_canonical_entity_key:' . $entityKey;
            continue;
        }
        $seen[$entityKey] = true;
        foreach (['field_keys', 'key_keys', 'relationship_keys'] as $listKey) {
            if (!is_array($entity[$listKey] ?? null)) {
                $errors[] = 'invalid_canonical_entity_' . $listKey . ':' . $entityKey;
            }
        }
    }
    if ($seen === []) {
        $errors[] = 'canonical_entities_required';
    }
    return array_values(array_unique($errors));
}

/** @param array<string,mixed> $entity @param list<array<string,mixed>> $relationships @return array<string,list<string>> */
function app_schema_proposal_entity_signature(array $entity, array $relationships): array
{
    $entityKey = (string) $entity['entity_key'];
    $relationshipKeys = [];
    foreach ($relationships as $relationship) {
        if (($relationship['from_entity'] ?? '') === $entityKey || ($relationship['to_entity'] ?? '') === $entityKey) {
            $relationshipKeys[] = (string) ($relationship['relationship_key'] ?? '');
        }
    }
    return [
        'field_keys' => array_values(array_map(static fn (array $field): string => (string) ($field['field_key'] ?? ''), $entity['fields'])),
        'key_keys' => array_values(array_map(static fn (array $key): string => (string) ($key['key_key'] ?? ''), $entity['keys'])),
        'relationship_keys' => $relationshipKeys,
    ];
}

/** @param array<string,mixed> $entity @return array<string,list<string>> */
function app_schema_proposal_snapshot_entity_signature(array $entity): array
{
    return [
        'field_keys' => array_values($entity['field_keys']),
        'key_keys' => array_values($entity['key_keys']),
        'relationship_keys' => array_values($entity['relationship_keys']),
    ];
}

/** @param array<string,list<string>> $left @param array<string,list<string>> $right */
function app_schema_proposal_signature_equals(array $left, array $right): bool
{
    foreach (['field_keys', 'key_keys', 'relationship_keys'] as $key) {
        $leftValues = $left[$key];
        $rightValues = $right[$key];
        sort($leftValues);
        sort($rightValues);
        if ($leftValues !== $rightValues) {
            return false;
        }
    }
    return true;
}
